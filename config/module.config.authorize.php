<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

use Admin\Entity\Access;
use Affiliation\Acl\Assertion\Affiliation as AffiliationAssertion;
use Affiliation\Acl\Assertion\Doa as DoaAssertion;
use Affiliation\Acl\Assertion\Loi as LoiAssertion;

return [
    'bjyauthorize' => [
        /* Currently, only controller and route guards exist
         */
        'guards' => [
            /* If this guard is specified here (i.e. it is enabled], it will block
             * access to all routes unless they are specified here.
             */
            'BjyAuthorize\Guard\Route' => [
                [
                    'route' => 'zfcadmin/affiliation/list',
                    'roles' => ['office'],
                ],
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
                    'route'     => 'zfcadmin/affiliation/doa/list',
                    'roles'     => ['office'],
                    'assertion' => DoaAssertion::class,
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
                    'route'     => 'zfcadmin/affiliation/loi/list',
                    'roles'     => ['office'],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/approval',
                    'roles'     => ['office'],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/missing',
                    'roles'     => ['office'],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/remind',
                    'roles'     => ['office'],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/reminders',
                    'roles'     => ['office'],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/view',
                    'roles'     => ['office'],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/edit',
                    'roles'     => ['office'],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/approve',
                    'roles'     => ['office'],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/questionnaire/question/list',
                    'roles'     => [Access::ACCESS_OFFICE],
                ],
                [
                    'route'     => 'zfcadmin/affiliation/questionnaire/question/view',
                    'roles'     => [Access::ACCESS_OFFICE],
                ],
                [
                    'route'     => 'zfcadmin/affiliation/questionnaire/question/new',
                    'roles'     => [Access::ACCESS_OFFICE],
                ],
                [
                    'route'     => 'zfcadmin/affiliation/questionnaire/question/edit',
                    'roles'     => [Access::ACCESS_OFFICE],
                ],
                [
                    'route'     => 'zfcadmin/affiliation/questionnaire/category/list',
                    'roles'     => [Access::ACCESS_OFFICE],
                ],
                [
                    'route'     => 'zfcadmin/affiliation/questionnaire/category/view',
                    'roles'     => [Access::ACCESS_OFFICE],
                ],
                [
                    'route'     => 'zfcadmin/affiliation/questionnaire/category/new',
                    'roles'     => [Access::ACCESS_OFFICE],
                ],
                [
                    'route'     => 'zfcadmin/affiliation/questionnaire/category/edit',
                    'roles'     => [Access::ACCESS_OFFICE],
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
                    'route'     => 'community/affiliation/edit/affiliation',
                    'roles'     => 'user',
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/add-associate',
                    'roles'     => 'user',
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/manage-associate',
                    'roles'     => 'user',
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/cost-and-effort',
                    'roles'     => 'user',
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/financial',
                    'roles'     => 'user',
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/update-effort-spent',
                    'roles'     => 'user',
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/description',
                    'roles'     => 'user',
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/doa/upload',
                    'roles'     => 'user',
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/doa/render',
                    'roles'     => 'user',
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/doa/replace',
                    'roles'     => 'user',
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/doa/download',
                    'roles'     => 'user',
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/loi/submit',
                    'roles'     => 'user',
                    'assertion' => LoiAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/loi/render',
                    'roles'     => 'user',
                    'assertion' => LoiAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/loi/replace',
                    'roles'     => 'user',
                    'assertion' => LoiAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/loi/download',
                    'roles'     => 'user',
                    'assertion' => LoiAssertion::class,
                ],
            ],
        ],
    ],
];
