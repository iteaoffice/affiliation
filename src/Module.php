<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    SoloDB
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 *
 * @version     4.0
 */

namespace Affiliation;

use Zend\ModuleManager\Feature;

//Makes the module class more strict
/**
 *
 */
class Module implements Feature\AutoloaderProviderInterface, Feature\ConfigProviderInterface
{
    /**
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\ClassMapAutoloader' => [
                __DIR__ . '/../autoload_classmap.php',
            ],
        ];
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
