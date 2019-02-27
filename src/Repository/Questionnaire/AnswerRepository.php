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

use Affiliation\Entity\Questionnaire\Answer;
use Affiliation\Entity\Questionnaire\Questionnaire;
use Affiliation\Entity\Affiliation;
use Doctrine\Common\Collections\Criteria;
use Gedmo\Sortable\Entity\Repository\SortableRepository;

/**
 * Class AnswerRepository
 * @package Affiliation\Repository\Questionnaire
 */
final class AnswerRepository extends SortableRepository
{
    public function getSorted(Questionnaire $questionnaire, Affiliation $affiliation): array
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('a', 'qq', 'q');
        $queryBuilder->from(Answer::class, 'a');
        $queryBuilder->innerJoin('a.questionnaireQuestion', 'qq');
        $queryBuilder->innerJoin('qq.question', 'q');
        $queryBuilder->innerJoin('q.category', 'c');
        $queryBuilder->where($queryBuilder->expr()->eq('qq.questionnaire', ':questionnaire'));
        $queryBuilder->andWhere($queryBuilder->expr()->eq('a.affiliation', ':affiliation'));
        $queryBuilder->orderBy('c.sequence', Criteria::ASC);
        $queryBuilder->addOrderBy('qq.sequence', Criteria::ASC);

        $queryBuilder->setParameter('questionnaire', $questionnaire);
        $queryBuilder->setParameter('affiliation', $affiliation);

        return $queryBuilder->getQuery()->getResult();
    }
}
