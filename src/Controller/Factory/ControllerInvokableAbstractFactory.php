<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category  Publication
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2016 ITEA Office (http://itea3.org)
 */

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
use Invoice\Service\InvoiceService;
use Member\Service\MemberService;
use Organisation\Service\OrganisationService;
use Program\Options\ModuleOptions as ProgramModuleOptions;
use Program\Service\ProgramService;
use Project\Service\ProjectService;
use Project\Service\ReportService;
use Project\Service\VersionService;
use Project\Service\WorkpackageService;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ControllerInvokableAbstractFactory
 *
 * @package Affiliation\Controller\Factory
 */
class ControllerInvokableAbstractFactory implements AbstractFactoryInterface
{
    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param                         $name
     * @param                         $requestedName
     *
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return (class_exists($requestedName)
            && in_array(AffiliationAbstractController::class, class_parents($requestedName)));
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface|ControllerManager $serviceLocator
     * @param string                                    $name
     * @param string                                    $requestedName
     *
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {

        /** @var AffiliationAbstractController $controller */
        $controller = new $requestedName();
        $controller->setServiceLocator($serviceLocator);

        $serviceManager = $serviceLocator->getServiceLocator();
        /** @var FormService $formService */
        $formService = $serviceManager->get(FormService::class);
        $controller->setFormService($formService);

        /** @var EntityManager $entityManager */
        $entityManager = $serviceManager->get(EntityManager::class);
        $controller->setEntityManager($entityManager);

        /** @var ProjectService $projectService */
        $projectService = $serviceManager->get(ProjectService::class);
        $controller->setProjectService($projectService);

        /** @var VersionService $versionService */
        $versionService = $serviceManager->get(VersionService::class);
        $controller->setVersionService($versionService);

        /** @var WorkpackageService $workpackageService */
        $workpackageService = $serviceManager->get(WorkpackageService::class);
        $controller->setWorkpackageService($workpackageService);

        /** @var AffiliationService $affiliationService */
        $affiliationService = $serviceManager->get(AffiliationService::class);
        $controller->setAffiliationService($affiliationService);

        /** @var GeneralService $generalService */
        $generalService = $serviceManager->get(GeneralService::class);
        $controller->setGeneralService($generalService);

        /** @var ContactService $contactService */
        $contactService = clone $serviceManager->get(ContactService::class);
        $controller->setContactService($contactService);

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $serviceManager->get(ModuleOptions::class);
        $controller->setModuleOptions($moduleOptions);

        /** @var ProgramService $programService */
        $programService = $serviceManager->get(ProgramService::class);
        $controller->setProgramService($programService);

        /** @var MemberService $memberService */
        $memberService = $serviceManager->get(MemberService::class);
        $controller->setMemberService($memberService);

        /** @var OrganisationService $organisationService */
        $organisationService = $serviceManager->get(OrganisationService::class);
        $controller->setOrganisationService($organisationService);

        /** @var DeeplinkService $deeplinkService */
        $deeplinkService = $serviceManager->get(DeeplinkService::class);
        $controller->setDeeplinkService($deeplinkService);

        /** @var LoiService $loiService */
        $loiService = $serviceManager->get(LoiService::class);
        $controller->setLoiService($loiService);

        /** @var DoaService $doaService */
        $doaService = $serviceManager->get(DoaService::class);
        $controller->setDoaService($doaService);

        /** @var EmailService $emailService */
        $emailService = $serviceManager->get(EmailService::class);
        $controller->setEmailService($emailService);

        /** @var InvoiceService $invoiceService */
        $invoiceService = $serviceManager->get(InvoiceService::class);
        $controller->setInvoiceService($invoiceService);

        /** @var ReportService $reportService */
        $reportService = $serviceManager->get(ReportService::class);
        $controller->setReportService($reportService);

        /** @var AffiliationSearchService $affiliationSearchService */
        $affiliationSearchService = $serviceManager->get(AffiliationSearchService::class);
        $controller->setAffiliationSearchService($affiliationSearchService);

        /** @var ProgramModuleOptions $programModuleOptions */
        $programModuleOptions = $serviceManager->get(ProgramModuleOptions::class);
        $controller->setProgramModuleOptions($programModuleOptions);

        return $controller;
    }
}
