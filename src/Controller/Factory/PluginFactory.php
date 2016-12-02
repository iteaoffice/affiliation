<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2016 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Controller\Factory;

use Affiliation\Options\ModuleOptions;
use Affiliation\Service\AffiliationService;
use Contact\Service\ContactService;
use General\Service\GeneralService;
use Interop\Container\ContainerInterface;
use Affiliation\Controller\Plugin\AbstractPlugin;
use Invoice\Service\InvoiceService;
use Organisation\Service\OrganisationService;
use Project\Service\ProjectService;
use Project\Service\VersionService;
use Zend\Mvc\Controller\PluginManager;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\View\HelperPluginManager;
use ZfcTwig\View\TwigRenderer;

/**
 * Class PluginFactory
 * @package Affiliation\Controller\Factory
 */
final class PluginFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface|PluginManager     $container
     * @param                                      $requestedName
     * @param array|null                           $options
     *
     * @return AbstractPlugin
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var AbstractPlugin $plugin */
        $plugin = new $requestedName($options);

        /** @var HelperPluginManager $helperPluginManager */
        $helperPluginManager = $container->get('ViewHelperManager');
        $plugin->setHelperPluginManager($helperPluginManager);

        /** @var AffiliationService $affiliationService */
        $affiliationService = $container->get(AffiliationService::class);
        $plugin->setAffiliationService($affiliationService);

        /** @var ProjectService $projectService */
        $projectService = $container->get(ProjectService::class);
        $plugin->setProjectService($projectService);

        /** @var ContactService $contactService */
        $contactService = $container->get(ContactService::class);
        $plugin->setContactService($contactService);

        /** @var OrganisationService $organisationService */
        $organisationService = $container->get(OrganisationService::class);
        $plugin->setOrganisationService($organisationService);

        /** @var GeneralService $generalService */
        $generalService = $container->get(GeneralService::class);
        $plugin->setGeneralService($generalService);

        /** @var VersionService $versionService */
        $versionService = $container->get(VersionService::class);
        $plugin->setVersionService($versionService);

        /** @var InvoiceService $invoiceService */
        $invoiceService = $container->get(InvoiceService::class);
        $plugin->setInvoiceService($invoiceService);

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get(ModuleOptions::class);
        $plugin->setModuleOptions($moduleOptions);

        /** @var TwigRenderer $twigRenderer */
        $twigRenderer = $container->get('ZfcTwigRenderer');
        $plugin->setTwigRenderer($twigRenderer);

        return $plugin;
    }
}
