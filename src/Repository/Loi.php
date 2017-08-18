<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Repository;

use Affiliation\Entity\Loi as LoiEntity;
use Doctrine\ORM\EntityRepository;
use Organisation\Entity\Organisation;

/**
 * @category    Affiliation
 */
class Loi extends EntityRepository
{
    /**
     * @return iterable
     */
    public function findNotApprovedLoi(): iterable
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('affiliation_entity_loi');
        $qb->from(LoiEntity::class, 'affiliation_entity_loi');
        $qb->join('affiliation_entity_loi.affiliation', 'affiliation_entity_affiliation');
        $qb->andWhere($qb->expr()->isNull('affiliation_entity_loi.dateApproved'));
        $qb->andWhere($qb->expr()->isNull('affiliation_entity_affiliation.dateEnd'));

        $qb->addOrderBy('affiliation_entity_loi.dateCreated', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param  Organisation $organisation
     *
     * @return LoiEntity[]
     */
    public function findLoiByOrganisation(Organisation $organisation): iterable
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('affiliation_entity_loi');
        $qb->from(LoiEntity::class, 'affiliation_entity_loi');
        $qb->join('affiliation_entity_loi.affiliation', 'affiliation_entity_affiliation');

        $qb->andWhere('affiliation_entity_affiliation.organisation = :organisation');
        $qb->setParameter('organisation', $organisation);

        $qb->addOrderBy('affiliation_entity_loi.dateCreated', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
