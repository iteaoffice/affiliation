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
                ['route' => 'json/affiliation/loi/approve', 'roles' => ['office']],
                ['route' => 'json/affiliation/doa/approve', 'roles' => ['office']],
                ['route' => 'json/affiliation/doa/decline', 'roles' => ['office']],
            ]
        ],
    ],
];
