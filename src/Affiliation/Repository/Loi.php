<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

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
     * @return LoiEntity[]
     */
    public function findNotApprovedLoi()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('l');
        $qb->from('Affiliation\Entity\Loi', 'l');
        $qb->join('l.affiliation', 'a');
        $qb->andWhere($qb->expr()->isNull('l.dateApproved'));
        $qb->andWhere($qb->expr()->isNull('a.dateEnd'));

        $qb->addOrderBy('l.dateCreated', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param  Organisation $organisation
     * @return LoiEntity[]
     */
    public function findLoiByOrganisation(Organisation $organisation)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('l');
        $qb->from('Affiliation\Entity\Loi', 'l');
        $qb->join('l.affiliation', 'a');

        $qb->andWhere('a.organisation = :organisation');
        $qb->setParameter('organisation', $organisation);

        $qb->addOrderBy('l.dateCreated', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
