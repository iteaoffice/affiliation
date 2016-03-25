<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * PHP Version 5
 *
 * @category    Achievement
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   2004-2016 ITEA Office
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/main for the canonical source repository
 */
namespace Affiliation\Search\Factory;

use Affiliation\Search\Service\AffiliationSearchService;
use Affiliation\Service\AffiliationService;
use Contact\Service\ContactService;
use Project\Service\ProjectService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AffiliationSearchFactory
 *
 * @package Affiliation\Search\Factory
 */
class AffiliationSearchFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return AffiliationSearchService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $searchService = new AffiliationSearchService();
        $searchService->setServiceLocator($serviceLocator);

        /** @var AffiliationService $affiliationService */
        $affiliationService = $serviceLocator->get(AffiliationService::class);
        $searchService->setAffiliationService($affiliationService);

        /** @var ProjectService $projectService */
        $projectService = $serviceLocator->get(ProjectService::class);
        $searchService->setProjectService($projectService);

        /** @var ContactService $contactService */
        $contactService = $serviceLocator->get(ContactService::class);
        $searchService->setContactService($contactService);

        return $searchService;
    }
}
