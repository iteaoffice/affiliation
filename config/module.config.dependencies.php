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

use Admin\Service\AdminService;
use Affiliation\Service;
use Contact\Provider\ContactProvider;
use Contact\Service\ContactService;
use Contact\Service\SelectionContactService;
use Doctrine\ORM\EntityManager;
use General\Service\CountryService;
use General\Service\EmailService;
use General\Service\GeneralService;
use Invoice\Service\InvoiceService;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Organisation\Service\OrganisationService;
use Organisation\Service\ParentService;
use Project\Service\ContractService;
use Project\Service\ProjectService;
use Project\Service\VersionService;
use ZfcTwig\View\TwigRenderer;

return [
    ConfigAbstractFactory::class => [
        Provider\AffiliationProvider::class                  => [
            VersionService::class,
            ContactProvider::class
        ],
        // Controller plugins
        Service\AffiliationService::class                    => [
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
            CountryService::class,
            EmailService::class,
            'ControllerPluginManager',
            TranslatorInterface::class
        ],
        Service\QuestionnaireService::class                  => [
            EntityManager::class,
            VersionService::class
        ],
        Service\DoaService::class                            => [
            EntityManager::class
        ],
        Service\LoiService::class                            => [
            EntityManager::class
        ],
        Controller\Plugin\RenderPaymentSheet::class          => [
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
        Controller\Plugin\RenderLoi::class                   => [
            Options\ModuleOptions::class,
            ContactService::class,
            TwigRenderer::class
        ],
        Controller\Plugin\MergeAffiliation::class            => [
            AdminService::class,
            EntityManager::class
        ],
        View\Helper\PaymentSheet::class                      => [
            ProjectService::class,
            ContractService::class,
            InvoiceService::class,
            Service\AffiliationService::class,
            ContactService::class,
            OrganisationService::class,
            VersionService::class,
            TwigRenderer::class,
        ],
        View\Helper\Questionnaire\QuestionnaireHelper::class => [
            Service\QuestionnaireService::class,
        ]
    ]
];
