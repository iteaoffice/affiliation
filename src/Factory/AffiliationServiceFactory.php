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
use Invoice\Service\InvoiceService;
use Organisation\Service\OrganisationService;
use Project\Service\VersionService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AffiliationServiceFactory
 *
 * @package Affiliation\Factory
 */
class AffiliationServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return AffiliationService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $affiliationService = new AffiliationService();
        $affiliationService->setServiceLocator($serviceLocator);

        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);
        $affiliationService->setEntityManager($entityManager);

        /** @var AdminService $adminService */
        $adminService = $serviceLocator->get(AdminService::class);
        $affiliationService->setAdminService($adminService);

        /** @var InvoiceService $invoiceService */
        $invoiceService = $serviceLocator->get(InvoiceService::class);
        $affiliationService->setInvoiceService($invoiceService);

        /** @var VersionService $versionService */
        $versionService = $serviceLocator->get(VersionService::class);
        $affiliationService->setVersionService($versionService);

        /** @var OrganisationService $organisationService */
        $organisationService = $serviceLocator->get(OrganisationService::class);
        $affiliationService->setOrganisationService($organisationService);

        /** @var Authorize $authorizeService */
        $authorizeService = $serviceLocator->get(Authorize::class);
        $affiliationService->setAuthorizeService($authorizeService);

        return $affiliationService;
    }
}
