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
use Interop\Container\ContainerInterface;
use Project\Service\ProjectService;
use Project\Service\VersionService;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class AffiliationSearchFactory
 *
 * @package Affiliation\Search\Factory
 */
final class AffiliationSearchFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return AffiliationSearchService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null
    ): AffiliationSearchService
    {
        /** @var AffiliationSearchService $searchService */
        $searchService = new $requestedName($options);
        $searchService->setServiceLocator($container);

        /** @var AffiliationService $affiliationService */
        $affiliationService = $container->get(AffiliationService::class);
        $searchService->setAffiliationService($affiliationService);

        /** @var ProjectService $projectService */
        $projectService = $container->get(ProjectService::class);
        $searchService->setProjectService($projectService);

        /** @var VersionService $versionService */
        $versionService = $container->get(VersionService::class);
        $searchService->setVersionService($versionService);

        /** @var ContactService $contactService */
        $contactService = $container->get(ContactService::class);
        $searchService->setContactService($contactService);

        return $searchService;
    }
}
