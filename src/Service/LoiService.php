<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Loi
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Service;

use Affiliation\Entity;
use Affiliation\Repository;
use Doctrine\Common\Collections\ArrayCollection;
use Organisation\Entity\Organisation;

/**
 * Class LoiService
 *
 * @package Affiliation\Service
 */
class LoiService extends ServiceAbstract
{
    /**
     * @param $id
     *
     * @return null|Entity\Loi
     */
    public function findLoiById($id)
    {
        return $this->getEntityManager()->getRepository(Entity\Loi::class)->find($id);
    }

    /**
     * Get a list of not approved lois.
     *
     * @return Entity\Loi[]|ArrayCollection
     */
    public function findNotApprovedLoi()
    {
        /** @var Repository\Loi $repository */
        $repository = $this->getEntityManager()->getRepository(Entity\Loi::class);

        return new ArrayCollection($repository->findNotApprovedLoi());
    }

    /**
     * Get a list Loi's by organisation
     *
     * @param Organisation $organisation
     *
     * @return Entity\Loi[]
     */
    public function findLoiByOrganisation(Organisation $organisation)
    {
        /** @var Repository\Loi $repository */
        $repository = $this->getEntityManager()->getRepository(Entity\Loi::class);

        return $repository->findLoiByOrganisation($organisation);
    }
}
