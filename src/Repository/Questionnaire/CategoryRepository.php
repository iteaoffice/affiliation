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

namespace Affiliation\Repository\Questionnaire;

use Affiliation\Entity\Questionnaire\Category;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CategoryRepository
 * @package Affiliation\Repository\Question
 */
final class CategoryRepository extends EntityRepository
{
    public function findFiltered(array $filter = []): QueryBuilder
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('c');
        $queryBuilder->from(Category::class, 'c');

        $direction = 'ASC';
        if (isset($filter['direction']) && \in_array(\strtoupper($filter['direction']), ['ASC', 'DESC'])) {
            $direction = \strtoupper($filter['direction']);
        }

        // Filter on the name
        if (\array_key_exists('search', $filter)) {
            $queryBuilder->andWhere($queryBuilder->expr()->like('c.category', ':like'));
            $queryBuilder->setParameter('like', \sprintf("%%%s%%", $filter['search']));
        }

        switch ($filter['order']) {
            case 'id':
                $queryBuilder->addOrderBy('c.id', $direction);
                break;
            case 'category':
                $queryBuilder->addOrderBy('c.category', $direction);
                break;
            default:
                $queryBuilder->addOrderBy('c.sequence', $direction);
        }

        return $queryBuilder;
    }
}
