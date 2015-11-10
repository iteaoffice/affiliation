<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Navigation\Factory;

use Affiliation\Navigation\Service\AffiliationNavigationService;
use Affiliation\Service\AffiliationService;
use Project\Service\ReportService;
use Zend\Navigation\Navigation;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * NodeService.
 *
 * this is a wrapper for node entity related services
 */
class AffiliationNavigationServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return AffiliationNavigationService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $affiliationNavigationService = new AffiliationNavigationService();
        $affiliationNavigationService->setTranslator($serviceLocator->get('viewhelpermanager')->get('translate'));
        /**
         * @var AffiliationService $affiliationService
         */
        $affiliationService = $serviceLocator->get(AffiliationService::class);
        $affiliationNavigationService->setAffiliationService($affiliationService);
        /**
         * @var ReportService $reportService
         */
        $reportService = $serviceLocator->get(ReportService::class);
        $affiliationNavigationService->setReportService($reportService);

        $application = $serviceLocator->get('application');
        $affiliationNavigationService->setRouteMatch($application->getMvcEvent()->getRouteMatch());
        $affiliationNavigationService->setRouter($application->getMvcEvent()->getRouter());
        /**
         * @var Navigation $navigation
         */
        $navigation = $serviceLocator->get('navigation');
        $affiliationNavigationService->setNavigation($navigation);

        return $affiliationNavigationService;
    }
}
