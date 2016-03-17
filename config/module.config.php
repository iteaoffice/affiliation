<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c] 2004-2015 ITEA Office (https://itea3.org]
 */
use Affiliation\Acl;
use Affiliation\Controller;
use Affiliation\Factory;
use Affiliation\Navigation\Factory\AffiliationNavigationServiceFactory;
use Affiliation\Navigation\Service\AffiliationNavigationService;
use Affiliation\Options;
use Affiliation\Service;
use Affiliation\View\Helper;

$config = [
    'controllers'     => [
        'invokables'         => [
            //Controller\AffiliationManagerController::class,
            //Controller\AffiliationController::class       ,
            //Controller\DoaManagerController::class        ,
            //Controller\LoiManagerController::class        ,
            //Controller\CommunityController::class         ,
            //Controller\DoaController::class               ,
            //Controller\LoiController::class               ,
            //Controller\EditController::class              ,
        ],
        'abstract_factories' => [
            Controller\Factory\ControllerInvokableAbstractFactory::class,
        ],
    ],
    'service_manager' => [
        'invokables'         => [
            'affiliation_affiliation_form_filter' => 'Affiliation\Form\FilterCreateAffiliation',
            'affiliation_description_form_filter' => 'Affiliation\Form\FilterCreateObject',
            'affiliation_loi_form_filter'         => 'Affiliation\Form\FilterCreateObject',
            'affiliation_doa_form_filter'         => 'Affiliation\Form\FilterCreateObject',
        ],
        'factories'          => [
            //Acl\Assertion\Affiliation::class,
            //Acl\Assertion\Doa::class,
            //Acl\Assertion\Loi::class,
            Service\AffiliationService::class => Factory\AffiliationServiceFactory::class,
            Service\DoaService::class         => Factory\DoaServiceFactory::class,
            Service\LoiService::class         => Factory\LoiServiceFactory::class,
            Service\FormService::class        => Factory\FormServiceFactory::class,
            Options\ModuleOptions::class      => Factory\ModuleOptionsFactory::class,
        ],
        'abstract_factories' => [
            Acl\Factory\AssertionInvokableAbstractFactory::class,
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
        ],
    ],
    'view_manager'    => [
        'template_map' => include __DIR__ . '/../template_map.php',
    ],
    'doctrine'        => [
        'driver'       => [
            'affiliation_annotation_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'paths' => [
                    __DIR__ . '/../src/Affiliation/Entity/',
                ],
            ],
            'orm_default'                   => [
                'drivers' => [
                    'Affiliation\Entity' => 'affiliation_annotation_driver',
                ],
            ],
        ],
        'eventmanager' => [
            'orm_default' => [
                'subscribers' => [
                    'Gedmo\Timestampable\TimestampableListener',
                    'Gedmo\Sluggable\SluggableListener',
                ],
            ],
        ],
    ],
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
