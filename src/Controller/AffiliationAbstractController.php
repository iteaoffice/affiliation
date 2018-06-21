<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */
declare(strict_types=1);

namespace Affiliation\Controller;

use Affiliation\Controller\Plugin;
use Affiliation\Entity\Affiliation;
use Affiliation\Options\ModuleOptions;
use Affiliation\Search\Service\AffiliationSearchService;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\DoaService;
use Affiliation\Service\FormService;
use Affiliation\Service\LoiService;
use Contact\Entity\Contact;
use Contact\Service\ContactService;
use Deeplink\Service\DeeplinkService;
use Doctrine\ORM\EntityManager;
use General\Service\EmailService;
use General\Service\GeneralService;
use Invoice\Service\InvoiceService;
use Mailing\Service\MailingService;
use Organisation\Service\OrganisationService;
use Organisation\Service\ParentService;
use Program\Options\ModuleOptions as ProgramModuleOptions;
use Program\Service\CallService;
use Program\Service\ProgramService;
use Project\Service\ContractService;
use Project\Service\ProjectService;
use Project\Service\ReportService;
use Project\Service\VersionService;
use Project\Service\WorkpackageService;
use Search\Service\AbstractSearchService;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Zend\Mvc\Plugin\Identity\Identity;
use Zend\View\HelperPluginManager;
use ZfcUser\Controller\Plugin\ZfcUserAuthentication;

/**
 * @category    Affiliation
 *
 * @method      ZfcUserAuthentication zfcUserAuthentication()
 * @method      Identity|Contact identity()
 * @method      FlashMessenger flashMessenger()
 * @method      bool isAllowed($resource, $action)
 * @method      Plugin\RenderPaymentSheet renderPaymentSheet(Affiliation $affiliation, int $year, int $period, bool $useContractData)
 * @method      Plugin\RenderDoa renderDoa()
 * @method      Plugin\RenderLoi renderLoi()
 * @method      Plugin\GetFilter getAffiliationFilter
 * @method      Plugin\MergeAffiliation mergeAffiliation($mainAffiliation, $affiliation)
 * @method      Response csvExport(AbstractSearchService $searchService, array $fields, bool $header = true)
 *
 */
abstract class AffiliationAbstractController extends AbstractActionController
{
    /**
     * @var EntityManager
     */
    protected $entityManager;
    /**
     * @var ModuleOptions
     */
    protected $moduleOptions;
    /**
     * @var FormService
     */
    protected $formService;
    /**
     * @var AffiliationService
     */
    protected $affiliationService;
    /**
     * @var InvoiceService
     */
    protected $invoiceService;
    /**
     * @var \Invoice\Options\ModuleOptions;
     */
    protected $invoiceModuleOptions;
    /**
     * @var DeeplinkService
     */
    protected $deeplinkService;
    /**
     * @var OrganisationService
     */
    protected $organisationService;
    /**
     * @var ParentService
     */
    protected $parentService;
    /**
     * @var ProjectService
     */
    protected $projectService;
    /**
     * @var CallService
     */
    protected $callService;
    /**
     * @var ContractService
     */
    protected $contractService;
    /**
     * @var ReportService
     */
    protected $reportService;
    /**
     * @var VersionService
     */
    protected $versionService;
    /**
     * @var WorkpackageService
     */
    protected $workpackageService;
    /**
     * @var ContactService
     */
    protected $contactService;
    /**
     * @var ProgramService
     */
    protected $programService;
    /**
     * @var GeneralService
     */
    protected $generalService;
    /**
     * @var MailingService
     */
    protected $mailingService;
    /**
     * @var EmailService
     */
    protected $emailService;
    /**
     * @var DoaService
     */
    protected $doaService;
    /**
     * @var LoiService
     */
    protected $loiService;
    /**
     * @var AffiliationSearchService
     */
    protected $affiliationSearchService;
    /**
     * @var ProgramModuleOptions
     */
    protected $programModuleOptions;
    /**
     * @var HelperPluginManager
     */
    protected $viewHelperManager;

    /**
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     *
     * @return AffiliationAbstractController
     */
    public function setEntityManager($entityManager): AffiliationAbstractController
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    /**
     * @return ModuleOptions
     */
    public function getModuleOptions(): ModuleOptions
    {
        return $this->moduleOptions;
    }

    /**
     * @param ModuleOptions $moduleOptions
     *
     * @return AffiliationAbstractController
     */
    public function setModuleOptions($moduleOptions): AffiliationAbstractController
    {
        $this->moduleOptions = $moduleOptions;

        return $this;
    }

    /**
     * @return FormService
     */
    public function getFormService(): FormService
    {
        return $this->formService;
    }

    /**
     * @param FormService $formService
     *
     * @return AffiliationAbstractController
     */
    public function setFormService($formService): AffiliationAbstractController
    {
        $this->formService = $formService;

        return $this;
    }

    /**
     * @return AffiliationService
     */
    public function getAffiliationService(): AffiliationService
    {
        return $this->affiliationService;
    }

    /**
     * @param AffiliationService $affiliationService
     *
     * @return AffiliationAbstractController
     */
    public function setAffiliationService($affiliationService): AffiliationAbstractController
    {
        $this->affiliationService = $affiliationService;

        return $this;
    }

    /**
     * @return InvoiceService
     */
    public function getInvoiceService(): InvoiceService
    {
        return $this->invoiceService;
    }

    /**
     * @param InvoiceService $invoiceService
     *
     * @return AffiliationAbstractController
     */
    public function setInvoiceService($invoiceService): AffiliationAbstractController
    {
        $this->invoiceService = $invoiceService;

        return $this;
    }

    /**
     * @return \Invoice\Options\ModuleOptions
     */
    public function getInvoiceModuleOptions(): \Invoice\Options\ModuleOptions
    {
        return $this->invoiceModuleOptions;
    }

    /**
     * @param \Invoice\Options\ModuleOptions $invoiceModuleOptions
     *
     * @return AffiliationAbstractController
     */
    public function setInvoiceModuleOptions(
        \Invoice\Options\ModuleOptions $invoiceModuleOptions
    ): AffiliationAbstractController {
        $this->invoiceModuleOptions = $invoiceModuleOptions;

        return $this;
    }

    /**
     * @return DeeplinkService
     */
    public function getDeeplinkService(): DeeplinkService
    {
        return $this->deeplinkService;
    }

    /**
     * @param DeeplinkService $deeplinkService
     *
     * @return AffiliationAbstractController
     */
    public function setDeeplinkService($deeplinkService): AffiliationAbstractController
    {
        $this->deeplinkService = $deeplinkService;

        return $this;
    }

    /**
     * @return OrganisationService
     */
    public function getOrganisationService(): OrganisationService
    {
        return $this->organisationService;
    }

    /**
     * @param OrganisationService $organisationService
     *
     * @return AffiliationAbstractController
     */
    public function setOrganisationService($organisationService): AffiliationAbstractController
    {
        $this->organisationService = $organisationService;

        return $this;
    }

    /**
     * @return ParentService
     */
    public function getParentService(): ParentService
    {
        return $this->parentService;
    }

    /**
     * @param ParentService $parentService
     *
     * @return AffiliationAbstractController
     */
    public function setParentService(ParentService $parentService): AffiliationAbstractController
    {
        $this->parentService = $parentService;

        return $this;
    }

    /**
     * @return ProjectService
     */
    public function getProjectService(): ProjectService
    {
        return $this->projectService;
    }

    /**
     * @param ProjectService $projectService
     *
     * @return AffiliationAbstractController
     */
    public function setProjectService($projectService): AffiliationAbstractController
    {
        $this->projectService = $projectService;

        return $this;
    }

    /**
     * @return ReportService
     */
    public function getReportService(): ReportService
    {
        return $this->reportService;
    }

    /**
     * @param ReportService $reportService
     *
     * @return AffiliationAbstractController
     */
    public function setReportService($reportService): AffiliationAbstractController
    {
        $this->reportService = $reportService;

        return $this;
    }

    /**
     * @return VersionService
     */
    public function getVersionService(): VersionService
    {
        return $this->versionService;
    }

    /**
     * @param VersionService $versionService
     *
     * @return AffiliationAbstractController
     */
    public function setVersionService($versionService): AffiliationAbstractController
    {
        $this->versionService = $versionService;

        return $this;
    }

    /**
     * @return ContractService
     */
    public function getContractService(): ContractService
    {
        return $this->contractService;
    }

    /**
     * @param ContractService $contractService
     *
     * @return AffiliationAbstractController
     */
    public function setContractService(ContractService $contractService): AffiliationAbstractController
    {
        $this->contractService = $contractService;

        return $this;
    }

    /**
     * @return WorkpackageService
     */
    public function getWorkpackageService(): WorkpackageService
    {
        return $this->workpackageService;
    }

    /**
     * @param WorkpackageService $workpackageService
     *
     * @return AffiliationAbstractController
     */
    public function setWorkpackageService($workpackageService): AffiliationAbstractController
    {
        $this->workpackageService = $workpackageService;

        return $this;
    }

    /**
     * @return ContactService
     */
    public function getContactService(): ContactService
    {
        return $this->contactService;
    }

    /**
     * @param ContactService $contactService
     *
     * @return AffiliationAbstractController
     */
    public function setContactService($contactService): AffiliationAbstractController
    {
        $this->contactService = $contactService;

        return $this;
    }

    /**
     * @return ProgramService
     */
    public function getProgramService(): ProgramService
    {
        return $this->programService;
    }

    /**
     * @param ProgramService $programService
     *
     * @return AffiliationAbstractController
     */
    public function setProgramService($programService): AffiliationAbstractController
    {
        $this->programService = $programService;

        return $this;
    }

    /**
     * @return GeneralService
     */
    public function getGeneralService(): GeneralService
    {
        return $this->generalService;
    }

    /**
     * @param GeneralService $generalService
     *
     * @return AffiliationAbstractController
     */
    public function setGeneralService($generalService): AffiliationAbstractController
    {
        $this->generalService = $generalService;

        return $this;
    }

    /**
     * @return MailingService
     */
    public function getMailingService(): MailingService
    {
        return $this->mailingService;
    }

    /**
     * @param MailingService $mailingService
     *
     * @return AffiliationAbstractController
     */
    public function setMailingService($mailingService): AffiliationAbstractController
    {
        $this->mailingService = $mailingService;

        return $this;
    }

    /**
     * @return EmailService
     */
    public function getEmailService(): EmailService
    {
        return $this->emailService;
    }

    /**
     * @param EmailService $emailService
     *
     * @return AffiliationAbstractController
     */
    public function setEmailService($emailService): AffiliationAbstractController
    {
        $this->emailService = $emailService;

        return $this;
    }

    /**
     * @return CallService
     */
    public function getCallService(): CallService
    {
        return $this->callService;
    }

    /**
     * @param CallService $callService
     *
     * @return AffiliationAbstractController
     */
    public function setCallService($callService): AffiliationAbstractController
    {
        $this->callService = $callService;

        return $this;
    }

    /**
     * @return DoaService
     */
    public function getDoaService(): DoaService
    {
        return $this->doaService;
    }

    /**
     * @param DoaService $doaService
     *
     * @return AffiliationAbstractController
     */
    public function setDoaService($doaService): AffiliationAbstractController
    {
        $this->doaService = $doaService;

        return $this;
    }

    /**
     * @return LoiService
     */
    public function getLoiService(): LoiService
    {
        return $this->loiService;
    }

    /**
     * @param LoiService $loiService
     *
     * @return AffiliationAbstractController
     */
    public function setLoiService($loiService): AffiliationAbstractController
    {
        $this->loiService = $loiService;

        return $this;
    }

    /**
     * @return AffiliationSearchService
     */
    public function getAffiliationSearchService(): AffiliationSearchService
    {
        return $this->affiliationSearchService;
    }

    /**
     * @param AffiliationSearchService $affiliationSearchService
     *
     * @return AffiliationAbstractController
     */
    public function setAffiliationSearchService($affiliationSearchService): AffiliationAbstractController
    {
        $this->affiliationSearchService = $affiliationSearchService;

        return $this;
    }

    /**
     * @return ProgramModuleOptions
     */
    public function getProgramModuleOptions(): ProgramModuleOptions
    {
        return $this->programModuleOptions;
    }

    /**
     * @param ProgramModuleOptions $programModuleOptions
     *
     * @return AffiliationAbstractController
     */
    public function setProgramModuleOptions($programModuleOptions): AffiliationAbstractController
    {
        $this->programModuleOptions = $programModuleOptions;

        return $this;
    }

    /**
     * Proxy for the flash messenger helper to have the string translated earlier.
     *
     * @param $string
     *
     * @return string
     */
    protected function translate($string)
    {
        /*
         * @var Translate
         */
        $translate = $this->getViewHelperManager()->get('translate');

        return $translate($string);
    }

    /**
     * @return HelperPluginManager
     */
    public function getViewHelperManager(): HelperPluginManager
    {
        return $this->viewHelperManager;
    }

    /**
     * @param HelperPluginManager $viewHelperManager
     *
     * @return AffiliationAbstractController
     */
    public function setViewHelperManager(HelperPluginManager $viewHelperManager): AffiliationAbstractController
    {
        $this->viewHelperManager = $viewHelperManager;

        return $this;
    }
}
