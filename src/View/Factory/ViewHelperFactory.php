<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * PHP Version 5
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   2004-2016 ITEA Office
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */

namespace Affiliation\View\Factory;

use Affiliation\View\Helper\AbstractViewHelper;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\View\HelperPluginManager;

/**
 * Class LinkInvokableFactory
 *
 * @package Affiliation\View\Factory
 */
final class ViewHelperFactory implements FactoryInterface
{
    /**
     * Create an instance of the requested class name.
     *
     * @param ContainerInterface|HelperPluginManager $container
     * @param string                                 $requestedName
     * @param null|array                             $options
     *
     * @return AbstractViewHelper
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AbstractViewHelper
    {
        /** @var AbstractViewHelper $viewHelper */
        $viewHelper = new $requestedName($options);
        $viewHelper->setServiceManager($container);
        $viewHelper->setHelperPluginManager($container->get('ViewHelperManager'));

        return $viewHelper;
    }
}
