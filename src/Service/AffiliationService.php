<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category  Affiliation
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Service;

use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Invoice;
use Contact\Entity\Contact;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use General\Entity\Country;
use Invoice\Entity\Method;
use Organisation\Entity\Type;
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
     * @param $id
     *
     * @return null|Affiliation
     */
    public function findAffiliationById($id)
    {
        return $this->getEntityManager()->getRepository(Affiliation::class)->find($id);
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return bool
     */
    public function isActive(Affiliation $affiliation)
    {
        return is_null($affiliation->getDateEnd());
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return bool
     */
    public function isSelfFunded(Affiliation $affiliation)
    {
        return $affiliation->getSelfFunded() === Affiliation::SELF_FUNDED
        && !is_null($affiliation->getDateSelfFunded());
    }

    /**
     * Checks if the affiliation has a DOA.
     *
     * @param Affiliation $affiliation
     *
     * @return bool
     */
    public function hasDoa(Affiliation $affiliation)
    {
        return !is_null($affiliation->getDoa());
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return bool
     */
    public function hasLoi(Affiliation $affiliation)
    {
        return !is_null($affiliation->getLoi());
    }

    /**
     * This function calculates the factor to which the contribution should be calculated.
     * We removed the switch on office to facilitate the contribution based invoicing
     *
     * @param Affiliation $affiliation
     * @param  int        $year
     * @param  int        $period
     *
     * @return float|int
     */
    public function parseContributionFactor(Affiliation $affiliation, $year, $period)
    {
        //Cast to ints as some values can originate form templates (== twig > might be string)
        $year = (int)$year;
        $period = (int)$period;

        switch (true) {
            case !$this->isFundedInYear($affiliation, $year):
                return 0;
            case $this->getProjectService()->parseEndYear($affiliation->getProject()) == $year
                && $this->getProjectService()->parseEndMonth($affiliation->getProject()) <= 6:
                return $period === 1 ? 1 : 0;
            default:
                return 0.5;
        }
    }

    /**
     * The VATnumber is first checked in the financial organisation. If that cannot be found we do a fallback
     * tot he organisation > finanical
     *
     * @param Affiliation $affiliation
     *
     * @return string|null
     */
    public function parseVatNumber(Affiliation $affiliation)
    {
        //Find first the corresponding organisation
        switch (true) {
            case !is_null($affiliation->getFinancial()):
                $organisation = $affiliation->getFinancial()->getOrganisation();
                break;
            default:
                $organisation = $affiliation->getOrganisation();
                break;
        }

        /**
         * Return the VAT number is there is a financial organisation
         */
        if (!is_null($organisation->getFinancial())) {
            return $organisation->getFinancial()->getVat();
        } else {
            return null;
        }
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return Contact
     */
    public function getFinancialContact(Affiliation $affiliation)
    {
        if (!is_null($affiliation->getFinancial())) {
            return $affiliation->getFinancial()->getContact();
        } else {
            return null;
        }
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return bool
     */
    public function canCreateInvoice(Affiliation $affiliation)
    {
        $errors = [];
        switch (true) {
            case $affiliation->getOrganisation()->getType()->getInvoice() === Type::NO_INVOICE
                && !($affiliation->getProject()->getCall()->getProgram()->getId() === 3
                    && $affiliation->getOrganisation()->getType()->getId() === Type::TYPE_UNIVERSITY):
                $errors[] = sprintf(
                    'No invoice is needed for %s',
                    $affiliation->getOrganisation()->getType()->getDescription()
                );
                break;
            case is_null($affiliation->getFinancial()):
                $errors[] = 'No financial organisation (affiliation financial) set for this partner';
                break;
            case !is_null($affiliation->getDateEnd()):
                $errors[] = 'Partner is de-activated';
                break;
            case is_null($affiliation->getFinancial()->getOrganisation()->getFinancial()):
                $errors[] = 'No financial information set for this organisation';
                break;
            case is_null($affiliation->getFinancial()->getContact()):
                $errors[] = 'No financial contact set for this organisation';
                break;

        }

        return $errors;
    }

    /**
     * This function calculates the factor to which the contribution should be calculated.
     * We removed the switch on office to facilitate the contribution based invoicing
     *
     * @param Affiliation $affiliation
     * @param  int        $projectYear
     * @param  int        $year
     * @param  int        $period
     *
     * @return float|int
     */
    public function parseContributionFactorDue(Affiliation $affiliation, $projectYear, $year, $period)
    {
        //Cast to ints as some values can originate form templates (== twig > might be string)
        $year = (int)$year;
        $period = (int)$period;

        switch (true) {
            case !$this->isFundedInYear($affiliation, $projectYear):
                return 0;
            case $projectYear < $year:
                return 1; //in the past is always 100% due
            case $projectYear === $year && $period === 2:
                //Current year, and period 2 (so  first period might have been invoiced, due is now the 1-that value
                return 1 - ($this->parseContributionFactor($affiliation, $year, $period));
            default:
                return 0;
        }
    }

    /**
     * @param Affiliation $affiliation
     * @param             $year
     * @param int         $source
     *
     * @return null|Funding
     */
    public function getFundingInYear(Affiliation $affiliation, $year, $source = Source::SOURCE_OFFICE)
    {
        //Cast to ints as some values can originate form templates (== twig > might be string)
        $year = (int)$year;

        foreach ($affiliation->getFunding() as $funding) {
            if ((int)$funding->getDateStart()->format("Y") === $year && $funding->getSource()->getId() === $source) {
                return $funding;
            }
        };

        return null;
    }

    /**
     * @param Affiliation $affiliation
     * @param             $year
     *
     * @return bool
     */
    public function isFundedInYear(Affiliation $affiliation, $year)
    {
        //Cast to ints as some values can originate form templates (== twig > might be string)
        $year = (int)$year;

        return is_null($this->getFundingInYear($affiliation, $year)) ? false
            : $this->getFundingInYear($affiliation, $year)->getStatus()->getId() === Status::STATUS_ALL_GOOD;
    }

    /**
     * @param Affiliation $affiliation
     * @param             $period
     * @param             $year
     *
     * @return Invoice[]
     */
    public function findAffiliationInvoiceByAffiliationPeriodAndYear(Affiliation $affiliation, $period, $year)
    {
        //Cast to ints as some values can originate form templates (== twig > might be string)
        $year = (int)$year;
        $period = (int)$period;

        return $affiliation->getInvoice()->filter(function (Invoice $invoice) use ($period, $year) {
            return $invoice->getPeriod() === $period && $invoice->getYear() === $year;
        });
    }

    /**
     * @param Affiliation $affiliation
     * @param Version     $version
     * @param             $year
     * @param             $period
     *
     * @return float|int
     * @throws \Exception
     */
    public function parseContributionDue(Affiliation $affiliation, Version $version, $year, $period)
    {
        $contributionDue = 0;
        //Cast to ints as some values can originate form templates (== twig > might be string)
        $year = (int)$year;
        $period = (int)$period;


        if (is_null($year) || is_null($period)) {
            throw new \InvalidArgumentException("Year and/or period cannot be null");
        }

        switch ($this->getInvoiceService()->findInvoiceMethod($version->getProject()->getCall()->getProgram())
            ->getId()) {
            case Method::METHOD_PERCENTAGE:
                //Fix the versionService
                $costsPerYear = $this->getVersionService()
                    ->findTotalCostVersionByAffiliationAndVersionPerYear($affiliation, $version);

                foreach ($costsPerYear as $costsYear => $cost) {
                    //fee
                    $fee = $this->getProjectService()->findProjectFeeByYear($costsYear);

                    $factor = $this->parseContributionFactorDue($affiliation, $costsYear, $year, $period);

                    //Only add the value to the contribution if the partner is funded in that year
                    if ($this->isFundedInYear($affiliation, $costsYear)) {
                        $contributionDue += $factor * $cost * ($fee->getPercentage() / 100);
                    }
                }

                break;

            case Method::METHOD_CONTRIBUTION:
                //Fix the versionService
                $effortPerYear = $this->getVersionService()
                    ->findTotalEffortVersionByAffiliationAndVersionPerYear($affiliation, $version);

                foreach ($effortPerYear as $effortYear => $effort) {
                    $fee = $this->getProjectService()->findProjectFeeByYear($year);

                    switch (true) {
                        case $period === 3:
                            //costs in the past
                            $factor = 1;
                            break;
                        default:
                            $factor = $this->parseContributionFactor($affiliation, $year, $period);
                    }

                    if ($this->isFundedInYear($affiliation, $year)) {
                        $contributionDue += $factor * $effort * $fee->getContribution();
                    }
                }
                break;

        }

        return $contributionDue;
    }

    /**
     * Sum up the amount paid already by the affiliation in the previous period
     * Exclude of course the credit notes
     *
     * @param Affiliation $affiliation
     * @param             $year
     * @param             $period
     *
     * @return float|int
     */
    public function parseContributionPaid(Affiliation $affiliation, $year, $period)
    {
        $countribitionPaid = 0;
        //Cast to ints as some values can originate form templates (== twig > might be string)
        $year = (int)$year;
        $period = (int)$period;

        //Sum the invoiced amount of all invoices for this affiliation
        foreach ($affiliation->getInvoice() as $invoice) {

            //Filter invoices of previous years or this year, but the previous period and already sent to accounting
            if (!is_null($invoice->getInvoice()->getDayBookNumber())
                && (($invoice->getPeriod() < $period && $invoice->getYear() == $year) || $invoice->getYear() < $year)
            ) {
                $countribitionPaid += $invoice->getAmountInvoiced();
            }
        }

        return $countribitionPaid;
    }

    /**
     * @param Affiliation $affiliation
     * @param  Version    $version
     * @param             $year
     * @param             $period
     *
     * @return float|int
     */
    public function parseBalance(Affiliation $affiliation, Version $version, $year, $period)
    {
        return $this->parseContributionDue($affiliation, $version, $year, $period)
        - $this->parseContributionPaid($affiliation, $year, $period);
    }

    /**
     * @param Affiliation $affiliation
     * @param  Version    $version
     * @param             $year
     * @param             $period
     *
     * @return float
     */
    public function parseTotal(Affiliation $affiliation, Version $version, $year, $period)
    {
        return $this->parseContribution($affiliation, $version, $year, $period) + $this->parseBalance(
            $affiliation,
            $version,
            $year,
            $period
        );
    }

    /**
     * @param Affiliation $affiliation
     * @param  Version    $version
     * @param             $year
     * @param             $period
     *
     * @return float
     */
    public function parseContribution(Affiliation $affiliation, Version $version, $year, $period)
    {
        $contribution = $this->parseContributionBase($affiliation, $version, $year)
            * $this->parseContributionFactor($affiliation, $year, $period) * $this->parseContributionFee(
                $version,
                $year
            );

        return $contribution;
    }

    /**
     * This function counts the effort or costs per affiliaton and returns the total per year. We pick the total amount out per given ear
     *
     * @param Affiliation $affiliation
     * @param  Version    $version
     * @param  int        $year
     *
     * @return float
     */
    public function parseContributionBase(Affiliation $affiliation, Version $version, $year)
    {
        $base = 0;
        //Cast to ints as some values can originate form templates (== twig > might be string)
        $year = (int)$year;

        /**
         * The base (the sum of the costs or effort in the version depends on the invoiceMethod (percentage === 'costs', contribution === 'effort')
         */
        switch ($this->getInvoiceService()->findInvoiceMethod($version->getProject()->getCall()->getProgram())
            ->getId()) {
            case Method::METHOD_PERCENTAGE:
                $costsPerYear
                    = $this->getVersionService()
                    ->findTotalCostVersionByAffiliationAndVersionPerYear($affiliation, $version);
                if (array_key_exists($year, $costsPerYear)) {
                    $base = $costsPerYear[$year];
                }

                break;
            case Method::METHOD_CONTRIBUTION:
                $effortPerYear
                    = $this->getVersionService()
                    ->findTotalEffortVersionByAffiliationAndVersionPerYear($affiliation, $version);
                if (array_key_exists($year, $effortPerYear)) {
                    $base = $effortPerYear[$year];
                }
                break;
        }

        return $base;
    }

    /**
     * @param  Version $version
     * @param          $year
     *
     * @return float|null
     */
    public function parseContributionFee(Version $version, $year)
    {
        //Cast to ints as some values can originate form templates (== twig > might be string)
        $year = (int)$year;

        /**
         * Based on the invoiceMethod we return or a percentage or the contribution
         */
        $fee = $this->getProjectService()->findProjectFeeByYear($year);

        switch ($this->getInvoiceService()->findInvoiceMethod($version->getProject()->getCall()->getProgram())
            ->getId()) {
            case Method::METHOD_PERCENTAGE:
                return $fee->getPercentage() / 100;
            case Method::METHOD_CONTRIBUTION:
                return $fee->getContribution();
        }
    }

    /**
     * @param Project $project
     * @param int     $which
     *
     * @return \Generator|Affiliation[]
     */
    public function findAffiliationByProjectAndWhich(Project $project, $which = self::WHICH_ONLY_ACTIVE)
    {
        $affiliations = $this->getEntityManager()->getRepository(Affiliation::class)
            ->findAffiliationByProjectAndWhich($project, $which);
        foreach ($affiliations as $affiliation) {
            yield $affiliation;
        }
    }

    /**
     * @param Version $version
     * @param int     $which
     *
     * @return ArrayCollection|Affiliation[]
     */
    public function findAffiliationByProjectVersionAndWhich(Version $version, $which = self::WHICH_ALL)
    {
        $affiliations = $this->getEntityManager()->getRepository(Affiliation::class)
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
        $affiliations = $this->getEntityManager()->getRepository(Affiliation::class)
            ->findAffiliationByProjectAndCountryAndWhich($project, $country, $which);
        foreach ($affiliations as $affiliation) {
            yield $affiliation;
        }
    }

    /**
     * @param Project $project
     * @param Country $country
     * @param int     $which
     *
     * @return int
     */
    public function findAmountOfAffiliationByProjectAndCountryAndWhich(
        Project $project,
        Country $country,
        $which = self::WHICH_ONLY_ACTIVE
    ) {
        return $this->getEntityManager()->getRepository(Affiliation::class)
            ->findAmountOfAffiliationByProjectAndCountryAndWhich($project, $country, $which);
    }

    /**
     * @param Project $project
     * @param Country $country
     * @param Call    $call
     *
     * @return int
     */
    public function findAmountOfAffiliationByCountryAndCall(Country $country, Call $call)
    {
        return $this->getEntityManager()->getRepository(Affiliation::class)
            ->findAmountOfAffiliationByCountryAndCall($country, $call);
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
        $affiliations = $this->getEntityManager()->getRepository(Affiliation::class)
            ->findAffiliationByProjectVersionAndCountryAndWhich($version, $country, $which);
        foreach ($affiliations as $affiliation) {
            yield $affiliation;
        }
    }

    /**
     * @param Version $version
     * @param Country $country
     * @param int     $which
     *
     * @return int
     */
    public function findAmountOfAffiliationByProjectVersionAndCountryAndWhich(
        Version $version,
        Country $country,
        $which = self::WHICH_ONLY_ACTIVE
    ) {
        return $this->getEntityManager()->getRepository(Affiliation::class)
            ->findAmountOfAffiliationByProjectVersionAndCountryAndWhich($version, $country, $which);
    }

    /**
     * Produce a list of affiliations grouped per country.
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
            $result->set(
                $country->getId(),
                $this->findAffiliationByProjectAndCountryAndWhich($project, $country, $which)
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
        $affiliations = $this->getEntityManager()->getRepository(Affiliation::class)
            ->findAffiliationByProjectAndWhich($project, $which);
        $result = [];
        foreach ($affiliations as $affiliation) {
            $result[$affiliation->getOrganisation()->getCountry()->getCountry()] = $affiliation->getOrganisation()
                ->getCountry();
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
        /*
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
            if ($affiliation->getOrganisation()->getId() === $contact->getContactOrganisation()->getOrganisation()
                    ->getId()
            ) {
                return $affiliation;
            }
        }

        return null;
    }

    /**
     * Give a list of all affiliations which do not have a doa.
     *
     * @return Affiliation[]
     */
    public function findAffiliationWithMissingDoa()
    {
        return $this->getEntityManager()->getRepository(Affiliation::class)->findAffiliationWithMissingDoa();
    }

    /**
     * Give a list of all affiliations which do not have a doa.
     *
     * @return Query
     */
    public function findAffiliationWithMissingLoi()
    {
        return $this->getEntityManager()->getRepository(Affiliation::class)->findAffiliationWithMissingLoi();
    }

    /**
     * Deactivate an affiliation.
     *
     * @param Affiliation $affiliation
     */
    public function deactivateAffiliation(Affiliation $affiliation)
    {
        $affiliation->setDateEnd(new \DateTime());
        $this->updateEntity($affiliation);
        $editYearRange = $this->getProjectService()->parseEditYearRange($affiliation->getProject());
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
     *
     * @param Affiliation $affiliation
     *
     * @return array
     */
    public function parseRenameOptions(Affiliation $baseAffiliation)
    {
        $options = [];
        $organisation = $baseAffiliation->getOrganisation();
        $contact = $baseAffiliation->getContact();
        /**
         * Go over the organisation and grab all its affiliations
         */
        foreach ($organisation->getAffiliation() as $affiliation) {
            $options[$affiliation->getOrganisation()->getCountry()->getCountry()]
            [$affiliation->getOrganisation()->getId()]
            [$affiliation->getBranch()]
                = $this->getOrganisationService()
                ->parseOrganisationWithBranch($affiliation->getBranch(), $affiliation->getOrganisation());
        }
        /**
         * Go over the organisation and join the clusters and grab all its affiliations
         */
        foreach ($organisation->getCluster() as $cluster) {
            foreach ($cluster->getMember() as $clusterMember) {
                foreach ($clusterMember->getAffiliation() as $affiliation) {
                    $options[$affiliation->getOrganisation()->getCountry()
                        ->getCountry()][$affiliation->getOrganisation()->getId()][$affiliation->getBranch()]
                        = $this->getOrganisationService()
                        ->parseOrganisationWithBranch($affiliation->getBranch(), $affiliation->getOrganisation());
                }
            }
        }
        /**
         * Go over the contact and grab all its affiliations
         */
        foreach ($contact->getAffiliation() as $affiliation) {
            $options[$affiliation->getOrganisation()->getCountry()->getCountry()]
            [$affiliation->getOrganisation()->getId()]
            [$affiliation->getBranch()]
                = $this->getOrganisationService()
                ->parseOrganisationWithBranch($affiliation->getBranch(), $affiliation->getOrganisation());
        }
        /**
         * Add the contact organisation (from the contact)
         */
        if (!is_null($contact->getContactOrganisation())) {
            $options[$contact->getContactOrganisation()->getOrganisation()->getCountry()
                ->getCountry()][$contact->getContactOrganisation()->getOrganisation()->getId()]
            [$contact->getContactOrganisation()->getBranch()]
                = $this->getOrganisationService()->parseOrganisationWithBranch($contact->getContactOrganisation()
                ->getBranch(), $contact->getContactOrganisation()->getOrganisation());
        }
        /**
         * Add the contact organisation (from the organisation)
         */
        if (!is_null($organisation->getContactOrganisation())) {
            /**
             * Add the contact organisation
             */
            $options[$contact->getContactOrganisation()->getOrganisation()->getCountry()
                ->getCountry()][$contact->getContactOrganisation()->getOrganisation()->getId()]
            [$contact->getContactOrganisation()->getBranch()]
                = $this->getOrganisationService()->parseOrganisationWithBranch($contact->getContactOrganisation()
                ->getBranch(), $contact->getContactOrganisation()->getOrganisation());
            /**
             * Go over the clusters
             */
            foreach ($organisation->getContactOrganisation() as $contactOrganisation) {
                foreach ($contactOrganisation->getOrganisation()->getCluster() as $cluster) {
                    foreach ($cluster->getMember() as $clusterMember) {
                        foreach ($clusterMember->getAffiliation() as $affiliation) {
                            $options[$affiliation->getOrganisation()->getCountry()
                                ->getCountry()][$affiliation->getOrganisation()->getId()][$affiliation->getBranch()]
                                = $this->getOrganisationService()
                                ->parseOrganisationWithBranch(
                                    $affiliation->getBranch(),
                                    $affiliation->getOrganisation()
                                );
                        }
                    }
                }
            }
        }

        return $options;
    }
}