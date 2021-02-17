<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation;

use Affiliation\Service;
use Application\Service\AssertionService;
use Calendar\Service\CalendarService;
use Contact\Service\ContactService;
use Doctrine\ORM\EntityManager;
use General\Service\CountryService;
use General\Service\EmailService;
use General\Service\GeneralService;
use Invoice\Options\ModuleOptions;
use Invoice\Service\InvoiceService;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Organisation\Service\OrganisationService;
use Organisation\Service\ParentService;
use Project\Service\ContractService;
use Project\Service\ProjectService;
use Project\Service\ReportService;
use Project\Service\VersionService;
use Project\Service\WorkpackageService;
use ZfcTwig\View\TwigRenderer;

return [
    ConfigAbstractFactory::class => [
        Controller\AffiliationController::class                        => [
            Service\AffiliationService::class,
            Service\QuestionnaireService::class,
            ProjectService::class,
            VersionService::class,
            ContactService::class,
            OrganisationService::class,
            ReportService::class,
            ContractService::class,
            WorkpackageService::class,
            InvoiceService::class,
            CalendarService::class,
            ParentService::class,
            ModuleOptions::class,
            AssertionService::class
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
        Controller\Admin\AffiliationController::class                  => [
            Service\AffiliationService::class,
            Service\QuestionnaireService::class,
            ProjectService::class,
            VersionService::class,
            ContractService::class,
            ContactService::class,
            OrganisationService::class,
            ReportService::class,
            WorkpackageService::class,
            InvoiceService::class,
            CalendarService::class,
            ParentService::class,
            AssertionService::class,
            TranslatorInterface::class
        ],
        Controller\Admin\IndexController::class                        => [
            Service\AffiliationService::class,
            ProjectService::class,
            OrganisationService::class,
            TranslatorInterface::class
        ],
        Controller\Admin\EditController::class                         => [
            Service\AffiliationService::class,
            TranslatorInterface::class,
            ProjectService::class,
            VersionService::class,
            ReportService::class,
            ContactService::class,
            OrganisationService::class,
            InvoiceService::class,
            ParentService::class,
            Service\FormService::class,
            EntityManager::class
        ],
        Controller\Json\DoaController::class                           => [
            Service\DoaService::class,
            Service\AffiliationService::class,
            ContactService::class,
            EmailService::class,
            TranslatorInterface::class
        ],
        Controller\Json\LoiController::class                           => [
            Service\LoiService::class,
            Service\AffiliationService::class,
            ContactService::class,
            TranslatorInterface::class
        ],
        Controller\DoaController::class                                => [
            Service\AffiliationService::class,
            ProjectService::class,
            GeneralService::class,
            TranslatorInterface::class,
            TwigRenderer::class
        ],
        Controller\Doa\ManagerController::class                        => [
            Service\DoaService::class,
            Service\AffiliationService::class,
            ContactService::class,
            ProjectService::class,
            GeneralService::class,
            EmailService::class,
            ModuleOptions::class,
            Service\FormService::class,
            TranslatorInterface::class,
        ],
        Controller\LoiController::class                                => [
            Service\LoiService::class,
            Service\AffiliationService::class,
            ProjectService::class,
            GeneralService::class,
            TranslatorInterface::class
        ],
        Controller\Loi\ManagerController::class                        => [
            Service\LoiService::class,
            ContactService::class,
            Service\AffiliationService::class,
            ProjectService::class,
            GeneralService::class,
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
    ]
];
