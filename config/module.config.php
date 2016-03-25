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
use Affiliation\Form;
use Affiliation\Options;
use Affiliation\Service;
use Affiliation\View;

$config = [
    'controllers'     => [
        'abstract_factories' => [
            Controller\Factory\ControllerInvokableAbstractFactory::class,
        ],
    ],
    'service_manager' => [
        'invokables'         => [
            'affiliation_description_form_filter' => Form\FilterCreateObject::class,
            'affiliation_loi_form_filter'         => Form\FilterCreateObject::class,
            'affiliation_doa_form_filter'         => Form\FilterCreateObject::class,
        ],
        'factories'          => [
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
        'aliases'   => [
            'doaLink'                    => View\Helper\DoaLink::class,
            'associateLink'              => View\Helper\AssociateLink::class,
            'affiliationLink'            => View\Helper\AffiliationLink::class,
            'loiLink'                    => View\Helper\LoiLink::class,
            'paymentSheet'               => View\Helper\PaymentSheet::class,
            'affiliationEffortSpentLink' => View\Helper\EffortSpentLink::class,
        ],
        'factories' => [
            View\Helper\AffiliationLink::class => View\Factory\LinkInvokableFactory::class,
            View\Helper\AssociateLink::class   => View\Factory\LinkInvokableFactory::class,
            View\Helper\EffortSpentLink::class => View\Factory\LinkInvokableFactory::class,
            View\Helper\DoaLink::class         => View\Factory\LinkInvokableFactory::class,
            View\Helper\LoiLink::class         => View\Factory\LinkInvokableFactory::class,
            View\Helper\PaymentSheet::class    => View\Factory\LinkInvokableFactory::class,
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
                    __DIR__ . '/../src/Entity/',
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
