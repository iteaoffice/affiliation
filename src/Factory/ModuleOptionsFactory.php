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

use Affiliation\Options\ModuleOptions;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ModuleOptionsFactory
 *
 * @package Affiliation\Factory
 */
final class ModuleOptionsFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return ModuleOptions
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');

        return new ModuleOptions(isset($config['affiliation_option']) ? $config['affiliation_option'] : []);
    }

    /**
     * @param ServiceLocatorInterface $container
     * @param string                  $canonicalName
     * @param string                  $requestedName
     *
     * @return ModuleOptions
     */
    public function createService(ServiceLocatorInterface $container, $canonicalName = null, $requestedName = null)
    {
        return $this($container, $requestedName);
    }
}
