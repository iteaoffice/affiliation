<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Doa
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Service;

use Affiliation\Entity\Doa;
use Doctrine\Common\Collections\ArrayCollection;
use Organisation\Entity\Organisation;

/**
 * Class DoaService
 * @package Affiliation\Service
 */
class DoaService extends ServiceAbstract
{
    /**
     * @param $id
     *
     * @return null|Doa
     */
    public function findDoaById($id): ?Doa
    {
        return $this->getEntityManager()->getRepository(Doa::class)->find($id);
    }

    /**
     * @return ArrayCollection
     */
    public function findNotApprovedDoa(): ArrayCollection
    {
        return new ArrayCollection($this->getEntityManager()->getRepository(Doa::class)->findNotApprovedDoa());
    }

    /**
     * @param Organisation $organisation
     * @return array
     */
    public function findDoaByOrganisation(Organisation $organisation): array
    {
        return $this->getEntityManager()->getRepository(Doa::class)->findDoaByOrganisation($organisation);
    }
}
