<?php
/**
 * ITEA Office all rights reserved
 *
 * PHP Version 7
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */

declare(strict_types=1);

namespace Affiliation;

use Admin\Service\AdminService;
use Affiliation\Search\Service\AffiliationSearchService;
use Affiliation\Service;
use Application\Service\AssertionService;
use Contact\Service\ContactService;
use Contact\Service\SelectionContactService;
use Deeplink\Service\DeeplinkService;
use Doctrine\ORM\EntityManager;
use General\Service\CountryService;
use General\Service\EmailService;
use General\Service\GeneralService;
use Invoice\Options\ModuleOptions;
use Invoice\Service\InvoiceService;
use Organisation\Service\OrganisationService;
use Organisation\Service\ParentService;
use Program\Service\CallService;
use Project\Service\ContractService;
use Project\Service\ProjectService;
use Project\Service\ReportService;
use Project\Service\VersionService;
use Project\Service\WorkpackageService;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use ZfcTwig\View\TwigRenderer;

return [
    ConfigAbstractFactory::class => [
        // Controller plugins
        Service\AffiliationService::class                              => [
            EntityManager::class,
            SelectionContactService::class,
            GeneralService::class,
            ProjectService::class,
            InvoiceService::class,
            ContractService::class,
            OrganisationService::class,
            VersionService::class,
            ParentService::class,
            ContactService::class,
            DeeplinkService::class,
            EmailService::class,
            'ViewHelperManager',
            'ControllerPluginManager',
            TranslatorInterface::class
        ],
        Service\QuestionnaireService::class                            => [
            EntityManager::class,
        ],
        Service\DoaService::class                                      => [
            EntityManager::class
        ],
        Service\LoiService::class                                      => [
            EntityManager::class
        ],
        Controller\AffiliationManagerController::class                 => [
            Service\AffiliationService::class,
            TranslatorInterface::class,
            ProjectService::class,
            VersionService::class,
            ContactService::class,
            OrganisationService::class,
            ReportService::class,
            WorkpackageService::class,
            InvoiceService::class,
            ParentService::class,
            CallService::class,
            AssertionService::class,
            EntityManager::class
        ],
        Controller\CommunityController::class                          => [
            Service\AffiliationService::class,
            ProjectService::class,
            VersionService::class,
            ContactService::class,
            OrganisationService::class,
            ReportService::class,
            ContractService::class,
            WorkpackageService::class,
            InvoiceService::class,
            ParentService::class,
            CallService::class,
            ModuleOptions::class,
            AssertionService::class,
            Service\QuestionnaireService::class
        ],
        Controller\DoaController::class                                => [
            Service\AffiliationService::class,
            ProjectService::class,
            GeneralService::class,
            TranslatorInterface::class,
            TwigRenderer::class
        ],
        Controller\DoaManagerController::class                         => [
            Service\DoaService::class,
            Service\AffiliationService::class,
            ContactService::class,
            ProjectService::class,
            GeneralService::class,
            EmailService::class,
            DeeplinkService::class,
            Service\FormService::class,
            EntityManager::class,
            TranslatorInterface::class
        ],
        Controller\EditController::class                               => [
            Service\AffiliationService::class,
            ProjectService::class,
            VersionService::class,
            ContactService::class,
            OrganisationService::class,
            CountryService::class,
            ReportService::class,
            ContractService::class,
            WorkpackageService::class,
            Service\FormService::class,
            EntityManager::class,
            TranslatorInterface::class
        ],
        Controller\LoiController::class                                => [
            Service\LoiService::class,
            Service\AffiliationService::class,
            ProjectService::class,
            GeneralService::class,
            TranslatorInterface::class
        ],
        Controller\LoiManagerController::class                         => [
            Service\LoiService::class,
            ContactService::class,
            Service\AffiliationService::class,
            ProjectService::class,
            GeneralService::class,
            EmailService::class,
            EntityManager::class,
            Service\FormService::class,
            TranslatorInterface::class
        ],
        Controller\Questionnaire\CategoryManagerController::class      => [
            Service\QuestionnaireService::class,
            Service\FormService::class,
            TranslatorInterface::class
        ],
        Controller\Questionnaire\QuestionManagerController::class      => [
            Service\QuestionnaireService::class,
            Service\FormService::class,
            TranslatorInterface::class
        ],
        Controller\Questionnaire\QuestionnaireManagerController::class => [
            Service\QuestionnaireService::class,
            Service\FormService::class,
            TranslatorInterface::class
        ],
        Controller\Questionnaire\QuestionnaireController::class        => [
            Service\AffiliationService::class,
            Service\QuestionnaireService::class,
            EntityManager::class,
            TranslatorInterface::class
        ],
        Controller\Plugin\RenderPaymentSheet::class                    => [
            Service\AffiliationService::class,
            Options\ModuleOptions::class,
            ProjectService::class,
            VersionService::class,
            ContractService::class,
            ContactService::class,
            OrganisationService::class,
            InvoiceService::class,
            TranslatorInterface::class
        ],
        Controller\Plugin\RenderDoa::class                             => [
            Options\ModuleOptions::class,
            ContactService::class,
            TwigRenderer::class
        ],
        Controller\Plugin\RenderLoi::class                             => [
            Options\ModuleOptions::class,
            ContactService::class,
            TwigRenderer::class
        ],
        Controller\Plugin\MergeAffiliation::class                      => [
            AdminService::class,
            EntityManager::class
        ],
        View\Helper\PaymentSheet::class                                => [
            ProjectService::class,
            ContractService::class,
            InvoiceService::class,
            Service\AffiliationService::class,
            ContactService::class,
            OrganisationService::class,
            VersionService::class,
            TwigRenderer::class,
        ],
        View\Helper\Questionnaire\QuestionnaireHelper::class           => [
            Service\QuestionnaireService::class,
        ]
    ]
];