<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c] 2004-2015 ITEA Office (https://itea3.org]
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
                    'route'     => 'zfcadmin/affiliation/view',
                    'roles'     => [strtolower(Access::ACCESS_OFFICE)],
                    'assertion' => AffiliationAssertion::class
                ],
                [
                    'route'     => 'zfcadmin/affiliation/edit',
                    'roles'     => [strtolower(Access::ACCESS_OFFICE)],
                    'assertion' => AffiliationAssertion::class
                ],
                [
                    'route'     => 'zfcadmin/affiliation/merge',
                    'roles'     => [strtolower(Access::ACCESS_OFFICE)],
                    'assertion' => AffiliationAssertion::class
                ],
                [
                    'route'     => 'zfcadmin/affiliation/edit-associate',
                    'roles'     => [strtolower(Access::ACCESS_OFFICE)],
                    'assertion' => AffiliationAssertion::class
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/list',
                    'roles'     => [strtolower(Access::ACCESS_OFFICE)],
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/approval',
                    'roles'     => [strtolower(Access::ACCESS_OFFICE)],
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/missing',
                    'roles'     => [strtolower(Access::ACCESS_OFFICE)],
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/view',
                    'roles'     => [strtolower(Access::ACCESS_OFFICE)],
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/edit',
                    'roles'     => [strtolower(Access::ACCESS_OFFICE)],
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/remind',
                    'roles'     => [strtolower(Access::ACCESS_OFFICE)],
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/reminders',
                    'roles'     => [strtolower(Access::ACCESS_OFFICE)],
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'zfcadmin/affiliation/doa/approve',
                    'roles'     => [strtolower(Access::ACCESS_OFFICE)],
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/list',
                    'roles'     => [strtolower(Access::ACCESS_OFFICE)],
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/approval',
                    'roles'     => [strtolower(Access::ACCESS_OFFICE)],
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/missing',
                    'roles'     => [strtolower(Access::ACCESS_OFFICE)],
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/remind',
                    'roles'     => [strtolower(Access::ACCESS_OFFICE)],
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/reminders',
                    'roles'     => [strtolower(Access::ACCESS_OFFICE)],
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/view',
                    'roles'     => [strtolower(Access::ACCESS_OFFICE)],
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/edit',
                    'roles'     => [strtolower(Access::ACCESS_OFFICE)],
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'zfcadmin/affiliation/loi/approve',
                    'roles'     => [strtolower(Access::ACCESS_OFFICE)],
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/payment-sheet',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => AffiliationAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/payment-sheet-pdf',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => AffiliationAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/affiliation',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => AffiliationAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/edit/affiliation',
                    'roles'     => strtolower(Access::ACCESS_USER),
                    'assertion' => AffiliationAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/edit/add-associate',
                    'roles'     => strtolower(Access::ACCESS_USER),
                    'assertion' => AffiliationAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/edit/financial',
                    'roles'     => strtolower(Access::ACCESS_USER),
                    'assertion' => AffiliationAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/edit/update-effort-spent',
                    'roles'     => strtolower(Access::ACCESS_USER),
                    'assertion' => AffiliationAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/edit/description',
                    'roles'     => strtolower(Access::ACCESS_USER),
                    'assertion' => AffiliationAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/doa/upload',
                    'roles'     => strtolower(Access::ACCESS_USER),
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/doa/render',
                    'roles'     => strtolower(Access::ACCESS_USER),
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/doa/replace',
                    'roles'     => strtolower(Access::ACCESS_USER),
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/doa/download',
                    'roles'     => strtolower(Access::ACCESS_USER),
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/loi/upload',
                    'roles'     => strtolower(Access::ACCESS_USER),
                    'assertion' => LoiAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/loi/render',
                    'roles'     => strtolower(Access::ACCESS_USER),
                    'assertion' => LoiAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/loi/replace',
                    'roles'     => strtolower(Access::ACCESS_USER),
                    'assertion' => LoiAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/loi/download',
                    'roles'     => strtolower(Access::ACCESS_USER),
                    'assertion' => LoiAssertion::class
                ],
            ],
        ],
    ],
];
