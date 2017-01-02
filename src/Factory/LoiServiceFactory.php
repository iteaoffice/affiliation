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
namespace Affiliation\Factory;

use Admin\Service\AdminService;
use Affiliation\Service\LoiService;
use BjyAuthorize\Service\Authorize;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class DoaServiceFactory
 *
 * @package Affiliation\Factory
 */
final class LoiServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return LoiService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LoiService
    {
        /** @var LoiService $loiService */
        $loiService = new $requestedName($options);
        $loiService->setServiceLocator($container);

        /** @var EntityManager $entityManager */
        $entityManager = $container->get(EntityManager::class);
        $loiService->setEntityManager($entityManager);

        /** @var AdminService $adminService */
        $adminService = $container->get(AdminService::class);
        $loiService->setAdminService($adminService);

        /** @var Authorize $authorizeService */
        $authorizeService = $container->get(Authorize::class);
        $loiService->setAuthorizeService($authorizeService);

        return $loiService;
    }
}
