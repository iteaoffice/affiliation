<?php

use Affiliation\Acl\Assertion\Affiliation as AffiliationAssertion;
use Affiliation\Acl\Assertion\Doa as DoaAssertion;
use Affiliation\Acl\Assertion\Loi as LoiAssertion;
use Affiliation\Acl\Assertion\QuestionnaireAssertion;
use BjyAuthorize\Guard\Route;

return [
    'bjyauthorize' => [
        'guards' => [
            Route::class => [
                [
                    'route' => 'zfcadmin/affiliation/list-csv',
                    'roles' => ['office'],
                ],
                [
                    'route'     => 'zfcadmin/affiliation/view',
                    'roles'     => ['office'],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/edit',
                    'roles'     => ['office'],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/merge',
                    'roles'     => ['office'],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/missing-affiliation-parent',
                    'roles'     => ['office'],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/edit-associate',
                    'roles'     => ['office'],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/add-associate',
                    'roles'     => ['office'],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/approval',
                    'roles'     => ['office'],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/missing',
                    'roles'     => ['office'],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/view',
                    'roles'     => ['office'],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/edit',
                    'roles'     => ['office'],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/remind',
                    'roles'     => ['office'],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/reminders',
                    'roles'     => ['office'],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/approve',
                    'roles'     => ['office'],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/decline',
                    'roles'     => ['office'],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/approval',
                    'roles'     => ['office'],
                    'assertion' => LoiAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/missing',
                    'roles'     => ['office'],
                    'assertion' => LoiAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/view',
                    'roles'     => ['office'],
                    'assertion' => LoiAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/edit',
                    'roles'     => ['office'],
                    'assertion' => LoiAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/approve',
                    'roles'     => ['office'],
                    'assertion' => LoiAssertion::class,
                ],
                [
                    'route' => 'zfcadmin/affiliation/questionnaire/list',
                    'roles' => ['office'],
                ],
                [
                    'route' => 'zfcadmin/affiliation/questionnaire/view',
                    'roles' => ['office'],
                ],
                [
                    'route' => 'zfcadmin/affiliation/questionnaire/new',
                    'roles' => ['office'],
                ],
                [
                    'route' => 'zfcadmin/affiliation/questionnaire/edit',
                    'roles' => ['office'],
                ],
                [
                    'route' => 'zfcadmin/affiliation/questionnaire/copy',
                    'roles' => ['office'],
                ],
                [
                    'route' => 'zfcadmin/affiliation/questionnaire/question/list',
                    'roles' => ['office'],
                ],
                [
                    'route' => 'zfcadmin/affiliation/questionnaire/question/view',
                    'roles' => ['office'],
                ],
                [
                    'route' => 'zfcadmin/affiliation/questionnaire/question/new',
                    'roles' => ['office'],
                ],
                [
                    'route' => 'zfcadmin/affiliation/questionnaire/question/edit',
                    'roles' => ['office'],
                ],
                [
                    'route' => 'zfcadmin/affiliation/questionnaire/category/list',
                    'roles' => ['office'],
                ],
                [
                    'route' => 'zfcadmin/affiliation/questionnaire/category/view',
                    'roles' => ['office'],
                ],
                [
                    'route' => 'zfcadmin/affiliation/questionnaire/category/new',
                    'roles' => ['office'],
                ],
                [
                    'route' => 'zfcadmin/affiliation/questionnaire/category/edit',
                    'roles' => ['office'],
                ],
                [
                    'route'     => 'community/affiliation/payment-sheet',
                    'roles'     => ['user'],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/payment-sheet-pdf',
                    'roles'     => ['user'],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/affiliation',
                    'roles'     => ['user'],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/technical-contact',
                    'roles'     => [],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/affiliation',
                    'roles'     => ['user'],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/add-associate',
                    'roles'     => ['user'],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/manage-associate',
                    'roles'     => ['user'],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/cost-and-effort',
                    'roles'     => ['user'],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/financial',
                    'roles'     => ['user'],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/update-effort-spent',
                    'roles'     => ['user'],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/description',
                    'roles'     => ['user'],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/doa/submit',
                    'roles'     => ['user'],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/doa/render',
                    'roles'     => ['user'],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/doa/replace',
                    'roles'     => ['user'],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/doa/download',
                    'roles'     => ['user'],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/loi/submit',
                    'roles'     => ['user'],
                    'assertion' => LoiAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/loi/render',
                    'roles'     => ['user'],
                    'assertion' => LoiAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/loi/replace',
                    'roles'     => ['user'],
                    'assertion' => LoiAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/loi/download',
                    'roles'     => ['user'],
                    'assertion' => LoiAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/questionnaire/view',
                    'roles'     => ['user'],
                    'assertion' => QuestionnaireAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/questionnaire/edit',
                    'roles'     => ['user'],
                    'assertion' => QuestionnaireAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/questionnaire/overview',
                    'roles'     => ['user'],
                    'assertion' => QuestionnaireAssertion::class,
                ],
            ],
        ],
    ],
];
