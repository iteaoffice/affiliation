<?php
/**
 * ITEA Office all rights reserved
 *
 * @category  Affiliation
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Service;

use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Invoice;
use Affiliation\Entity\Loi;
use Affiliation\Entity\LoiObject;
use Affiliation\Repository;
use Contact\Entity\Contact;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use General\Entity\Country;
use Invoice\Entity\Method;
use Organisation\Entity\Financial;
use Organisation\Entity\OParent;
use Organisation\Entity\Organisation;
use Organisation\Entity\Type;
use Program\Entity\Call\Call;
use Project\Entity\Funding\Funding;
use Project\Entity\Funding\Source;
use Project\Entity\Funding\Status;
use Project\Entity\Project;
use Project\Entity\Version\Version;
use Zend\Validator\File\MimeType;

/**
 * Class AffiliationService
 *
 * @package Affiliation\Service
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
     * @return null|Affiliation|object
     */
    public function findAffiliationById($id):?Affiliation
    {
        return $this->getEntityManager()->getRepository(Affiliation::class)->find($id);
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return bool
     */
    public function isSelfFunded(Affiliation $affiliation): bool
    {
        return $affiliation->getSelfFunded() === Affiliation::SELF_FUNDED
            && !is_null($affiliation->getDateSelfFunded());
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return bool
     */
    public function isActiveInVersion(Affiliation $affiliation): bool
    {
        return !$affiliation->getVersion()->isEmpty();
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return bool
     */
    public function isActive(Affiliation $affiliation): bool
    {
        return is_null($affiliation->getDateEnd());
    }

    /**
     * Checks if the affiliation has a DOA.
     *
     * @param Affiliation $affiliation
     *
     * @return bool
     */
    public function hasDoa(Affiliation $affiliation): bool
    {
        return !is_null($affiliation->getDoa());
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return bool
     */
    public function hasLoi(Affiliation $affiliation): bool
    {
        return !is_null($affiliation->getLoi());
    }

    /**
     * Upload a LOI to the system and store it for the user.
     *
     * @param array $file
     * @param Contact $contact
     * @param Affiliation $affiliation
     *
     * @return Loi
     */
    public function uploadLoi(array $file, Contact $contact, Affiliation $affiliation): Loi
    {
        $loiObject = new LoiObject();
        $loiObject->setObject(file_get_contents($file['tmp_name']));
        $loi = new Loi();
        $loi->setContact($contact);
        $loi->setAffiliation($affiliation);
        $loi->setSize($file['size']);

        $fileTypeValidator = new MimeType();
        $fileTypeValidator->isValid($file);
        $loi->setContentType($this->getGeneralService()->findContentTypeByContentTypeName($fileTypeValidator->type));

        $loiObject->setLoi($loi);
        $this->newEntity($loiObject);

        return $loiObject->getLoi();
    }

    /**
     * @param Contact $contact
     * @param Affiliation $affiliation
     * @return Loi
     */
    public function submitLoi(Contact $contact, Affiliation $affiliation): Loi
    {
        $loi = new Loi();
        $loi->setContact($contact);
        $loi->setApprover($contact);
        $loi->setDateSigned(new \DateTime());
        $loi->setDateApproved(new \DateTime());
        $loi->setAffiliation($affiliation);

        $this->newEntity($loi);

        return $loi;
    }


    /**
     * @return Affiliation[]
     */
    public function findNotValidatedSelfFundedAffiliation(): array
    {
        /** @var \Affiliation\Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);

        return $repository->findNotValidatedSelfFundedAffiliation();
    }

    /**
     * @return Query
     */
    public function findMissingAffiliationParent(): Query
    {
        /** @var \Affiliation\Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);

        return $repository->findMissingAffiliationParent();
    }

    /**
     * The VATnumber is first checked in the financial organisation. If that cannot be found we do a fallback
     * tot he organisation > financial
     *
     * @param Affiliation $affiliation
     *
     * @return string|null
     */
    public function parseVatNumber(Affiliation $affiliation):?string
    {
        $financial = $this->findOrganisationFinancial($affiliation);

        if (is_null($financial)) {
            return null;
        }

        return $financial->getVat();
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return null|\Organisation\Entity\Financial
     */
    public function findOrganisationFinancial(Affiliation $affiliation): ?Financial
    {
        $organisation = null;

        // We need to find the financial organisation and will do that in order of importance
        // We will first try to find the organisation is if we can find this return the financial in the end
        if (!is_null($affiliation->getParentOrganisation())) {
            // We have to deal with the parent system
            $parent = $affiliation->getParentOrganisation()->getParent();

            $organisation = $parent->getOrganisation();
            if (!$parent->getFinancial()->isEmpty()) {
                $organisation = $parent->getFinancial()->first()->getOrganisation();
            }
        }

        // Organisation still not found, try to find it via the old way
        if (is_null($organisation)) {
            $organisation = $affiliation->getOrganisation();

            if (!is_null($affiliation->getFinancial())) {
                $organisation = $affiliation->getFinancial()->getOrganisation();
            }
        }

        if (is_null($organisation) || is_null($organisation->getFinancial())) {
            return null;
        }

        return $organisation->getFinancial();
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return Contact
     */
    public function getFinancialContact(Affiliation $affiliation): ?Contact
    {
        if (is_null($affiliation->getFinancial())) {
            return null;
        }

        return $affiliation->getFinancial()->getContact();
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return array
     */
    public function canCreateInvoice(Affiliation $affiliation): array
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
            case !$this->isActive($affiliation):
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
     * @param Affiliation $affiliation
     * @param             $period
     * @param             $year
     *
     * @return Invoice[]|ArrayCollection
     */
    public function findAffiliationInvoiceByAffiliationPeriodAndYear(Affiliation $affiliation, $period, $year)
    {
        //Cast to int as some values can originate form templates (== twig > might be string)
        $year = (int)$year;
        $period = (int)$period;

        return $affiliation->getInvoice()->filter(
            function (Invoice $invoice) use ($period, $year) {
                return $invoice->getPeriod() === $period && $invoice->getYear() === $year;
            }
        );
    }


    /**
     * @return \Affiliation\Entity\Affiliation[]
     */
    public function findAffiliationInProjectLog()
    {
        /** @var Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);

        return $repository->findAffiliationInProjectLog();
    }

    /**
     * @param Affiliation $affiliation
     * @param  Version $version
     * @param             $year
     * @param             $period
     *
     * @return float
     */
    public function parseTotal(Affiliation $affiliation, Version $version, int $year, int $period = null): float
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
     * @param Version $version
     * @param int $year
     * @param int|null $period
     * @return float
     */
    public function parseContribution(Affiliation $affiliation, Version $version, int $year, int $period = null): float
    {
        //Depending on the invoice method we setup the contribution roles
        switch ($this->parseInvoiceMethod($version)) {
            case Method::METHOD_FUNDING:
                return $this->parseContributionBase($affiliation, $version, $year)
                    * $this->parseContributionFee(
                        $version,
                        $year,
                        $affiliation->getParentOrganisation()->getParent()
                    );
                break;
            case Method::METHOD_CONTRIBUTION:
            case Method::METHOD_PERCENTAGE:
                if (is_null($period)) {
                    return $this->parseContributionFee($version, $year);
                }

                return $this->parseContributionBase($affiliation, $version, $year)
                    * $this->parseContributionFactor($affiliation, $year, $period) * $this->parseContributionFee(
                        $version,
                        $year
                    );
                break;
        }


        return 0;
    }

    /**
     * @param Version $version
     * @return int
     */
    public function parseInvoiceMethod(Version $version): int
    {
        return (int)$this->getInvoiceService()->findInvoiceMethod($version->getProject()->getCall()->getProgram())
            ->getId();
    }

    /**
     * This function counts the effort or costs per affiliaton and returns the total per year. We pick the total amount out per given ear
     *
     * @param Affiliation $affiliation
     * @param  Version $version
     * @param  int $year
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
        switch ($this->parseInvoiceMethod($version)) {
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
            case Method::METHOD_FUNDING:
                $totalFunding
                    = $this->getVersionService()
                    ->findTotalFundingVersionByAffiliationAndVersion($affiliation, $version);

                $base = $totalFunding;
                break;
        }

        return $base;
    }

    /**
     * @param Version $version
     * @param $year
     * @param OParent|null $parent
     * @return float|int|string
     * @throws \Exception
     */
    public function parseContributionFee(Version $version, $year, OParent $parent = null)
    {
        //Cast to ints as some values can originate form templates (== twig > might be string)
        $year = (int)$year;

        /**
         * Based on the invoiceMethod we return or a percentage or the contribution
         */
        $fee = $this->getProjectService()->findProjectFeeByYear($year);

        switch ($this->parseInvoiceMethod($version)) {
            case Method::METHOD_PERCENTAGE:
                return $fee->getPercentage() / 100;
            case Method::METHOD_CONTRIBUTION:
                return $fee->getContribution();
            case Method::METHOD_FUNDING:
                if (is_null($parent)) {
                    throw new \InvalidArgumentException("Invoice cannot be funding when no parent is known");
                }

                //The payment factor for funding is the factor divided by 3 in three years
                return ($this->getParentService()->parseInvoiceFactor(
                    $parent,
                    $year
                ) / 100) / (3 * $this->getParentService()->parseMembershipFactor($parent));
            default:
                throw new \InvalidArgumentException(sprintf("Unknown contribution fee in %s", __FUNCTION__));
        }
    }

    /**
     * This function calculates the factor to which the contribution should be calculated.
     * We removed the switch on office to facilitate the contribution based invoicing
     *
     * @param Affiliation $affiliation
     * @param  int $year
     * @param  int $period
     *
     * @return float|int
     */
    public function parseContributionFactor(Affiliation $affiliation, $year, $period)
    {
        //Cast to int as some values can originate form templates (== twig > might be string)
        $year = (int)$year;
        $period = (int)$period;

        switch (true) {
            case !$this->isFundedInYear($affiliation, $year):
                return 0;
            case $this->getProjectService()->parseEndYear($affiliation->getProject()) === $year
                && $this->getProjectService()->parseEndMonth($affiliation->getProject()) <= 6:
                return $period === 1 ? 1 : 0;
            default:
                return 0.5;
        }
    }

    /**
     * @param Affiliation $affiliation
     * @param             $year
     *
     * @return bool
     */
    public function isFundedInYear(Affiliation $affiliation, $year): bool
    {
        //Cast to int as some values can originate form templates (== twig > might be string)
        $year = (int)$year;

        return is_null($this->getFundingInYear($affiliation, $year)) ? false
            : $this->getFundingInYear($affiliation, $year)->getStatus()->getId() === Status::STATUS_ALL_GOOD;
    }

    /**
     * @param Affiliation $affiliation
     * @param             $year
     * @param int $source
     *
     * @return null|Funding
     */
    public function getFundingInYear(Affiliation $affiliation, $year, $source = Source::SOURCE_OFFICE):?Funding
    {
        //Cast to ints as some values can originate form templates (== twig > might be string)
        $year = (int)$year;

        foreach ($affiliation->getFunding() as $funding) {
            if ((int)$funding->getDateStart()->format('Y') === $year && $funding->getSource()->getId() === $source) {
                return $funding;
            }
        };

        return null;
    }

    /**
     * @param Affiliation $affiliation
     * @param Version $version
     * @param int $year
     * @param int|null $period
     * @return float
     */
    public function parseBalance(Affiliation $affiliation, Version $version, int $year, int $period = null): float
    {
        return $this->parseContributionDue(
            $affiliation,
            $version,
            $year,
            $period
        ) - $this->parseContributionPaid($affiliation, $year, $period);
    }

    /**
     * @param Affiliation $affiliation
     * @param Version $version
     * @param int $year
     * @param int|null $period
     * @return float
     */
    public function parseContributionDue(
        Affiliation $affiliation,
        Version $version,
        int $year,
        int $period = null
    ): float {
        $contributionDue = 0;

        switch ($this->parseInvoiceMethod($version)) {
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
                        case is_null($period):
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

        return (float)$contributionDue;
    }

    /**
     * @param Affiliation $affiliation
     * @param $projectYear
     * @param int $year
     * @param int|null $period
     * @return int
     */
    public function parseContributionFactorDue(
        Affiliation $affiliation,
        $projectYear,
        int $year,
        int $period = null
    ): float {
        //Cast to ints as some values can originate form templates (== twig > might be string)
        switch (true) {
            case !$this->isFundedInYear($affiliation, $projectYear):
                return (float)0;
            case is_null($period) || $projectYear < $year:
                return (float)1; //in the past is always 100% due
            case $projectYear === $year && $period === 2:
                //Current year, and period 2 (so  first period might have been invoiced, due is now the 1-that value
                return (float)1 - $this->parseContributionFactor($affiliation, $year, $period);
            default:
                return (float)0;
        }
    }

    /**
     * Sum up the amount paid already by the affiliation in the previous period
     * Exclude of course the credit notes
     *
     * @param Affiliation $affiliation
     * @param int $year
     * @param int|null $period
     *
     * @return float
     */
    public function parseContributionPaid(Affiliation $affiliation, int $year, int $period = null): float
    {
        $contributionPaid = 0;

        //Sum the invoiced amount of all invoices for this affiliation
        foreach ($affiliation->getInvoice() as $invoice) {
            //Filter invoices of previous years or this year, but the previous period and already sent to accounting
            if (!is_null($invoice->getInvoice()->getDayBookNumber())) {
                if (!is_null($period)) {
                    //When we have a period, we also take the period fo the current year into account
                    if ($invoice->getYear() < $year || ($invoice->getPeriod() < $period && $invoice->getYear() === $year)) {
                        $contributionPaid += $invoice->getAmountInvoiced();
                    }
                } else {
                    //We have no period, so only count the invoices of the last year
                    if ($invoice->getYear() < $year) {
                        $contributionPaid += $invoice->getAmountInvoiced();
                    }
                }
            }
        }

        return (float)$contributionPaid;
    }

    /**
     * @param Project $project
     * @param int $which
     *
     * @return Affiliation[]|ArrayCollection
     */
    public function findAffiliationByProjectAndWhich(
        Project $project,
        $which = self::WHICH_ONLY_ACTIVE
    ): ArrayCollection {
        /** @var \Affiliation\Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);
        $affiliations = $repository->findAffiliationByProjectAndWhich($project, $which);

        if (is_null($affiliations)) {
            $affiliations = [];
        }

        return new ArrayCollection($affiliations);
    }

    /**
     * @param Version $version
     * @param Country $country
     * @param int $which
     *
     * @return Affiliation[]|ArrayCollection
     */
    public function findAffiliationByProjectVersionAndCountryAndWhich(
        Version $version,
        Country $country,
        $which = self::WHICH_ONLY_ACTIVE
    ): ArrayCollection {
        /** @var \Affiliation\Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);
        $affiliations = $repository->findAffiliationByProjectVersionAndCountryAndWhich($version, $country, $which);

        if (is_null($affiliations)) {
            $affiliations = [];
        }

        return new ArrayCollection($affiliations);
    }

    /**
     * @param Version $version
     * @param int $which
     *
     * @return ArrayCollection|Affiliation[]
     */
    public function findAffiliationByProjectVersionAndWhich(Version $version, $which = self::WHICH_ALL): ArrayCollection
    {
        /** @var \Affiliation\Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);
        $affiliations = $repository->findAffiliationByProjectVersionAndWhich($version, $which);

        if (is_null($affiliations)) {
            $affiliations = [];
        }

        return new ArrayCollection($affiliations);
    }

    /**
     * @param OParent $parent
     * @param int $which
     * @return ArrayCollection|Affiliation[]
     */
    public function findAffiliationByParentAndWhich(OParent $parent, $which = self::WHICH_ONLY_ACTIVE): ArrayCollection
    {
        /** @var \Affiliation\Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);
        $affiliations = $repository->findAffiliationByParentAndWhich($parent, $which);

        if (is_null($affiliations)) {
            $affiliations = [];
        }

        return new ArrayCollection($affiliations);
    }

    /**
     * @param Project $project
     * @param int $criterion
     * @param int $which
     * @return ArrayCollection|Affiliation[]
     */
    public function findAffiliationByProjectAndWhichAndCriterion(
        Project $project,
        int $criterion,
        int $which = self::WHICH_ONLY_ACTIVE
    ): ArrayCollection {
        /** @var Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);

        return new ArrayCollection($repository->findAffiliationByProjectAndWhichAndCriterion(
            $project,
            $criterion,
            $which
        ));
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
    ): int {
        /** @var \Affiliation\Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);

        return $repository->findAmountOfAffiliationByProjectAndCountryAndWhich($project, $country, $which);
    }

    /**
     * @param Country $country
     * @param Call $call
     *
     * @return int
     */
    public function findAmountOfAffiliationByCountryAndCall(
        Country $country,
        Call $call
    ): int {
        /** @var \Affiliation\Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);

        return $repository->findAmountOfAffiliationByCountryAndCall($country, $call);
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
    ): int {
        /** @var \Affiliation\Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);

        return $repository->findAmountOfAffiliationByProjectVersionAndCountryAndWhich($version, $country, $which);
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
    ): ArrayCollection {
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
     * @param int $which
     *
     * @return \General\Entity\Country[]
     */
    public function findAffiliationCountriesByProjectAndWhich(
        Project $project,
        $which = self::WHICH_ONLY_ACTIVE
    ): array {
        /** @var \Affiliation\Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);

        /**
         * @var $affiliations Affiliation[]
         */
        $affiliations = $repository->findAffiliationByProjectAndWhich($project, $which);
        $result = [];
        foreach ($affiliations as $affiliation) {
            $country = $affiliation->getOrganisation()->getCountry();

            $result[$country->getCountry()] = $country;
        }

        ksort($result);

        return $result;
    }

    /**
     * @param Project $project
     * @param Country $country
     * @param int $which
     *
     * @return Affiliation[]|ArrayCollection
     */
    public function findAffiliationByProjectAndCountryAndWhich(
        Project $project,
        Country $country,
        $which = self::WHICH_ONLY_ACTIVE
    ): ArrayCollection {
        /** @var \Affiliation\Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);
        $affiliations = $repository->findAffiliationByProjectAndCountryAndWhich($project, $country, $which);

        if (is_null($affiliations)) {
            $affiliations = [];
        }

        return new ArrayCollection($affiliations);
    }

    /**
     * @param Organisation $organisation
     *
     * @deprecated
     * @return ArrayCollection|Affiliation[]
     */
    public function findAffiliationByOrganisation(
        Organisation $organisation
    ): ArrayCollection {
        /** @var \Affiliation\Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);

        return new ArrayCollection($repository->findAffiliationByOrganisation($organisation));
    }

    /**
     * @param Organisation $organisation
     *
     * @return ArrayCollection|Affiliation[]
     */
    public function findAffiliationByOrganisationViaParentOrganisation(
        Organisation $organisation
    ): ArrayCollection {
        /** @var \Affiliation\Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);

        return new ArrayCollection($repository->findAffiliationByOrganisationViaParentOrganisation($organisation));
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
            return null;
        }
        foreach ($project->getAffiliation() as $affiliation) {
            if ($which === self::WHICH_ONLY_ACTIVE && !$this->isActive($affiliation)) {
                continue;
            }
            if ($which === self::WHICH_ONLY_INACTIVE && $this->isActive($affiliation)) {
                continue;
            }

            //Do a match on the organisation or technical contact
            if ($affiliation->getContact() === $contact
                || $affiliation->getOrganisation()->getId() ===
                $contact->getContactOrganisation()->getOrganisation()->getId()
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
        /** @var \Affiliation\Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);

        return $repository->findAffiliationWithMissingDoa();
    }

    /**
     * Give a list of all affiliations which do not have a doa.
     *
     * @return Query
     */
    public function findAffiliationWithMissingLoi()
    {
        /** @var \Affiliation\Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);

        return $repository->findAffiliationWithMissingLoi();
    }

    /**
     * Deactivate an affiliation.
     *
     * @param Affiliation $affiliation
     */
    public function deactivateAffiliation(
        Affiliation $affiliation
    ) {
        $affiliation->setDateEnd(new \DateTime());
        $this->updateEntity($affiliation);
        /*
         * Remove the current cost and effort of the affiliation
         */
        foreach ($affiliation->getEffort() as $effort) {
            $this->getProjectService()->removeEntity($effort);
        }
        /*
         * Remove the current cost and effort of the affiliation
         */
        foreach ($affiliation->getCost() as $cost) {
            $this->getProjectService()->removeEntity($cost);
        }
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return bool
     */
    public function affiliationHasCostOrEffortInDraft(
        Affiliation $affiliation
    ): bool {
        $effortInDraft = 0;
        foreach ($affiliation->getEffort() as $effort) {
            $effortInDraft += $effort->getEffort();
        }

        $costInDraft = 0;
        foreach ($affiliation->getCost() as $cost) {
            $costInDraft += $cost->getCosts();
        }

        return $effortInDraft > 0 || $costInDraft > 0;
    }

    /**
     * Reactivate an affiliation.
     *
     * @param Affiliation $affiliation
     */
    public function reactivateAffiliation(
        Affiliation $affiliation
    ) {
        $affiliation->setDateEnd(null);
        $this->updateEntity($affiliation);
    }

    /**
     * @return array
     */
    public function findLogAffiliations()
    {
        /** @var \Affiliation\Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);

        return $repository->findAffiliationInProjectLog();
    }

    /**
     * @param Affiliation $baseAffiliation
     *
     * @return array
     */
    public function parseRenameOptions(
        Affiliation $baseAffiliation
    ): array {
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
                        ->getCountry()][$affiliation->getOrganisation()
                        ->getId()][$affiliation->getBranch()]
                        = $this->getOrganisationService()
                        ->parseOrganisationWithBranch(
                            $affiliation->getBranch(),
                            $affiliation->getOrganisation()
                        );
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
                = $this->getOrganisationService()->parseOrganisationWithBranch(
                    $contact->getContactOrganisation()
                    ->getBranch(),
                    $contact->getContactOrganisation()->getOrganisation()
                );
        }
        /**
         * Add the contact organisation (from the organisation)
         */
        if (!is_null($organisation->getContactOrganisation())) {
            /**
             * Add the contact organisation
             */
            if (!is_null($contact->getContactOrganisation())) {
                $options[$contact->getContactOrganisation()->getOrganisation()->getCountry()
                    ->getCountry()][$contact->getContactOrganisation()->getOrganisation()->getId()]
                [$contact->getContactOrganisation()->getBranch()]
                    = $this->getOrganisationService()->parseOrganisationWithBranch(
                        $contact->getContactOrganisation()
                        ->getBranch(),
                        $contact->getContactOrganisation()->getOrganisation()
                    );
            }
            /**
             * Go over the clusters
             */
            foreach ($organisation->getContactOrganisation() as $contactOrganisation) {
                foreach ($contactOrganisation->getOrganisation()->getCluster() as $cluster) {
                    foreach ($cluster->getMember() as $clusterMember) {
                        foreach ($clusterMember->getAffiliation() as $affiliation) {
                            $options[$affiliation->getOrganisation()->getCountry()
                                ->getCountry()][$affiliation->getOrganisation()
                                ->getId()][$affiliation->getBranch()]
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
