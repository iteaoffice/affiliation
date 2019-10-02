<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Repository;

use Affiliation\Entity;
use Doctrine\ORM\EntityRepository;
use Organisation\Entity\Organisation;

/**
 * @category    Affiliation
 */
final class Doa extends EntityRepository
{
    public function findNotApprovedDigitalDoa(): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('affiliation_entity_doa');
        $qb->from(Entity\Doa::class, 'affiliation_entity_doa');
        $qb->join('affiliation_entity_doa.affiliation', 'affiliation_entity_affiliation');
        $qb->andWhere($qb->expr()->isNull('affiliation_entity_doa.size'));
        $qb->andWhere($qb->expr()->isNull('affiliation_entity_doa.dateApproved'));
        $qb->andWhere($qb->expr()->isNull('affiliation_entity_affiliation.dateEnd'));

        $qb->addOrderBy('affiliation_entity_doa.dateCreated', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findNotApprovedUploadedDoa(): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('affiliation_entity_doa');
        $qb->from(Entity\Doa::class, 'affiliation_entity_doa');
        $qb->join('affiliation_entity_doa.affiliation', 'affiliation_entity_affiliation');
        $qb->join('affiliation_entity_doa.object', 'affiliation_entity_doa_object');
        $qb->andWhere($qb->expr()->isNull('affiliation_entity_doa.dateApproved'));
        $qb->andWhere($qb->expr()->isNull('affiliation_entity_affiliation.dateEnd'));

        $qb->addOrderBy('affiliation_entity_doa.dateCreated', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findDoaByOrganisation(Organisation $organisation): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('affiliation_entity_doa');
        $qb->from(Entity\Doa::class, 'affiliation_entity_doa');
        $qb->join('affiliation_entity_doa.affiliation', 'affiliation_entity_affiliation');

        $qb->andWhere('affiliation_entity_affiliation.organisation = :organisation');
        $qb->setParameter('organisation', $organisation);

        $qb->addOrderBy('affiliation_entity_doa.dateCreated', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
