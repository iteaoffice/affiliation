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
 * @link        http://github.com/iteaoffice/main for the canonical source repository
 */
namespace Affiliation\Factory;

use Admin\Service\AdminService;
use Affiliation\Service\AffiliationService;
use BjyAuthorize\Service\Authorize;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Invoice\Service\InvoiceService;
use Organisation\Service\OrganisationService;
use Project\Service\VersionService;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AffiliationServiceFactory
 *
 * @package Affiliation\Factory
 */
final class AffiliationServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return AffiliationService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $affiliationService = new AffiliationService($options);
        $affiliationService->setServiceLocator($container);

        /** @var EntityManager $entityManager */
        $entityManager = $container->get(EntityManager::class);
        $affiliationService->setEntityManager($entityManager);

        /** @var AdminService $adminService */
        $adminService = $container->get(AdminService::class);
        $affiliationService->setAdminService($adminService);

        /** @var InvoiceService $invoiceService */
        $invoiceService = $container->get(InvoiceService::class);
        $affiliationService->setInvoiceService($invoiceService);

        /** @var VersionService $versionService */
        $versionService = $container->get(VersionService::class);
        $affiliationService->setVersionService($versionService);

        /** @var OrganisationService $organisationService */
        $organisationService = $container->get(OrganisationService::class);
        $affiliationService->setOrganisationService($organisationService);

        /** @var Authorize $authorizeService */
        $authorizeService = $container->get(Authorize::class);
        $affiliationService->setAuthorizeService($authorizeService);

        return $affiliationService;
    }

    /**
     * @param ServiceLocatorInterface $container
     * @param string                  $canonicalName
     * @param string                  $requestedName
     *
     * @return AffiliationService
     */
    public function createService(ServiceLocatorInterface $container, $canonicalName = null, $requestedName = null)
    {
        return $this($container, $requestedName);
    }
}
