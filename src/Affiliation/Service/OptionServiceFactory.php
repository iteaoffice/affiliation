<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Service
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Affiliation\Service;

use Affiliation\Options\ModuleOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory responsible of retrieving an array containing the program configuration
 *
 * @author Johan van der Heide <johan.van.der.heide@itea3.org>
 */
class OptionServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ModuleOptions
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        return new ModuleOptions($config['affiliation_option']);
    }
}
