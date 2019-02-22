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

namespace Affiliation\Repository\Question;

use Affiliation\Entity\Question\Category;
use Affiliation\Entity\Question\Question;
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
        $queryBuilder->select('q');
        $queryBuilder->from(Question::class, 'q');

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
            case 'category':
                $queryBuilder->addOrderBy('q.cquestion', $direction);
                break;
            default:
                $queryBuilder->addOrderBy('q.sequence', $direction);
        }

        return $queryBuilder;
    }
}
