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

use Affiliation\Entity\Doa as DoaEntity;
use Doctrine\ORM\EntityRepository;
use Organisation\Entity\Organisation;

/**
 * @category    Affiliation
 */
class Doa extends EntityRepository
{
    /**
     * @return DoaEntity[]
     */
    public function findNotApprovedDoa()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('d');
        $qb->from('Affiliation\Entity\Doa', 'd');
        $qb->join('d.affiliation', 'a');
        $qb->andWhere($qb->expr()->isNull('d.dateApproved'));
        $qb->andWhere($qb->expr()->isNull('a.dateEnd'));

        $qb->addOrderBy('d.dateCreated', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param  Organisation $organisation
     *
     * @return DoaEntity[]
     */
    public function findDoaByOrganisation(Organisation $organisation)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('d');
        $qb->from('Affiliation\Entity\Doa', 'd');
        $qb->join('d.affiliation', 'a');

        $qb->andWhere('a.organisation = :organisation');
        $qb->setParameter('organisation', $organisation);

        $qb->addOrderBy('d.dateCreated', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
