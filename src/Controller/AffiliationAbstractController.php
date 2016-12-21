<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Controller;

use Affiliation\Controller\Plugin;
use Affiliation\Options\ModuleOptions;
use Affiliation\Search\Service\AffiliationSearchService;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\DoaService;
use Affiliation\Service\FormService;
use Affiliation\Service\LoiService;
use Contact\Service\ContactService;
use Deeplink\Service\DeeplinkService;
use Doctrine\ORM\EntityManager;
use General\Service\EmailService;
use General\Service\GeneralService;
use Invoice\Service\InvoiceService;
use Mailing\Service\MailingService;
use Organisation\Service\OrganisationService;
use Program\Options\ModuleOptions as ProgramModuleOptions;
use Program\Service\ProgramService;
use Project\Service\ProjectService;
use Project\Service\ReportService;
use Project\Service\VersionService;
use Project\Service\WorkpackageService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Zend\View\HelperPluginManager;
use ZfcUser\Controller\Plugin\ZfcUserAuthentication;

/**
 * @category    Affiliation
 *
 * @method      ZfcUserAuthentication zfcUserAuthentication()
 * @method      FlashMessenger flashMessenger()
 * @method      bool isAllowed($resource, $action)
 * @method      Plugin\RenderPaymentSheet renderPaymentSheet()
 * @method      Plugin\RenderDoa renderDoa()
 * @method      Plugin\RenderLoi renderLoi()
 * @method      Plugin\GetFilter getAffiliationFilter
 * @method      Plugin\MergeAffiliation mergeAffiliation($mainAffiliation, $affiliation)
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
     * @var DeeplinkService
     */
    protected $deeplinkService;
    /**
     * @var OrganisationService
     */
    protected $organisationService;
    /**
     * @var ProjectService
     */
    protected $projectService;
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
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     *
     * @return AffiliationAbstractController
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    /**
     * @return ModuleOptions
     */
    public function getModuleOptions()
    {
        return $this->moduleOptions;
    }

    /**
     * @param ModuleOptions $moduleOptions
     *
     * @return AffiliationAbstractController
     */
    public function setModuleOptions($moduleOptions)
    {
        $this->moduleOptions = $moduleOptions;

        return $this;
    }

    /**
     * @return FormService
     */
    public function getFormService()
    {
        return $this->formService;
    }

    /**
     * @param FormService $formService
     *
     * @return AffiliationAbstractController
     */
    public function setFormService($formService)
    {
        $this->formService = $formService;

        return $this;
    }

    /**
     * @return AffiliationService
     */
    public function getAffiliationService()
    {
        return $this->affiliationService;
    }

    /**
     * @param AffiliationService $affiliationService
     *
     * @return AffiliationAbstractController
     */
    public function setAffiliationService($affiliationService)
    {
        $this->affiliationService = $affiliationService;

        return $this;
    }

    /**
     * @return InvoiceService
     */
    public function getInvoiceService()
    {
        return $this->invoiceService;
    }

    /**
     * @param InvoiceService $invoiceService
     *
     * @return AffiliationAbstractController
     */
    public function setInvoiceService($invoiceService)
    {
        $this->invoiceService = $invoiceService;

        return $this;
    }

    /**
     * @return DeeplinkService
     */
    public function getDeeplinkService()
    {
        return $this->deeplinkService;
    }

    /**
     * @param DeeplinkService $deeplinkService
     *
     * @return AffiliationAbstractController
     */
    public function setDeeplinkService($deeplinkService)
    {
        $this->deeplinkService = $deeplinkService;

        return $this;
    }

    /**
     * @return OrganisationService
     */
    public function getOrganisationService()
    {
        return $this->organisationService;
    }

    /**
     * @param OrganisationService $organisationService
     *
     * @return AffiliationAbstractController
     */
    public function setOrganisationService($organisationService)
    {
        $this->organisationService = $organisationService;

        return $this;
    }

    /**
     * @return ProjectService
     */
    public function getProjectService()
    {
        return $this->projectService;
    }

    /**
     * @param ProjectService $projectService
     *
     * @return AffiliationAbstractController
     */
    public function setProjectService($projectService)
    {
        $this->projectService = $projectService;

        return $this;
    }

    /**
     * @return ReportService
     */
    public function getReportService()
    {
        return $this->reportService;
    }

    /**
     * @param ReportService $reportService
     *
     * @return AffiliationAbstractController
     */
    public function setReportService($reportService)
    {
        $this->reportService = $reportService;

        return $this;
    }

    /**
     * @return VersionService
     */
    public function getVersionService()
    {
        return $this->versionService;
    }

    /**
     * @param VersionService $versionService
     *
     * @return AffiliationAbstractController
     */
    public function setVersionService($versionService)
    {
        $this->versionService = $versionService;

        return $this;
    }

    /**
     * @return WorkpackageService
     */
    public function getWorkpackageService()
    {
        return $this->workpackageService;
    }

    /**
     * @param WorkpackageService $workpackageService
     *
     * @return AffiliationAbstractController
     */
    public function setWorkpackageService($workpackageService)
    {
        $this->workpackageService = $workpackageService;

        return $this;
    }

    /**
     * @return ContactService
     */
    public function getContactService()
    {
        return $this->contactService;
    }

    /**
     * @param ContactService $contactService
     *
     * @return AffiliationAbstractController
     */
    public function setContactService($contactService)
    {
        $this->contactService = $contactService;

        return $this;
    }

    /**
     * @return ProgramService
     */
    public function getProgramService()
    {
        return $this->programService;
    }

    /**
     * @param ProgramService $programService
     *
     * @return AffiliationAbstractController
     */
    public function setProgramService($programService)
    {
        $this->programService = $programService;

        return $this;
    }

    /**
     * @return GeneralService
     */
    public function getGeneralService()
    {
        return $this->generalService;
    }

    /**
     * @param GeneralService $generalService
     *
     * @return AffiliationAbstractController
     */
    public function setGeneralService($generalService)
    {
        $this->generalService = $generalService;

        return $this;
    }

    /**
     * @return MailingService
     */
    public function getMailingService()
    {
        return $this->mailingService;
    }

    /**
     * @param MailingService $mailingService
     *
     * @return AffiliationAbstractController
     */
    public function setMailingService($mailingService)
    {
        $this->mailingService = $mailingService;

        return $this;
    }

    /**
     * @return EmailService
     */
    public function getEmailService()
    {
        return $this->emailService;
    }

    /**
     * @param EmailService $emailService
     *
     * @return AffiliationAbstractController
     */
    public function setEmailService($emailService)
    {
        $this->emailService = $emailService;

        return $this;
    }

    /**
     * @return DoaService
     */
    public function getDoaService()
    {
        return $this->doaService;
    }

    /**
     * @param DoaService $doaService
     *
     * @return AffiliationAbstractController
     */
    public function setDoaService($doaService)
    {
        $this->doaService = $doaService;

        return $this;
    }

    /**
     * @return LoiService
     */
    public function getLoiService()
    {
        return $this->loiService;
    }

    /**
     * @param LoiService $loiService
     *
     * @return AffiliationAbstractController
     */
    public function setLoiService($loiService)
    {
        $this->loiService = $loiService;

        return $this;
    }

    /**
     * @return AffiliationSearchService
     */
    public function getAffiliationSearchService()
    {
        return $this->affiliationSearchService;
    }

    /**
     * @param AffiliationSearchService $affiliationSearchService
     *
     * @return AffiliationAbstractController
     */
    public function setAffiliationSearchService($affiliationSearchService)
    {
        $this->affiliationSearchService = $affiliationSearchService;

        return $this;
    }

    /**
     * @return ProgramModuleOptions
     */
    public function getProgramModuleOptions()
    {
        return $this->programModuleOptions;
    }

    /**
     * @param ProgramModuleOptions $programModuleOptions
     *
     * @return AffiliationAbstractController
     */
    public function setProgramModuleOptions($programModuleOptions)
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
