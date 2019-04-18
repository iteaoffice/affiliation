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
use Affiliation\Acl\Assertion\QuestionnaireAssertion;

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
                    'route'     => 'zfcadmin/affiliation/list-csv',
                    'roles'     => [Access::ACCESS_OFFICE],
                ],
                [
                    'route'     => 'zfcadmin/affiliation/view',
                    'roles'     => [Access::ACCESS_OFFICE],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/edit',
                    'roles'     => [Access::ACCESS_OFFICE],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/merge',
                    'roles'     => [Access::ACCESS_OFFICE],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/missing-affiliation-parent',
                    'roles'     => [Access::ACCESS_OFFICE],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/edit-associate',
                    'roles'     => [Access::ACCESS_OFFICE],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/add-associate',
                    'roles'     => [Access::ACCESS_OFFICE],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/list',
                    'roles'     => [Access::ACCESS_OFFICE],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/approval',
                    'roles'     => [Access::ACCESS_OFFICE],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/missing',
                    'roles'     => [Access::ACCESS_OFFICE],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/view',
                    'roles'     => [Access::ACCESS_OFFICE],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/edit',
                    'roles'     => [Access::ACCESS_OFFICE],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/remind',
                    'roles'     => [Access::ACCESS_OFFICE],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/reminders',
                    'roles'     => [Access::ACCESS_OFFICE],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/approve',
                    'roles'     => [Access::ACCESS_OFFICE],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/list',
                    'roles'     => [Access::ACCESS_OFFICE],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/approval',
                    'roles'     => [Access::ACCESS_OFFICE],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/missing',
                    'roles'     => [Access::ACCESS_OFFICE],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/remind',
                    'roles'     => [Access::ACCESS_OFFICE],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/reminders',
                    'roles'     => [Access::ACCESS_OFFICE],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/view',
                    'roles'     => [Access::ACCESS_OFFICE],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/edit',
                    'roles'     => [Access::ACCESS_OFFICE],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/approve',
                    'roles'     => [Access::ACCESS_OFFICE],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'zfcadmin/affiliation/questionnaire/list',
                    'roles'     => [Access::ACCESS_OFFICE],
                ],
                [
                    'route'     => 'zfcadmin/affiliation/questionnaire/view',
                    'roles'     => [Access::ACCESS_OFFICE],
                ],
                [
                    'route'     => 'zfcadmin/affiliation/questionnaire/new',
                    'roles'     => [Access::ACCESS_OFFICE],
                ],
                [
                    'route'     => 'zfcadmin/affiliation/questionnaire/edit',
                    'roles'     => [Access::ACCESS_OFFICE],
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
                    'roles'     => [Access::ACCESS_USER],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/affiliation',
                    'roles'     => [Access::ACCESS_USER],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/affiliation',
                    'roles'     => [Access::ACCESS_USER],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/add-associate',
                    'roles'     => [Access::ACCESS_USER],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/manage-associate',
                    'roles'     => [Access::ACCESS_USER],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/cost-and-effort',
                    'roles'     => [Access::ACCESS_USER],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/financial',
                    'roles'     => [Access::ACCESS_USER],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/update-effort-spent',
                    'roles'     => [Access::ACCESS_USER],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/edit/description',
                    'roles'     => [Access::ACCESS_USER],
                    'assertion' => AffiliationAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/doa/upload',
                    'roles'     => [Access::ACCESS_USER],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/doa/render',
                    'roles'     => [Access::ACCESS_USER],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/doa/replace',
                    'roles'     => [Access::ACCESS_USER],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/doa/download',
                    'roles'     => [Access::ACCESS_USER],
                    'assertion' => DoaAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/loi/submit',
                    'roles'     => [Access::ACCESS_USER],
                    'assertion' => LoiAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/loi/render',
                    'roles'     => [Access::ACCESS_USER],
                    'assertion' => LoiAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/loi/replace',
                    'roles'     => [Access::ACCESS_USER],
                    'assertion' => LoiAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/loi/download',
                    'roles'     => [Access::ACCESS_USER],
                    'assertion' => LoiAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/questionnaire/view',
                    'roles'     => [Access::ACCESS_USER],
                    'assertion' => QuestionnaireAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/questionnaire/edit',
                    'roles'     => [Access::ACCESS_USER],
                    'assertion' => QuestionnaireAssertion::class,
                ],
                [
                    'route'     => 'community/affiliation/questionnaire/overview',
                    'roles'     => [Access::ACCESS_USER],
                    'assertion' => QuestionnaireAssertion::class,
                ],
            ],
        ],
    ],
];
