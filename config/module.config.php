<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c] 2004-2014 ITEA Office (http://itea3.org]
 */
use Affiliation\Acl\Assertion;
use Affiliation\Controller;
use Affiliation\Navigation\Factory\AffiliationNavigationServiceFactory;
use Affiliation\Navigation\Service\AffiliationNavigationService;
use Affiliation\Service;
use Affiliation\View\Helper;

$config = [
    'controllers'     => [
        'initializers' => [
            Controller\ControllerInitializer::class
        ],
        'invokables'   => [
            Controller\AffiliationManagerController::class => Controller\AffiliationManagerController::class,
            Controller\AffiliationController::class        => Controller\AffiliationController::class,
            Controller\DoaManagerController::class         => Controller\DoaManagerController::class,
            Controller\LoiManagerController::class         => Controller\LoiManagerController::class,
            Controller\CommunityController::class          => Controller\CommunityController::class,
            Controller\DoaController::class                => Controller\DoaController::class,
            Controller\LoiController::class                => Controller\LoiController::class,
            Controller\EditController::class               => Controller\EditController::class,

        ],
    ],
    'service_manager' => [
        'initializers' => [
            Service\ServiceInitializer::class
        ],
        'invokables'   => [
            Service\AffiliationService::class     => Service\AffiliationService::class,
            Service\DoaService::class             => Service\DoaService::class,
            Service\LoiService::class             => Service\LoiService::class,
            Service\FormService::class            => Service\FormService::class,
            Assertion\Affiliation::class          => Assertion\Affiliation::class,
            Assertion\Doa::class                  => Assertion\Doa::class,
            Assertion\Loi::class                  => Assertion\Loi::class,
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
            'affiliationLink'            => Helper\AffiliationLink::class,
            'associateLink'              => Helper\AssociateLink::class,
            'affiliationEffortSpentLink' => Helper\EffortSpentLink::class,
            'doaLink'                    => Helper\DoaLink::class,
            'loiLink'                    => Helper\LoiLink::class,
            'paymentSheet'               => Helper\PaymentSheet::class,
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
    __DIR__ . '/module.config.routes.admin.php',
    __DIR__ . '/module.config.navigation.php',
    __DIR__ . '/module.config.authorize.php',
    __DIR__ . '/module.option.affiliation.php',
];
foreach ($configFiles as $configFile) {
    $config = Zend\Stdlib\ArrayUtils::merge($config, include $configFile);
}
return $config;
