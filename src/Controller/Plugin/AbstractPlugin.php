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

namespace Affiliation\Controller\Plugin;

use Affiliation\Options\ModuleOptions;
use Affiliation\Service\AffiliationService;
use Contact\Service\ContactService;
use General\Service\GeneralService;
use Invoice\Service\InvoiceService;
use Organisation\Service\OrganisationService;
use Project\Service\ProjectService;
use Project\Service\VersionService;
use Zend\Http\Request;
use Zend\I18n\View\Helper\Translate;
use Zend\Mvc\Controller\Plugin\AbstractPlugin as ZendAbstractPlugin;
use Zend\Router\Http\RouteMatch;
use Zend\View\HelperPluginManager;
use ZfcTwig\View\TwigRenderer;

/**
 * Class AbstractPlugin
 *
 * @package Affiliation\Controller\Plugin
 */
class AbstractPlugin extends ZendAbstractPlugin
{
    /**
     * @var HelperPluginManager
     */
    protected $helperPluginManager;

    /**
     * @var AffiliationService
     */
    protected $affiliationService;

    /**
     * @var ProjectService
     */
    protected $projectService;

    /**
     * @var ContactService
     */
    protected $contactService;

    /**
     * @var OrganisationService
     */
    protected $organisationService;

    /**
     * @var GeneralService
     */
    protected $generalService;

    /**
     * @var VersionService
     */
    protected $versionService;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var ModuleOptions
     */
    protected $moduleOptions;
    /**
     * @var TwigRenderer
     */
    protected $twigRenderer;
    /**
     * @var RouteMatch
     */
    protected $routeMatch;
    /**
     * @var Request
     */
    protected $request;

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
     * @return AbstractPlugin
     */
    public function setAffiliationService(AffiliationService $affiliationService): AbstractPlugin
    {
        $this->affiliationService = $affiliationService;

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
     * @return AbstractPlugin
     */
    public function setProjectService(ProjectService $projectService): AbstractPlugin
    {
        $this->projectService = $projectService;

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
     * @return AbstractPlugin
     */
    public function setContactService(ContactService $contactService): AbstractPlugin
    {
        $this->contactService = $contactService;

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
     * @return AbstractPlugin
     */
    public function setOrganisationService(OrganisationService $organisationService): AbstractPlugin
    {
        $this->organisationService = $organisationService;

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
     * @return AbstractPlugin
     */
    public function setModuleOptions(ModuleOptions $moduleOptions): AbstractPlugin
    {
        $this->moduleOptions = $moduleOptions;

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
     * @return AbstractPlugin
     */
    public function setGeneralService(GeneralService $generalService): AbstractPlugin
    {
        $this->generalService = $generalService;

        return $this;
    }

    /**
     * @return TwigRenderer
     */
    public function getTwigRenderer(): TwigRenderer
    {
        return $this->twigRenderer;
    }

    /**
     * @param TwigRenderer $twigRenderer
     *
     * @return AbstractPlugin
     */
    public function setTwigRenderer(TwigRenderer $twigRenderer): AbstractPlugin
    {
        $this->twigRenderer = $twigRenderer;

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
     * @return AbstractPlugin
     */
    public function setVersionService(VersionService $versionService): AbstractPlugin
    {
        $this->versionService = $versionService;

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
     * @return AbstractPlugin
     */
    public function setInvoiceService(InvoiceService $invoiceService): AbstractPlugin
    {
        $this->invoiceService = $invoiceService;

        return $this;
    }

    /**
     * @return RouteMatch
     */
    public function getRouteMatch(): RouteMatch
    {
        return $this->routeMatch;
    }

    /**
     * @param RouteMatch $routeMatch
     *
     * @return AbstractPlugin
     */
    public function setRouteMatch(RouteMatch $routeMatch): AbstractPlugin
    {
        $this->routeMatch = $routeMatch;

        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param Request $request
     *
     * @return AbstractPlugin
     */
    public function setRequest(Request $request): AbstractPlugin
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Translate a string
     *
     * @param $string
     *
     * @return string
     */
    protected function translate($string): string
    {
        /** @var Translate $translator */
        $translator = $this->getHelperPluginManager()->get('translate');

        return $translator ? $translator($string) : $string;
    }

    /**
     * @return HelperPluginManager
     */
    public function getHelperPluginManager(): HelperPluginManager
    {
        return $this->helperPluginManager;
    }

    /**
     * @param HelperPluginManager $helperPluginManager
     *
     * @return AbstractPlugin
     */
    public function setHelperPluginManager(HelperPluginManager $helperPluginManager): AbstractPlugin
    {
        $this->helperPluginManager = $helperPluginManager;

        return $this;
    }
}
