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

use Affiliation\Entity\Questionnaire\Question;;

use Affiliation\Entity\Questionnaire\Questionnaire;
use Doctrine\ORM\EntityManager;

/**
 * Class AffiliationQuestionService
 * @package Affiliation\Service
 */
class AffiliationQuestionService extends AbstractService
{

    public function __construct(
        EntityManager $entityManager
    ) {
        parent::__construct($entityManager);
    }

    public function hasAnswers(Question $question): bool
    {
        return ($this->entityManager->getRepository(Question::class)->hasAnswers($question));
    }

    public function getSortedAnswers(Questionnaire $questionnaire): array
    {
        return $this->entityManager->getRepository(Questionnaire::class)->getSortedAnswers($questionnaire);
    }
}
