<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    SoloDB
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (https://itea3.org)
 *
 * @version     4.0
 */

namespace Affiliation;

use Affiliation\Controller\Plugin\RenderDoa;
use Affiliation\Controller\Plugin\RenderLoi;
use Affiliation\Controller\Plugin\RenderPaymentSheet;
use Affiliation\Navigation\Service\AffiliationNavigationService;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature;
use Zend\Mvc\Controller\PluginManager;
use Zend\Mvc\MvcEvent;

//Makes the module class more strict
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
        return [
            'Zend\Loader\ClassMapAutoloader' => [
                __DIR__ . '/../../autoload_classmap.php',
            ],
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/../../src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * Go to the service configuration.
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return include __DIR__ . '/../../config/services.config.php';
    }

    /**
     * Move this to here to have config cache working.
     *
     * @return array
     */
    public function getControllerPluginConfig()
    {
        return [
            'factories' => [
                'renderPaymentSheet' => function (PluginManager $sm) {
                    $renderPaymentSheet = new RenderPaymentSheet();
                    $renderPaymentSheet->setServiceLocator($sm->getServiceLocator());

                    return $renderPaymentSheet;
                },
                'renderDoa'          => function (PluginManager $sm) {
                    $renderDoa = new RenderDoa();
                    $renderDoa->setServiceLocator($sm->getServiceLocator());

                    return $renderDoa;
                },
                'renderLoi'          => function (PluginManager $sm) {
                    $renderLoi = new RenderLoi();
                    $renderLoi->setServiceLocator($sm->getServiceLocator());

                    return $renderLoi;
                },
            ],
        ];
    }

    /**
     * Listen to the bootstrap event.
     *
     * @param EventInterface $e
     *
     * @return array
     */
    public function onBootstrap(EventInterface $e)
    {
        $app = $e->getParam('application');
        $em = $app->getEventManager();
        $em->attach(MvcEvent::EVENT_DISPATCH, function (MvcEvent $event) {
            $event->getApplication()->getServiceManager()
                ->get(AffiliationNavigationService::class)->update();
        });
    }
}
