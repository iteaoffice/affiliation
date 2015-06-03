<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Doa
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
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
     * @var Doa
     */
    protected $doa;

    /**
     * @param int $id
     *
     * @return DoaService;
     */
    public function setDoaId($id)
    {
        $this->setDoa($this->findEntityById('doa', $id));

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return is_null($this->doa) || is_null($this->doa->getId());
    }

    /**
     * Get a list of not approved doas.
     *
     * @return Doa[]
     */
    public function findNotApprovedDoa()
    {
        return new ArrayCollection($this->getEntityManager()->getRepository($this->getFullEntityName('doa'))->findNotApprovedDoa());
    }

    /**
     * Get a list DOA's by organisation
     *
     * @return Doa[]
     */
    public function findDoaByOrganisation(Organisation $organisation)
    {
        return $this->getEntityManager()->getRepository($this->getFullEntityName('doa'))->findDoaByOrganisation($organisation);
    }

    /**
     * @param \Affiliation\Entity\Doa $doa
     *
     * @return $this;
     */
    public function setDoa($doa)
    {
        $this->doa = $doa;

        return $this;
    }

    /**
     * @return \Affiliation\Entity\Doa
     */
    public function getDoa()
    {
        return $this->doa;
    }
}
