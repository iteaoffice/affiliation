<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Repository
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Affiliation\Repository;

use Affiliation\Entity\Loi as LoiEntity;
use Doctrine\ORM\EntityRepository;

/**
 * @category    Affiliation
 * @package     Repository
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
        $qb->andWhere($qb->expr()->isNull('l.dateApproved'));

        $qb->addOrderBy('l.dateCreated', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
