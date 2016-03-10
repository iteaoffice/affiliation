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
use Affiliation\Service\LoiService;
use BjyAuthorize\Service\Authorize;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class DoaServiceFactory
 *
 * @package Affiliation\Factory
 */
class LoiServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return AffiliationService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $loiService = new LoiService();
        $loiService->setServiceLocator($serviceLocator);

        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);
        $loiService->setEntityManager($entityManager);

        /** @var AdminService $adminService */
        $adminService = $serviceLocator->get(AdminService::class);
        $loiService->setAdminService($adminService);

        /** @var Authorize $authorizeService */
        $authorizeService = $serviceLocator->get(Authorize::class);
        $loiService->setAuthorizeService($authorizeService);

        return $loiService;
    }
}
