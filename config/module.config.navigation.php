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
                                                        'id' => Affiliation\Entity\Affiliation::class
                                                    ],
                                                    'invokables' => [
                                                        Affiliation\Navigation\Invokable\AffiliationLabel::class
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
                                                                'id' => 'affiliation-id'
                                                            ]
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
                                                                Affiliation\Navigation\Invokable\DoaLabel::class
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
                                                                'id' => 'affiliation-id'
                                                            ]
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
                                                                Affiliation\Navigation\Invokable\LoiLabel::class
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
                            'entities'   => [
                                'id' => Affiliation\Entity\Affiliation::class
                            ],
                            'invokables' => [
                                Affiliation\Navigation\Invokable\AffiliationLabel::class
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
];
