<?php

use Admin\Navigation\Factory\NavigationInvokableFactory;
use Affiliation\Acl;
use Affiliation\Controller;
use Affiliation\Factory;
use Affiliation\InputFilter;
use Affiliation\Navigation;
use Affiliation\Options;
use Affiliation\Search;
use Affiliation\Service;
use Affiliation\View;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Gedmo\Sluggable\SluggableListener;
use Gedmo\Timestampable\TimestampableListener;
use General\View\Factory\LinkHelperFactory;
use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Laminas\Stdlib;

/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

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
            Controller\Questionnaire\QuestionnaireController::class        => ConfigAbstractFactory::class,
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
            Acl\Assertion\Affiliation::class                             => Factory\InvokableFactory::class,
            Acl\Assertion\Doa::class                                     => Factory\InvokableFactory::class,
            Acl\Assertion\Loi::class                                     => Factory\InvokableFactory::class,
            Acl\Assertion\QuestionnaireAssertion::class                  => Factory\InvokableFactory::class,
            Navigation\Invokable\AffiliationLabel::class                 => NavigationInvokableFactory::class,
            Navigation\Invokable\DoaLabel::class                         => NavigationInvokableFactory::class,
            Navigation\Invokable\LoiLabel::class                         => NavigationInvokableFactory::class,
            Navigation\Invokable\Questionnaire\CategoryLabel::class      => NavigationInvokableFactory::class,
            Navigation\Invokable\Questionnaire\QuestionLabel::class      => NavigationInvokableFactory::class,
            Navigation\Invokable\Questionnaire\QuestionnaireLabel::class => NavigationInvokableFactory::class,
        ],
    ],
    'controller_plugins' => [
        'aliases'   => [
            'renderPaymentSheet'   => Controller\Plugin\RenderPaymentSheet::class,
            'renderLoi'            => Controller\Plugin\RenderLoi::class,
            'getAffiliationFilter' => Controller\Plugin\GetFilter::class,
            'mergeAffiliation'     => Controller\Plugin\MergeAffiliation::class,
        ],
        'factories' => [
            Controller\Plugin\RenderPaymentSheet::class => ConfigAbstractFactory::class,
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
            'affiliationQuestionnaireLink'    => View\Helper\Questionnaire\QuestionnaireLink::class,
            'questionnaireHelper'             => View\Helper\Questionnaire\QuestionnaireHelper::class
        ],
        'factories' => [
            View\Helper\AffiliationLink::class => LinkHelperFactory::class,
            View\Helper\AssociateLink::class   => LinkHelperFactory::class,
            View\Helper\EffortSpentLink::class => LinkHelperFactory::class,
            View\Helper\DoaLink::class         => LinkHelperFactory::class,
            View\Helper\LoiLink::class         => LinkHelperFactory::class,

            View\Helper\Questionnaire\CategoryLink::class      => LinkHelperFactory::class,
            View\Helper\Questionnaire\QuestionLink::class      => LinkHelperFactory::class,
            View\Helper\Questionnaire\QuestionnaireLink::class => LinkHelperFactory::class,

            View\Helper\Questionnaire\QuestionnaireHelper::class => ConfigAbstractFactory::class,
            View\Helper\PaymentSheet::class                      => ConfigAbstractFactory::class,
        ],
    ],
    'view_manager'       => [
        'template_map' => include __DIR__ . '/../template_map.php',
    ],
    'doctrine'           => [
        'driver'       => [
            'affiliation_annotation_driver' => [
                'class' => AnnotationDriver::class,
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
                    TimestampableListener::class,
                    SluggableListener::class,
                ],
            ],
        ],
    ],
];

foreach (Stdlib\Glob::glob(__DIR__ . '/module.config.{,*}.php', Stdlib\Glob::GLOB_BRACE) as $file) {
    $config = Stdlib\ArrayUtils::merge($config, include $file);
}

return $config;
