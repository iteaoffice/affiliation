<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category Affiliation
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */

namespace Affiliation\Service;

use Affiliation\Entity\Affiliation;
use Contact\Entity\Contact;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use General\Entity\Country;
use Invoice\Entity\Method;
use Program\Entity\Call\Call;
use Project\Entity\Funding\Funding;
use Project\Entity\Funding\Source;
use Project\Entity\Funding\Status;
use Project\Entity\Project;
use Project\Entity\Version\Version;

/**
 * AffiliationService.
 *
 * this is a generic wrapper service for all the other services
 *
 * First parameter of all methods (lowercase, underscore_separated)
 * will be used to fetch the correct model service, one exception is the 'linkModel'
 * method.
 */
class AffiliationService extends ServiceAbstract
{
    /**
     * Constant to determine which affiliations must be taken from the database.
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

    public function isSelfFunded()
    {
        return $this->affiliation->getSelfFunded() === Affiliation::SELF_FUNDED;
    }


    /**
     * Checks if the affiliation has a DOA.
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
     * This function calculates the factor to which the contribution should be calculated.
     * We removed the switch on office to facilitate the contribution based invoicing
     *
     * @param  int $year
     * @param  int $period
     * @return float|int
     */
    public function parseContributionFactor($year, $period)
    {
        $projectService = $this->getProjectService()->setProject($this->affiliation->getProject());

        switch (true) {
            case !$this->isFundedInYear($year):
                return 0;
            case $projectService->parseEndYear() == $year && $projectService->parseEndMonth() <= 6:
                return $period === 1 ? 1 : 0;
            default:
                return 0.5;
        }
    }

    /**
     * This function calculates the factor to which the contribution should be calculated.
     * We removed the switch on office to facilitate the contribution based invoicing
     *
     * @param  int $projectYear
     * @param  int $year
     * @param  int $period
     * @return float|int
     */
    public function parseContributionFactorDue($projectYear, $year, $period)
    {
        switch (true) {
            case !$this->isFundedInYear($projectYear):
                return 0;
            case $projectYear < $year:
                return 1; //in the past is always 100% due
            case $projectYear === $year && $period === 2:
                //Current year, and period 2 (so  first period might have been invoiced, due is now the 1-that value
                return 1 - ($this->parseContributionFactor($year, $period));
            default:
                return 0;
        }
    }


    /**
     * @param $year
     * @return Funding|null
     */
    public function getFundingInYear($year)
    {
        foreach ($this->getAffiliation()->getFunding() as $funding) {
            if ((int)$funding->getDateStart()->format("Y") === $year && $funding->getSource()->getId() === Source::SOURCE_OFFICE) {
                return $funding;
            }
        };

        return null;
    }


    /**
     *
     */
    public function isFundedInYear($year)
    {
        return is_null($this->getFundingInYear($year)) ? false : $this->getFundingInYear($year)->getStatus()->getId() === Status::STATUS_ALL_GOOD;
    }

    /**
     * @param Version $version
     * @param $year
     * @param $period
     * @return float|int
     * @throws \Exception
     */
    public function parseContributionDue(Version $version, $year, $period)
    {
        $contributionDue = 0;

        switch ($this->getInvoiceService()->findInvoiceMethod($version->getProject()->getCall()->getProgram())->getId()) {

            case Method::METHOD_PERCENTAGE:

                //Fix the versionService
                $costsPerYear = $this->getVersionService()->findTotalCostVersionByAffiliationAndVersionPerYear(
                    $this->getAffiliation(),
                    $version
                );


                foreach ($costsPerYear as $costsYear => $cost) {
                    //fee
                    $fee = $this->getProjectService()->findProjectFeeByYear($year);

                    $factor = $this->parseContributionFactorDue($costsYear, $year, $period);

                    //Only add the value to the contribution if the partner is funded in that year
                    if ($this->isFundedInYear($costsYear)) {
                        $contributionDue += $factor * $cost * ($fee->getPercentage() / 100);
                    }
                }

                break;

            case Method::METHOD_CONTRIBUTION:

                //Fix the versionService
                $effortPerYear = $this->getVersionService()->findTotalEffortVersionByAffiliationAndVersionPerYear(
                    $this->getAffiliation(),
                    $version
                );


                foreach ($effortPerYear as $effortYear => $effort) {
                    $fee = $this->getProjectService()->findProjectFeeByYear($year);

                    switch (true) {
                        case $period === 3: //costs in the past
                            $factor = 1;
                            break;
                        default:
                            $factor = $this->parseContributionFactor($year, $period);
                    }

                    if ($this->isFundedInYear($year)) {
                        $contributionDue += $factor * $effort * $fee->getContribution();
                    }
                }
                break;


        }

        return $contributionDue;
    }

    /**
     * Sum up the amount paid already by the affilation in the previous period
     * Exclude of course the credit notes
     *
     * @param  $year
     * @param  $period
     * @return float|int
     */
    public function parseContributionPaid($year, $period)
    {
        $countribitionPaid = 0;

        //Sum the invoiced amount of all invoices for this affiliation
        foreach ($this->getAffiliation()->getInvoice() as $invoice) {
            //Filter invoices of previous years or this year, but the previous period
            if (is_null($invoice->getInvoice()->getCreditOriginal())
                && (($invoice->getPeriod() < $period && $invoice->getYear() == $year)
                    || $invoice->getYear() < $year)
            ) {
                $countribitionPaid += $invoice->getAmountInvoiced();
            }
        }

        return $countribitionPaid;
    }

    /**
     * @param Version $version
     * @param $year
     * @param $period
     * @return float|int
     */
    public function parseBalance(Version $version, $year, $period)
    {
        return $this->parseContributionDue($version, $year, $period) - $this->parseContributionPaid($year, $period);
    }

    /**
     * @param Version $version
     * @param $year
     * @param $period
     * @return float
     */
    public function parseTotal(Version $version, $year, $period)
    {
        return $this->parseContribution($version, $year, $period) + $this->parseBalance($version, $year, $period);
    }

    /**
     * @param Version $version
     * @param $year
     * @param $period
     * @return float
     */
    public function parseContribution(Version $version, $year, $period)
    {
        $contribution = $this->parseContributionBase($version, $year) *
            $this->parseContributionFactor($year, $period) *
            $this->parseContributionFee($version, $year);

        return $contribution;
    }

    /**
     * This function counts the effort or costs per affiliaton and returns the total per year. We pick the total amount out per given ear
     *
     * @param  Version $version
     * @param  int $year
     * @return float
     */
    public function parseContributionBase(Version $version, $year)
    {
        /**
         * We need the versionService to calculate the costs or efforts
         */
        $versionService = $this->getVersionService()->setVersion($version);
        $base = 0;

        /**
         * The base (the sum of the costs or effort in the version depends on the invoiceMethod (percentage === 'costs', contribution === 'effort')
         */
        switch ($this->getInvoiceService()->findInvoiceMethod($version->getProject()->getCall()->getProgram())->getId()) {
            case Method::METHOD_PERCENTAGE:
                $costsPerYear = $versionService->findTotalCostVersionByAffiliationAndVersionPerYear(
                    $this->getAffiliation(),
                    $version
                );
                if (array_key_exists($year, $costsPerYear)) {
                    $base = $costsPerYear[$year];
                }

                break;
            case Method::METHOD_CONTRIBUTION:
                $effortPerYear = $versionService->findTotalEffortVersionByAffiliationAndVersionPerYear(
                    $this->getAffiliation(),
                    $version
                );
                if (array_key_exists($year, $effortPerYear)) {
                    $base = $effortPerYear[$year];
                }
                break;
        }

        return $base;
    }

    /**
     * @param Version $version
     * @param $year
     * @return float|null
     */
    public function parseContributionFee(Version $version, $year)
    {
        /**
         * Based on the invoiceMethod we return or a percentage or the contriubtion
         */
        $fee = $this->getProjectService()->findProjectFeeByYear($year);

        switch ($this->getInvoiceService()->findInvoiceMethod($version->getProject()->getCall()->getProgram())->getId()) {
            case Method::METHOD_PERCENTAGE:
                return $fee->getPercentage() / 100;
            case Method::METHOD_CONTRIBUTION:
                return $fee->getContribution();
            default:
                return null;
        }
    }

    /**
     * @param Project $project
     * @param int $which
     *
     * @return \Generator
     */
    public function findAffiliationByProjectAndWhich(Project $project, $which = self::WHICH_ONLY_ACTIVE)
    {
        $affiliations = $this->getEntityManager()
            ->getRepository(Affiliation::class)
            ->findAffiliationByProjectAndWhich($project, $which);
        foreach ($affiliations as $affiliation) {
            yield $this->createServiceElement($affiliation);
        }
    }

    /**
     * @param Version $version
     * @param int $which
     *
     * @return ArrayCollection
     */
    public function findAffiliationByProjectVersionAndWhich(Version $version, $which = self::WHICH_ALL)
    {
        $affiliations = $this->getEntityManager()
            ->getRepository(Affiliation::class)
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
     * @param int $which
     *
     * @return \Generator
     */
    public function findAffiliationByProjectAndCountryAndWhich(
        Project $project,
        Country $country,
        $which = self::WHICH_ONLY_ACTIVE
    ) {
        $affiliations = $this->getEntityManager()
            ->getRepository(Affiliation::class)
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
     * @param Project $project
     * @param Country $country
     * @param int $which
     *
     * @return int
     */
    public function findAmountOfAffiliationByProjectAndCountryAndWhich(
        Project $project,
        Country $country,
        $which = self::WHICH_ONLY_ACTIVE
    ) {
        return $this->getEntityManager()
            ->getRepository(Affiliation::class)
            ->findAmountOfAffiliationByProjectAndCountryAndWhich(
                $project,
                $country,
                $which
            );
    }

    /**
     * @param Project $project
     * @param Country $country
     * @param Call $call
     *
     * @return int
     */
    public function findAmountOfAffiliationByCountryAndCall(Country $country, Call $call)
    {
        return $this->getEntityManager()
            ->getRepository(Affiliation::class)
            ->findAmountOfAffiliationByCountryAndCall($country, $call);
    }

    /**
     * @param Version $version
     * @param Country $country
     * @param int $which
     *
     * @return \Generator
     */
    public function findAffiliationByProjectVersionAndCountryAndWhich(
        Version $version,
        Country $country,
        $which = self::WHICH_ONLY_ACTIVE
    ) {
        $affiliations = $this->getEntityManager()
            ->getRepository(Affiliation::class)
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
     * @param Version $version
     * @param Country $country
     * @param int $which
     *
     * @return int
     */
    public function findAmountOfAffiliationByProjectVersionAndCountryAndWhich(
        Version $version,
        Country $country,
        $which = self::WHICH_ONLY_ACTIVE
    ) {
        return $this->getEntityManager()
            ->getRepository(Affiliation::class)
            ->findAmountOfAffiliationByProjectVersionAndCountryAndWhich(
                $version,
                $country,
                $which
            );
    }

    /**
     * Produce a list of affiliations grouped per country.
     *
     * @param Project $project
     * @param int $which
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
     * @param int $which
     *
     * @return \General\Entity\Country[]
     */
    public function findAffiliationCountriesByProjectAndWhich(Project $project, $which = self::WHICH_ONLY_ACTIVE)
    {
        /**
         * @var $affiliations Affiliation[]
         */
        $affiliations = $this->getEntityManager()
            ->getRepository(Affiliation::class)
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
     * @param int $which
     *
     * @return null|Affiliation
     */
    public function findAffiliationByProjectAndContactAndWhich(
        Project $project,
        Contact $contact,
        $which = self::WHICH_ONLY_ACTIVE
    ) {
        /*
         * If the contact has no contact organisation, return null because we will not have a affiliation
         */
        if (is_null($contact->getContactOrganisation())) {
            return;
        }
        foreach ($project->getAffiliation() as $affiliation) {
            if ($which === self::WHICH_ONLY_ACTIVE && !is_null($affiliation->getDateEnd())) {
                continue;
            }
            if ($which === self::WHICH_ONLY_INACTIVE && is_null($affiliation->getDateEnd())) {
                continue;
            }
            if ($affiliation->getOrganisation()->getId() === $contact->getContactOrganisation()->getOrganisation()->getId()
            ) {
                return $affiliation;
            }
        }

        return;
    }

    /**
     * Give a list of all affiliations which do not have a doa.
     *
     * @return Affiliation[]
     */
    public function findAffiliationWithMissingDoa()
    {
        return $this->getEntityManager()->getRepository(
            Affiliation::class
        )->findAffiliationWithMissingDoa();
    }

    /**
     * Give a list of all affiliations which do not have a doa.
     *
     * @return Query
     */
    public function findAffiliationWithMissingLoi()
    {
        return $this->getEntityManager()->getRepository(
            Affiliation::class
        )->findAffiliationWithMissingLoi();
    }

    /**
     * Deactivate an affiliation.
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
        /*
         * Remove the current cost and effort of the affiliation
         */
        foreach ($affiliation->getEffort() as $effort) {
            if ($effort->getDateStart()->format('Y') >= $minEditYear) {
                $this->getProjectService()->removeEntity($effort);
            }
        }
        /*
         * Remove the current cost and effort of the affiliation
         */
        foreach ($affiliation->getCost() as $cost) {
            if ($cost->getDateStart()->format('Y') >= $minEditYear) {
                $this->getProjectService()->removeEntity($cost);
            }
        }
    }

    /**
     * Reactivate an affiliation.
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
     * his affiliation.
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
                    $options[$affiliation->getOrganisation()->getCountry()->getCountry()][$affiliation->getOrganisation()->getId()][$affiliation->getBranch()] =
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
            $options[$contact->getContactOrganisation()->getOrganisation()->getCountry()->getCountry()][$contact->getContactOrganisation()->getOrganisation()->getId()]
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
            $options[$contact->getContactOrganisation()->getOrganisation()->getCountry()->getCountry()][$contact->getContactOrganisation()->getOrganisation()->getId()]
            [$contact->getContactOrganisation()->getBranch()] =
                $this->getOrganisationService()->parseOrganisationWithBranch(
                    $contact->getContactOrganisation()->getBranch()
                );
            /**
             * Go over the clusters
             */
            foreach ($organisation->getContactOrganisation() as $contactOrganisation) {
                foreach ($contactOrganisation->getOrganisation()->getCluster() as $cluster) {
                    foreach ($cluster->getMember() as $clusterMember) {
                        foreach ($clusterMember->getAffiliation() as $affiliation) {
                            $this->getOrganisationService()->setOrganisation($affiliation->getOrganisation());
                            $options[$affiliation->getOrganisation()->getCountry()->getCountry()][$affiliation->getOrganisation()->getId()][$affiliation->getBranch()] =
                                $this->getOrganisationService()->parseOrganisationWithBranch($affiliation->getBranch());
                        }
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
}
