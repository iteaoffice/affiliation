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

use Affiliation\Entity\Questionnaire\Questionnaire;
use Affiliation\Entity\Questionnaire\QuestionnaireQuestion;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;

/**
 * Class QuestionnaireRepository
 * @package Affiliation\Repository\Questionnaire
 */
final class QuestionnaireRepository extends EntityRepository
{
    public function getSortedAnswers(Questionnaire $questionnaire): array
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('a', 'qq', 'q');
        $queryBuilder->from(QuestionnaireQuestion::class, 'qq');
        $queryBuilder->innerJoin('qq.question', 'q');
        $queryBuilder->innerJoin('q.category', 'c');
        $queryBuilder->innerJoin('qq.answers', 'a');
        $queryBuilder->where($queryBuilder->expr()->eq('qq.questionnaire', ':questionnaire'));
        $queryBuilder->orderBy('c.sequence', Criteria::ASC);
        $queryBuilder->addOrderBy('qq.sequence', Criteria::ASC);

        $queryBuilder->setParameter('questionnaire', $questionnaire);

        return $queryBuilder->getQuery()->getResult();
    }
}
