<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Doa
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Service;

use Affiliation\Entity\Doa;
use Doctrine\Common\Collections\ArrayCollection;
use Organisation\Entity\Organisation;

/**
 * DoaService.
 *
 * this is a generic wrapper service for all the other services
 *
 * First parameter of all methods (lowercase, underscore_separated)
 * will be used to fetch the correct model service, one exception is the 'linkModel'
 * method.
 */
class DoaService extends ServiceAbstract
{
    /**
     * @param $id
     *
     * @return null|Doa
     */
    public function findDoaById($id)
    {
        return $this->getEntityManager()->getRepository(Doa::class)->find($id);
    }

    /**
     * Get a list of not approved doas.
     *
     * @return Doa[]|ArrayCollection
     */
    public function findNotApprovedDoa()
    {
        return new ArrayCollection($this->getEntityManager()->getRepository(Doa::class)->findNotApprovedDoa());
    }

    /**
     * Get a list DOA's by organisation
     *
     * @return Doa[]
     */
    public function findDoaByOrganisation(Organisation $organisation)
    {
        return $this->getEntityManager()->getRepository(Doa::class)->findDoaByOrganisation($organisation);
    }
}
