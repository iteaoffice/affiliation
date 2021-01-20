<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace AffiliationTest;

use Affiliation\Module;
use Affiliation\Search\Service\AffiliationSearchService;
use Testing\Util\AbstractServiceTest;
use Laminas\Mvc\Application;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Laminas\View\HelperPluginManager;

/**
 * Class GeneralTest
 *
 * @package GeneralTest\Entity
 */
class ModuleTest extends AbstractServiceTest
{
    public function testCanFindConfiguration(): void
    {
        $module = new Module();
        $config = $module->getConfig();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('service_manager', $config);
        $this->assertArrayHasKey(ConfigAbstractFactory::class, $config);
    }

    public function testInstantiationOfConfigAbstractFactories(): void
    {
        $module = new Module();
        $config = $module->getConfig();

        $abstractFacories = $config[ConfigAbstractFactory::class] ?? [];

        foreach ($abstractFacories as $service => $dependencies) {
            $instantiatedDependencies = [];
            foreach ($dependencies as $dependency) {
                if ($dependency === 'Application') {
                    $dependency = Application::class;
                }
                if ($dependency === 'ViewHelperManager') {
                    $dependency = HelperPluginManager::class;
                }
                if ($dependency === 'ControllerPluginManager') {
                    $dependency = PluginManager::class;
                }

                $instantiatedDependencies[]
                    = $this->getMockBuilder($dependency)->disableOriginalConstructor()->getMock();
            }

            $instance = new $service(...$instantiatedDependencies);

            $this->assertInstanceOf($service, $instance);
        }
    }
}
