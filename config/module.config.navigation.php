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
            'affiliation' => [
                'label'    => _("txt-partner-admin"),
                'resource' => 'zfcadmin',
                'route'    => 'zfcadmin/affiliation-manager/doa/list',
                'pages'    => [
                    'doa' => [
                        'label' => _("txt-doa-administration"),
                        'route' => 'zfcadmin/affiliation-manager/doa/list',
                    ],
                    'loi' => [
                        'label' => _("txt-loi-administration"),
                        'route' => 'zfcadmin/affiliation-manager/loi/list',
                    ],
                ],
            ],
        ],
    ],
];

//
