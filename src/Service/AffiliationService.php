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
use Contact\Controller\Plugin\ContactActions;
use Contact\Entity\Contact;
use Contact\Entity\ContactOrganisation;
use Deeplink\View\Helper\DeeplinkLink;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use General\Entity\Country;
use General\Entity\Currency;
use Invoice\Entity\Method;
use Organisation\Entity\Financial;
use Organisation\Entity\OParent;
use Organisation\Entity\Organisation;
use Organisation\Entity\Type;
use Program\Entity\Call\Call;
use Program\Entity\Program;
use Project\Entity\Contract\Version as ContractVersion;
use Project\Entity\Funding\Funding;
use Project\Entity\Funding\Source;
use Project\Entity\Funding\Status;
use Project\Entity\Project;
use Project\Entity\Version\Version;
use Zend\Mvc\Controller\PluginManager;
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
    public const WHICH_ALL = 1;
    public const WHICH_ONLY_ACTIVE = 2;
    public const WHICH_ONLY_INACTIVE = 3;

    /**
     * @param $id
     *
     * @return null|Affiliation|object
     */
    public function findAffiliationById($id): ?Affiliation
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
            && !\is_null($affiliation->getDateSelfFunded());
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
     * Checks if the affiliation has a DOA.
     *
     * @param Affiliation $affiliation
     *
     * @return bool
     */
    public function hasDoa(Affiliation $affiliation): bool
    {
        return null !== $affiliation->getDoa();
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return bool
     */
    public function hasLoi(Affiliation $affiliation): bool
    {
        return null !== $affiliation->getLoi();
    }

    /**
     * Returns true when the affiliation has a contract with a version
     *
     * @param Affiliation $affiliation
     *
     * @return bool
     */
    public function useActiveContract(Affiliation $affiliation): bool
    {
        /** Only use the contract is the flag (invoice method) is set */
        if (null === $affiliation->getInvoiceMethod()
            || $affiliation->getInvoiceMethod()->getId() !== Method::METHOD_PERCENTAGE_CONTRACT
        ) {
            return false;
        }

        if ($affiliation->getContract()->isEmpty()) {
            return false;
        }

        foreach ($affiliation->getContract() as $contract) {
            if (!$contract->getVersion()->isEmpty()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Upload a LOI to the system and store it for the user.
     *
     * @param array       $file
     * @param Contact     $contact
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
    public function parseVatNumber(Affiliation $affiliation): ?string
    {
        $financial = $this->findOrganisationFinancial($affiliation);

        if (null === $financial) {
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
        if (!\is_null($affiliation->getParentOrganisation())) {
            // We have to deal with the parent system
            $parent = $affiliation->getParentOrganisation()->getParent();

            $organisation = $parent->getOrganisation();
            if (!$parent->getFinancial()->isEmpty()) {
                $organisation = $parent->getFinancial()->first()->getOrganisation();
            }
        }

        // Organisation still not found, try to find it via the old way
        if (null === $organisation) {
            $organisation = $affiliation->getOrganisation();

            if (!\is_null($affiliation->getFinancial())) {
                $organisation = $affiliation->getFinancial()->getOrganisation();
            }
        }

        if (\is_null($organisation) || \is_null($organisation->getFinancial())) {
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
        if (\is_null($affiliation->getFinancial())) {
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
            case \is_null($affiliation->getFinancial()):
                $errors[] = 'No financial organisation (affiliation financial) set for this partner';
                break;
            case !$this->isActive($affiliation):
                $errors[] = 'Partner is de-activated';
                break;
            case \is_null($affiliation->getFinancial()->getOrganisation()->getFinancial()):
                $errors[] = 'No financial information set for this organisation';
                break;
            case \is_null($affiliation->getFinancial()->getContact()):
                $errors[] = 'No financial contact set for this organisation';
                break;
        }

        return $errors;
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return bool
     */
    public function isActive(Affiliation $affiliation): bool
    {
        return \is_null($affiliation->getDateEnd());
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

    public function findAffiliationInProjectLog(): array
    {
        /** @var Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);

        return $repository->findAffiliationInProjectLog();
    }

    public function parseTotal(
        Affiliation $affiliation,
        Version $version,
        int $year,
        ?int $period = null
    ): float {
        return $this->parseContribution($affiliation, $version, null, $year, $period, false) + $this->parseBalance(
            $affiliation,
            $version,
            $year,
            $period
        );
    }

    public function parseContribution(
        Affiliation $affiliation,
        ?Version $version,
        ?ContractVersion $contractVersion,
        int $year,
        ?int $period = null,
        bool $useContractData = true,
        bool $omitExchangeRate = false,
        ?int $exchangeRateYear = null
    ): float {

        //The percentage method can also be done on contract base, but therefore we need to know if we want to have it and if we have a contract
        $invoiceMethod = $this->parseInvoiceMethod($affiliation, $useContractData);

        switch ($invoiceMethod) {
            case Method::METHOD_FUNDING:
            case Method::METHOD_FUNDING_MEMBER:
                return $this->parseContributionBase($affiliation, $version, null, $year, false)
                    * $this->parseContributionFee(
                        $affiliation,
                        $year,
                        $affiliation->getParentOrganisation()->getParent()
                    );


            case Method::METHOD_CONTRIBUTION:
            case Method::METHOD_PERCENTAGE:
                if (null === $version) {
                    return 0;
                }

                //@todo: have no idea what this statement is doing here, should be releated to the calculation of PENTA
                //if (null !== $period) {
                //return $this->parseContributionFee($affiliation, $year);
                //}


                return $this->parseContributionBase($affiliation, $version, null, $year, false)
                    * $this->parseContributionFactor($affiliation, $year, $period) * $this->parseContributionFee(
                        $affiliation,
                        $year
                    );

            case Method::METHOD_PERCENTAGE_CONTRACT:
                if (null === $contractVersion) {
                    return 0;
                }

                $fee = $this->parseContributionBase($affiliation, null, $contractVersion, $year)
                    * $this->parseContributionFactor($affiliation, $year, $period) * $this->parseContributionFee(
                        $affiliation,
                        $year
                    );

                if ($omitExchangeRate) {
                    return $fee;
                }

                return $fee / $this->parseExchangeRate($affiliation, $exchangeRateYear ?? (int)date('Y'), $period);
        }

        return (float)0;
    }

    public function parseInvoiceMethod(Affiliation $affiliation, bool $useContractData = true): int
    {
        //When the partnre has an invoice method defined, we only return it when the $useContractData is set to
        //True and otherwise we will return the normal
        if (null !== $affiliation->getInvoiceMethod()) {
            if ($affiliation->getInvoiceMethod()->getId() === Method::METHOD_PERCENTAGE_CONTRACT) {
                if ($useContractData) {
                    return Method::METHOD_PERCENTAGE_CONTRACT;
                }

                return Method::METHOD_PERCENTAGE;
            }

            return $affiliation->getInvoiceMethod()->getId();
        }

        $invoiceMethod = (int)$this->getInvoiceService()->findInvoiceMethod(
            $affiliation->getProject()->getCall()->getProgram()
        )->getId();

        //The percentage method depends on the fact if we have a contract or not.
        $contractVersion = $this->getContractService()->findLatestContractVersionByAffiliation($affiliation);

        //Force the invoiceMethod back to _percentage_ when we don't want to use contract data
        if ($invoiceMethod === Method::METHOD_PERCENTAGE_CONTRACT && (null === $contractVersion || !$useContractData)) {
            $invoiceMethod = Method::METHOD_PERCENTAGE;
        }

        return $invoiceMethod;
    }

    public function parseContributionBase(
        Affiliation $affiliation,
        ?Version $version,
        ?ContractVersion $contractVersion,
        int $year,
        bool $useContractData = true
    ): float {
        $base = 0;

        //The percentage method can also be done on contract base, but therefore we need to know if we want to have it and if we have a contract
        $invoiceMethod = $this->parseInvoiceMethod($affiliation, $useContractData);

        /**
         * The base (the sum of the costs or effort in the version depends on the invoiceMethod (percentage === 'costs', contribution === 'effort')
         */
        switch ($invoiceMethod) {
            case Method::METHOD_PERCENTAGE:
                if (null === $version) {
                    throw new \InvalidArgumentException(
                        "The contract version cannot be null for parsing the contribution base"
                    );
                }

                $costsPerYear
                    = $this->getVersionService()
                    ->findTotalCostVersionByAffiliationAndVersionPerYear($affiliation, $version);
                if (array_key_exists($year, $costsPerYear)) {
                    return (float)$costsPerYear[$year];
                }

                break;
            case Method::METHOD_PERCENTAGE_CONTRACT:
                if (null === $contractVersion) {
                    throw new \InvalidArgumentException(
                        "The contract version cannot be null for parsing the contribution base"
                    );
                }

                $costsPerYear
                    = $this->getContractService()
                    ->findTotalCostVersionByAffiliationAndVersionPerYear($affiliation, $contractVersion);
                if (array_key_exists($year, $costsPerYear)) {
                    return (float)$costsPerYear[$year];
                }

                break;
            case Method::METHOD_CONTRIBUTION:
                $effortPerYear
                    = $this->getVersionService()
                    ->findTotalEffortVersionByAffiliationAndVersionPerYear($affiliation, $version);
                if (array_key_exists($year, $effortPerYear)) {
                    return (float)$effortPerYear[$year];
                }
                break;
            case Method::METHOD_FUNDING_MEMBER:
            case Method::METHOD_FUNDING:
                return $this->getVersionService()
                    ->findTotalFundingVersionByAffiliationAndVersion($affiliation, $version);
        }

        return (float)$base;
    }

    /**
     * @param Affiliation  $affiliation
     * @param              $year
     * @param OParent|null $parent
     *
     * @return float|int|string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function parseContributionFee(Affiliation $affiliation, $year, OParent $parent = null)
    {
        //Cast to int as some values can originate form templates (== twig > might be string)
        $year = (int)$year;

        /**
         * Based on the invoiceMethod we return or a percentage or the contribution
         */
        $fee = $this->getProjectService()->findProjectFeeByYear($year);

        if (null === $fee) {
            return 0;
        }

        switch ($this->parseInvoiceMethod($affiliation)) {
            case Method::METHOD_PERCENTAGE:
            case Method::METHOD_PERCENTAGE_CONTRACT:
                return $fee->getPercentage() / 100;
            case Method::METHOD_CONTRIBUTION:
                return $fee->getContribution();
            case Method::METHOD_FUNDING_MEMBER:
                if (null === $parent) {
                    throw new \InvalidArgumentException("Invoice cannot be funding when no parent is known");
                }

                $invoiceFactor = $this->getParentService()->parseInvoiceFactor(
                    $parent,
                    $affiliation->getProject()->getCall()->getProgram()
                ) / 100;

                if ($parent->isMember()) {
                    $membershipFactor = $this->getParentService()->parseMembershipFactor($parent);

                    return $invoiceFactor / (3 * $membershipFactor);
                }

                $doaFactor = $this->getParentService()->parseDoaFactor(
                    $parent,
                    $affiliation->getProject()->getCall()->getProgram()
                );

                if ($doaFactor === 0) {
                    return 0;
                }

                //The payment factor for funding is the factor divided by 3 in three years
                return $invoiceFactor / (3 * $doaFactor);


            case Method::METHOD_FUNDING:
                if (null === $parent) {
                    throw new \InvalidArgumentException("Invoice cannot be funding when no parent is known");
                }

                //Funding PENTA === 1.5 %
                $invoiceFactor = 1.5 / 100;

                $doaFactor = $this->getParentService()->parseDoaFactor(
                    $parent,
                    $affiliation->getProject()->getCall()->getProgram()
                );

                if ($parent->isMember() || $doaFactor > 0) {
                    return $invoiceFactor / 3;
                }

                return 0;

            default:
                throw new \InvalidArgumentException(sprintf("Unknown contribution fee in %s", __FUNCTION__));
        }
    }

    /**
     * This function calculates the factor to which the contribution should be calculated.
     * We removed the switch on office to facilitate the contribution based invoicing
     *
     * @param Affiliation $affiliation
     * @param int         $year
     * @param int|null    $period
     *
     * @return float|int
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function parseContributionFactor(Affiliation $affiliation, int $year, ?int $period = null): float
    {
        switch (true) {
            case !$this->isFundedInYear($affiliation, $year):
                return (float)0;
            case \is_null($period):
                return 1;
            case $this->getProjectService()->parseEndYear($affiliation->getProject()) === $year
                && $this->getProjectService()->parseEndMonth($affiliation->getProject()) <= 6:
                return (float)($period === 1 ? 1 : 0);
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

        return \is_null($this->getFundingInYear($affiliation, $year)) ? false
            : $this->getFundingInYear($affiliation, $year)->getStatus()->getId() === Status::STATUS_ALL_GOOD;
    }

    /**
     * @param Affiliation $affiliation
     * @param             $year
     * @param int         $source
     *
     * @return null|Funding
     */
    public function getFundingInYear(Affiliation $affiliation, $year, $source = Source::SOURCE_OFFICE): ?Funding
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
     * @param int         $year
     * @param int|null    $period
     *
     * @return float
     */
    public function parseExchangeRate(
        Affiliation $affiliation,
        int $year,
        ?int $period = null
    ): float {

        //The percentage method can also be done on contract base, but therefore we need to know if we want to have it and if we have a contract
        $invoiceMethod = $this->parseInvoiceMethod($affiliation);

        /**
         * The base (the sum of the costs or effort in the version depends on the invoiceMethod (percentage === 'costs', contribution === 'effort')
         */
        switch ($invoiceMethod) {
            case Method::METHOD_PERCENTAGE:
            case Method::METHOD_CONTRIBUTION:
            case Method::METHOD_FUNDING:
                return (float)1;
            case Method::METHOD_PERCENTAGE_CONTRACT:
                //The percentage method depends on the fact if we have a contract or not.
                $contractVersion = $this->getContractService()->findLatestContractVersionByAffiliation($affiliation);

                $exchangeRate = $this->getContractService()->findExchangeRateInInvoicePeriod(
                    $contractVersion->getContract()->getCurrency(),
                    $year,
                    $period
                );

                //Fallback to 1 when we find no exchange rate
                if (null === $exchangeRate) {
                    return 1;
                }

                return (float)$exchangeRate->getRate();
        }

        return (float)1;
    }

    public function parseBalance(
        Affiliation $affiliation,
        Version $version,
        int $year,
        ?int $period = null
    ): float {
        //Based on the invoice method we will only have a balance when we are working with percentage
        $invoiceMethod = $this->parseInvoiceMethod($affiliation);
        if (\in_array($invoiceMethod, [Method::METHOD_FUNDING, Method::METHOD_FUNDING_MEMBER], true)) {
            return 0;
        }

        return $this->parseContributionDue(
            $affiliation,
            $version,
            $year,
            $period
        ) - $this->parseContributionPaid($affiliation, $year, $period);
    }

    /**
     * @param Affiliation $affiliation
     * @param Version     $version
     * @param int         $year
     * @param int|null    $period
     *
     * @return float
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function parseContributionDue(
        Affiliation $affiliation,
        Version $version,
        int $year,
        int $period = null
    ): float {
        $contributionDue = 0;

        //The percentage method can also be done on contract base, but therefore we need to know if we want to have it and if we have a contract
        $invoiceMethod = $this->parseInvoiceMethod($affiliation);

        switch ($invoiceMethod) {
            case Method::METHOD_PERCENTAGE:
            case Method::METHOD_PERCENTAGE_CONTRACT:
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
                        case \is_null($period):
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
     * @param             $projectYear
     * @param int         $year
     * @param int|null    $period
     *
     * @return float
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function parseContributionFactorDue(
        Affiliation $affiliation,
        $projectYear,
        int $year,
        int $period = null
    ): float {
        //Cast to int as some values can originate form templates (== twig > might be string)
        switch (true) {
            case !$this->isFundedInYear($affiliation, $projectYear):
                return (float)0;
            case \is_null($period) || $projectYear < $year:
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
     * @param int         $year
     * @param int|null    $period
     *
     * @return float
     */
    public function parseContributionPaid(Affiliation $affiliation, int $year, int $period = null): float
    {
        $contributionPaid = 0;

        //Sum the invoiced amount of all invoices for this affiliation
        foreach ($affiliation->getInvoice() as $invoice) {
            //Filter invoices of previous years or this year, but the previous period and already sent to accounting
            if (!\is_null($invoice->getInvoice()->getDayBookNumber())) {
                if (!\is_null($period)) {
                    //When we have a period, we also take the period fo the current year into account
                    if ($invoice->getYear() < $year
                        || ($invoice->getPeriod() < $period
                            && $invoice->getYear() === $year)
                    ) {
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
     * @param Affiliation     $affiliation
     * @param ContractVersion $version
     * @param int             $year
     * @param int|null        $period
     *
     * @return float
     */
    public function parseContractTotal(
        Affiliation $affiliation,
        ContractVersion $version,
        int $year,
        ?int $period = null
    ): float {
        return $this->parseTotalByInvoiceLines($affiliation, $version, $year, $period);
    }

    /**
     * @param Affiliation     $affiliation
     * @param ContractVersion $contractVersion
     * @param int             $year
     * @param int|null        $period
     *
     * @return float
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function parseTotalByInvoiceLines(
        Affiliation $affiliation,
        ContractVersion $contractVersion,
        int $year,
        ?int $period = null
    ): float {
        $total = 0.0;

        foreach ($this->findInvoiceLines($affiliation, $contractVersion, $year, $period) as $line) {
            $total += $line->lineTotal;
        }

        return $total;
    }

    public function findInvoiceLines(
        Affiliation $affiliation,
        ContractVersion $contractVersion,
        int $year,
        ?int $period = null
    ): array {
        $lines = [];

        $currency = $contractVersion->getContract()->getCurrency();

        if (null === $currency) {
            return $lines;
        }

        $exchangeRate = $this->contractService->findExchangeRateInInvoicePeriod($currency, $year, $period);

        if (null === $exchangeRate) {
            return $lines;
        }

        $yearAndPeriod = [];
        //Fist go over the years and the period to collect the $years and the corresponding periods
        for ($otherYear = $year - 6; $otherYear <= $year; $otherYear++) {
            //Force the period to null for the lower years, or when the partner has not been invoiced yet
            foreach ([1, 2] as $invoicePeriod) {
                //Skip the second period if we are dealing with the first period
                if ($otherYear === $year && $period === 1 && $invoicePeriod === 2) {
                    continue;
                }

                if (!$this->affiliationHasInvoiceInYearAndPeriod($affiliation, $otherYear, $invoicePeriod)) {
                    $yearAndPeriod[$otherYear][] = $invoicePeriod;
                }
            }
        }

        foreach ($yearAndPeriod as $invoiceYear => $invoicePeriod) {
            if (\count($invoicePeriod) === 2) {
                $invoicePeriod = null;
            } else {
                $invoicePeriod = \array_pop($invoicePeriod);
            }

            //Derive the contribution
            $contribution = $this->parseContribution(
                $affiliation,
                null,
                $contractVersion,
                $invoiceYear,
                $invoicePeriod,
                true,
                false,
                $year
            );
            if ($contribution !== 0.0) {
                $line = new \stdClass();
                $line->year = $invoiceYear;
                $line->period = $invoicePeriod;
                $line->periodOrdinal = $invoiceYear . (null === $invoicePeriod ? '' : '-' . $invoicePeriod . 'H');
                $line->description = $this->parseInvoiceLine(
                    $affiliation,
                    $invoiceYear,
                    $currency,
                    $invoicePeriod
                );
                $line->lineTotal = $contribution;

                $lines[] = $line;
            }
        }

        return $lines;
    }

    public function affiliationHasInvoiceInYearAndPeriod(Affiliation $affiliation, int $year, int $period): bool
    {
        $hasInvoice = false;

        foreach ($affiliation->getInvoice() as $affiliationInvoice) {
            //This is a check for the current year, we only need to check if the period has not been invoiced yet in this year
            if ($affiliationInvoice->hasYearAndPeriod($year, $period)) {
                $hasInvoice = true;
            }
        }

        return $hasInvoice;
    }

    public function parseInvoiceLine(
        Affiliation $affiliation,
        int $year,
        Currency $currency,
        ?int $period = null
    ): string {
        $contributionFactor = $this->parseContributionFactor($affiliation, $year, $period);

        return sprintf(
            $this->translate("txt-%s%%-contribution-for-%s"),
            number_format($contributionFactor * 100, 0),
            $year,
            $currency->getSymbol()
        );
    }

    /**
     * This function calculates the amount invoiced in a given year
     *
     * @param Affiliation $affiliation
     * @param int         $year
     *
     * @return float
     */
    public function parseAmountInvoicedInYearByAffiliation(Affiliation $affiliation, int $year): float
    {
        $amountInvoiced = 0;

        foreach ($affiliation->getInvoice() as $affiliationInvoice) {
            if ($affiliationInvoice->getYear() === $year) {
                $amountInvoiced += $affiliationInvoice->getAmountInvoiced();
            }
        }

        return $amountInvoiced;
    }

    public function hasInvoiceInPast(Affiliation $affiliation, int $year): bool
    {
        $hasBeenInvoicedInPast = false;

        foreach ($affiliation->getInvoice() as $affiliationInvoice) {
            if ($affiliationInvoice->getYear() < $year) {
                $hasBeenInvoicedInPast = true;
            }
        }

        return $hasBeenInvoicedInPast;
    }

    /**
     * @param Project $project
     * @param int     $which
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

        if (null === $affiliations) {
            $affiliations = [];
        }

        return new ArrayCollection($affiliations);
    }

    /**
     * @param Version $version
     * @param Country $country
     * @param int     $which
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

        if (null === $affiliations) {
            $affiliations = [];
        }

        return new ArrayCollection($affiliations);
    }

    /**
     * @param Version $version
     * @param int     $which
     *
     * @return ArrayCollection|Affiliation[]
     */
    public function findAffiliationByProjectVersionAndWhich(Version $version, $which = self::WHICH_ALL): ArrayCollection
    {
        /** @var \Affiliation\Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);
        $affiliations = $repository->findAffiliationByProjectVersionAndWhich($version, $which);

        if (null === $affiliations) {
            $affiliations = [];
        }

        return new ArrayCollection($affiliations);
    }

    /**
     * @param OParent $parent
     * @param Program $program
     * @param int     $which
     *
     * @return ArrayCollection
     */
    public function findAffiliationByParentAndProgramAndWhich(
        OParent $parent,
        Program $program,
        $which = self::WHICH_ONLY_ACTIVE
    ): ArrayCollection {
        /** @var \Affiliation\Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);
        $affiliations = $repository->findAffiliationByParentAndProgramAndWhich($parent, $program, $which);

        if (null === $affiliations) {
            $affiliations = [];
        }

        return new ArrayCollection($affiliations);
    }

    /**
     * @param Affiliation  $affiliation
     * @param Contact|null $contact
     * @param string|null  $email
     *
     * @return Contact
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function addAssociate(Affiliation $affiliation, Contact $contact = null, string $email = null): Contact
    {
        if (null === $contact && empty($email)) {
            throw new \InvalidArgumentException("Both contact and email address cannot be null to add an associate");
        }

        //Boolean to see if the contact whas known already
        $hasContact = true;

        //When we have an email, create a contact based also on the $affiliation
        if (!empty($email)) {
            //Try to find the contact based on the email address
            $contact = $this->getContactService()->findContactByEmail($email);


            //If we don't find the contact by email, we will create it.
            if (null === $contact) {
                $hasContact = false;

                /** @var PluginManager $controllerPluginManager */
                $controllerPluginManager = $this->getServiceLocator()->get('ControllerPluginManager');
                /** @var ContactActions $contactActions */
                $contactActions = $controllerPluginManager->get(ContactActions::class);

                $contact = $contactActions->createContact(
                    $email,
                    sprintf(
                        "Created via invitation for %s in %s",
                        $affiliation->getOrganisation(),
                        $affiliation->getProject()
                    ),
                    $email
                );
            }

            //Check if the contact has already an organisation
            if (null === $contact->getContactOrganisation()) {
                $contactOrganisation = new ContactOrganisation();
                $contactOrganisation->setContact($contact);
                $contactOrganisation->setOrganisation($affiliation->getOrganisation());
                $this->getContactService()->save($contactOrganisation);

                $this->getContactService()->addNoteToContact(
                    'Set organisation to ' . $affiliation->getOrganisation(),
                    'Account upgrade via associate',
                    $contact
                );
            }
        }

        $affiliation->addAssociate($contact);
        $this->updateEntity($affiliation);

        $this->getAdminService()->refreshAccessRolesByContact($contact);

        //Send an email to the invitee

        $this->emailService->setWebInfo("/project/add_associate:associate");
        $this->emailService->addTo($contact);

        /**
         * @var $deeplinkLink DeeplinkLink
         */
        $deeplinkLink = $this->getServiceLocator()->get('ViewHelperManager')->get('deeplinkLink');

        //The contact can be found. But we forward him to the profile-edit
        $targetProfile = $this->getDeeplinkService()->createTargetFromRoute('community/contact/profile/edit');
        $deeplinkProfile = $this->getDeeplinkService()->createDeeplink($targetProfile, $contact);
        $targetPartner = $this->getDeeplinkService()->createTargetFromRoute('community/affiliation/affiliation');
        $deeplinkPartner = $this->getDeeplinkService()->createDeeplink(
            $targetPartner,
            $contact,
            null,
            $affiliation->getId()
        );

        $this->emailService->setTemplateVariable('edit_profile_url', $deeplinkLink($deeplinkProfile, 'view', 'link'));
        $this->emailService->setTemplateVariable('partner_page_url', $deeplinkLink($deeplinkPartner, 'view', 'link'));
        $this->emailService->setTemplateVariable('project', $affiliation->getProject()->parseFullName());
        $this->emailService->setTemplateVariable('organisation', $affiliation->parseBranchedName());
        $this->emailService->setTemplateVariable('has_contact', $hasContact);
        $this->emailService->setTemplateVariable('technical_contact', $affiliation->getContact()->parseFullName());
        $this->emailService->setTemplateVariable(
            'technical_contact_organisation',
            $this->getContactService()->parseOrganisation($affiliation->getContact())
        );
        $this->emailService->setTemplateVariable('technical_contact_email', $affiliation->getContact()->getEmail());

        $this->emailService->send();

        return $contact;
    }

    /**
     * @param Project $project
     * @param int     $criterion
     * @param int     $which
     *
     * @return ArrayCollection|Affiliation[]
     */
    public function findAffiliationByProjectAndWhichAndCriterion(
        Project $project,
        int $criterion,
        int $which = self::WHICH_ONLY_ACTIVE
    ): ArrayCollection {
        /** @var Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);

        return new ArrayCollection(
            $repository->findAffiliationByProjectAndWhichAndCriterion(
                $project,
                $criterion,
                $which
            )
        );
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
    ): int {
        /** @var \Affiliation\Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);

        return $repository->findAmountOfAffiliationByProjectAndCountryAndWhich($project, $country, $which);
    }

    /**
     * @param Country $country
     * @param Call    $call
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
     * @param int     $which
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
     * @param int     $which
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
     * @param int     $which
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
     * @param int     $which
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

        if (\is_null($affiliations)) {
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
     * @param int     $which
     *
     * @return null|Affiliation
     */
    public function findAffiliationByProjectAndContactAndWhich(
        Project $project,
        Contact $contact,
        $which = self::WHICH_ONLY_ACTIVE
    ): ?Affiliation {
        /*
         * If the contact has no contact organisation, return null because we will not have a affiliation
         */
        if (null === $contact->getContactOrganisation()) {
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
    public function findAffiliationWithMissingDoa(): array
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
    public function findAffiliationWithMissingLoi(): Query
    {
        /** @var \Affiliation\Repository\Affiliation $repository */
        $repository = $this->getEntityManager()->getRepository(Affiliation::class);

        return $repository->findAffiliationWithMissingLoi();
    }

    /**
     * @param Affiliation $affiliation
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function deactivateAffiliation(Affiliation $affiliation): void
    {
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
    public function affiliationHasCostOrEffortInDraft(Affiliation $affiliation): bool
    {
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
    public function reactivateAffiliation(Affiliation $affiliation): void
    {
        $affiliation->setDateEnd(null);
        $this->updateEntity($affiliation);
    }

    /**
     * @return array
     */
    public function findLogAffiliations(): array
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
        if (null !== $contact->getContactOrganisation()) {
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
        if (null !== $organisation->getContactOrganisation()) {
            /**
             * Add the contact organisation
             */
            if (null !== $contact->getContactOrganisation()) {
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
