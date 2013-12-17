<?php
/**
 * Japaveh Webdesign copyright message placeholder
 *
 * @category    Affiliation
 * @package     Service
 * @author      Johan van der Heide <info@japaveh.nl>
 * @copyright   Copyright (c) 2004-2013 Japaveh Webdesign (http://japaveh.nl)
 */
namespace Affiliation\Service;

use Project\Entity\Project;

use Affiliation\Entity\Affiliation;

/**
 * AffiliationService
 *
 * this is a generic wrapper service for all the other services
 *
 * First parameter of all methods (lowercase, underscore_separated)
 * will be used to fetch the correct model service, one exception is the 'linkModel'
 * method.
 *
 */
class AffiliationService extends ServiceAbstract
{
    /**
     * Constant to determine which affiliations must be taken from the database
     */
    const WHICH_ALL           = 1;
    const WHICH_ONLY_ACTIVE   = 2;
    const WHICH_ONLY_INACTIVE = 3;

    /**
     * @var Affiliation
     */
    protected $affiliation;

    /**
     * @param int $id
     *
     * @return AffiliationService;
     */
    public function setAffiliationId($id)
    {
        $this->setAffiliation($this->findEntityById('affiliation', $id));

        return $this;
    }

    /**
     * @param Project $project
     * @param int     $which
     *
     * @return AffiliationService[]
     */
    public function findAffiliationByProjectAndWhich(Project $project, $which)
    {
        $affiliations = $this->getEntityManager()
            ->getRepository($this->getFullEntityName('affiliation'))
            ->findAffiliationByProjectAndWhich($project, $which);
        $result       = array();
        foreach ($affiliations as $affiliation) {
            $result[] = $this->createServiceElement($affiliation);
        }

        return $result;
    }

    /**
     * @param Project $project
     * @param int     $which
     *
     * @return \General\Entity\Country[]
     */
    public function findAffiliationCountriesByProjectAndWhich(Project $project, $which)
    {
        $affiliations = $this->getEntityManager()
            ->getRepository($this->getFullEntityName('affiliation'))
            ->findAffiliationByProjectAndWhich($project, $which);

        $result = array();
        foreach ($affiliations as $affiliation) {
            $result[$affiliation->getOrganisation()->getCountry()->getCountry()] =
                $affiliation->getOrganisation()->getCountry();
        }

        ksort($result);

        return $result;
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return createServiceElement
     */
    private function createServiceElement(Affiliation $affiliation)
    {
        $affiliationService = new self();
        $affiliationService->setServiceLocator($this->getServiceLocator());
        $affiliationService->setAffiliation($affiliation);

        return $affiliationService;
    }

    /**
     * @param \Affiliation\Entity\Affiliation $affiliation
     */
    public function setAffiliation($affiliation)
    {
        $this->affiliation = $affiliation;
    }

    /**
     * @return \Affiliation\Entity\Affiliation
     */
    public function getAffiliation()
    {
        return $this->affiliation;
    }
}
