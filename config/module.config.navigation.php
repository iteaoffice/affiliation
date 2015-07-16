<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
return [
    'navigation' => [
        'admin' => [
            // And finally, here is where we define our page hierarchy
            'organisation' => [
                'pages' => [
                    'doa-approval' => [
                        'label' => _("txt-doa-approval"),
                        'route' => 'zfcadmin/affiliation/doa/approval',
                    ],
                    'doa-missing'  => [
                        'label' => _("txt-missing-doa"),
                        'route' => 'zfcadmin/affiliation/doa/missing',
                    ],
                    'loi-approval' => [
                        'label' => _("txt-loi-approval"),
                        'route' => 'zfcadmin/affiliation/loi/approval',
                    ],
                    'loi-missing'  => [
                        'label' => _("txt-missing-loi"),
                        'route' => 'zfcadmin/affiliation/loi/missing',
                    ],
                ],
            ],
        ],
    ],
];

//
