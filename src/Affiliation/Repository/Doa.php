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

use Affiliation\Entity\Doa as DoaEntity;
use Doctrine\ORM\EntityRepository;

/**
 * @category    Affiliation
 * @package     Repository
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
        $qb->andWhere($qb->expr()->isNull('d.dateApproved'));

        $qb->addOrderBy('d.dateCreated', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
