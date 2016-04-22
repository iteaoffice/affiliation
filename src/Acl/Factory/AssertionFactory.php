<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * PHP Version 5
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   2004-2016 ITEA Office
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */
namespace Affiliation\Acl\Factory;

use Admin\Service\AdminService;
use Affiliation\Acl\Assertion\AssertionAbstract;
use Affiliation\Service\AffiliationService;
use Contact\Service\ContactService;
use Interop\Container\ContainerInterface;
use Project\Acl\Assertion\Project as ProjectAssertion;
use Project\Service\ProjectService;
use Project\Service\ReportService;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AssertionFactory
 *
 * @package Affiliation\Acl\Factory
 */
class AssertionFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param                    $requestedName
     * @param array|null         $options
     *
     * @return mixed
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var $assertion AssertionAbstract */
        $assertion = new $requestedName($options);
        $assertion->setServiceLocator($container);

        /** @var AffiliationService $affiliationService */
        $affiliationService = $container->get(AffiliationService::class);
        $assertion->setAffiliationService($affiliationService);

        /** @var AdminService $adminService */
        $adminService = $container->get(AdminService::class);
        $assertion->setAdminService($adminService);

        /** @var ContactService $contactService */
        $contactService = $container->get(ContactService::class);
        $assertion->setContactService($contactService);

        //Inject the logged in user if applicable
        /** @var AuthenticationService $authenticationService */
        $authenticationService = $container->get('Application\Authentication\Service');
        if ($authenticationService->hasIdentity()) {
            $assertion->setContact($authenticationService->getIdentity());
        }

        /** @var ProjectService $projectService */
        $projectService = $container->get(ProjectService::class);
        $assertion->setProjectService($projectService);

        /** @var ReportService $reportService */
        $reportService = $container->get(ReportService::class);
        $assertion->setReportService($reportService);

        /** @var ProjectAssertion $projectAssertion */
        $projectAssertion = $container->get(ProjectAssertion::class);
        $assertion->setProjectAssertion($projectAssertion);

        return $assertion;
    }

    /**
     * @param ServiceLocatorInterface $container
     * @param null                    $canonicalName
     * @param                         $requestedName
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $container, $canonicalName = null, $requestedName = null)
    {
        return $this($container, $requestedName);
    }
}
