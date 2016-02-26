<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
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
                    'affiliation'  => [
                        'label'   => _("txt-nav-project-partner"),
                        'route'   => 'zfcadmin/affiliation/view',
                        'visible' => false,
                        'params'  => [
                            'setters' => [
                                'label' => Affiliation\Navigation\Invokable\AffiliationLabel::class
                            ],
                            'entity'  => \Affiliation\Entity\Affiliation::class,
                        ],
                        'pages'   => [
                            'edit' => [
                                'label'   => _('txt-nav-edit'),
                                'route'   => 'zfcadmin/affiliation/edit',
                                'visible' => false,
                                'params'  => [
                                    'entity' => \Affiliation\Entity\Affiliation::class,
                                ],
                            ],
                            'merge' => [
                                'label'   => _('txt-merge-with-other'),
                                'route'   => 'zfcadmin/affiliation/merge',
                                'visible' => false,
                                'params'  => [
                                    'entity' => \Affiliation\Entity\Affiliation::class,
                                ],
                            ],
                        ],

                    ],
                ],
            ],
        ],
    ],
];

//
