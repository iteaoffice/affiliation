<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2016 ITEA Office (https://itea3.org)
 */
return [
    'navigation' => [
        'community' => [
            'project' => [
                'pages' => [
                    'projects' => [
                        'pages' => [
                            'project-basics' => [
                                'pages' => [
                                    'project-partners' => [
                                        'pages' => [
                                            'affiliation' => [
                                                'label'   => _("txt-nav-project-partner"),
                                                'route'   => 'community/affiliation/affiliation',
                                                'visible' => false,
                                                'params'  => [
                                                    'entities'   => [
                                                        'id' => Affiliation\Entity\Affiliation::class,
                                                    ],
                                                    'invokables' => [
                                                        Affiliation\Navigation\Invokable\AffiliationLabel::class,
                                                    ],
                                                ],
                                                'pages'   => [
                                                    'edit-affiliation' => [
                                                        'label'   => _('txt-edit-affiliation'),
                                                        'route'   => 'community/affiliation/edit/affiliation',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities' => [
                                                                'id' => \Affiliation\Entity\Affiliation::class,
                                                            ],
                                                        ],
                                                    ],
                                                    'edit-description' => [
                                                        'label'   => _('txt-edit-description'),
                                                        'route'   => 'community/affiliation/edit/description',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities' => [
                                                                'id' => \Affiliation\Entity\Affiliation::class,
                                                            ],
                                                        ],
                                                    ],
                                                    'edit-financial'   => [
                                                        'label'   => _('txt-edit-financial'),
                                                        'route'   => 'community/affiliation/edit/financial',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities' => [
                                                                'id' => \Affiliation\Entity\Affiliation::class,
                                                            ],
                                                        ],
                                                    ],
                                                    'add-associate'    => [
                                                        'label'   => _('txt-add-associate'),
                                                        'route'   => 'community/affiliation/edit/add-associate',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities' => [
                                                                'id' => \Affiliation\Entity\Affiliation::class,
                                                            ],
                                                        ],
                                                    ],
                                                    'upload-doa'       => [
                                                        'label'   => _('txt-upload-doa'),
                                                        'route'   => 'community/affiliation/doa/upload',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities'   => [
                                                                'id' => \Affiliation\Entity\Affiliation::class,
                                                            ],
                                                            'routeParam' => [
                                                                'id' => 'affiliationId',
                                                            ],
                                                        ],
                                                    ],
                                                    'replace-doa'      => [
                                                        'label'   => _('txt-replace-doa'),
                                                        'route'   => 'community/affiliation/doa/replace',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities'   => [
                                                                'id' => \Affiliation\Entity\Doa::class,
                                                            ],
                                                            'invokables' => [
                                                                Affiliation\Navigation\Invokable\DoaLabel::class,
                                                            ],
                                                        ],
                                                    ],
                                                    'upload-loi'       => [
                                                        'label'   => _('txt-upload-loi'),
                                                        'route'   => 'community/affiliation/loi/upload',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities'   => [
                                                                'id' => \Affiliation\Entity\Affiliation::class,
                                                            ],
                                                            'routeParam' => [
                                                                'id' => 'affiliationId',
                                                            ],
                                                        ],
                                                    ],
                                                    'replace-loi'      => [
                                                        'label'   => _('txt-replace-loi'),
                                                        'route'   => 'community/affiliation/loi/replace',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities'   => [
                                                                'id' => \Affiliation\Entity\Affiliation::class,
                                                            ],
                                                            'invokables' => [
                                                                Affiliation\Navigation\Invokable\LoiLabel::class,
                                                            ],
                                                        ],
                                                    ],
                                                    'payment-sheet'    => [
                                                        'label'   => _('txt-affiliation-payment-sheet'),
                                                        'route'   => 'community/affiliation/payment-sheet',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities' => [
                                                                'id' => \Affiliation\Entity\Affiliation::class,
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'admin'     => [
            'organisation' => [
                // And finally, here is where we define our page hierarchy
                'pages' => [
                    'affiliation-list' => [
                        'label' => _("txt-nav-affiliation-list-statistics"),
                        'order' => 15,
                        'route' => 'zfcadmin/affiliation/list',
                    ],
                    'missing-affiliation-parent' => [
                        'label' => _("txt-nav-missing-affiliation-parent"),
                        'order' => 20,
                        'route' => 'zfcadmin/affiliation/missing-affiliation-parent',
                    ],
                    'doa-approval'     => [
                        'label' => _("txt-nav-doa-approval"),
                        'order' => 80,
                        'route' => 'zfcadmin/affiliation/doa/approval',
                        'pages' => [
                            'edit' => [
                                'label'   => _('txt-nav-edit'),
                                'route'   => 'zfcadmin/affiliation/doa/edit',
                                'visible' => false,
                                'params'  => [
                                    'entities'   => [
                                        'id' => \Affiliation\Entity\Doa::class,
                                    ],
                                    'invokables' => [
                                        Affiliation\Navigation\Invokable\DoaLabel::class,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'doa-missing'      => [
                        'label' => _("txt-nav-missing-doa"),
                        'order' => 90,
                        'route' => 'zfcadmin/affiliation/doa/missing',
                        'pages' => [
                            'edit'      => [
                                'label'   => _('txt-nav-doa-remind'),
                                'route'   => 'zfcadmin/affiliation/doa/remind',
                                'visible' => false,
                            ],
                            'reminders' => [
                                'label'   => _('txt-nav-doa-reminders'),
                                'route'   => 'zfcadmin/affiliation/doa/reminders',
                                'visible' => false,
                            ],
                        ],
                    ],
                    'loi-approval'     => [
                        'label' => _("txt-nav-loi-approval"),
                        'order' => 100,
                        'route' => 'zfcadmin/affiliation/loi/approval',
                    ],
                    'loi-missing'      => [
                        'label' => _("txt-nav-missing-loi"),
                        'order' => 110,
                        'route' => 'zfcadmin/affiliation/loi/missing',
                    ],

                ],
            ],
            'project'      => [
                'pages' => [
                    'project-list' => [
                        'pages' => [
                            'project-view' => [
                                'pages' => [
                                    'affiliation' => [
                                        'label'   => _("txt-nav-project-partner"),
                                        'route'   => 'zfcadmin/affiliation/view',
                                        'visible' => false,
                                        'params'  => [
                                            'entities'   => [
                                                'id' => Affiliation\Entity\Affiliation::class,
                                            ],
                                            'invokables' => [
                                                Affiliation\Navigation\Invokable\AffiliationLabel::class,
                                            ],
                                        ],
                                        'pages'   => [
                                            'edit'  => [
                                                'label'   => _('txt-nav-edit'),
                                                'route'   => 'zfcadmin/affiliation/edit',
                                                'visible' => false,
                                                'params'  => [
                                                    'entities' => [
                                                        'id' => \Affiliation\Entity\Affiliation::class,
                                                    ],
                                                ],
                                            ],
                                            'merge' => [
                                                'label'   => _('txt-merge-with-other'),
                                                'route'   => 'zfcadmin/affiliation/merge',
                                                'visible' => false,
                                                'params'  => [
                                                    'entities' => [
                                                        'id' => \Affiliation\Entity\Affiliation::class,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
