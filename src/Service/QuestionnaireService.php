<?php

/**
 * ITEA Office all rights reserved
 *
 * @category  Affiliation
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2019 ITEA Office (https://itea3.org)
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
use Project\Entity\Calendar\Calendar as ProjectCalendar;
use Doctrine\ORM\EntityManager;

/**
 * Class QuestionnaireService
 * @package Affiliation\Service
 */
class QuestionnaireService extends AbstractService
{
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
        $answer = false;
        /** @var QuestionnaireQuestion $questionnaireQuestion */
        $questionnaireQuestion = $questionnaire->getQuestionnaireQuestions()->first();
        if ($questionnaireQuestion) {
            /** @var Answer $answer */
            $answer = $questionnaireQuestion->getAnswers()->first();
        }

        // No, or not from this affiliation or new answers
        if (! $answer || $answer->getAffiliation() !== $affiliation || $answer->isEmpty()) {
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

                //Persist the $answer to be able to only flush at the end
                $this->entityManager->persist($answer);

                $answers[] = $answer;
            }
            return $answers;
        }

        return $this->entityManager->getRepository(Answer::class)->getSorted($questionnaire, $affiliation);
    }

    public function parseCompletedPercentage(Questionnaire $questionnaire, Affiliation $affiliation): int
    {
        $questionCount = \count($questionnaire->getQuestionnaireQuestions());
        if ($questionCount === 0) { // Prevent division by zero
            return 100;
        }
        $answers       = $this->entityManager->getRepository(Answer::class)
            ->getSorted($questionnaire, $affiliation);
        $answerCount   = 0;

        /** @var Answer $answer */
        foreach ($answers as $answer) {
            $value = $answer->getValue();
            if (! empty($value) || ! $answer->getQuestionnaireQuestion()->getQuestion()->getRequired()) {
                $answerCount++;
            }
        }

        return ($answerCount === 0) ? 0 : (int) \round((($answerCount / $questionCount) * 100));
    }

    public function getAvailableQuestionnaires(Affiliation $affiliation): array
    {
        $availableQuestionnaires = [];
        $now                     = new \DateTime();
        if ($now >= $affiliation->getDateCreated()) {
            /** @var Phase $startPhase */
            $startPhase = $this->entityManager->getRepository(Phase::class)->find(Phase::PHASE_PROJECT_START);
            $availableQuestionnaires += $startPhase->getQuestionnaires()->toArray();
        }
        /** @var ProjectCalendar $lastProjectCalendar */
        $lastProjectCalendar = $affiliation->getProject()->getProjectCalendar()->last();
        // Last calendar item is a final review
        if ($lastProjectCalendar && \preg_match('/final/i', $lastProjectCalendar->getCalendar()->getCalendar())) {
            /** @var Phase $startPhase */
            $endPhase = $this->entityManager->getRepository(Phase::class)->find(Phase::PHASE_PROJECT_END);
            $availableQuestionnaires += $endPhase->getQuestionnaires()->toArray();
        }

        return $availableQuestionnaires;
    }

    public function getStartDate(Questionnaire $questionnaire, Affiliation $affiliation): ?\DateTime
    {
        switch ($questionnaire->getPhase()->getId()) {
            case Phase::PHASE_PROJECT_START:
                return $affiliation->getDateCreated();

            case Phase::PHASE_PROJECT_END:
                /** @var ProjectCalendar $lastProjectCalendar */
                $lastProjectCalendar = $affiliation->getProject()->getProjectCalendar()->last();
                // There is a final project review. Take that as reference date for the project end
                if ($lastProjectCalendar && \preg_match('/final/i', $lastProjectCalendar->getCalendar()->getCalendar())) {
                    $startDate = clone $lastProjectCalendar->getCalendar()->getDateEnd();
                    return $startDate->sub(new \DateInterval('P3M'));
                }
        }
        return null;
    }

    public function getEndDate(Questionnaire $questionnaire, Affiliation $affiliation): ?\DateTime
    {
        switch ($questionnaire->getPhase()->getId()) {
            case Phase::PHASE_PROJECT_START:
                $endDate = clone $affiliation->getDateCreated();
                return $endDate->add(new \DateInterval('P3M'));

            case Phase::PHASE_PROJECT_END:
                /** @var ProjectCalendar $lastProjectCalendar */
                $lastProjectCalendar = $affiliation->getProject()->getProjectCalendar()->last();
                // There is a final project review. Take that as reference date for the project end
                if ($lastProjectCalendar && \preg_match('/final/i', $lastProjectCalendar->getCalendar()->getCalendar())) {
                    $endDate = clone $lastProjectCalendar->getCalendar()->getDateEnd();
                    return $endDate->add(new \DateInterval('P1M'));
                }
        }
        return null;
    }

    /*
     * Checks whether a questionnaire is open purely based on the current state of the project/affiliation.
     * Whether the user has rights (Technical Contact or Office) should be checked via the Questionnaire assertion class.
     */
    public function isOpen(Questionnaire $questionnaire, Affiliation $affiliation): bool
    {
        $now       = new \DateTime();
        $startDate = $this->getStartDate($questionnaire, $affiliation);
        $endDate   = $this->getEndDate($questionnaire, $affiliation);
        return (
            (($startDate !== null) && ($now >= $startDate))
            && (($endDate === null) || ($now <= $endDate))
        );
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
}
