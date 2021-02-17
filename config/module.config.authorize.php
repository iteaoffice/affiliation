<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

use Affiliation\Acl\Assertion;
use BjyAuthorize\Guard\Route;

return [
    'bjyauthorize' => [
        'guards' => [
            Route::class => [
                [
                    'route'     => 'community/affiliation/affiliation',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/details',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/description',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/market-access',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/costs-and-effort',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/project-versions',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/financial',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/parent',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/contract',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/payment-sheet',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/payment-sheet-pdf',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/contacts',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/reporting',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/achievements',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/questionnaires',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/affiliation',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/financial',
                    'roles'     => [],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/manage-associates',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/add-associate',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/costs-and-effort',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/description',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/market-access',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/technical-contact',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/financial',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/effort-spent',
                    'roles'     => ['user'],
                    'assertion' => Assertion\AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/doa/submit',
                    'roles'     => ['user'],
                    'assertion' => Assertion\DoaAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/doa/render',
                    'roles'     => ['user'],
                    'assertion' => Assertion\DoaAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/doa/replace',
                    'roles'     => ['user'],
                    'assertion' => Assertion\DoaAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/doa/download',
                    'roles'     => ['user'],
                    'assertion' => Assertion\DoaAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/loi/submit',
                    'roles'     => ['user'],
                    'assertion' => Assertion\LoiAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/loi/render',
                    'roles'     => ['user'],
                    'assertion' => Assertion\LoiAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/loi/replace',
                    'roles'     => ['user'],
                    'assertion' => Assertion\LoiAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/loi/download',
                    'roles'     => ['user'],
                    'assertion' => Assertion\LoiAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/questionnaire/view',
                    'roles'     => ['user'],
                    'assertion' => Assertion\QuestionnaireAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/questionnaire/edit',
                    'roles'     => ['user'],
                    'assertion' => Assertion\QuestionnaireAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/questionnaire/overview',
                    'roles'     => ['user'],
                    'assertion' => Assertion\QuestionnaireAssertion::class,
                ],
            ],
        ],
    ],
];
