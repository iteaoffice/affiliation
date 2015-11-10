<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Loi
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Service;

use Affiliation\Entity\Loi;
use Doctrine\Common\Collections\ArrayCollection;
use Organisation\Entity\Organisation;

/**
 * LoiService.
 *
 * this is a generic wrapper service for all the other services
 *
 * First parameter of all methods (lowercase, underscore_separated)
 * will be used to fetch the correct model service, one exception is the 'linkModel'
 * method.
 */
class LoiService extends ServiceAbstract
{
    /**
     * @var Loi
     */
    protected $loi;

    /**
     * @param int $id
     *
     * @return LoiService;
     */
    public function setLoiId($id)
    {
        $this->setLoi($this->findEntityById('loi', $id));

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return is_null($this->loi) || is_null($this->loi->getId());
    }

    /**
     * Get a list of not approved lois.
     *
     * @return Loi[]|ArrayCollection
     */
    public function findNotApprovedLoi()
    {
        return new ArrayCollection($this->getEntityManager()->getRepository($this->getFullEntityName('loi'))->findNotApprovedLoi());
    }

    /**
     * Get a list Loi's by organisation
     *
     * @return Loi[]
     */
    public function findLoiByOrganisation(Organisation $organisation)
    {
        return $this->getEntityManager()->getRepository($this->getFullEntityName('loi'))->findLoiByOrganisation($organisation);
    }

    /**
     * @param \Affiliation\Entity\Loi $loi
     *
     * @return $this;
     */
    public function setLoi($loi)
    {
        $this->loi = $loi;

        return $this;
    }

    /**
     * @return \Affiliation\Entity\Loi
     */
    public function getLoi()
    {
        return $this->loi;
    }
}
