<?php
/**
 * ITEA Office all rights reserved
 *
 * @category  Affiliation
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Service;

use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Questionnaire\Answer;
use Affiliation\Entity\Questionnaire\Phase;
use Affiliation\Entity\Questionnaire\Question;
use Affiliation\Entity\Questionnaire\Questionnaire;
use Affiliation\Entity\Questionnaire\QuestionnaireQuestion;
use Doctrine\ORM\EntityManager;

/**
 * Class QuestionnaireService
 * @package Affiliation\Service
 */
class QuestionnaireService extends AbstractService
{
    public function __construct(
        EntityManager $entityManager
    ) {
        parent::__construct($entityManager);
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
        $answer = false;
        /** @var QuestionnaireQuestion $questionnaireQuestion */
        $questionnaireQuestion = $questionnaire->getQuestionnaireQuestions()->first();
        if ($questionnaireQuestion) {
            /** @var Answer $answer */
            $answer = $questionnaireQuestion->getAnswers()->first();
        }

        // No or new answers
        if (!$answer || $answer->isEmpty()) {
            static $sortedQuestionnaireQuestions = [];
            if (!isset($sortedQuestionnaireQuestions[$questionnaire->getId()])) {
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
                $answers[] = $answer;
            }
            return $answers;
        }

        return $this->entityManager->getRepository(Answer::class)->getSorted($questionnaire, $affiliation);
    }

    public function parseCompletedPercentage(Affiliation $affiliation, Questionnaire $questionnaire = null): float
    {
        if ($questionnaire === null) {
            return 0.0;
        }
        $questionCount = \count($questionnaire->getQuestionnaireQuestions());
        $answers       = $this->entityManager->getRepository(Answer::class)
            ->getSorted($questionnaire, $affiliation);
        $answerCount   = 0;

        /** @var Answer $answer */
        foreach ($answers as $answer) {
            $value = $answer->getValue();
            if (!empty($value) || !$answer->getQuestionnaireQuestion()->getQuestion()->getRequired()) {
                $answerCount++;
            }
        }

        return ($answerCount === 0) ? 0.0 : \round((($answerCount / $questionCount) * 100));
    }

    public function getAvailableQuestionnaires(Affiliation $affiliation): array
    {
        $availableQuestionnaires = [];
        $now = new \DateTime();
        if ($now >= $affiliation->getDateCreated()) {
            /** @var Phase $startPhase */
            $startPhase = $this->entityManager->getRepository(Phase::class)->find(Phase::PHASE_PROJECT_START);
            $availableQuestionnaires += $startPhase->getQuestionnaires()->toArray();
        }
        $finalReviewPlanned = false;
        $lastProjectCalendar = $affiliation->getProject()->getProjectCalendar()->last();

        return $availableQuestionnaires;
    }
}
