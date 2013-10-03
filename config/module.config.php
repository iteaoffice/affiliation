<?php
/**
 * Japaveh Webdesign copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <info@japaveh.nl>
 * @copyright   Copyright (c) 2004-2013 Japaveh Webdesign (http://japaveh.nl)
 */
$config = array(
    'controllers'     => array(
        'invokables' => array(
            'affiliation'         => 'Affiliation\Controller\AffiliationController',
            'affiliation-manager' => 'Affiliation\Controller\AffiliationManagerController',
        ),
    ),
    'view_helpers'    => array(
        'invokables' => array(
            'affiliationLink' => 'Affiliation\View\Helper\AffiliationLink',

        )
    ),
    'view_manager'    => array(
        'template_path_stack' => array(
            __DIR__ . '/../view'
        ),
    ),
    'service_manager' => array(
        'factories'  => array(
            'affiliation-assertion' => 'Affiliation\Acl\Assertion\Affiliation',
        ),
        'invokables' => array(
            'affiliation_generic_service'         => 'Affiliation\Service\GeneralService',
            'affiliation_affiliation_service'     => 'Affiliation\Service\AffiliationService',
            'affiliation_form_service'            => 'Affiliation\Service\FormService',
            'affiliation_affiliation_form_filter' => 'Affiliation\Form\FilterCreateAffiliation',

        )
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
);

foreach ($configFiles as $configFile) {
    $config = Zend\Stdlib\ArrayUtils::merge($config, include $configFile);
}

return $config;
