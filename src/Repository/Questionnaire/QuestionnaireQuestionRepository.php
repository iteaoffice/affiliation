<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Repository\Questionnaire;

use Affiliation\Entity\Questionnaire\Questionnaire;
use Affiliation\Entity\Questionnaire\QuestionnaireQuestion;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Gedmo\Sortable\Entity\Repository\SortableRepository;

/**
 * Class QuestionnaireQuestionRepository
 * @package Affiliation\Repository\Questionnaire
 */
final class QuestionnaireQuestionRepository extends SortableRepository
{
    public function getSorted(Questionnaire $questionnaire): array
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('qq', 'q', 'qc');
        $queryBuilder->from(QuestionnaireQuestion::class, 'qq');
        $queryBuilder->innerJoin('qq.question', 'q');
        $queryBuilder->innerJoin('q.category', 'qc');
        $queryBuilder->where($queryBuilder->expr()->eq('qq.questionnaire', ':questionnaire'));
        $queryBuilder->andWhere($queryBuilder->expr()->eq('q.enabled', 1));
        $queryBuilder->orderBy('qc.sequence', Criteria::ASC);
        $queryBuilder->addOrderBy('qq.sequence', Criteria::ASC);

        $queryBuilder->setParameter('questionnaire', $questionnaire);

        return $queryBuilder->getQuery()->getResult();
    }
}
