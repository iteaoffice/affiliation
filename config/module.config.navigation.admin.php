<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

namespace Affiliation;

return [
    'navigation' => [
        'admin' => [
            'project' => [
                'pages' => [
                    'project-list' => [
                        'pages' => [
                            'project-view' => [
                                'pages' => [
                                    'affiliations' => [
                                        'pages' => [
                                            'affiliation' => [
                                                'route'   => 'zfcadmin/affiliation/details',
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
                                                    'edit-affiliation' => [
                                                        'label'   => _('txt-nav-edit'),
                                                        'route'   => 'zfcadmin/affiliation/edit/affiliation',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities' => [
                                                                'id' => Entity\Affiliation::class,
                                                            ],
                                                        ],
                                                    ],
                                                    'description'      => [
                                                        'label'   => _('txt-description'),
                                                        'route'   => 'zfcadmin/affiliation/description',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities' => [
                                                                'id' => Entity\Affiliation::class,
                                                            ],
                                                        ],
                                                        'pages'   => [
                                                            'edit' => [
                                                                'label'   => _('txt-nav-edit-description'),
                                                                'route'   => 'zfcadmin/affiliation/edit/description',
                                                                'visible' => false,
                                                                'params'  => [
                                                                    'entities' => [
                                                                        'id' => Entity\Affiliation::class,
                                                                    ],
                                                                ],
                                                            ],
                                                        ]
                                                    ],
                                                    'market-access'    => [
                                                        'label'   => _('txt-market-access'),
                                                        'route'   => 'zfcadmin/affiliation/market-access',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities' => [
                                                                'id' => Entity\Affiliation::class,
                                                            ],
                                                        ],
                                                        'pages'   => [
                                                            'edit' => [
                                                                'label'   => _('txt-nav-edit-market-access'),
                                                                'route'   => 'zfcadmin/affiliation/edit/market-access',
                                                                'visible' => false,
                                                                'params'  => [
                                                                    'entities' => [
                                                                        'id' => Entity\Affiliation::class,
                                                                    ],
                                                                ],
                                                            ],
                                                        ]
                                                    ],
                                                    'costs-and-effort' => [
                                                        'label'   => _('txt-costs-and-effort'),
                                                        'route'   => 'zfcadmin/affiliation/costs-and-effort',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities' => [
                                                                'id' => Entity\Affiliation::class,
                                                            ],
                                                        ],
                                                    ],
                                                    'project-versions' => [
                                                        'label'   => _('txt-project-versions'),
                                                        'route'   => 'zfcadmin/affiliation/project-versions',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities' => [
                                                                'id' => Entity\Affiliation::class,
                                                            ],
                                                        ],
                                                    ],
                                                    'financial'        => [
                                                        'label'   => _('txt-financial'),
                                                        'route'   => 'zfcadmin/affiliation/financial',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities' => [
                                                                'id' => Entity\Affiliation::class,
                                                            ],
                                                        ],
                                                        'pages'   => [
                                                            'edit' => [
                                                                'label'   => _('txt-nav-edit-financial-information'),
                                                                'route'   => 'zfcadmin/affiliation/edit/financial',
                                                                'visible' => false,
                                                                'params'  => [
                                                                    'entities' => [
                                                                        'id' => Entity\Affiliation::class,
                                                                    ],
                                                                ],
                                                            ],
                                                            'contract' => [
                                                                'label'   => _('txt-contract'),
                                                                'route'   => 'zfcadmin/affiliation/contract',
                                                                'visible' => false,
                                                                'params'  => [
                                                                    'entities' => [
                                                                        'id' => Entity\Affiliation::class,
                                                                    ],
                                                                ],
                                                            ],
                                                            'parent'   => [
                                                                'label'   => _('txt-membership'),
                                                                'route'   => 'zfcadmin/affiliation/parent',
                                                                'visible' => false,
                                                                'params'  => [
                                                                    'entities' => [
                                                                        'id' => Entity\Affiliation::class,
                                                                    ],
                                                                ],
                                                            ],
                                                            'payment-sheet'       => [
                                                                'label'   => _('txt-affiliation-payment-sheet'),
                                                                'route'   => 'zfcadmin/affiliation/payment-sheet',
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
                                                        'route'   => 'zfcadmin/affiliation/contacts',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities' => [
                                                                'id' => Entity\Affiliation::class,
                                                            ],
                                                        ],
                                                        'pages'   => [
                                                            'manage-associates' => [
                                                                'label'   => _('txt-nav-manage-associates'),
                                                                'route'   => 'zfcadmin/affiliation/edit/manage-associates',
                                                                'visible' => false,
                                                                'params'  => [
                                                                    'entities' => [
                                                                        'id' => Entity\Affiliation::class,
                                                                    ],
                                                                ],
                                                            ],
                                                            'edit-associate'    => [
                                                                'label'   => _('txt-nav-edit-associate'),
                                                                'route'   => 'zfcadmin/affiliation/edit/associate',
                                                                'visible' => false,
                                                                'params'  => [
                                                                    'entities' => [
                                                                        'id' => Entity\Affiliation::class,
                                                                    ],
                                                                ],
                                                            ],
                                                            'add-associate'     => [
                                                                'label'  => _('txt-nav-add-associate'),
                                                                'route'  => 'zfcadmin/affiliation/edit/add-associate',
                                                                'params' => [
                                                                    'entities' => [
                                                                        'id' => Entity\Affiliation::class,
                                                                    ],
                                                                ],
                                                            ],
                                                        ]
                                                    ],
                                                    'reporting' => [
                                                        'label'   => _('txt-reports-on-project-progress'),
                                                        'route'   => 'zfcadmin/affiliation/reporting',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities' => [
                                                                'id' => Entity\Affiliation::class,
                                                            ],
                                                        ],
                                                    ],

                                                    'achievements' => [
                                                        'label'   => _('txt-achievements'),
                                                        'route'   => 'zfcadmin/affiliation/achievements',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities' => [
                                                                'id' => Entity\Affiliation::class,
                                                            ],
                                                        ],
                                                    ],

                                                    'view-doa' => [
                                                        'label'   => _('txt-nav-view-doa'),
                                                        'route'   => 'zfcadmin/affiliation/doa/view',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities'   => [
                                                                'id' => Entity\Doa::class,
                                                            ],
                                                            'invokables' => [
                                                                Navigation\Invokable\DoaLabel::class,
                                                            ],
                                                        ],
                                                        'pages'   => [
                                                            'edit-doa' => [
                                                                'label'   => _('txt-nav-edit-doa'),
                                                                'route'   => 'zfcadmin/affiliation/doa/edit',
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
                                                        ]
                                                    ],
                                                    'view-loi' => [
                                                        'label'   => _('txt-nav-view-loi'),
                                                        'route'   => 'zfcadmin/affiliation/loi/view',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities'   => [
                                                                'id' => Entity\Loi::class,
                                                            ],
                                                            'invokables' => [
                                                                Navigation\Invokable\LoiLabel::class,
                                                            ],
                                                        ],
                                                        'pages'   => [
                                                            'edit-loi' => [
                                                                'label'   => _('txt-nav-edit-loi'),
                                                                'route'   => 'zfcadmin/affiliation/loi/edit',
                                                                'visible' => false,
                                                                'params'  => [
                                                                    'entities'   => [
                                                                        'id' => Entity\Loi::class,
                                                                    ],
                                                                    'invokables' => [
                                                                        Navigation\Invokable\LoiLabel::class,
                                                                    ],
                                                                ],
                                                            ],
                                                        ]
                                                    ],

                                                    'merge' => [
                                                        'label'   => _('txt-merge-with-other'),
                                                        'route'   => 'zfcadmin/affiliation/merge',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities' => [
                                                                'id' => Entity\Affiliation::class,
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ]
                                    ]
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'tools'   => [
                'pages' => [
                    'missing-affiliation-parent' => [
                        'label' => _('txt-nav-missing-affiliation-parent'),
                        'order' => 20,
                        'route' => 'zfcadmin/affiliation/missing-parent',
                    ],
                    'doa-approval'               => [
                        'label' => _('txt-nav-doa-approval'),
                        'order' => 80,
                        'route' => 'zfcadmin/affiliation/doa/approval',
                        'pages' => [
                            'edit' => [
                                'label'   => _('txt-nav-edit'),
                                'route'   => 'zfcadmin/affiliation/doa/edit',
                                'visible' => false,
                                'params'  => [
                                    'entities'   => [
                                        'id' => Doa::class,
                                    ],
                                    'invokables' => [
                                        Navigation\Invokable\DoaLabel::class,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'doa-missing'                => [
                        'label' => _('txt-nav-missing-doa'),
                        'order' => 90,
                        'route' => 'zfcadmin/affiliation/doa/missing',
                        'pages' => [
                            'remind'    => [
                                'label'  => _('txt-nav-doa-remind'),
                                'route'  => 'zfcadmin/affiliation/doa/remind',
                                'params' => [
                                    'entities'   => [
                                        'id' => Entity\Affiliation::class,
                                    ],
                                    'routeParam' => [
                                        'id' => 'affiliationId'
                                    ],
                                    'invokables' => [
                                        Navigation\Invokable\AffiliationLabel::class,
                                    ],
                                ],
                            ],
                            'reminders' => [
                                'label'   => _('txt-nav-doa-reminders'),
                                'route'   => 'zfcadmin/affiliation/doa/reminders',
                                'visible' => false,
                            ],
                        ],
                    ],
                    'loi-approval'               => [
                        'label' => _('txt-nav-loi-approval'),
                        'order' => 100,
                        'route' => 'zfcadmin/affiliation/loi/approval',
                    ],
                    'loi-missing'                => [
                        'label' => _('txt-nav-missing-loi'),
                        'order' => 110,
                        'route' => 'zfcadmin/affiliation/loi/missing',
                        'pages' => [
                            'loi-remind' => [
                                'label'  => _('txt-nav-loi-reminder'),
                                'route'  => 'zfcadmin/affiliation/loi/remind',
                                'params' => [
                                    'entities'   => [
                                        'id' => Entity\Affiliation::class,
                                    ],
                                    'routeParam' => [
                                        'id' => 'affiliationId'
                                    ],
                                    'invokables' => [
                                        Navigation\Invokable\AffiliationLabel::class,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            ],
            'config'  => [
                'pages' => [
                    'questionnaire-list'     => [
                        'label' => _('txt-nav-affiliation-questionnaire-list'),
                        'route' => 'zfcadmin/questionnaire/list',
                        'pages' => [
                            'view-questionnaire' => [
                                'route'   => 'zfcadmin/questionnaire/view',
                                'visible' => false,
                                'params'  => [
                                    'entities'   => [
                                        'id' => Entity\Questionnaire\Questionnaire::class,
                                    ],
                                    'invokables' => [
                                        Navigation\Invokable\Questionnaire\QuestionnaireLabel::class,
                                    ],
                                ],
                                'pages'   => [
                                    'edit-questionnaire' => [
                                        'label'   => _('txt-edit'),
                                        'route'   => 'zfcadmin/questionnaire/edit',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Entity\Questionnaire\Questionnaire::class,
                                            ],
                                        ],
                                    ],
                                    'copy-questionnaire' => [
                                        'label'   => _('txt-copy'),
                                        'route'   => 'zfcadmin/questionnaire/copy',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Entity\Questionnaire\Questionnaire::class,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'new-questionnaire'  => [
                                'label'   => _('txt-new-questionnaire'),
                                'route'   => 'zfcadmin/questionnaire/new',
                                'visible' => false,
                            ],
                        ],
                    ],
                    'question-list'          => [
                        'label' => _('txt-nav-affiliation-question-list'),
                        'route' => 'zfcadmin/questionnaire/question/list',
                        'pages' => [
                            'view-question' => [
                                'route'   => 'zfcadmin/questionnaire/question/view',
                                'visible' => false,
                                'params'  => [
                                    'entities'   => [
                                        'id' => Entity\Questionnaire\Question::class,
                                    ],
                                    'invokables' => [
                                        Navigation\Invokable\Questionnaire\QuestionLabel::class,
                                    ],
                                ],
                                'pages'   => [
                                    'edit-question' => [
                                        'label'   => _('txt-edit'),
                                        'route'   => 'zfcadmin/questionnaire/question/edit',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Entity\Questionnaire\Question::class,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'new-question'  => [
                                'label'   => _('txt-new-question'),
                                'route'   => 'zfcadmin/questionnaire/question/new',
                                'visible' => false,
                            ],
                        ],
                    ],
                    'question-category-list' => [
                        'label' => _('txt-nav-affiliation-question-category-list'),
                        'route' => 'zfcadmin/questionnaire/category/list',
                        'pages' => [
                            'view-question-category' => [
                                'route'   => 'zfcadmin/questionnaire/category/view',
                                'visible' => false,
                                'params'  => [
                                    'entities'   => [
                                        'id' => Entity\Questionnaire\Category::class,
                                    ],
                                    'invokables' => [
                                        Navigation\Invokable\Questionnaire\CategoryLabel::class,
                                    ],
                                ],
                                'pages'   => [
                                    'edit-question-category' => [
                                        'label'   => _('txt-edit'),
                                        'route'   => 'zfcadmin/questionnaire/category/edit',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Entity\Questionnaire\Category::class,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'new-question-category'  => [
                                'label'   => _('txt-new-question-category'),
                                'route'   => 'zfcadmin/questionnaire/category/new',
                                'visible' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
