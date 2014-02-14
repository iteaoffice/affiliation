<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Service
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Affiliation\Service;

use Project\Entity\Project;

use Affiliation\Entity\Affiliation;
use General\Entity\Country;
use Contact\Entity\Contact;

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
    public function findAffiliationByProjectAndWhich(Project $project, $which = 2)
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
     * Produce a list of affiliations per country
     *
     * @param Project $project
     * @param Country $country
     * @param int     $which
     *
     * @return AffiliationService[]
     */
    public function findAffiliationByProjectAndCountryAndWhich(Project $project, Country $country, $which = 2)
    {
        $affiliations = $this->getEntityManager()
            ->getRepository($this->getFullEntityName('affiliation'))
            ->findAffiliationByProjectAndCountryAndWhich($project, $country, $which);
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
    public function findAffiliationCountriesByProjectAndWhich(Project $project, $which = self::WHICH_ONLY_ACTIVE)
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
     * @param Project $project
     * @param Contact $contact
     * @param int     $which
     *
     * @return null|Affiliation
     */
    public function findAffiliationByProjectAndContactAndWhich(Project $project, Contact $contact, $which = self::WHICH_ONLY_ACTIVE)
    {
        /**
         * If the contact has no contact organisation, return null because we will not have a affiliation
         */
        if (is_null($contact->getContactOrganisation())) {
            return null;
        }

        foreach ($project->getAffiliation() as $affiliation) {
            if ($which === self::WHICH_ONLY_ACTIVE && !is_null($affiliation->getDateEnd())) {
                continue;
            }
            if ($which === self::WHICH_ONLY_INACTIVE && is_null($affiliation->getDateEnd())) {
                continue;
            }
            if ($affiliation->getOrganisation()->getId() === $contact->getContactOrganisation()->getOrganisation()->getId()) {
                return $affiliation;
            }
        }

        return null;
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return $this
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
