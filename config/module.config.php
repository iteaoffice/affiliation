<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
use Affiliation\Controller\ControllerInitializer;
use Affiliation\Service\ServiceInitializer;

$config = array(
    'controllers'     => array(
        'initializers' => array(
            ControllerInitializer::class
        ),
        'invokables'   => array(
            'affiliation-community' => 'Affiliation\Controller\CommunityController',
            'affiliation-manager'   => 'Affiliation\Controller\AffiliationManagerController',
            'affiliation-doa'       => 'Affiliation\Controller\DoaController',
            'affiliation-loi'       => 'Affiliation\Controller\LoiController',
        ),
    ),
    'service_manager' => array(
        'initializers' => array(
            ServiceInitializer::class
        ),
        'invokables'   => array(
            'affiliation_affiliation_service'     => 'Affiliation\Service\AffiliationService',
            'affiliation_form_service'            => 'Affiliation\Service\FormService',
            'affiliation_affiliation_form_filter' => 'Affiliation\Form\FilterCreateAffiliation',
            'affiliation_assertion'               => 'Affiliation\Acl\Assertion\Affiliation',
            'affiliation_module_options'          => 'Affiliation\Service\OptionServiceFactory',
        ),
    ),
    'view_helpers'    => array(
        'invokables' => array(
            'affiliationLink' => 'Affiliation\View\Helper\AffiliationLink',
            'doaLink'         => 'Affiliation\View\Helper\DoaLink',
            'loiLink'         => 'Affiliation\View\Helper\LoiLink',

        )
    ),
    'view_manager'    => array(
        'template_map' => include __DIR__ . '/../template_map.php',
    ),
    'doctrine'        => array(
        'driver'       => array(
            'affiliation_annotation_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'paths' => array(
                    __DIR__ . '/../src/Affiliation/Entity/'
                )
            ),
            'orm_default'                   => array(
                'drivers' => array(
                    'Affiliation\Entity' => 'affiliation_annotation_driver',
                )
            )
        ),
        'eventmanager' => array(
            'orm_default' => array(
                'subscribers' => array(
                    'Gedmo\Timestampable\TimestampableListener',
                    'Gedmo\Sluggable\SluggableListener',
                )
            ),
        ),
    )
);

$configFiles = array(
    __DIR__ . '/module.config.routes.php',
    __DIR__ . '/module.config.navigation.php',
    __DIR__ . '/module.config.authorize.php',
    __DIR__ . '/module.option.affiliation.php',
);

foreach ($configFiles as $configFile) {
    $config = Zend\Stdlib\ArrayUtils::merge($config, include $configFile);
}

return $config;
