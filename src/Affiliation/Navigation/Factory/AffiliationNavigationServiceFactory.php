<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Service
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Affiliation\Navigation\Factory;

use Affiliation\Navigation\Service\AffiliationNavigationService;
use Affiliation\Service\AffiliationService;
use Zend\Navigation\Navigation;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * NodeService
 *
 * this is a wrapper for node entity related services
 *
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
         * @var $affiliationService AffiliationService
         */
        $affiliationService = $serviceLocator->get(AffiliationService::class);
        $affiliationNavigationService->setAffiliationService($affiliationService);
        /**
         * @var $affiliationService AffiliationService
         */
        $affiliationService = $serviceLocator->get(AffiliationService::class);
        $affiliationNavigationService->setAffiliationService($affiliationService);
        $application = $serviceLocator->get('application');
        $affiliationNavigationService->setRouteMatch($application->getMvcEvent()->getRouteMatch());
        $affiliationNavigationService->setRouter($application->getMvcEvent()->getRouter());
        /**
         * @var $navigation Navigation
         */
        $navigation = $serviceLocator->get('navigation');
        $affiliationNavigationService->setNavigation($navigation);

        return $affiliationNavigationService;
    }
}
