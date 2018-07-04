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
 * @link        http://github.com/iteaoffice/main for the canonical source repository
 */

declare(strict_types=1);

namespace Affiliation\Factory;

use Admin\Service\AdminService;
use Affiliation\Service\AffiliationService;
use BjyAuthorize\Service\Authorize;
use Doctrine\ORM\EntityManager;
use General\Service\EmailService;
use General\Service\GeneralService;
use Interop\Container\ContainerInterface;
use Invoice\Service\InvoiceService;
use Organisation\Service\OrganisationService;
use Organisation\Service\ParentService;
use Project\Service\ContractService;
use Project\Service\VersionService;
use Zend\ServiceManager\Factory\FactoryInterface;

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
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AffiliationService
    {
        /** @var AffiliationService $affiliationService */
        $affiliationService = new $requestedName($options);
        $affiliationService->setServiceLocator($container);

        /** @var EntityManager $entityManager */
        $entityManager = $container->get(EntityManager::class);
        $affiliationService->setEntityManager($entityManager);

        /** @var AdminService $adminService */
        $adminService = $container->get(AdminService::class);
        $affiliationService->setAdminService($adminService);

        /** @var GeneralService $generalService */
        $generalService = $container->get(GeneralService::class);
        $affiliationService->setGeneralService($generalService);

        /** @var InvoiceService $invoiceService */
        $invoiceService = $container->get(InvoiceService::class);
        $affiliationService->setInvoiceService($invoiceService);

        /** @var VersionService $versionService */
        $versionService = $container->get(VersionService::class);
        $affiliationService->setVersionService($versionService);

        /** @var ContractService $contractService */
        $contractService = $container->get(ContractService::class);
        $affiliationService->setContractService($contractService);

        /** @var ParentService $parentService */
        $parentService = $container->get(ParentService::class);
        $affiliationService->setParentService($parentService);

        /** @var OrganisationService $organisationService */
        $organisationService = $container->get(OrganisationService::class);
        $affiliationService->setOrganisationService($organisationService);

        /** @var Authorize $authorizeService */
        $authorizeService = $container->get(Authorize::class);
        $affiliationService->setAuthorizeService($authorizeService);

        /** @var EmailService $emailService */
        $emailService = $container->get(EmailService::class);
        $affiliationService->setEmailService($emailService);

        return $affiliationService;
    }
}
