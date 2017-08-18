<?php
/**
 * ITEA Office all rights reserved
 *
 * PHP Version 7
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */
declare(strict_types=1);

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
use Zend\ServiceManager\Factory\FactoryInterface;

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
     * @param array|null $options
     *
     * @return AssertionAbstract
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AssertionAbstract
    {
        /** @var AssertionAbstract $assertion */
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
}
