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
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class GeneralServiceFactory
 *
 * @package General\Factory
 */
class ModuleOptionsFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ModuleOptions
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        return new ModuleOptions(isset($config['affiliation_option']) ? $config['affiliation_option'] : []);
    }
}
