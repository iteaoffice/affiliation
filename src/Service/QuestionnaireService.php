<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Service;

use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Questionnaire\Answer;
use Affiliation\Entity\Questionnaire\Phase;
use Affiliation\Entity\Questionnaire\Question;
use Affiliation\Entity\Questionnaire\Questionnaire;
use Affiliation\Entity\Questionnaire\QuestionnaireQuestion;
use Contact\Entity\Contact;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManager;
use Project\Entity\Calendar\Calendar as ProjectCalendar;
use Project\Entity\Version\Type;
use Project\Entity\Version\Version;
use Project\Service\VersionService;

use function count;
use function preg_match;
use function round;

/**
 * Class QuestionnaireService
 * @package Affiliation\Service
 */
class QuestionnaireService extends AbstractService
{
    private VersionService $versionService;

    public function __construct(
        EntityManager $entityManager,
        VersionService $versionService
    ) {
        parent::__construct($entityManager);
        $this->versionService = $versionService;
    }

    public function questionHasAnswers(Question $question): bool
    {
        return ($this->entityManager->getRepository(Question::class)->hasAnswers($question));
    }

    public function hasAnswers(Questionnaire $questionnaire): bool
    {
        return ($this->entityManager->getRepository(Questionnaire::class)->hasAnswers($questionnaire));
    }

    public function getSortedAnswers(Questionnaire $questionnaire, Affiliation $affiliation): array
    {
        $hasAnswers = false;
        /** @var QuestionnaireQuestion $questionnaireQuestion */
        $questionnaireQuestion = $questionnaire->getQuestionnaireQuestions()->first();

        //Check the first answer
        if ($questionnaireQuestion) {
            /** @var Answer $answer */
            $hasAnswers = ! $questionnaireQuestion->getAnswers()->filter(
                static function (Answer $answer) use ($affiliation) {
                    return $answer->getAffiliation() === $affiliation;
                }
            )->isEmpty();
        }



        // No answers, or not from this affiliation or new answers
        if (! $hasAnswers) {
            static $sortedQuestionnaireQuestions = [];
            if (! isset($sortedQuestionnaireQuestions[$questionnaire->getId()])) {
                $sortedQuestionnaireQuestions[$questionnaire->getId()] =
                    $this->entityManager->getRepository(QuestionnaireQuestion::class)->getSorted(
                        $questionnaire
                    );
            }
            $answers        = [];
            $answerTemplate = new Answer();
            /** @var QuestionnaireQuestion $questionnaireQuestion */
            foreach ($sortedQuestionnaireQuestions[$questionnaire->getId()] as $questionnaireQuestion) {
                $answer = clone $answerTemplate;
                $answer->setQuestionnaireQuestion($questionnaireQuestion);
                $answer->setAffiliation($affiliation);

                // Persist the $answer to be able to only flush at the end
                $this->entityManager->persist($answer);

                $answers[] = $answer;
            }

            return $answers;
        }

        return $this->entityManager->getRepository(Answer::class)->getSorted($questionnaire, $affiliation);
    }

    public function hasPendingQuestionnaires(Contact $contact): bool
    {
        foreach ($contact->getAffiliation() as $affiliation) {
            foreach ($this->getAvailableQuestionnaires($affiliation) as $questionnaire) {
                if (
                    $this->isOpen($questionnaire, $affiliation)
                    && ($this->parseCompletedPercentage($questionnaire, $affiliation) < 100)
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getAvailableQuestionnaires(Affiliation $affiliation): array
    {
        $availableQuestionnaires = [];
        $now                     = new DateTime();
        if ($now >= $affiliation->getDateCreated()) {
            /** @var Phase $startPhase */
            $startPhase              = $this->entityManager->getRepository(Phase::class)->find(Phase::PHASE_PROJECT_START);
            $availableQuestionnaires += $this->entityManager->getRepository(Questionnaire::class)->findBy([
                'phase'            => $startPhase,
                'organisationType' => $affiliation->getOrganisation()->getType(),
                'programCall'      => $affiliation->getProject()->getCall()
            ]);
        }
        /** @var ProjectCalendar $lastProjectCalendar */
        $lastProjectCalendar = $affiliation->getProject()->getProjectCalendar()->last();
        // Last calendar item is a final review
        if ($lastProjectCalendar && preg_match('/final/i', $lastProjectCalendar->getCalendar()->getCalendar())) {
            /** @var Phase $endPhase */
            $endPhase                = $this->entityManager->getRepository(Phase::class)->find(Phase::PHASE_PROJECT_END);
            $availableQuestionnaires += $this->entityManager->getRepository(Questionnaire::class)->findBy([
                'phase'            => $endPhase,
                'organisationType' => $affiliation->getOrganisation()->getType(),
                'programCall'      => $affiliation->getProject()->getCall()
            ]);
        }

        return $availableQuestionnaires;
    }

    public function isOpen(Questionnaire $questionnaire, Affiliation $affiliation): bool
    {
        $now       = new DateTime();
        $startDate = $this->getStartDate($questionnaire, $affiliation);
        $endDate   = $this->getEndDate($questionnaire, $affiliation);

        return (
            (($startDate !== null) && ($now >= $startDate))
            && (($endDate === null) || ($now <= $endDate))
        );
    }

    public function getStartDate(Questionnaire $questionnaire, Affiliation $affiliation): ?DateTime
    {
        if ($questionnaire->getPhase() !== null) {
            switch ($questionnaire->getPhase()->getId()) {
                case Phase::PHASE_PROJECT_START:
                    $poVersion = $this->versionService->findLatestVersionByType(
                        $affiliation->getProject(),
                        $this->versionService->findVersionTypeById(Type::TYPE_PO)
                    );
                    if ($poVersion instanceof Version && $poVersion->isApproved()) {
                        return $poVersion->getDateReviewed();
                    }
                    break;

                case Phase::PHASE_PROJECT_END:
                    /** @var ProjectCalendar $lastProjectCalendar */
                    $lastProjectCalendar = $affiliation->getProject()->getProjectCalendar()->last();
                    // There is a final project review. Take that as reference date for the project end
                    if ($lastProjectCalendar && preg_match('/final/i', $lastProjectCalendar->getCalendar()->getCalendar())) {
                        $startDate = clone $lastProjectCalendar->getCalendar()->getDateEnd();
                        return $startDate->sub(new DateInterval('P3M'));
                    }
            }
        }
        return null;
    }

    /*
     * Checks whether a questionnaire is open purely based on the current state of the project/affiliation.
     * Whether the user has rights (Technical Contact or Office) should be checked via the Questionnaire assertion class.
     */

    public function getEndDate(Questionnaire $questionnaire, Affiliation $affiliation): ?DateTime
    {
        if ($questionnaire->getPhase() !== null) {
            switch ($questionnaire->getPhase()->getId()) {
                case Phase::PHASE_PROJECT_START:
                    $endDate = $this->getStartDate($questionnaire, $affiliation);
                    if ($endDate !== null) {
                        $endDate = clone $endDate;
                        return $endDate->add(new DateInterval('P1Y'));
                    }
                    break;

                case Phase::PHASE_PROJECT_END:
                    /** @var ProjectCalendar $lastProjectCalendar */
                    $lastProjectCalendar = $affiliation->getProject()->getProjectCalendar()->last();
                    // There is a final project review. Take that as reference date for the project end
                    if ($lastProjectCalendar && preg_match('/final/i', $lastProjectCalendar->getCalendar()->getCalendar())) {
                        $endDate = clone $lastProjectCalendar->getCalendar()->getDateEnd();
                        return $endDate->add(new DateInterval('P1M'));
                    }
            }
        }
        return null;
    }

    public function parseCompletedPercentage(Questionnaire $questionnaire, Affiliation $affiliation): int
    {
        $questionCount = count($questionnaire->getQuestionnaireQuestions());
        if ($questionCount === 0) { // Prevent division by zero
            return 100;
        }
        $answers     = $this->entityManager->getRepository(Answer::class)
            ->getSorted($questionnaire, $affiliation);
        $answerCount = 0;

        /** @var Answer $answer */
        foreach ($answers as $answer) {
            $value = $answer->getValue();
            if (! empty($value) || ! $answer->getQuestionnaireQuestion()->getQuestion()->getRequired()) {
                $answerCount++;
            }
        }

        return ($answerCount === 0) ? 0 : (int)round((($answerCount / $questionCount) * 100));
    }

    public function copyQuestionnaire(Questionnaire $questionnaire): Questionnaire
    {
        $copy = new Questionnaire();
        $copy->setQuestionnaire($questionnaire->getQuestionnaire());
        $copy->setDescription($questionnaire->getDescription());
        $copy->setOrganisationType($questionnaire->getOrganisationType());
        $copy->setProgramCall($questionnaire->getProgramCall());
        $copy->setPhase($questionnaire->getPhase());
        /** @var QuestionnaireQuestion $questionnaireQuestion */
        foreach ($questionnaire->getQuestionnaireQuestions() as $questionnaireQuestion) {
            $newQuestionnaireQuestion = new QuestionnaireQuestion();
            $newQuestionnaireQuestion->setQuestionnaire($copy);
            $newQuestionnaireQuestion->setQuestion($questionnaireQuestion->getQuestion());
            $newQuestionnaireQuestion->setSequence($questionnaireQuestion->getSequence());
            $copy->getQuestionnaireQuestions()->add($newQuestionnaireQuestion);
        }

        return $copy;
    }
}
