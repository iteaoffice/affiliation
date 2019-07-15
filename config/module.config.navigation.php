<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Doa;
use Affiliation\Entity\Loi;
use Affiliation\Entity\Questionnaire;
use Affiliation\Navigation\Invokable;

$communityNavigation = [
    'project' => [
        'pages' => [
            'project-basics'         => [
                'pages' => [
                    'project-partners' => [
                        'pages' => [
                            'affiliation' => [
                                'label'   => _('txt-nav-project-partner'),
                                'route'   => 'community/affiliation/affiliation',
                                'visible' => false,
                                'params'  => [
                                    'entities'   => [
                                        'id' => Affiliation::class,
                                    ],
                                    'invokables' => [
                                        Invokable\AffiliationLabel::class,
                                    ],
                                ],
                                'pages'   => [
                                    'edit-affiliation'    => [
                                        'label'   => _('txt-edit-affiliation'),
                                        'route'   => 'community/affiliation/edit/affiliation',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Affiliation::class,
                                            ],
                                        ],
                                    ],
                                    'edit-description'    => [
                                        'label'   => _('txt-edit-description'),
                                        'route'   => 'community/affiliation/edit/description',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Affiliation::class,
                                            ],
                                        ],
                                    ],
                                    'edit-financial'      => [
                                        'label'   => _('txt-edit-financial'),
                                        'route'   => 'community/affiliation/edit/financial',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Affiliation::class,
                                            ],
                                        ],
                                    ],
                                    'add-associate'       => [
                                        'label'   => _('txt-add-associate'),
                                        'route'   => 'community/affiliation/edit/add-associate',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Affiliation::class,
                                            ],
                                        ],
                                    ],
                                    'manage-associate'    => [
                                        'label'   => _('txt-manage-associate'),
                                        'route'   => 'community/affiliation/edit/manage-associate',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Affiliation::class,
                                            ],
                                        ],
                                    ],
                                    'cost-and-effort'     => [
                                        'label'   => _('txt-edit-cost-and-effort'),
                                        'route'   => 'community/affiliation/edit/cost-and-effort',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Affiliation::class,
                                            ],
                                        ],
                                    ],
                                    'submit-doa'          => [
                                        'label'   => _('txt-submit-doa'),
                                        'route'   => 'community/affiliation/doa/submit',
                                        'visible' => false,
                                        'params'  => [
                                            'entities'   => [
                                                'id' => Affiliation::class,
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
                                                'id' => Doa::class,
                                            ],
                                            'invokables' => [
                                                Invokable\DoaLabel::class,
                                            ],
                                        ],
                                    ],
                                    'submit-loi'          => [
                                        'label'   => _('txt-submit-loi'),
                                        'route'   => 'community/affiliation/loi/submit',
                                        'visible' => false,
                                        'params'  => [
                                            'entities'   => [
                                                'id' => Affiliation::class,
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
                                                'id' => Affiliation::class,
                                            ],
                                            'invokables' => [
                                                Invokable\LoiLabel::class,
                                            ],
                                        ],
                                    ],
                                    'payment-sheet'       => [
                                        'label'   => _('txt-affiliation-payment-sheet'),
                                        'route'   => 'community/affiliation/payment-sheet',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Affiliation::class,
                                            ],
                                        ],
                                    ],
                                    'update-effort-spent' => [
                                        'label'   => _('txt-update-effort-spent'),
                                        'route'   => 'community/affiliation/edit/update-effort-spent',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Affiliation::class,
                                            ],
                                        ],
                                    ],
                                    'view-questionnaire'  => [
                                        'label'   => _('txt-view-questionnaire'),
                                        'route'   => 'community/affiliation/questionnaire/view',
                                        'visible' => false,
                                        'params'  => [
                                            'entities'   => [
                                                'id' => Affiliation::class,
                                            ],
                                            'routeParam' => [
                                                'id' => 'affiliationId',
                                            ],
                                        ],
                                    ],
                                    'edit-questionnaire'  => [
                                        'label'   => _('txt-edit-questionnaire'),
                                        'route'   => 'community/affiliation/questionnaire/edit',
                                        'visible' => false,
                                        'params'  => [
                                            'entities'   => [
                                                'id' => Affiliation::class,
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
                'route' => 'community/affiliation/questionnaire/overview',
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
        'admin'      => [
            'project' => [
                'pages' => [
                    'project-list' => [
                        'pages' => [
                            'project-view' => [
                                'pages' => [
                                    'affiliations' => [
                                        'pages' => [
                                            'affiliation' => [
                                                'label'   => _('txt-nav-project-partner'),
                                                'route'   => 'zfcadmin/affiliation/view',
                                                'visible' => false,
                                                'params'  => [
                                                    'entities'   => [
                                                        'id' => Affiliation::class,
                                                    ],
                                                    'invokables' => [
                                                        Invokable\AffiliationLabel::class,
                                                    ],
                                                ],
                                                'pages'   => [
                                                    'edit'           => [
                                                        'label'   => _('txt-nav-edit'),
                                                        'route'   => 'zfcadmin/affiliation/edit',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities' => [
                                                                'id' => Affiliation::class,
                                                            ],
                                                        ],
                                                    ],
                                                    'view-doa'       => [
                                                        'label'   => _('txt-nav-view-doa'),
                                                        'route'   => 'zfcadmin/affiliation/doa/view',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities'   => [
                                                                'id' => Doa::class,
                                                            ],
                                                            'invokables' => [
                                                                Invokable\DoaLabel::class,
                                                            ],
                                                        ],
                                                        'pages'   => [
                                                            'edit-doa' => [
                                                                'label'   => _('txt-nav-edit-doa'),
                                                                'route'   => 'zfcadmin/affiliation/doa/edit',
                                                                'visible' => false,
                                                                'params'  => [
                                                                    'entities'   => [
                                                                        'id' => Doa::class,
                                                                    ],
                                                                    'invokables' => [
                                                                        Invokable\DoaLabel::class,
                                                                    ],
                                                                ],
                                                            ],
                                                        ]
                                                    ],
                                                    'view-loi'       => [
                                                        'label'   => _('txt-nav-view-loi'),
                                                        'route'   => 'zfcadmin/affiliation/loi/view',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities'   => [
                                                                'id' => Loi::class,
                                                            ],
                                                            'invokables' => [
                                                                Invokable\LoiLabel::class,
                                                            ],
                                                        ],
                                                        'pages'   => [
                                                            'edit-loi' => [
                                                                'label'   => _('txt-nav-edit-loi'),
                                                                'route'   => 'zfcadmin/affiliation/loi/edit',
                                                                'visible' => false,
                                                                'params'  => [
                                                                    'entities'   => [
                                                                        'id' => Loi::class,
                                                                    ],
                                                                    'invokables' => [
                                                                        Invokable\LoiLabel::class,
                                                                    ],
                                                                ],
                                                            ],
                                                        ]
                                                    ],
                                                    'edit-associate' => [
                                                        'label'   => _('txt-nav-edit-associate'),
                                                        'route'   => 'zfcadmin/affiliation/edit-associate',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities' => [
                                                                'id' => Affiliation::class,
                                                            ],
                                                        ],
                                                    ],
                                                    'add-associate'  => [
                                                        'label'  => _('txt-nav-add-associate'),
                                                        'route'  => 'zfcadmin/affiliation/add-associate',
                                                        'params' => [
                                                            'entities' => [
                                                                'id' => Affiliation::class,
                                                            ],
                                                        ],
                                                    ],
                                                    'merge'          => [
                                                        'label'   => _('txt-merge-with-other'),
                                                        'route'   => 'zfcadmin/affiliation/merge',
                                                        'visible' => false,
                                                        'params'  => [
                                                            'entities' => [
                                                                'id' => Affiliation::class,
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
                        'route' => 'zfcadmin/affiliation/missing-affiliation-parent',
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
                                        Invokable\DoaLabel::class,
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
                                        'id' => Affiliation::class,
                                    ],
                                    'routeParam' => [
                                        'id' => 'affiliationId'
                                    ],
                                    'invokables' => [
                                        Invokable\AffiliationLabel::class,
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
                                        'id' => Affiliation::class,
                                    ],
                                    'routeParam' => [
                                        'id' => 'affiliationId'
                                    ],
                                    'invokables' => [
                                        Invokable\AffiliationLabel::class,
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
                        'route' => 'zfcadmin/affiliation/questionnaire/list',
                        'pages' => [
                            'view-questionnaire' => [
                                'route'   => 'zfcadmin/affiliation/questionnaire/view',
                                'visible' => false,
                                'params'  => [
                                    'entities'   => [
                                        'id' => Questionnaire\Questionnaire::class,
                                    ],
                                    'invokables' => [
                                        Invokable\Questionnaire\QuestionnaireLabel::class,
                                    ],
                                ],
                                'pages'   => [
                                    'edit-questionnaire' => [
                                        'label'   => _('txt-edit'),
                                        'route'   => 'zfcadmin/affiliation/questionnaire/edit',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Questionnaire\Questionnaire::class,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'new-questionnaire'  => [
                                'label'   => _('txt-new-questionnaire'),
                                'route'   => 'zfcadmin/affiliation/questionnaire/new',
                                'visible' => false,
                            ],
                        ],
                    ],
                    'question-list'          => [
                        'label' => _('txt-nav-affiliation-question-list'),
                        'route' => 'zfcadmin/affiliation/questionnaire/question/list',
                        'pages' => [
                            'view-question' => [
                                'route'   => 'zfcadmin/affiliation/questionnaire/question/view',
                                'visible' => false,
                                'params'  => [
                                    'entities'   => [
                                        'id' => Questionnaire\Question::class,
                                    ],
                                    'invokables' => [
                                        Invokable\Questionnaire\QuestionLabel::class,
                                    ],
                                ],
                                'pages'   => [
                                    'edit-question' => [
                                        'label'   => _('txt-edit'),
                                        'route'   => 'zfcadmin/affiliation/questionnaire/question/edit',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Questionnaire\Question::class,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'new-question'  => [
                                'label'   => _('txt-new-question'),
                                'route'   => 'zfcadmin/affiliation/questionnaire/question/new',
                                'visible' => false,
                            ],
                        ],
                    ],
                    'question-category-list' => [
                        'label' => _('txt-nav-affiliation-question-category-list'),
                        'route' => 'zfcadmin/affiliation/questionnaire/category/list',
                        'pages' => [
                            'view-question-category' => [
                                'route'   => 'zfcadmin/affiliation/questionnaire/category/view',
                                'visible' => false,
                                'params'  => [
                                    'entities'   => [
                                        'id' => Questionnaire\Category::class,
                                    ],
                                    'invokables' => [
                                        Invokable\Questionnaire\CategoryLabel::class,
                                    ],
                                ],
                                'pages'   => [
                                    'edit-question-category' => [
                                        'label'   => _('txt-edit'),
                                        'route'   => 'zfcadmin/affiliation/questionnaire/category/edit',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Questionnaire\Category::class,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'new-question-category'  => [
                                'label'   => _('txt-new-question-category'),
                                'route'   => 'zfcadmin/affiliation/questionnaire/category/new',
                                'visible' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
