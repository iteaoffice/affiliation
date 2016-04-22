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
use Affiliation\Service\DoaService;
use BjyAuthorize\Service\Authorize;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class DoaServiceFactory
 *
 * @package Affiliation\Factory
 */
final class DoaServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return DoaService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $doaService = new DoaService($options);
        $doaService->setServiceLocator($container);

        /** @var EntityManager $entityManager */
        $entityManager = $container->get(EntityManager::class);
        $doaService->setEntityManager($entityManager);

        /** @var AdminService $adminService */
        $adminService = $container->get(AdminService::class);
        $doaService->setAdminService($adminService);

        /** @var Authorize $authorizeService */
        $authorizeService = $container->get(Authorize::class);
        $doaService->setAuthorizeService($authorizeService);

        return $doaService;
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
