<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Service;

use Affiliation\Entity\AbstractEntity;
use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Doa;
use Affiliation\Entity\DoaObject;
use Affiliation\Entity\Invoice;
use Affiliation\Entity\Loi;
use Affiliation\Entity\LoiObject;
use Affiliation\Repository;
use Affiliation\ValueObject\PaymentSheetPeriod;
use Contact\Controller\Plugin\ContactActions;
use Contact\Entity\Address;
use Contact\Entity\AddressType;
use Contact\Entity\Contact;
use Contact\Entity\ContactOrganisation;
use Contact\Service\ContactService;
use Contact\Service\SelectionContactService;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use DragonBe\Vies\Vies;
use General\Entity\Country;
use General\Entity\Currency;
use General\Service\CountryService;
use General\Service\EmailService;
use General\Service\GeneralService;
use InvalidArgumentException;
use Invoice\Entity\Method;
use Invoice\Service\InvoiceService;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Validator\File\MimeType;
use Organisation\Entity\Financial;
use Organisation\Entity\Organisation;
use Organisation\Entity\ParentEntity;
use Organisation\Entity\Type;
use Organisation\Service\OrganisationService;
use Organisation\Service\ParentService;
use Program\Entity\Call\Call;
use Program\Entity\Program;
use Project\Entity\Contract\Version as ContractVersion;
use Project\Entity\Funding\Funding;
use Project\Entity\Funding\Source;
use Project\Entity\Funding\Status;
use Project\Entity\Project;
use Project\Entity\Rationale;
use Project\Entity\Version\Version;
use Project\Entity\Workpackage\Workpackage;
use Project\Service\ContractService;
use Project\Service\ProjectService;
use Project\Service\VersionService;
use stdClass;
use Throwable;

use function array_pop;
use function count;
use function in_array;
use function ksort;
use function number_format;
use function round;
use function sprintf;

/**
 * Class AffiliationService
 *
 * @package Affiliation\Service
 */
class AffiliationService extends AbstractService
{
    public const WHICH_ALL           = 1;
    public const WHICH_ONLY_ACTIVE   = 2;
    public const WHICH_ONLY_INACTIVE = 3;
    public const WHICH_INVOICING     = 4;

    public const AFFILIATION_DEACTIVATE = 'deacivate';
    public const AFFILIATION_REACTIVATE = 'reactivate';

    private GeneralService $generalService;
    private ProjectService $projectService;
    private InvoiceService $invoiceService;
    private ContractService $contractService;
    private OrganisationService $organisationService;
    private VersionService $versionService;
    private ParentService $parentService;
    private ContactService $contactService;
    private CountryService $countryService;
    private EmailService $emailService;
    private PluginManager $controllerPluginManager;
    private TranslatorInterface $translator;

    public function __construct(
        EntityManager $entityManager,
        SelectionContactService $selectionContactService,
        GeneralService $generalService,
        ProjectService $projectService,
        InvoiceService $invoiceService,
        ContractService $contractService,
        OrganisationService $organisationService,
        VersionService $versionService,
        ParentService $parentService,
        ContactService $contactService,
        CountryService $countryService,
        EmailService $emailService,
        PluginManager $controllerPluginManager,
        TranslatorInterface $translator
    ) {
        parent::__construct($entityManager, $selectionContactService);

        $this->generalService          = $generalService;
        $this->projectService          = $projectService;
        $this->invoiceService          = $invoiceService;
        $this->contractService         = $contractService;
        $this->organisationService     = $organisationService;
        $this->versionService          = $versionService;
        $this->parentService           = $parentService;
        $this->contactService          = $contactService;
        $this->countryService          = $countryService;
        $this->emailService            = $emailService;
        $this->controllerPluginManager = $controllerPluginManager;
        $this->translator              = $translator;
    }

    public static function useContractInVersion(Affiliation $affiliation, Version $version): bool
    {
        if (! self::useActiveContract($affiliation)) {
            return false;
        }

        $affiliationVersion = self::findAffiliationVersion($affiliation, $version);

        //If we cannot find the affiliation in the version
        if (null === $affiliationVersion) {
            return false;
        }

        return $affiliationVersion->hasContractVersion();
    }

    public static function useActiveContract(Affiliation $affiliation): bool
    {
        /** Only use the contract is the flag (invoice method) is set */
        if ($affiliation->hasInvoiceMethodFPP()) {
            return false;
        }

        if ($affiliation->getContract()->isEmpty()) {
            return false;
        }

        if (! $affiliation->getContractVersion()->isEmpty()) {
            return true;
        }

        return false;
    }

    public static function findAffiliationVersion(
        Affiliation $affiliation,
        Version $version
    ): ?\Affiliation\Entity\Version {
        $affiliationVersion = $version->getAffiliationVersion()->filter(
            static function (\Affiliation\Entity\Version $affiliationVersion) use ($affiliation) {
                return $affiliationVersion->getAffiliation() === $affiliation;
            }
        )->first();

        if ($affiliationVersion) {
            return $affiliationVersion;
        }

        return null;
    }

    public function findAffiliationById(int $id): ?Affiliation
    {
        return $this->entityManager->getRepository(Affiliation::class)->find($id);
    }

    public function isActiveInVersion(Affiliation $affiliation): bool
    {
        return ! $affiliation->getVersion()->isEmpty();
    }

    public function hasDoa(Affiliation $affiliation): bool
    {
        return null !== $affiliation->getDoa();
    }

    public function hasParentDoa(Affiliation $affiliation): bool
    {
        if (! $affiliation->hasParentOrganisation()) {
            return false;
        }

        return $affiliation->getParentOrganisation()->getParent()->getDoa()->exists(static function ($id, \Organisation\Entity\Parent\Doa $doa) use ($affiliation) {
            return $affiliation->getProject()->getCall()->getProgram() === $doa->getProgram();
        });
    }

    public function hasLoi(Affiliation $affiliation): bool
    {
        return null !== $affiliation->getLoi();
    }

    public function parsePaymentSheetPeriods(Affiliation $affiliation): array
    {
        $periods = [];

        $currentYear  = (int)date('Y');
        $currentMonth = (int)date('m');

        foreach ($this->projectService->parseYearRange($affiliation->getProject()) as $year) {
            foreach ([1, 2] as $period) {
                //Stop the script for the second half of the year for the current year
                if ($currentYear === $year && $currentMonth < 6 && $period === 2) {
                    break 2;
                }

                //Stop the script for the next years in the first 10 months of the current year
                if ($currentYear < $year && (($currentMonth > 10 && $period === 2) || $currentMonth <= 10)) {
                    break 2;
                }

                $periods[] = new PaymentSheetPeriod($year, $period);
            }
        }

        return $periods;
    }

    public function uploadLoi(array $file, Contact $contact, Affiliation $affiliation): Loi
    {
        $loiObject = new LoiObject();
        $loiObject->setObject(file_get_contents($file['tmp_name']));

        $loi = $affiliation->getLoi();
        if (null === $loi) {
            $loi = new Loi();
            $loi->setAffiliation($affiliation);
        }

        $loi->setContact($contact);
        $loi->setAffiliation($affiliation);
        $loi->setSize($file['size']);

        $fileTypeValidator = new MimeType();
        $fileTypeValidator->isValid($file);
        $loi->setContentType($this->generalService->findContentTypeByContentTypeName($fileTypeValidator->type));

        $loiObject->setLoi($loi);
        $this->save($loiObject);

        return $loiObject->getLoi();
    }

    public function save(AbstractEntity $entity): AbstractEntity
    {
        parent::save($entity);

        if ($entity instanceof Affiliation) {
            $this->projectService->flushPermitsByEntityAndId($entity->getProject(), (int)$entity->getProject()->getId());
        }

        return $entity;
    }

    public function submitLoi(Contact $contact, Affiliation $affiliation): Loi
    {
        $loi = $affiliation->getLoi();
        if (null === $loi) {
            $loi = new Loi();
            $loi->setAffiliation($affiliation);
        }
        $loi->setContact($contact);
        $loi->setApprover($contact);
        $loi->setDateSigned(new DateTime());
        $loi->setDateApproved(new DateTime());

        $this->save($loi);

        return $loi;
    }

    public function uploadDoa(array $file, Contact $contact, Affiliation $affiliation): Doa
    {
        $doaObject = new DoaObject();
        $doaObject->setObject(file_get_contents($file['tmp_name']));

        $doa = $affiliation->getDoa();

        if (null === $doa) {
            $doa = new Doa();
            $doa->setAffiliation($affiliation);
        }

        $doa->setContact($contact);
        $doa->setSize($file['size']);

        $fileTypeValidator = new MimeType();
        $fileTypeValidator->isValid($file);
        $doa->setContentType($this->generalService->findContentTypeByContentTypeName($fileTypeValidator->type));

        $doaObject->setDoa($doa);
        $this->save($doaObject);

        return $doaObject->getDoa();
    }

    public function submitDoa(Contact $contact, Affiliation $affiliation, array $data): Doa
    {
        $doa = $affiliation->getDoa();

        if (null === $doa) {
            $doa = new Doa();
            $doa->setAffiliation($affiliation);
        }

        $doa->setContact($contact);
        $doa->setDateSigned(new DateTime());
        $doa->setGroupName($data['group_name']);
        $doa->setChamberOfCommerceNumber($data['chamber_of_commerce_number']);
        $doa->setChamberOfCommerceLocation($data['chamber_of_commerce_location']);

        $this->save($doa);

        return $doa;
    }

    public function getFinancialFormData(Affiliation $affiliation): array
    {
        $formData              = [
            'preferredDelivery' => \Organisation\Entity\Financial::EMAIL_DELIVERY,
            'omitContact'       => \Organisation\Entity\Financial::OMIT_CONTACT,
        ];
        $branch                = null;
        $financialAddress      = null;
        $organisationFinancial = null;

        if ($affiliation->hasFinancial()) {
            $organisationFinancial = $affiliation->getFinancial()->getOrganisation()->getFinancial();
            $branch                = $affiliation->getFinancial()->getBranch();

            /** @var ContactService $contactService */
            $formData['contact'] = $affiliation->getFinancial()->getContact()->getId();

            $financialAddress = $this->contactService->getFinancialAddress(
                $affiliation->getFinancial()->getContact()
            );

            if (null !== $financialAddress) {
                $formData['address']        = $financialAddress->getAddress();
                $formData['zipCode']        = $financialAddress->getZipCode();
                $formData['city']           = $financialAddress->getCity();
                $formData['addressCountry'] = $financialAddress->getCountry()->getId();
            }

            $formData['organisation']      = $this->organisationService
                ->parseOrganisationWithBranch($branch, $affiliation->getFinancial()->getOrganisation());
            $formData['registeredCountry'] = $affiliation->getFinancial()->getOrganisation()->getCountry()->getId();
        }

        if (! $affiliation->hasFinancial()) {
            $formData['organisation']      = $this->organisationService
                ->parseOrganisationWithBranch($branch, $affiliation->getOrganisation());
            $formData['registeredCountry'] = $affiliation->getOrganisation()->getCountry()->getId();
        }

        if (null !== $organisationFinancial) {
            $formData['preferredDelivery'] = $organisationFinancial->getEmail();
            $formData['vat']               = $organisationFinancial->getVat();
            $formData['omitContact']       = $organisationFinancial->getOmitContact();
        }

        return $formData;
    }

    public function saveFinancial(
        Affiliation $affiliation,
        string $vat,
        int $countryId,
        string $organisationName,
        int $contactId,
        int $preferredDelivery,
        int $omitContact,
        string $address,
        string $zipCode,
        string $city,
        int $addressCountryId
    ): void {
        //We need to find the organisation, first by trying the VAT, then via the name and country and then just create it
        $organisation = null;

        //Check if an organisation with the given VAT is already found
        $organisationFinancial = $this->organisationService->findFinancialOrganisationWithVAT($vat);
        /** @var Country $country */
        $country = $this->countryService->find(Country::class, $countryId);


        //We have not found a financial organisation, so we are forced to create a new one
        if (null === $organisationFinancial) {
            //Now we try to find the corresponding organisation
            //try to find the organisation based on te country and name
            if (null === $organisation) {
                $organisation = $this->organisationService
                    ->findOrganisationByNameCountry(
                        $organisationName,
                        $country
                    );
            }

            /**
             * If the organisation is still not found, create it
             */
            if (null === $organisation) {
                $organisation = new \Organisation\Entity\Organisation();
                $organisation->setOrganisation($organisationName);
                $organisation->setCountry($country);
                $organisationType = $this->organisationService->find(Type::class, Type::TYPE_UNKNOWN);
                $organisation->setType($organisationType);
            }

            //Check if the organisation already has a financial organisation
            $organisationFinancial = $organisation->getFinancial();
            if (null === $organisationFinancial) {
                $organisationFinancial = new \Organisation\Entity\Financial();
                $organisationFinancial->setOrganisation($organisation);
            }

            //Only set the VAT when it is not empty
            if (! empty($vat) && empty($organisationFinancial->getVat())) {
                $organisationFinancial->setVat($vat);
            }
        }

        //If the organisation is found, it has by default an organisation
        if (null !== $organisationFinancial) {
            $organisation = $organisationFinancial->getOrganisation();
        }

        /** @var Contact $contact */
        $contact = $this->contactService->findContactById($contactId);

        /**
         * Update the affiliationFinancial
         */
        $affiliationFinancial = $affiliation->getFinancial();
        if (null === $affiliationFinancial) {
            $affiliationFinancial = new \Affiliation\Entity\Financial();
            $affiliationFinancial->setAffiliation($affiliation);
        }

        $affiliationFinancial->setContact($contact);
        $affiliationFinancial->setOrganisation($organisation);

        //Update the branch is complicated so we create a dedicated function for it in the
        $affiliationFinancial->setBranch(OrganisationService::determineBranch($organisationName, $organisation->getOrganisation()));
        $this->save($affiliationFinancial);

        //Now we do a VAT update
        if (! empty($vat)) {
            //Do an in-situ vat check
            $vies = new Vies();
            try {
                $countryCode = $country->getCd();
                $result      = $vies->validateVat($countryCode, str_replace($countryCode, '', $vat));

                if ($result->isValid()) {
                    //Update the financial
                    $organisationFinancial->setVatStatus(\Organisation\Entity\Financial::VAT_STATUS_VALID);
                } else {
                    //Update the financial
                    $organisationFinancial->setVatStatus(\Organisation\Entity\Financial::VAT_STATUS_INVALID);
                }
                $organisationFinancial->setDateVat(new \DateTime());
            } catch (Throwable $e) {
                //Only update the vatStatus when not set already
                if (null === $organisationFinancial->getVatStatus()) {
                    $organisationFinancial->setVatStatus(\Organisation\Entity\Financial::VAT_STATUS_UNCHECKED);
                    $organisationFinancial->setDateVat(new \DateTime());
                }
            }
        }

        $organisationFinancial->setEmail($preferredDelivery);
        $organisationFinancial->setOmitContact($omitContact);
        $this->organisationService->save($organisationFinancial);


        /**
         * save the financial address
         */
        $financialAddress = $this->contactService->getFinancialAddress($contact);

        /** @var Country $addressCountry */
        $addressCountry = $this->countryService->find(Country::class, $addressCountryId);

        if (null === $financialAddress) {
            $financialAddress = new Address();
            $financialAddress->setContact($contact);
            /**
             * @var $addressType AddressType
             */
            $addressType = $this->contactService->find(AddressType::class, AddressType::ADDRESS_TYPE_FINANCIAL);
            $financialAddress->setType($addressType);
        }

        $financialAddress->setAddress($address);
        $financialAddress->setZipCode($zipCode);
        $financialAddress->setCity($city);
        $financialAddress->setCountry($addressCountry);
        $this->contactService->save($financialAddress);
    }

    /**
     * @return Affiliation[]
     */
    public function findNotValidatedSelfFundedAffiliation(): array
    {
        /** @var Repository\Affiliation $repository */
        $repository = $this->entityManager->getRepository(Affiliation::class);

        return $repository->findNotValidatedSelfFundedAffiliation();
    }

    public function findMissingAffiliationParent(): Query
    {
        /** @var Repository\Affiliation $repository */
        $repository = $this->entityManager->getRepository(Affiliation::class);

        return $repository->findMissingAffiliationParent();
    }

    public function parseVatNumber(Affiliation $affiliation): ?string
    {
        $financial = $this->findOrganisationFinancial($affiliation);

        if (null === $financial) {
            return null;
        }

        return $financial->getVat();
    }

    public function findOrganisationFinancial(Affiliation $affiliation): ?Financial
    {
        $organisation = null;

        // We need to find the financial organisation and will do that in order of importance
        // We will first try to find the organisation is if we can find this return the financial in the end
        if (null !== $affiliation->getParentOrganisation()) {
            // We have to deal with the parent system
            $parent = $affiliation->getParentOrganisation()->getParent();

            if (! $parent->getFinancial()->isEmpty()) {
                $organisation = $parent->getFinancial()->first()->getOrganisation();
            }
        }

        // Organisation still not found, try to find it via the old way
        if (null === $organisation) {
            $organisation = $affiliation->getOrganisation();

            if (null !== $affiliation->getFinancial()) {
                $organisation = $affiliation->getFinancial()->getOrganisation();
            }
        }

        if (null === $organisation || null === $organisation->getFinancial()) {
            return null;
        }

        return $organisation->getFinancial();
    }

    public function updateCountryRationaleByAffiliation(Affiliation $affiliation, string $action): void
    {
        switch ($action) {
            case self::AFFILIATION_DEACTIVATE:
                $rationale = $this->projectService->findRationaleByProjectAndCountry(
                    $affiliation->getProject(),
                    $affiliation->getOrganisation()->getCountry()
                );

                // We need to check the rationale and maybe delete or update the contact persons.
                if (null !== $rationale && $rationale->getContact()->getId() === $affiliation->getContact()->getId()) {
                    // There is only 1 rationale, and our partner is contact person, so we need to update the contact
                    // or delete the rationale if there are no other countries available for the project
                    $countryFound = false;
                    // The country is still active in the project, we need to assign a new rationale responsible
                    $affiliations = $this->findAffiliationByProjectAndCountryAndWhich(
                        $affiliation->getProject(),
                        $affiliation->getOrganisation()->getCountry()
                    );
                    foreach ($affiliations as $otherAffiliation) {
                        // Give the country rationale to the first contact in the affiliation
                        $rationale->setContact($otherAffiliation->getContact());
                        $this->save($otherAffiliation);
                        break 2;
                    }

                    if (! $countryFound) {
                        $this->projectService->delete($rationale);
                    }
                }

                break;
            case self::AFFILIATION_REACTIVATE:
                // Simply use this function as a proxy to the generate function
                $this->projectService->generateCountryRationaleByProject($affiliation->getProject());
                break;
        }
    }

    public function findAffiliationByProjectAndCountryAndWhich(
        Project $project,
        Country $country,
        int $which = self::WHICH_ONLY_ACTIVE
    ): ArrayCollection {
        /** @var Repository\Affiliation $repository */
        $repository   = $this->entityManager->getRepository(Affiliation::class);
        $affiliations = $repository->findAffiliationByProjectAndCountryAndWhich($project, $country, $which);

        if (count($affiliations) === 0) {
            $affiliations = [];
        }

        return new ArrayCollection($affiliations);
    }

    public function getFinancialContact(Affiliation $affiliation): ?Contact
    {
        if (null === $affiliation->getFinancial()) {
            return null;
        }

        return $affiliation->getFinancial()->getContact();
    }

    public function canCreateInvoice(Affiliation $affiliation, Method $method = null): array
    {
        $errors = [];
        switch (true) {
            case null === $affiliation->getFinancial():
                $errors[] = 'No financial organisation (affiliation financial) set for this partner';
                break;
            case ! $affiliation->isActive():
                $errors[] = 'Partner is de-activated';
                break;
            case null === $affiliation->getFinancial()->getOrganisation()->getFinancial():
                $errors[] = 'No financial information set for this organisation';
                break;
            case null === $affiliation->getFinancial()->getContact():
                $errors[] = 'No financial contact set for this organisation';
                break;
            case null !== $method && null !== $affiliation->getInvoiceMethod()
                && $affiliation->getInvoiceMethod()->getId() !== $method->getId():
                $errors[] = sprintf(
                    'Invoice method is already set to %s, so invoice on %s cannot be created',
                    $affiliation->getInvoiceMethod()->getMethod(),
                    $method->getMethod()
                );
                break;
        }

        return $errors;
    }

    public function findAffiliationInProjectLog(): array
    {
        /** @var Repository\Affiliation $repository */
        $repository = $this->entityManager->getRepository(Affiliation::class);

        return $repository->findAffiliationInProjectLog();
    }

    public function parseTotalByProject(Project $project, int $year, int $period): float
    {
        $projectTotal = 0;

        //Find first the latestVersion
        $latestVersion = $this->projectService->getLatestNotRejectedProjectVersion($project);

        if (null === $latestVersion) {
            return $projectTotal;
        }

        foreach ($latestVersion->getAffiliationVersion() as $affiliationVersion) {
            $affiliation = $affiliationVersion->getAffiliation();

            if (! $affiliation->isActive()) {
                continue;
            }


            if (
                null !== $affiliation->getInvoiceMethod()
                && $affiliation->getInvoiceMethod()->getId() !== Method::METHOD_PERCENTAGE
            ) {
                continue;
            }


            $projectTotal += $this->parseTotalExcludingInvoiced($affiliation, $latestVersion, $year, $period);
        }

        return $projectTotal;
    }

    public function parseTotalExcludingInvoiced(
        Affiliation $affiliation,
        Version $version,
        int $year,
        ?int $period = null
    ): float {
        $amountInvoiced = 0;
        /** @var Invoice $affiliationInvoice */
        foreach ($this->findAffiliationInvoiceByAffiliationPeriodAndYear($affiliation, $period, $year) as $affiliationInvoice) {
            $amountInvoiced += $affiliationInvoice->getAmountInvoiced();
        }

        return $this->parseTotal($affiliation, $version, $year, $period) - $amountInvoiced;
    }

    public function findAffiliationInvoiceByAffiliationPeriodAndYear(
        Affiliation $affiliation,
        int $period,
        int $year
    ): ArrayCollection {
        return $affiliation->getInvoice()->filter(
            static function (Invoice $invoice) use ($period, $year) {
                return $invoice->getPeriod() === $period && $invoice->getYear() === $year;
            }
        );
    }

    public function parseTotal(
        Affiliation $affiliation,
        Version $version,
        int $year,
        ?int $period = null
    ): float {
        if ($this->parseInvoiceMethod($affiliation, false) === Method::METHOD_PERCENTAGE_CONTRACT) {
            print 'Parse total cannot be used for contracts';
            return 0.0;
        }

        return $this->parseContribution($affiliation, $version, null, $year, $period, false) + $this->parseBalance(
            $affiliation,
            $version,
            $year,
            $period
        );
    }

    public function parseInvoiceMethod(Affiliation $affiliation, bool $useContractData = true): int
    {
        //When the partner has an invoice method defined, we only return it when the $useContractData is set to
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

        $invoiceMethod = (int)$this->invoiceService->findInvoiceMethod(
            $affiliation->getProject()->getCall()->getProgram()
        )->getId();


        //The percentage method depends on the fact if we have a contract or not.
        $contractVersion = $this->contractService->findLatestContractVersionByAffiliation($affiliation);

        //Force the invoiceMethod back to _percentage_ when we don't want to use contract data
        if ($invoiceMethod === Method::METHOD_PERCENTAGE_CONTRACT && (null === $contractVersion || ! $useContractData)) {
            $invoiceMethod = Method::METHOD_PERCENTAGE;
        }

        return $invoiceMethod;
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
                if (null === $affiliation->getParentOrganisation()) {
                    return 0;
                }

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
                    throw new InvalidArgumentException(
                        'The contract version cannot be null for parsing the contribution base'
                    );
                }

                $costsPerYear
                    = $this->versionService
                    ->findTotalCostVersionByAffiliationAndVersionPerYear($affiliation, $version);
                if (array_key_exists($year, $costsPerYear)) {
                    return (float)$costsPerYear[$year];
                }

                break;
            case Method::METHOD_PERCENTAGE_CONTRACT:
                if (null === $contractVersion) {
                    throw new InvalidArgumentException(
                        'The contract version cannot be null for parsing the contribution base'
                    );
                }

                $costsPerYear
                    = $this->contractService
                    ->findTotalCostVersionByAffiliationAndVersionPerYear($affiliation, $contractVersion);
                if (array_key_exists($year, $costsPerYear)) {
                    return (float)$costsPerYear[$year];
                }

                break;
            case Method::METHOD_CONTRIBUTION:
                $effortPerYear
                    = $this->versionService
                    ->findTotalEffortVersionByAffiliationAndVersionPerYear($affiliation, $version);
                if (array_key_exists($year, $effortPerYear)) {
                    return (float)$effortPerYear[$year];
                }
                break;
            case Method::METHOD_FUNDING_MEMBER:
            case Method::METHOD_FUNDING:
                return $this->versionService
                    ->findTotalFundingVersionByAffiliationAndVersion($affiliation, $version);
        }

        return (float)$base;
    }

    public function parseContributionFee(Affiliation $affiliation, int $year, ParentEntity $parent = null)
    {
        /**
         * Based on the invoiceMethod we return or a percentage or the contribution
         */
        $fee = $this->projectService->findProjectFeeByYear($year);

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
                    throw new InvalidArgumentException('Invoice cannot be funding when no parent is known');
                }

                $invoiceFactor = $this->parentService->parseInvoiceFactor(
                    $parent,
                    $affiliation->getProject()->getCall()->getProgram()
                ) / 100;

                if ($parent->isMember()) {
                    $membershipFactor = $this->parentService->parseMembershipFactor($parent);

                    return $invoiceFactor / (3 * $membershipFactor);
                }

                $doaFactor = $this->parentService->parseDoaFactor(
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
                    throw new InvalidArgumentException('Invoice cannot be funding when no parent is known');
                }

                //Funding PENTA === 1.8 %
                $invoiceFactor = ParentService::PARENT_INVOICE_FACTOR / 100;

                $doaFactor = $this->parentService->parseDoaFactor(
                    $parent,
                    $affiliation->getProject()->getCall()->getProgram()
                );

                if ($parent->isMember() || $doaFactor > 0) {
                    return $invoiceFactor / 3;
                }

                return 0;

            default:
                throw new InvalidArgumentException(sprintf('Unknown contribution fee in %s', __FUNCTION__));
        }
    }

    public function parseContributionFactor(Affiliation $affiliation, int $year, ?int $period = null): float
    {
        switch (true) {
            case ! $this->isFundedInYear($affiliation, $year):
                return (float)0;
            case null === $period:
                return 1;
            case $this->projectService->parseEndYear($affiliation->getProject()) === $year
                && $this->projectService->parseEndMonth($affiliation->getProject()) <= 6:
                return (float)($period === 1 ? 1 : 0);
            default:
                return 0.5;
        }
    }

    public function isFundedInYear(Affiliation $affiliation, $year): bool
    {
        //Cast to int as some values can originate form templates (== twig > might be string)
        $year = (int)$year;

        return null === $this->getFundingInYear($affiliation, $year) ? false
            : $this->getFundingInYear($affiliation, $year)->getStatus()->getId() === Status::STATUS_ALL_GOOD;
    }

    public function getFundingInYear(Affiliation $affiliation, $year, $source = Source::SOURCE_OFFICE): ?Funding
    {
        //Cast to ints as some values can originate form templates (== twig > might be string)
        $year = (int)$year;

        foreach ($affiliation->getFunding() as $funding) {
            if ((int)$funding->getDateStart()->format('Y') === $year && $funding->getSource()->getId() === $source) {
                return $funding;
            }
        }

        return null;
    }

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
                $contractVersion = $this->contractService->findLatestContractVersionByAffiliation($affiliation);

                if (null === $contractVersion) {
                    return 1;
                }

                $exchangeRate = $this->contractService->findExchangeRateInInvoicePeriod(
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

        return 1;
    }

    public function parseBalance(
        Affiliation $affiliation,
        Version $version,
        int $year,
        ?int $period = null
    ): float {
        //Based on the invoice method we will only have a balance when we are working with percentage
        $invoiceMethod = $this->parseInvoiceMethod($affiliation);
        if (in_array($invoiceMethod, [Method::METHOD_FUNDING, Method::METHOD_FUNDING_MEMBER], true)) {
            return 0;
        }

        $balance
            = $this->parseContributionDue(
                $affiliation,
                $version,
                $year,
                $period
            ) - $this->parseContributionPaid($affiliation, $year, $period);

        return $balance;
    }

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
                $costsPerYear = $this->versionService
                    ->findTotalCostVersionByAffiliationAndVersionPerYear($affiliation, $version);


                foreach ($costsPerYear as $costsYear => $cost) {
                    //fee
                    $fee    = $this->projectService->findProjectFeeByYear($costsYear);
                    $factor = $this->parseContributionFactorDue($affiliation, $costsYear, $year, $period);

                    //Only add the value to the contribution if the partner is funded in that year
                    if ($this->isFundedInYear($affiliation, $costsYear)) {
                        $contributionDue += $factor * $cost * ($fee->getPercentage() / 100);
                    }
                }

                break;
            case Method::METHOD_CONTRIBUTION:
                //Fix the versionService
                $effortPerYear = $this->versionService
                    ->findTotalEffortVersionByAffiliationAndVersionPerYear($affiliation, $version);

                foreach ($effortPerYear as $effortYear => $effort) {
                    $fee = $this->projectService->findProjectFeeByYear($year);

                    switch (true) {
                        case null === $period:
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

        return round($contributionDue, 2);
    }

    public function parseContributionFactorDue(
        Affiliation $affiliation,
        int $projectYear,
        int $year,
        int $period = null
    ): float {
        switch (true) {
            case null === $period || $projectYear < $year:
                return (float)1; //in the past is always 100% due
            case $projectYear === $year && $period === 2:
                //Current year, and period 2 (so  first period might have been invoiced, due is now the 1-that value
                return (float)1 - $this->parseContributionFactor($affiliation, $year, $period);
            case ! $this->isFundedInYear($affiliation, $projectYear):
            default:
                return (float)0;
        }
    }

    public function parseContributionPaid(Affiliation $affiliation, int $year, int $period = null): float
    {
        $contributionPaid = 0;

        //Sum the invoiced amount of all invoices for this affiliation
        foreach ($affiliation->getInvoice() as $invoice) {
            //Filter invoices of previous years or this year, but the previous period and already sent to accounting
            if (null !== $period) {
                //When we have a period, we also take the period fo the current year into account
                if (
                    $invoice->getYear() < $year
                    || ($invoice->getPeriod() < $period
                        && $invoice->getYear() === $year)
                ) {
                    $contributionPaid += $invoice->getAmountInvoiced();
                }
            } else {
                if ($invoice->getYear() < $year) {
                    $contributionPaid += $invoice->getAmountInvoiced();
                }
            }
        }

        return round($contributionPaid, 2);
    }

    public function parseContractTotalByProject(Project $project, int $year, int $period): float
    {
        $projectTotal = 0;

        //Find first the latestVersion
        $latestVersion = $this->projectService->getLatestNotRejectedProjectVersion($project);

        if (null === $latestVersion) {
            return $projectTotal;
        }


        foreach ($latestVersion->getAffiliationVersion() as $affiliationVersion) {
            /** @var Affiliation $affiliation */
            $affiliation = $affiliationVersion->getAffiliation();

            if (! $affiliation->isActive()) {
                continue;
            }

            $contractVersion = $this->contractService->findLatestContractVersionByAffiliation($affiliation);

            if (null === $contractVersion) {
                continue;
            }

            if (
                null !== $affiliation->getInvoiceMethod()
                && $affiliation->getInvoiceMethod()->getId() !== Method::METHOD_PERCENTAGE_CONTRACT
            ) {
                continue;
            }


            $projectTotal += $this->parseContractTotal($affiliation, $contractVersion, $year, $period, false);
        }

        return $projectTotal;
    }

    public function parseContractTotal(
        Affiliation $affiliation,
        ContractVersion $version,
        int $year,
        ?int $period = null,
        bool $onlyCurrentPeriod = false
    ): float {
        return $this->parseTotalByInvoiceLines($affiliation, $version, $year, $period, $onlyCurrentPeriod);
    }

    public function parseTotalByInvoiceLines(
        Affiliation $affiliation,
        ContractVersion $contractVersion,
        int $year,
        ?int $period = null,
        bool $onlyCurrentPeriod = false
    ): float {
        $total = 0.0;

        foreach ($this->findInvoiceLines($affiliation, $contractVersion, $year, $period, $onlyCurrentPeriod) as $line) {
            $total += $line->lineTotal;
        }

        return $total;
    }

    public function findInvoiceLines(
        Affiliation $affiliation,
        ContractVersion $contractVersion,
        int $year,
        ?int $period = null,
        bool $onlyCurrentPeriod = false
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

                if (
                    ! $this->affiliationHasInvoiceInYearAndPeriod($affiliation, $otherYear, $invoicePeriod)
                ) {
                    $yearAndPeriod[$otherYear][] = $invoicePeriod;
                }
            }
        }

        if ($onlyCurrentPeriod) {
            $yearAndPeriod          = [];
            $yearAndPeriod[$year][] = $period;
        }

        foreach ($yearAndPeriod as $invoiceYear => $invoicePeriod) {
            if (count($invoicePeriod) === 2) {
                $invoicePeriod = null;
            } else {
                $invoicePeriod = array_pop($invoicePeriod);
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
                $line                = new stdClass();
                $line->year          = $invoiceYear;
                $line->period        = $invoicePeriod;
                $line->periodOrdinal = $invoiceYear . (null === $invoicePeriod ? '' : '-' . $invoicePeriod . 'H');
                $line->description   = $this->parseInvoiceLine(
                    $affiliation,
                    $invoiceYear,
                    $currency,
                    $invoicePeriod
                );
                $line->lineTotal     = round($contribution, 2);

                $lines[] = $line;
            }
        }

        return $lines;
    }

    public function affiliationHasInvoiceInYearAndPeriod(Affiliation $affiliation, int $year, int $period): bool
    {
        return null !== $this->findAffiliationInvoiceInYearAndPeriod($affiliation, $year, $period);
    }

    public function findAffiliationInvoiceInYearAndPeriod(Affiliation $affiliation, int $year, int $period): ?Invoice
    {
        foreach ($affiliation->getInvoice() as $affiliationInvoice) {
            //When the invoice is a credit invoice, skip the invoice
            if (
                $this->invoiceService->hasCredit($affiliationInvoice->getInvoice())
                || $this->invoiceService->isCredit($affiliationInvoice->getInvoice())
            ) {
                continue;
            }

            //This is a check for the current year, we only need to check if the period has not been invoiced yet in this year
            if ($affiliationInvoice->hasYearAndPeriod($year, $period)) {
                return $affiliationInvoice;
            }
        }

        return null;
    }

    public function parseInvoiceLine(
        Affiliation $affiliation,
        int $year,
        Currency $currency,
        ?int $period = null
    ): string {
        $contributionFactor = $this->parseContributionFactor($affiliation, $year, $period);

        return sprintf(
            $this->translator->translate("txt-%s%%-contribution-for-%s"),
            number_format($contributionFactor * 100, 0),
            $year,
            $currency->getSymbol()
        );
    }

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

    public function findSortedAffiliationByWorkpackage(Workpackage $workpackage, string $sort, string $direction): ArrayCollection
    {
        /** @var Affiliation[]|ArrayCollection $affiliations */
        $affiliations = $this->findAffiliationByProjectAndWhich($workpackage->getProject());

        //create an array where we can sort on
        $sortArray = [];


        /** @var Affiliation $affiliation */
        foreach ($affiliations as $affiliation) {
            //Create a local array of the effort for this workpackage
            $totalEffort   = 0;
            $effortPerYear = [];
            foreach ($affiliation->getEffort() as $effort) {
                if ($effort->getWorkpackage() === $workpackage) {
                    $totalEffort                                         += $effort->getEffort();
                    $effortPerYear[$effort->getDateStart()->format('Y')] = $effort->getEffort();
                }
            }

            switch ($sort) {
                case 'partner':
                    $sortArray[$affiliation->getId()] = $affiliation->parseBranchedName();
                    break;
                case 'total':
                    $sortArray[$affiliation->getId()] = $totalEffort;
                    break;
                default:
                    $sortArray[$affiliation->getId()] = $effortPerYear[$sort] ?? 0;
                    break;
            }
        }

        if ($direction === 'asc') {
            asort($sortArray);
        }
        if ($direction === 'desc') {
            arsort($sortArray);
        }

        $sortArrayValues = array_keys($sortArray);

        $iterator = $affiliations->getIterator();
        $iterator->uasort(function (Affiliation $a, Affiliation $b) use ($sortArrayValues) {
            return ((array_search($a->getId(), $sortArrayValues, true) > array_search($b->getId(), $sortArrayValues, true)) ? 1 : -1);
        });
        return new ArrayCollection(iterator_to_array($iterator));
    }

    public function findAffiliationByProjectAndWhich(
        Project $project,
        int $which = self::WHICH_ONLY_ACTIVE
    ): ArrayCollection {
        /** @var Repository\Affiliation $repository */
        $repository   = $this->entityManager->getRepository(Affiliation::class);
        $affiliations = $repository->findAffiliationByProjectAndWhich($project, $which);

        if (null === $affiliations) {
            $affiliations = [];
        }

        return new ArrayCollection($affiliations);
    }

    public function findAffiliationByProjectVersionAndCountryAndWhich(
        Version $version,
        Country $country,
        int $which = self::WHICH_ONLY_ACTIVE
    ): ArrayCollection {
        $repository   = $this->entityManager->getRepository(Affiliation::class);
        $affiliations = $repository->findAffiliationByProjectVersionAndCountryAndWhich($version, $country, $which);

        if (null === $affiliations) {
            $affiliations = [];
        }

        return new ArrayCollection($affiliations);
    }

    public function findAffiliationByParentAndProgramAndWhich(
        ParentEntity $parent,
        Program $program,
        int $which = self::WHICH_ONLY_ACTIVE,
        int $year = null
    ): ArrayCollection {
        $repository   = $this->entityManager->getRepository(Affiliation::class);
        $affiliations = $repository->findAffiliationByParentAndProgramAndWhich($parent, $program, $which, $year);

        if (null === $affiliations) {
            $affiliations = [];
        }

        return new ArrayCollection($affiliations);
    }

    public function findAffiliationByProjectVersionAndWhich(
        Version $version,
        int $which = self::WHICH_ALL
    ): ArrayCollection {
        /** @var Repository\Affiliation $repository */
        $repository   = $this->entityManager->getRepository(Affiliation::class);
        $affiliations = $repository->findAffiliationByProjectVersionAndWhich($version, $which);

        if (null === $affiliations) {
            $affiliations = [];
        }

        return new ArrayCollection($affiliations);
    }

    public function findAffiliationByWorkPackage(Workpackage $workPackage): ArrayCollection
    {
        /** @var Repository\Affiliation $repository */
        $repository   = $this->entityManager->getRepository(Affiliation::class);
        $affiliations = $repository->findAffiliationByWorkPackage($workPackage);

        if (null === $affiliations) {
            $affiliations = [];
        }

        return new ArrayCollection($affiliations);
    }

    public function addAssociate(Affiliation $affiliation, Contact $contact = null, string $email = null): Contact
    {
        if (null === $contact && empty($email)) {
            throw new InvalidArgumentException('Both contact and email address cannot be null to add an associate');
        }

        //Boolean to see if the contact whas known already
        $hasContact = true;

        //When we have an email, create a contact based also on the $affiliation
        if (! empty($email)) {
            //Try to find the contact based on the email address
            $contact = $this->contactService->findContactByEmail($email);


            //If we don't find the contact by email, we will create it.
            if (null === $contact) {
                $hasContact = false;

                /** @var ContactActions $contactActions */
                $contactActions = $this->controllerPluginManager->get(ContactActions::class);

                $contact = $contactActions->createContact(
                    $email,
                    sprintf(
                        'Created via invitation for %s in %s',
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
                $this->contactService->save($contactOrganisation);

                $this->contactService->addNoteToContact(
                    'Set organisation to ' . $affiliation->getOrganisation(),
                    'Account upgrade via associate',
                    $contact
                );
            }
        }

        $affiliation->addAssociate($contact);
        $this->save($affiliation);


        $emailBuilder = $this->emailService->createNewWebInfoEmailBuilder('/project/add_associate:associate');
        $emailBuilder->addContactTo($contact);

        $emailBuilder->addDeeplink('community/contact/profile/edit', 'edit_profile_url', $contact);
        $emailBuilder->addDeeplink('community/affiliation/details', 'partner_page_url', $contact, null, $affiliation->getId());

        $templateVariables = [
            'project'                        => $affiliation->getProject()->parseFullName(),
            'organisation'                   => $affiliation->parseBranchedName(),
            'has_contact'                    => $hasContact,
            'technical_contact'              => $affiliation->getContact()->parseFullName(),
            'technical_contact_organisation' => $this->contactService->parseOrganisation($affiliation->getContact()),
            'technical_contact_country'      => $this->contactService->parseCountry($affiliation->getContact()),
            'technical_contact_email'        => $affiliation->getContact()->getEmail()
        ];
        $emailBuilder->setTemplateVariables($templateVariables);

        $this->emailService->sendBuilder($emailBuilder);

        return $contact;
    }

    public function findAffiliationByProjectAndWhichAndCriterion(
        Project $project,
        int $criterion,
        int $which = self::WHICH_ONLY_ACTIVE
    ): ArrayCollection {
        /** @var Repository\Affiliation $repository */
        $repository = $this->entityManager->getRepository(Affiliation::class);

        return new ArrayCollection(
            $repository->findAffiliationByProjectAndWhichAndCriterion(
                $project,
                $criterion,
                $which
            )
        );
    }

    public function findAmountOfAffiliationByProjectAndCountryAndWhich(
        Project $project,
        Country $country,
        int $which = self::WHICH_ONLY_ACTIVE
    ): int {
        /** @var Repository\Affiliation $repository */
        $repository = $this->entityManager->getRepository(Affiliation::class);

        return $repository->findAmountOfAffiliationByProjectAndCountryAndWhich($project, $country, $which);
    }

    public function findAmountOfAffiliationByCountryAndCall(
        Country $country,
        Call $call
    ): int {
        /** @var Repository\Affiliation $repository */
        $repository = $this->entityManager->getRepository(Affiliation::class);

        return $repository->findAmountOfAffiliationByCountryAndCall($country, $call);
    }

    public function findAmountOfAffiliationByProjectVersionAndCountryAndWhich(
        Version $version,
        Country $country,
        int $which = self::WHICH_ONLY_ACTIVE
    ): int {
        $repository = $this->entityManager->getRepository(Affiliation::class);

        return $repository->findAmountOfAffiliationByProjectVersionAndCountryAndWhich($version, $country, $which);
    }

    public function findAffiliationByProjectPerCountryAndWhich(
        Project $project,
        int $which = self::WHICH_ONLY_ACTIVE
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

    public function findAffiliationCountriesByProjectAndWhich(
        Project $project,
        int $which = self::WHICH_ONLY_ACTIVE
    ): array {
        $repository = $this->entityManager->getRepository(Affiliation::class);

        /**
         * @var $affiliations Affiliation[]
         */
        $affiliations = $repository->findAffiliationByProjectAndWhich($project, $which);
        $result       = [];
        foreach ($affiliations as $affiliation) {
            $country = $affiliation->getOrganisation()->getCountry();

            $result[$country->getCountry()] = $country;
        }

        ksort($result);

        return $result;
    }

    /**
     * @param Organisation $organisation
     *
     * @return ArrayCollection|Affiliation[]
     * @deprecated
     */
    public function findAffiliationByOrganisation(
        Organisation $organisation
    ): ArrayCollection {
        /** @var Repository\Affiliation $repository */
        $repository = $this->entityManager->getRepository(Affiliation::class);

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
        /** @var Repository\Affiliation $repository */
        $repository = $this->entityManager->getRepository(Affiliation::class);

        return new ArrayCollection($repository->findAffiliationByOrganisationViaParentOrganisation($organisation));
    }

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
            if ($which === self::WHICH_ONLY_ACTIVE && ! $affiliation->isActive()) {
                continue;
            }
            if ($which === self::WHICH_ONLY_INACTIVE && $affiliation->isActive()) {
                continue;
            }

            //Do a match on the organisation or technical contact
            if (
                $affiliation->getContact() === $contact
                || $affiliation->getOrganisation()->getId() ===
                $contact->getContactOrganisation()->getOrganisation()->getId()
            ) {
                return $affiliation;
            }
        }

        return null;
    }

    public function findAffiliationWithMissingDoa(bool $invoiceViaParent = false): array
    {
        /** @var Repository\Affiliation $repository */
        $repository = $this->entityManager->getRepository(Affiliation::class);

        if ($invoiceViaParent) {
            return $repository->findAffiliationWithMissingProjectDoaAndNoMember();
        }

        return $repository->findAffiliationWithMissingDoa();
    }

    public function findAffiliationWithMissingLoi(): Query
    {
        /** @var Repository\Affiliation $repository */
        $repository = $this->entityManager->getRepository(Affiliation::class);

        return $repository->findAffiliationWithMissingLoi();
    }

    public function deactivateAffiliation(Affiliation $affiliation): void
    {
        $affiliation->setDateEnd(new DateTime());
        $this->save($affiliation);
        /*
         * Remove the current cost and effort of the affiliation
         */
        foreach ($affiliation->getEffort() as $effort) {
            $this->projectService->delete($effort);
        }
        /*
         * Remove the current cost and effort of the affiliation
         */
        foreach ($affiliation->getCost() as $cost) {
            $this->projectService->delete($cost);
        }
    }

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

    public function reactivateAffiliation(Affiliation $affiliation): void
    {
        $affiliation->setDateEnd(null);
        $this->save($affiliation);
    }

    public function findLogAffiliations(): array
    {
        /** @var Repository\Affiliation $repository */
        $repository = $this->entityManager->getRepository(Affiliation::class);

        return $repository->findAffiliationInProjectLog();
    }

    public function parseRenameOptions(
        Affiliation $baseAffiliation
    ): array {
        $options      = [];
        $organisation = $baseAffiliation->getOrganisation();
        $contact      = $baseAffiliation->getContact();

        /**
         * Go over the organisation and grab all its affiliations
         */
        foreach ($organisation->getAffiliation() as $affiliation) {
            $options[$affiliation->getOrganisation()->getCountry()->getCountry()]
            [$affiliation->getOrganisation()->getId()]
            [$affiliation->getBranch()]
                = $this->organisationService
                ->parseOrganisationWithBranch($affiliation->getBranch(), $affiliation->getOrganisation());
        }
        /**
         * Go over the contact and grab all its affiliations
         */
        foreach ($contact->getAffiliation() as $affiliation) {
            $options[$affiliation->getOrganisation()->getCountry()->getCountry()]
            [$affiliation->getOrganisation()->getId()]
            [$affiliation->getBranch()]
                = $this->organisationService
                ->parseOrganisationWithBranch($affiliation->getBranch(), $affiliation->getOrganisation());
        }
        /**
         * Add the contact organisation (from the contact)
         */
        if (null !== $contact->getContactOrganisation()) {
            $options[$contact->getContactOrganisation()->getOrganisation()->getCountry()
                ->getCountry()][$contact->getContactOrganisation()->getOrganisation()->getId()]
            [$contact->getContactOrganisation()->getBranch()]
                = $this->organisationService->parseOrganisationWithBranch(
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
                    = $this->organisationService->parseOrganisationWithBranch(
                        $contact->getContactOrganisation()
                        ->getBranch(),
                        $contact->getContactOrganisation()->getOrganisation()
                    );
            }
        }

        return $options;
    }
}
