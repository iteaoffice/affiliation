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
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Zend\Stdlib;

$config = [
    'controllers'        => [
        'factories' => [
            Controller\AffiliationManagerController::class                 => ConfigAbstractFactory::class,
            Controller\CommunityController::class                          => ConfigAbstractFactory::class,
            Controller\DoaController::class                                => ConfigAbstractFactory::class,
            Controller\DoaManagerController::class                         => ConfigAbstractFactory::class,
            Controller\EditController::class                               => ConfigAbstractFactory::class,
            Controller\LoiController::class                                => ConfigAbstractFactory::class,
            Controller\LoiManagerController::class                         => ConfigAbstractFactory::class,
            Controller\Questionnaire\CategoryManagerController::class      => ConfigAbstractFactory::class,
            Controller\Questionnaire\QuestionManagerController::class      => ConfigAbstractFactory::class,
            Controller\Questionnaire\QuestionnaireManagerController::class => ConfigAbstractFactory::class,
        ],
    ],
    'service_manager'    => [
        'factories' => [
            Service\AffiliationService::class                            => ConfigAbstractFactory::class,
            Service\QuestionnaireService::class                          => ConfigAbstractFactory::class,
            Service\DoaService::class                                    => ConfigAbstractFactory::class,
            Service\LoiService::class                                    => ConfigAbstractFactory::class,
            Service\FormService::class                                   => Factory\FormServiceFactory::class,
            InputFilter\AffiliationFilter::class                         => Factory\InputFilterFactory::class,
            InputFilter\DescriptionFilter::class                         => Factory\InputFilterFactory::class,
            Options\ModuleOptions::class                                 => Factory\ModuleOptionsFactory::class,
            Search\Service\AffiliationSearchService::class               => ConfigAbstractFactory::class,
            Acl\Assertion\Affiliation::class                             => Factory\InvokableFactory::class,
            Acl\Assertion\Doa::class                                     => Factory\InvokableFactory::class,
            Acl\Assertion\Loi::class                                     => Factory\InvokableFactory::class,
            Navigation\Invokable\AffiliationLabel::class                 => Factory\InvokableFactory::class,
            Navigation\Invokable\DoaLabel::class                         => Factory\InvokableFactory::class,
            Navigation\Invokable\LoiLabel::class                         => Factory\InvokableFactory::class,
            Navigation\Invokable\Questionnaire\CategoryLabel::class      => Factory\InvokableFactory::class,
            Navigation\Invokable\Questionnaire\QuestionLabel::class      => Factory\InvokableFactory::class,
            Navigation\Invokable\Questionnaire\QuestionnaireLabel::class => Factory\InvokableFactory::class,
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
            Controller\Plugin\RenderPaymentSheet::class => ConfigAbstractFactory::class,
            Controller\Plugin\RenderDoa::class          => ConfigAbstractFactory::class,
            Controller\Plugin\RenderLoi::class          => ConfigAbstractFactory::class,
            Controller\Plugin\GetFilter::class          => Factory\InvokableFactory::class,
            Controller\Plugin\MergeAffiliation::class   => ConfigAbstractFactory::class,
        ],
    ],
    'view_helpers'       => [
        'aliases'   => [
            'doaLink'                         => View\Helper\DoaLink::class,
            'associateLink'                   => View\Helper\AssociateLink::class,
            'affiliationLink'                 => View\Helper\AffiliationLink::class,
            'loiLink'                         => View\Helper\LoiLink::class,
            'paymentSheet'                    => View\Helper\PaymentSheet::class,
            'affiliationEffortSpentLink'      => View\Helper\EffortSpentLink::class,
            'affiliationQuestionCategoryLink' => View\Helper\Questionnaire\CategoryLink::class,
            'affiliationQuestionLink'         => View\Helper\Questionnaire\QuestionLink::class,
            'affiliationQuestionnaireLink'    => View\Helper\Questionnaire\QuestionnaireLink::class
        ],
        'factories' => [
            View\Helper\AffiliationLink::class                 => View\Factory\ViewHelperFactory::class,
            View\Helper\AssociateLink::class                   => View\Factory\ViewHelperFactory::class,
            View\Helper\EffortSpentLink::class                 => View\Factory\ViewHelperFactory::class,
            View\Helper\DoaLink::class                         => View\Factory\ViewHelperFactory::class,
            View\Helper\LoiLink::class                         => View\Factory\ViewHelperFactory::class,
            View\Helper\PaymentSheet::class                    => ConfigAbstractFactory::class,
            View\Helper\Questionnaire\CategoryLink::class      => View\Factory\ViewHelperFactory::class,
            View\Helper\Questionnaire\QuestionLink::class      => View\Factory\ViewHelperFactory::class,
            View\Helper\Questionnaire\QuestionnaireLink::class => View\Factory\ViewHelperFactory::class,
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
