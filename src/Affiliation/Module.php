<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    SoloDB
 * @package     Affiliation
 * @subpackage  Module
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 * @version     4.0
 */
namespace Affiliation;

use Zend\ModuleManager\Feature; //Makes the module class more strict
use Zend\EventManager\EventInterface;

use Affiliation\Service\FormServiceAwareInterface;
use Affiliation\Controller\Plugin\RenderLoi;
use Affiliation\Controller\Plugin\RenderDoa;

/**
 *
 */
class Module implements
    Feature\AutoloaderProviderInterface,
    Feature\ServiceProviderInterface,
    Feature\ConfigProviderInterface,
    Feature\BootstrapListenerInterface
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/../../autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/../../src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * Go to the service configuration
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return include __DIR__ . '/../../config/services.config.php';
    }

    /**
     * @return array
     */
    public function getControllerConfig()
    {
        return array(
            'initializers' => array(
                function ($instance, $sm) {
                    if ($instance instanceof FormServiceAwareInterface) {
                        $sm          = $sm->getServiceLocator();
                        $formService = $sm->get('affiliation_form_service');
                        $instance->setFormService($formService);
                    }
                },
            ),
        );
    }

    /**
     * Move this to here to have config cache working
     * @return array
     */
    public function getControllerPluginConfig()
    {
        return array(
            'factories' => array(
                'renderDoa' => function ($sm) {
                    $renderDoa = new RenderDoa();
                    $renderDoa->setServiceLocator($sm->getServiceLocator());

                    return $renderDoa;
                },
                'renderLoi' => function ($sm) {
                    $renderLoi = new RenderLoi();
                    $renderLoi->setServiceLocator($sm->getServiceLocator());

                    return $renderLoi;
                },
            )
        );
    }

    /**
     * Listen to the bootstrap event
     *
     * @param EventInterface $e
     *
     * @return array
     */
    public function onBootstrap(EventInterface $e)
    {
        // TODO: Implement onBootstrap() method.
    }
}
