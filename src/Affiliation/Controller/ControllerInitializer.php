<?php
/**
 * Japaveh Webdesign copyright message placeholder
 *
 * @category    Admin
 * @package     Controller
 * @author      Johan van der Heide <info@japaveh.nl>
 * @copyright   2004-2014 Japaveh Webdesign
 * @license     http://solodb.net/license.txt proprietary
 * @link        http://solodb.net
 */
namespace Affiliation\Controller;

use Affiliation\Service\AffiliationServiceAwareInterface;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\DoaServiceAwareInterface;
use Affiliation\Service\DoaService;
use Affiliation\Service\LoiServiceAwareInterface;
use Affiliation\Service\LoiService;
use Affiliation\Service\FormServiceAwareInterface;
use Affiliation\Service\FormService;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Japaveh Webdesign copyright message placeholder
 *
 * @category    Admin
 * @package     Controller
 * @author      Johan van der Heide <info@japaveh.nl>
 * @copyright   2004-2014 Japaveh Webdesign
 * @license     http://solodb.net/license.txt proprietary
 * @link        http://solodb.net
 */
class ControllerInitializer implements InitializerInterface
{
    /**
     * @param                                           $instance
     * @param ServiceLocatorInterface|ControllerManager $serviceLocator
     *
     * @return void
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if (!is_object($instance)) {
            return;
        }
        $arrayCheck = [
            FormServiceAwareInterface::class        => FormService::class,
            AffiliationServiceAwareInterface::class => AffiliationService::class,
            DoaServiceAwareInterface::class         => DoaService::class,
            LoiServiceAwareInterface::class         => LoiService::class,
        ];
        /**
         * @var $sm ServiceLocatorInterface
         */
        $sm = $serviceLocator->getServiceLocator();
        /**
         * Go over each interface to see if we should add an interface
         */
        foreach (class_implements($instance) as $interface) {
            if (array_key_exists($interface, $arrayCheck)) {
                $this->setInterface($instance, $interface, $sm->get($arrayCheck[$interface]));
            }
        }

        return;
    }

    /**
     * @param $interface
     * @param $instance
     * @param $service
     */
    protected function setInterface($instance, $interface, $service)
    {
        foreach (get_class_methods($interface) as $setter) {
            if (strpos($setter, 'set') !== false) {
                $instance->$setter($service);
            }
        }
    }
}
