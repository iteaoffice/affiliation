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
        'admin' => [
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
<<<<<<< Updated upstream
                            'entities' => [
                                'id' => Affiliation\Entity\Affiliation::class
                            ],
                            'invokables' => [
                                Affiliation\Navigation\Invokable\AffiliationLabel::class
                            ],
                            
=======
                            'entities'   => [
                                'id' => \Affiliation\Entity\Affiliation::class,
                            ],
                            'invokables' => [
                                Affiliation\Navigation\Invokable\AffiliationLabel::class
                            ]
>>>>>>> Stashed changes
                        ],
                        'pages'   => [
                            'edit'  => [
                                'label'   => _('txt-nav-edit'),
                                'route'   => 'zfcadmin/affiliation/edit',
                                'visible' => false,
                                'params'  => [
                                    'entities' => [
<<<<<<< Updated upstream
                                        'id' => Affiliation\Entity\Affiliation::class
=======
                                        'id' => \Affiliation\Entity\Affiliation::class,
>>>>>>> Stashed changes
                                    ],
                                ],
                            ],
                            'merge' => [
                                'label'   => _('txt-merge-with-other'),
                                'route'   => 'zfcadmin/affiliation/merge',
                                'visible' => false,
                                'params'  => [
                                    'entities' => [
<<<<<<< Updated upstream
                                        'id' => Affiliation\Entity\Affiliation::class
=======
                                        'id' => \Affiliation\Entity\Affiliation::class,
>>>>>>> Stashed changes
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
