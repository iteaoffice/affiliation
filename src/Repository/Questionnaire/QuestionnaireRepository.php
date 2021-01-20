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
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class QuestionnaireRepository
 * @package Affiliation\Repository\Questionnaire
 */
final class QuestionnaireRepository extends EntityRepository
{
    public function findFiltered(array $filter = []): QueryBuilder
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('q', 'p', 'qq');
        $queryBuilder->from(Questionnaire::class, 'q');
        $queryBuilder->innerJoin('q.phase', 'p');
        $queryBuilder->leftJoin('q.questionnaireQuestions', 'qq');

        $direction = 'ASC';
        if (isset($filter['direction']) && \in_array(\strtoupper($filter['direction']), ['ASC', 'DESC'])) {
            $direction = \strtoupper($filter['direction']);
        }

        // Filter on the name
        if (\array_key_exists('search', $filter)) {
            $queryBuilder->andWhere($queryBuilder->expr()->like('q.questionnaire', ':like'));
            $queryBuilder->setParameter('like', \sprintf("%%%s%%", $filter['search']));
        }

        switch ($filter['order']) {
            case 'id':
                $queryBuilder->addOrderBy('q.id', $direction);
                break;
            case 'questionnaire':
                $queryBuilder->addOrderBy('q.questionnaire', $direction);
                break;
            case 'phase':
                $queryBuilder->addOrderBy('p.phase', $direction);
                break;
            default:
                $queryBuilder->addOrderBy('q.id', $direction);
        }

        return $queryBuilder;
    }

    public function hasAnswers(Questionnaire $questionnaire): bool
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('COUNT(a.id)');
        $queryBuilder->from(Questionnaire::class, 'q');
        $queryBuilder->innerJoin('q.questionnaireQuestions', 'qq');
        $queryBuilder->innerJoin('qq.answers', 'a');
        $queryBuilder->where($queryBuilder->expr()->eq('q', ':questionnaire'));
        $queryBuilder->setParameter('questionnaire', $questionnaire);

        return ((int) $queryBuilder->getQuery()->getSingleScalarResult() > 0);
    }
}
