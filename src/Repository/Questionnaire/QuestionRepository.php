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

use Affiliation\Entity\Questionnaire\Question;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class QuestionRepository
 * @package Affiliation\Repository\Question
 */
final class QuestionRepository extends EntityRepository
{
    public function findFiltered(array $filter = []): QueryBuilder
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('q', 'c');
        $queryBuilder->from(Question::class, 'q');
        $queryBuilder->innerJoin('q.category', 'c');

        $direction = 'ASC';
        if (isset($filter['direction']) && \in_array(\strtoupper($filter['direction']), ['ASC', 'DESC'])) {
            $direction = \strtoupper($filter['direction']);
        }

        // Filter on the name
        if (\array_key_exists('search', $filter)) {
            $queryBuilder->andWhere($queryBuilder->expr()->like('q.question', ':like'));
            $queryBuilder->setParameter('like', \sprintf("%%%s%%", $filter['search']));
        }

        switch ($filter['order']) {
            case 'id':
                $queryBuilder->addOrderBy('q.id', $direction);
                break;
            case 'question':
                $queryBuilder->addOrderBy('q.question', $direction);
                break;
            case 'category':
                $queryBuilder->addOrderBy('c.category', $direction);
                break;
            default:
                $queryBuilder->addOrderBy('q.id', $direction);
        }

        return $queryBuilder;
    }

    public function hasAnswers(Question $question): bool
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('COUNT(a.id)');
        $queryBuilder->from(Question::class, 'q');
        $queryBuilder->innerJoin('q.questionnaireQuestions', 'qq');
        $queryBuilder->innerJoin('qq.answers', 'a');
        $queryBuilder->where($queryBuilder->expr()->eq('q', ':question'));
        $queryBuilder->setParameter('question', $question);

        return ((int) $queryBuilder->getQuery()->getSingleScalarResult() > 0);
    }
}
