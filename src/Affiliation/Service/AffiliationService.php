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

use Affiliation\Entity\Affiliation;
use Contact\Entity\Contact;
use Doctrine\Common\Collections\ArrayCollection;
use General\Entity\Country;
use Organisation\Service\OrganisationService;
use Project\Entity\Project;
use Project\Entity\Version\Version;
use Project\Service\ProjectService;

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
    const WHICH_ALL = 1;
    const WHICH_ONLY_ACTIVE = 2;
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
     * @return bool
     */
    public function isEmpty()
    {
        return is_null($this->affiliation) || is_null($this->affiliation->getId());
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return is_null($this->affiliation->getDateEnd());
    }

    /**
     * Checks if the affiliation has a DOA
     *
     * @return bool
     */
    public function hasDoa()
    {
        return !is_null($this->affiliation->getDoa());
    }

    /**
     * @return bool
     */
    public function hasLoi()
    {
        return !is_null($this->affiliation->getLoi());
    }

    /**
     * @param Project $project
     * @param int     $which
     *
     * @return \Generator
     */
    public function findAffiliationByProjectAndWhich(Project $project, $which = self::WHICH_ONLY_ACTIVE)
    {
        $affiliations = $this->getEntityManager()
            ->getRepository($this->getFullEntityName('Affiliation'))
            ->findAffiliationByProjectAndWhich($project, $which);
        foreach ($affiliations as $affiliation) {
            yield $this->createServiceElement($affiliation);
        }
    }

    /**
     * @param Version $version
     * @param int     $which
     *
     * @return ArrayCollection
     */
    public function findAffiliationByProjectVersionAndWhich(Version $version, $which = self::WHICH_ALL)
    {
        $affiliations = $this->getEntityManager()
            ->getRepository($this->getFullEntityName('Affiliation'))
            ->findAffiliationByProjectVersionAndWhich($version, $which);

        $result = new ArrayCollection();

        foreach ($affiliations as $affiliation) {
            $result->add($affiliation);
        }

        return $result;
    }

    /**
     * @param Project $project
     * @param Country $country
     * @param int     $which
     *
     * @return \Generator
     */
    public function findAffiliationByProjectAndCountryAndWhich(
        Project $project,
        Country $country,
        $which = self::WHICH_ONLY_ACTIVE
    ) {
        $affiliations = $this->getEntityManager()
            ->getRepository($this->getFullEntityName('Affiliation'))
            ->findAffiliationByProjectAndCountryAndWhich(
                $project,
                $country,
                $which
            );
        foreach ($affiliations as $affiliation) {
            yield $this->createServiceElement($affiliation);
        }
    }

    /**
     * @param Version $version
     * @param Country $country
     * @param int     $which
     *
     * @return \Generator
     */
    public function findAffiliationByProjectVersionAndCountryAndWhich(
        Version $version,
        Country $country,
        $which = self::WHICH_ONLY_ACTIVE
    ) {
        $affiliations = $this->getEntityManager()
            ->getRepository($this->getFullEntityName('Affiliation'))
            ->findAffiliationByProjectVersionAndCountryAndWhich(
                $version,
                $country,
                $which
            );
        foreach ($affiliations as $affiliation) {
            yield $this->createServiceElement($affiliation);
        }
    }

    /**
     * Produce a list of affiliations grouped per country
     *
     * @param Project $project
     * @param int     $which
     *
     * @return ArrayCollection
     */
    public function findAffiliationByProjectPerCountryAndWhich(
        Project $project,
        $which = self::WHICH_ONLY_ACTIVE
    ) {
        $countries = $this->findAffiliationCountriesByProjectAndWhich($project, $which);

        $result = new ArrayCollection();
        foreach ($countries as $country) {
            /**
             * @var $affiliations Affiliation[]
             */
            $result->set(
                $country->getId(),
                $this->findAffiliationByProjectAndCountryAndWhich(
                    $project,
                    $country,
                    $which
                )
            );
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
        /**
         * @var $affiliations Affiliation[]
         */
        $affiliations = $this->getEntityManager()
            ->getRepository($this->getFullEntityName('affiliation'))
            ->findAffiliationByProjectAndWhich($project, $which);
        $result = [];
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
    public function findAffiliationByProjectAndContactAndWhich(
        Project $project,
        Contact $contact,
        $which = self::WHICH_ONLY_ACTIVE
    ) {
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
            if ($affiliation->getOrganisation()->getId() ===
                $contact->getContactOrganisation()->getOrganisation()
                    ->getId()
            ) {
                return $affiliation;
            }
        }

        return null;
    }

    /**
     * Deactivate an affiliation
     *
     * @param Affiliation $affiliation
     */
    public function deactivateAffiliation(Affiliation $affiliation)
    {
        $projectService = $this->getProjectService()->setProject($affiliation->getProject());
        $affiliation->setDateEnd(new \DateTime());
        $this->updateEntity($affiliation);
        $editYearRange = $projectService->parseEditYearRange();
        $minEditYear = array_shift($editYearRange);
        /**
         * Remove the current cost and effort of the affiliation
         */
        foreach ($affiliation->getEffort() as $effort) {
            if ($effort->getDateStart()->format('Y') >= $minEditYear) {
                $this->getProjectService()->removeEntity($effort);
            }
        }
        /**
         * Remove the current cost and effort of the affiliation
         */
        foreach ($affiliation->getCost() as $cost) {
            if ($cost->getDateStart()->format('Y') >= $minEditYear) {
                $this->getProjectService()->removeEntity($cost);
            }
        }
    }

    /**
     * Reactivate an affiliation
     *
     * @param Affiliation $affiliation
     */
    public function reactivateAffiliation(Affiliation $affiliation)
    {
        $affiliation->setDateEnd(null);
        $this->updateEntity($affiliation);
    }

    /**
     * This function creates an array of organisations with branches which are optional when a user wants to change
     * his affiliation
     */
    public function parseRenameOptions()
    {
        $options = [];
        $organisation = $this->getAffiliation()->getOrganisation();
        $contact = $this->getAffiliation()->getContact();
        /**
         * Go over the organisation and grab all its affiliations
         */
        foreach ($organisation->getAffiliation() as $affiliation) {
            $this->getOrganisationService()->setOrganisation($affiliation->getOrganisation());
            $options[$affiliation->getOrganisation()->getCountry()->getCountry()]
            [$affiliation->getOrganisation()->getId()]
            [$affiliation->getBranch()] =
                $this->getOrganisationService()->parseOrganisationWithBranch($affiliation->getBranch());
        }
        /**
         * Go over the organisation and join the clusters and grab all its affiliations
         */
        foreach ($organisation->getCluster() as $cluster) {
            foreach ($cluster->getMember() as $clusterMember) {
                foreach ($clusterMember->getAffiliation() as $affiliation) {
                    $this->getOrganisationService()->setOrganisation($affiliation->getOrganisation());
                    $options[$affiliation->getOrganisation()->getCountry()->getCountry()][$affiliation->getOrganisation(
                    )->getId()][$affiliation->getBranch()] =
                        $this->getOrganisationService()->parseOrganisationWithBranch($affiliation->getBranch());
                }
            }
        }
        /**
         * Go over the contact and grab all its affiliations
         */
        foreach ($contact->getAffiliation() as $affiliation) {
            $this->getOrganisationService()->setOrganisation($affiliation->getOrganisation());
            $options[$affiliation->getOrganisation()->getCountry()->getCountry()]
            [$affiliation->getOrganisation()->getId()]
            [$affiliation->getBranch()] =
                $this->getOrganisationService()->parseOrganisationWithBranch($affiliation->getBranch());
        }
        /**
         * Add the contact organisation (from the contact)
         */
        if (!is_null($contact->getContactOrganisation())) {
            $this->getOrganisationService()->setOrganisation($contact->getContactOrganisation()->getOrganisation());
            $options[$contact->getContactOrganisation()->getOrganisation()->getCountry()->getCountry(
            )][$contact->getContactOrganisation()->getOrganisation()->getId()]
            [$contact->getContactOrganisation()->getBranch()] =
                $this->getOrganisationService()->parseOrganisationWithBranch(
                    $contact->getContactOrganisation()->getBranch()
                );
        }
        /**
         * Add the contact organisation (from the organisation)
         */
        if (!is_null($organisation->getContactOrganisation())) {
            /**
             * Add the contact organisation
             */
            $this->getOrganisationService()->setOrganisation($contact->getContactOrganisation()->getOrganisation());
            $options[$contact->getContactOrganisation()->getOrganisation()->getCountry()->getCountry(
            )][$contact->getContactOrganisation()->getOrganisation()->getId()]
            [$contact->getContactOrganisation()->getBranch()] =
                $this->getOrganisationService()->parseOrganisationWithBranch(
                    $contact->getContactOrganisation()->getBranch()
                );
            /**
             * Go over the clusters
             */
            foreach ($organisation->getContactOrganisation()->getOrganisation()->getCluster() as $cluster) {
                foreach ($cluster->getMember() as $clusterMember) {
                    foreach ($clusterMember->getAffiliation() as $affiliation) {
                        $this->getOrganisationService()->setOrganisation($affiliation->getOrganisation());
                        $options[$affiliation->getOrganisation()->getCountry()->getCountry(
                        )][$affiliation->getOrganisation()->getId()][$affiliation->getBranch()] =
                            $this->getOrganisationService()->parseOrganisationWithBranch($affiliation->getBranch());
                    }
                }
            }
        }

        return $options;
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return $this
     */
    private function createServiceElement(Affiliation $affiliation)
    {
        $affiliationService = clone $this;
        $affiliationService->setAffiliation($affiliation);

        return $affiliationService;
    }

    /**
     * @param \Affiliation\Entity\Affiliation $affiliation
     *
     * @return $this;
     */
    public function setAffiliation($affiliation)
    {
        $this->affiliation = $affiliation;

        return $this;
    }

    /**
     * @return \Affiliation\Entity\Affiliation
     */
    public function getAffiliation()
    {
        return $this->affiliation;
    }

    /**
     * Gateway to the Project Service
     *
     * @return ProjectService
     */
    public function getProjectService()
    {
        return $this->getServiceLocator()->get(ProjectService::class);
    }

    /**
     * Gateway to the Organisation Service
     *
     * @return OrganisationService
     */
    public function getOrganisationService()
    {
        return $this->getServiceLocator()->get('organisation_organisation_service');
    }
}
