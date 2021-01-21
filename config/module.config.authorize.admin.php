<?php
/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

use BjyAuthorize\Guard\Route;

return [
    'bjyauthorize' => [
        'guards' => [
            Route::class => [
                ['route' => 'zfcadmin/affiliation/details', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/description', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/market-access', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/costs-and-effort', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/project-versions', 'roles' => ['office']],

                ['route' => 'zfcadmin/affiliation/financial', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/contract', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/parent', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/payment-sheet', 'roles' => ['office']],

                ['route' => 'zfcadmin/affiliation/contacts', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/reporting', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/achievements', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/questionnaires', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/merge', 'roles' => ['office']],

                ['route' => 'zfcadmin/affiliation/missing-parent', 'roles' => ['office']],

                ['route' => 'zfcadmin/affiliation/edit/affiliation', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/edit/manage-associates', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/edit/add-associate', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/edit/associate', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/edit/financial', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/edit/description', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/edit/market-access', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/edit/effort-spent', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/edit/technical-contct', 'roles' => ['office']],

                ['route' => 'zfcadmin/affiliation/loi/approval', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/loi/missing', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/loi/view', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/loi/edit', 'roles' => ['office']],

                ['route' => 'zfcadmin/affiliation/doa/approval', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/doa/missing', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/doa/view', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/doa/edit', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/doa/remind', 'roles' => ['office']],
                ['route' => 'zfcadmin/affiliation/doa/reminders', 'roles' => ['office']],

                ['route' => 'zfcadmin/questionnaire/list', 'roles' => ['office']],
                ['route' => 'zfcadmin/questionnaire/view', 'roles' => ['office']],
                ['route' => 'zfcadmin/questionnaire/edit', 'roles' => ['office']],
                ['route' => 'zfcadmin/questionnaire/new', 'roles' => ['office']],
                ['route' => 'zfcadmin/questionnaire/copy', 'roles' => ['office']],

                ['route' => 'zfcadmin/questionnaire/question/list', 'roles' => ['office']],
                ['route' => 'zfcadmin/questionnaire/question/view', 'roles' => ['office']],
                ['route' => 'zfcadmin/questionnaire/question/edit', 'roles' => ['office']],
                ['route' => 'zfcadmin/questionnaire/question/new', 'roles' => ['office']],

                ['route' => 'zfcadmin/questionnaire/category/list', 'roles' => ['office']],
                ['route' => 'zfcadmin/questionnaire/category/view', 'roles' => ['office']],
                ['route' => 'zfcadmin/questionnaire/category/edit', 'roles' => ['office']],
                ['route' => 'zfcadmin/questionnaire/category/new', 'roles' => ['office']],

            ]
        ],
    ],
];
