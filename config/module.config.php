<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */
use Affiliation\Acl;
use Affiliation\Controller;
use Affiliation\Factory;
use Affiliation\InputFilter;
use Affiliation\Navigation;
use Affiliation\Options;
use Affiliation\Search;
use Affiliation\Service;
use Affiliation\View;
use Zend\Stdlib;

$config = [
    'controllers'        => [
        'factories' => [
            Controller\AffiliationManagerController::class => Controller\Factory\ControllerFactory::class,
            Controller\CommunityController::class          => Controller\Factory\ControllerFactory::class,
            Controller\DoaController::class                => Controller\Factory\ControllerFactory::class,
            Controller\DoaManagerController::class         => Controller\Factory\ControllerFactory::class,
            Controller\EditController::class               => Controller\Factory\ControllerFactory::class,
            Controller\LoiController::class                => Controller\Factory\ControllerFactory::class,
            Controller\LoiManagerController::class         => Controller\Factory\ControllerFactory::class,
        ],
    ],
    'service_manager'    => [
        'factories' => [
            Service\AffiliationService::class              => Factory\AffiliationServiceFactory::class,
            Service\DoaService::class                      => Factory\DoaServiceFactory::class,
            Service\LoiService::class                      => Factory\LoiServiceFactory::class,
            Service\FormService::class                     => Factory\FormServiceFactory::class,
            InputFilter\AffiliationFilter::class           => Factory\InputFilterFactory::class,
            InputFilter\DescriptionFilter::class           => Factory\InputFilterFactory::class,
            Options\ModuleOptions::class                   => Factory\ModuleOptionsFactory::class,
            Search\Service\AffiliationSearchService::class => Search\Factory\AffiliationSearchFactory::class,
            Acl\Assertion\Affiliation::class               => Acl\Factory\AssertionFactory::class,
            Acl\Assertion\Doa::class                       => Acl\Factory\AssertionFactory::class,
            Acl\Assertion\Loi::class                       => Acl\Factory\AssertionFactory::class,
            Acl\Assertion\Loi::class                       => Acl\Factory\AssertionFactory::class,
            Navigation\Invokable\AffiliationLabel::class   => Navigation\Factory\NavigationInvokableFactory::class,
            Navigation\Invokable\DoaLabel::class           => Navigation\Factory\NavigationInvokableFactory::class,
            Navigation\Invokable\LoiLabel::class           => Navigation\Factory\NavigationInvokableFactory::class,
        ],
    ],
    'controller_plugins' => [
        'aliases'   => [
            'renderPaymentSheet'   => Controller\Plugin\RenderPaymentSheet::class,
            'renderDoa'            => Controller\Plugin\RenderDoa::class,
            'renderLoi'            => Controller\Plugin\RenderLoi::class,
            'getAffiliationFilter' => Controller\Plugin\GetFilter::class,
            'mergeAffiliation'     => Controller\Plugin\MergeAffiliation::class,
        ],
        'factories' => [
            Controller\Plugin\RenderPaymentSheet::class => Controller\Factory\PluginFactory::class,
            Controller\Plugin\RenderDoa::class          => Controller\Factory\PluginFactory::class,
            Controller\Plugin\RenderLoi::class          => Controller\Factory\PluginFactory::class,
            Controller\Plugin\GetFilter::class          => Controller\Factory\PluginFactory::class,
            Controller\Plugin\MergeAffiliation::class   => Controller\Factory\PluginFactory::class,
        ],
    ],
    'view_helpers'       => [
        'aliases'   => [
            'doaLink'                    => View\Helper\DoaLink::class,
            'associateLink'              => View\Helper\AssociateLink::class,
            'affiliationLink'            => View\Helper\AffiliationLink::class,
            'loiLink'                    => View\Helper\LoiLink::class,
            'paymentSheet'               => View\Helper\PaymentSheet::class,
            'affiliationEffortSpentLink' => View\Helper\EffortSpentLink::class,
        ],
        'factories' => [
            View\Helper\AffiliationLink::class => View\Factory\ViewHelperFactory::class,
            View\Helper\AssociateLink::class   => View\Factory\ViewHelperFactory::class,
            View\Helper\EffortSpentLink::class => View\Factory\ViewHelperFactory::class,
            View\Helper\DoaLink::class         => View\Factory\ViewHelperFactory::class,
            View\Helper\LoiLink::class         => View\Factory\ViewHelperFactory::class,
            View\Helper\PaymentSheet::class    => View\Factory\ViewHelperFactory::class,
        ],
    ],
    'view_manager'       => [
        'template_map' => include __DIR__ . '/../template_map.php',
    ],
    'doctrine'           => [
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

foreach (Stdlib\Glob::glob(__DIR__ . '/module.config.{,*}.php', Stdlib\Glob::GLOB_BRACE) as $file) {
    $config = Stdlib\ArrayUtils::merge($config, include $file);
}

return $config;
