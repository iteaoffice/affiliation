<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c] 2004-2014 ITEA Office (http://itea3.org]
 */
use Affiliation\Acl\Assertion\Affiliation as AffiliationAssertion;
use Affiliation\Acl\Assertion\Doa as DoaAssertion;
use Affiliation\Acl\Assertion\Loi as LoiAssertion;
use Affiliation\Controller\ControllerInitializer;
use Affiliation\Navigation\Factory\AffiliationNavigationServiceFactory;
use Affiliation\Navigation\Service\AffiliationNavigationService;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\DoaService;
use Affiliation\Service\FormService;
use Affiliation\Service\LoiService;
use Affiliation\Service\ServiceInitializer;

$config = [
    'controllers'     => [
        'initializers' => [
            ControllerInitializer::class
        ],
        'invokables'   => [
            'affiliation-community'   => 'Affiliation\Controller\CommunityController',
            'affiliation-manager'     => 'Affiliation\Controller\AffiliationManagerController',
            'affiliation-doa'         => 'Affiliation\Controller\DoaController',
            'affiliation-loi'         => 'Affiliation\Controller\LoiController',
            'affiliation-edit'        => 'Affiliation\Controller\EditController',
            'affiliation-doa-manager' => 'Affiliation\Controller\DoaManagerController',
            'affiliation-loi-manager' => 'Affiliation\Controller\LoiManagerController',
        ],
    ],
    'service_manager' => [
        'initializers' => [
            ServiceInitializer::class
        ],
        'invokables'   => [
            AffiliationService::class             => AffiliationService::class,
            DoaService::class                     => DoaService::class,
            LoiService::class                     => LoiService::class,
            FormService::class                    => FormService::class,
            AffiliationAssertion::class           => AffiliationAssertion::class,
            DoaAssertion::class                   => DoaAssertion::class,
            LoiAssertion::class                   => LoiAssertion::class,
            'affiliation_affiliation_form_filter' => 'Affiliation\Form\FilterCreateAffiliation',
            'affiliation_description_form_filter' => 'Affiliation\Form\FilterCreateObject',
            'affiliation_loi_form_filter'         => 'Affiliation\Form\FilterCreateObject',
            'affiliation_doa_form_filter'         => 'Affiliation\Form\FilterCreateObject',
        ],
        'factories'    => [
            'affiliation_module_options'        => 'Affiliation\Service\OptionServiceFactory',
            AffiliationNavigationService::class => AffiliationNavigationServiceFactory::class,
        ],
    ],
    'view_helpers'    => [
        'invokables' => [
            'affiliationLink' => 'Affiliation\View\Helper\AffiliationLink',
            'paginationLink'  => 'Affiliation\View\Helper\PaginationLink',
            'doaLink'         => 'Affiliation\View\Helper\DoaLink',
            'loiLink'         => 'Affiliation\View\Helper\LoiLink',
        ]
    ],
    'view_manager'    => [
        'template_map' => include __DIR__ . '/../template_map.php',
    ],
    'doctrine'        => [
        'driver'       => [
            'affiliation_annotation_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'paths' => [
                    __DIR__ . '/../src/Affiliation/Entity/'
                ]
            ],
            'orm_default'                   => [
                'drivers' => [
                    'Affiliation\Entity' => 'affiliation_annotation_driver',
                ]
            ]
        ],
        'eventmanager' => [
            'orm_default' => [
                'subscribers' => [
                    'Gedmo\Timestampable\TimestampableListener',
                    'Gedmo\Sluggable\SluggableListener',
                ]
            ],
        ],
    ]
];
$configFiles = [
    __DIR__ . '/module.config.routes.php',
    __DIR__ . '/module.config.navigation.php',
    __DIR__ . '/module.config.authorize.php',
    __DIR__ . '/module.option.affiliation.php',
];
foreach ($configFiles as $configFile) {
    $config = Zend\Stdlib\ArrayUtils::merge($config, include $configFile);
}
return $config;
