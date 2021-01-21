<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

namespace Affiliation;

$communityNavigation = [
    'project' => [
        'pages' => [
            'project-basics'         => [
                'pages' => [
                    'project-partners' => [
                        'pages' => [
                            'affiliation' => [
                                'label'   => _('txt-nav-project-partner'),
                                'route'   => 'community/affiliation/details',
                                'visible' => false,
                                'params'  => [
                                    'entities'   => [
                                        'id' => Entity\Affiliation::class,
                                    ],
                                    'invokables' => [
                                        Navigation\Invokable\AffiliationLabel::class,
                                    ],
                                ],
                                'pages'   => [
                                    'edit'          => [
                                        'label'   => _('txt-edit-affiliation'),
                                        'route'   => 'community/affiliation/edit/affiliation',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Entity\Affiliation::class,
                                            ],
                                        ],
                                    ],
                                    'description'   => [
                                        'label'   => _('txt-description'),
                                        'route'   => 'community/affiliation/description',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Entity\Affiliation::class,
                                            ],
                                        ],
                                        'pages'   => [
                                            'edit' => [
                                                'label'   => _('txt-nav-edit-description'),
                                                'route'   => 'community/affiliation/edit/description',
                                                'visible' => false,
                                                'params'  => [
                                                    'entities' => [
                                                        'id' => Entity\Affiliation::class,
                                                    ],
                                                ],
                                            ],
                                        ]
                                    ],
                                    'market-access' => [
                                        'label'   => _('txt-market-access'),
                                        'route'   => 'community/affiliation/market-access',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Entity\Affiliation::class,
                                            ],
                                        ],
                                        'pages'   => [
                                            'edit' => [
                                                'label'   => _('txt-nav-edit-market-access'),
                                                'route'   => 'community/affiliation/edit/market-access',
                                                'visible' => true,
                                                'params'  => [
                                                    'entities' => [
                                                        'id' => Entity\Affiliation::class,
                                                    ],
                                                ],
                                            ],
                                        ]
                                    ],

                                    'financial'        => [
                                        'label'   => _('txt-financial'),
                                        'route'   => 'community/affiliation/financial',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Entity\Affiliation::class,
                                            ],
                                        ],
                                        'pages'   => [
                                            'edit' => [
                                                'label'   => _('txt-nav-edit-financial-information'),
                                                'route'   => 'community/affiliation/edit/financial',
                                                'visible' => false,
                                                'params'  => [
                                                    'entities' => [
                                                        'id' => Entity\Affiliation::class,
                                                    ],
                                                ],
                                            ],
                                        ]
                                    ],
                                    'contacts'         => [
                                        'label'   => _('txt-contacts'),
                                        'route'   => 'community/affiliation/contacts',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Entity\Affiliation::class,
                                            ],
                                        ],
                                        'pages'   => [
                                            'manage-associates' => [
                                                'label'   => _('txt-nav-manage-associates'),
                                                'route'   => 'community/affiliation/edit/manage-associates',
                                                'visible' => false,
                                                'params'  => [
                                                    'entities' => [
                                                        'id' => Entity\Affiliation::class,
                                                    ],
                                                ],
                                            ],
                                            'add-associate'     => [
                                                'label'  => _('txt-nav-add-associate'),
                                                'route'  => 'community/affiliation/edit/add-associate',
                                                'params' => [
                                                    'entities' => [
                                                        'id' => Entity\Affiliation::class,
                                                    ],
                                                ],
                                            ],
                                        ]
                                    ],
                                    'costs-and-effort' => [
                                        'label'   => _('txt-costs-and-effort'),
                                        'route'   => 'community/affiliation/costs-and-effort',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Entity\Affiliation::class,
                                            ],
                                        ],
                                        'pages'   => [
                                            'edit' => [
                                                'label'   => _('txt-edit'),
                                                'route'   => 'community/affiliation/edit/costs-and-effort',
                                                'visible' => false,
                                                'params'  => [
                                                    'entities' => [
                                                        'id' => Entity\Affiliation::class,
                                                    ],
                                                ],
                                            ],
                                        ]
                                    ],


                                    'technical-contact-leader' => [
                                        'label'   => _('txt-manage-technical-contacts'),
                                        'route'   => 'community/affiliation/technical-contact',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Entity\Affiliation::class,
                                            ],
                                        ],
                                    ],

                                    'submit-doa'          => [
                                        'label'   => _('txt-submit-doa'),
                                        'route'   => 'community/affiliation/doa/submit',
                                        'visible' => false,
                                        'params'  => [
                                            'entities'   => [
                                                'id' => Entity\Affiliation::class,
                                            ],
                                            'routeParam' => [
                                                'id' => 'affiliationId',
                                            ],
                                        ],
                                    ],
                                    'replace-doa'         => [
                                        'label'   => _('txt-replace-doa'),
                                        'route'   => 'community/affiliation/doa/replace',
                                        'visible' => false,
                                        'params'  => [
                                            'entities'   => [
                                                'id' => Entity\Doa::class,
                                            ],
                                            'invokables' => [
                                                Navigation\Invokable\DoaLabel::class,
                                            ],
                                        ],
                                    ],
                                    'submit-loi'          => [
                                        'label'   => _('txt-submit-loi'),
                                        'route'   => 'community/affiliation/loi/submit',
                                        'visible' => false,
                                        'params'  => [
                                            'entities'   => [
                                                'id' => Entity\Affiliation::class,
                                            ],
                                            'routeParam' => [
                                                'id' => 'affiliationId',
                                            ],
                                        ],
                                    ],
                                    'replace-loi'         => [
                                        'label'   => _('txt-replace-loi'),
                                        'route'   => 'community/affiliation/loi/replace',
                                        'visible' => false,
                                        'params'  => [
                                            'entities'   => [
                                                'id' => Entity\Affiliation::class,
                                            ],
                                            'invokables' => [
                                                Navigation\Invokable\LoiLabel::class,
                                            ],
                                        ],
                                    ],
                                    'payment-sheet'       => [
                                        'label'   => _('txt-affiliation-payment-sheet'),
                                        'route'   => 'community/affiliation/payment-sheet',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Entity\Affiliation::class,
                                            ],
                                        ],
                                    ],
                                    'update-effort-spent' => [
                                        'label'   => _('txt-update-effort-spent'),
                                        'route'   => 'community/affiliation/edit/update-effort-spent',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Entity\Affiliation::class,
                                            ],
                                        ],
                                    ],
                                    'view-questionnaire'  => [
                                        'label'   => _('txt-view-questionnaire'),
                                        'route'   => 'community/questionnaire/view',
                                        'visible' => false,
                                        'params'  => [
                                            'entities'   => [
                                                'id' => Entity\Affiliation::class,
                                            ],
                                            'routeParam' => [
                                                'id' => 'affiliationId',
                                            ],
                                        ],
                                    ],
                                    'edit-questionnaire'  => [
                                        'label'   => _('txt-edit-questionnaire'),
                                        'route'   => 'community/questionnaire/edit',
                                        'visible' => false,
                                        'params'  => [
                                            'entities'   => [
                                                'id' => Entity\Affiliation::class,
                                            ],
                                            'routeParam' => [
                                                'id' => 'affiliationId',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'questionnaire-overview' => [
                'label' => _('txt-questionnaire-overview'),
                'route' => 'community/questionnaire/overview',
            ],
        ],
    ],
];

return [
    'navigation' => [
        'community'  => $communityNavigation,
        'community2' => ['index' => [
            'pages' => $communityNavigation
        ],
        ],
    ],
];
