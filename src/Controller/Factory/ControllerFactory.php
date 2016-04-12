<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * PHP Version 5
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   2004-2016 ITEA Office
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
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
use Interop\Container\ContainerInterface;
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
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ControllerFactory
 *
 * @package Project\Controller\Factory
 */
class ControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface|ControllerManager $container
     * @param                                      $requestedName
     * @param array|null                           $options
     *
     * @return mixed
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var AffiliationAbstractController $controller */
        $controller = new $requestedName();
        $controller->setServiceLocator($container);

        $serviceManager = $container->getServiceLocator();

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
        $contactService = $serviceManager->get(ContactService::class);
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

    /**
     * @param ServiceLocatorInterface $container
     * @param string                  $canonicalName
     * @param string                  $requestedName
     *
     * @return AffiliationAbstractController
     */
    public function createService(ServiceLocatorInterface $container, $canonicalName = null, $requestedName = null)
    {
        return $this($container, $requestedName);
    }
}
