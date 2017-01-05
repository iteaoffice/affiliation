<?php
/**
 * ITEA Office all rights reserved
 *
 * PHP Version 7
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */
declare(strict_types=1);

namespace Affiliation\Controller\Factory;

use Affiliation\Controller\AffiliationAbstractController;
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
use Interop\Container\ContainerInterface;
use Invoice\Service\InvoiceService;
use Organisation\Service\OrganisationService;
use Organisation\Service\ParentService;
use Program\Options\ModuleOptions as ProgramModuleOptions;
use Program\Service\ProgramService;
use Project\Service\ProjectService;
use Project\Service\ReportService;
use Project\Service\VersionService;
use Project\Service\WorkpackageService;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\View\HelperPluginManager;

/**
 * Class ControllerFactory
 *
 * @package Project\Controller\Factory
 */
final class ControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface|ControllerManager $container
     * @param string                               $requestedName
     * @param array|null                           $options
     *
     * @return AffiliationAbstractController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var AffiliationAbstractController $controller */
        $controller = new $requestedName($options);

        /** @var FormService $formService */
        $formService = $container->get(FormService::class);
        $controller->setFormService($formService);

        /** @var EntityManager $entityManager */
        $entityManager = $container->get(EntityManager::class);
        $controller->setEntityManager($entityManager);

        /** @var ProjectService $projectService */
        $projectService = $container->get(ProjectService::class);
        $controller->setProjectService($projectService);

        /** @var VersionService $versionService */
        $versionService = $container->get(VersionService::class);
        $controller->setVersionService($versionService);

        /** @var WorkpackageService $workpackageService */
        $workpackageService = $container->get(WorkpackageService::class);
        $controller->setWorkpackageService($workpackageService);

        /** @var AffiliationService $affiliationService */
        $affiliationService = $container->get(AffiliationService::class);
        $controller->setAffiliationService($affiliationService);

        /** @var GeneralService $generalService */
        $generalService = $container->get(GeneralService::class);
        $controller->setGeneralService($generalService);

        /** @var ContactService $contactService */
        $contactService = $container->get(ContactService::class);
        $controller->setContactService($contactService);

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get(ModuleOptions::class);
        $controller->setModuleOptions($moduleOptions);

        /** @var ProgramService $programService */
        $programService = $container->get(ProgramService::class);
        $controller->setProgramService($programService);

        /** @var OrganisationService $organisationService */
        $organisationService = $container->get(OrganisationService::class);
        $controller->setOrganisationService($organisationService);

        /** @var ParentService $parentService */
        $parentService = $container->get(ParentService::class);
        $controller->setParentService($parentService);

        /** @var DeeplinkService $deeplinkService */
        $deeplinkService = $container->get(DeeplinkService::class);
        $controller->setDeeplinkService($deeplinkService);

        /** @var LoiService $loiService */
        $loiService = $container->get(LoiService::class);
        $controller->setLoiService($loiService);

        /** @var DoaService $doaService */
        $doaService = $container->get(DoaService::class);
        $controller->setDoaService($doaService);

        /** @var EmailService $emailService */
        $emailService = $container->get(EmailService::class);
        $controller->setEmailService($emailService);

        /** @var InvoiceService $invoiceService */
        $invoiceService = $container->get(InvoiceService::class);
        $controller->setInvoiceService($invoiceService);

        /** @var ReportService $reportService */
        $reportService = $container->get(ReportService::class);
        $controller->setReportService($reportService);

        /** @var AffiliationSearchService $affiliationSearchService */
        $affiliationSearchService = $container->get(AffiliationSearchService::class);
        $controller->setAffiliationSearchService($affiliationSearchService);

        /** @var ProgramModuleOptions $programModuleOptions */
        $programModuleOptions = $container->get(ProgramModuleOptions::class);
        $controller->setProgramModuleOptions($programModuleOptions);

        /** @var HelperPluginManager $viewHelperManager */
        $viewHelperManager = $container->get('ViewHelperManager');
        $controller->setViewHelperManager($viewHelperManager);

        return $controller;
    }
}
