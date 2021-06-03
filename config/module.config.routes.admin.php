<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation;

use Affiliation\Controller;
use Laminas\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'zfcadmin' => [
                'child_routes' => [
                    'affiliation'   => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'    => '/affiliation',
                            'defaults' => [
                                'controller' => Controller\Admin\AffiliationController::class,
                                'action'     => 'index',
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes'  => [
                            'details'          => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/[:id]/details.html',
                                    'defaults' => [
                                        'action' => 'details',
                                    ],
                                ],
                            ],
                            'description'      => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/[:id]/description.html',
                                    'defaults' => [
                                        'action' => 'description',
                                    ],
                                ],
                            ],
                            'market-access'    => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/[:id]/market-access.html',
                                    'defaults' => [
                                        'action' => 'market-access',
                                    ],
                                ],
                            ],
                            'costs-and-effort' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/[:id]/costs-and-effort.html',
                                    'defaults' => [
                                        'action' => 'costs-and-effort',
                                    ],
                                ],
                            ],
                            'project-versions' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/[:id]/project-versions.html',
                                    'defaults' => [
                                        'action' => 'project-versions',
                                    ],
                                ],
                            ],

                            'financial'     => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/[:id]/financial.html',
                                    'defaults' => [
                                        'action' => 'financial',
                                    ],
                                ],
                            ],
                            'contract'      => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/[:id]/contract.html',
                                    'defaults' => [
                                        'action' => 'contract',
                                    ],
                                ],
                            ],
                            'parent'        => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/[:id]/parent.html',
                                    'defaults' => [
                                        'action' => 'parent',
                                    ],
                                ],
                            ],
                            'payment-sheet' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/[:id]/payment-sheet.html',
                                    'defaults' => [
                                        'action'    => 'payment-sheet',
                                        'privilege' => 'payment-sheet',
                                    ],
                                ],
                            ],

                            'contacts'       => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/[:id]/contacts.html',
                                    'defaults' => [
                                        'action' => 'contacts',
                                    ],
                                ],
                            ],
                            'reporting'      => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/[:id]/reporting.html',
                                    'defaults' => [
                                        'action' => 'reporting',
                                    ],
                                ],
                            ],
                            'achievements'   => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/[:id]/achievements.html',
                                    'defaults' => [
                                        'action' => 'achievements',
                                    ],
                                ],
                            ],
                            'questionnaires' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/[:id]/questionnaires.html',
                                    'defaults' => [
                                        'action' => 'questionnaires',
                                    ],
                                ],
                            ],
                            'merge'          => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/merge/[:id].html',
                                    'defaults' => [
                                        'action' => 'merge',
                                    ],
                                ],
                            ],
                            'missing-parent' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/missing-parent[/f-:encodedFilter][/page-:page].html',
                                    'defaults' => [
                                        'controller' => Controller\Admin\IndexController::class,
                                        'action'     => 'missing-affiliation-parent',
                                    ],
                                ],
                            ],
                            'edit'           => [
                                'type'         => 'Literal',
                                'options'      => [
                                    'route'    => '/edit',
                                    'defaults' => [
                                        'controller' => Controller\Admin\EditController::class,
                                        'action'     => 'edit',
                                    ],
                                ],
                                'child_routes' => [
                                    'affiliation'       => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/[:id]/affiliation.html',
                                            'defaults' => [
                                                'action' => 'affiliation',
                                            ],
                                        ],
                                    ],
                                    'associate'         => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/associate/affiliation-[:id]/contact-[:contact].html',
                                            'defaults' => [
                                                'action' => 'associate',
                                            ],
                                        ],
                                    ],
                                    'technical-contact' => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/[:id]/technical-contact.html',
                                            'defaults' => [
                                                'action' => 'technical-contact',
                                            ],
                                        ],
                                    ],
                                    'manage-associates' => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/[:id]/manage/associates.html',
                                            'defaults' => [
                                                'action' => 'manage-associates',
                                            ],
                                        ],
                                    ],
                                    'add-associate'     => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/[:id]/associate/add.html',
                                            'defaults' => [
                                                'action' => 'add-associate',
                                            ],
                                        ],
                                    ],
                                    'costs-and-effort'  => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '[:id]/costs-and-effort.html',
                                            'defaults' => [
                                                'action' => 'costs-and-effort',
                                            ],
                                        ],
                                    ],
                                    'financial'         => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/[:id]/financial.html',
                                            'defaults' => [
                                                'action' => 'financial',
                                            ],
                                        ],
                                    ],
                                    'description'       => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/[:id]/description.html',
                                            'defaults' => [
                                                'action' => 'description',
                                            ],
                                        ],
                                    ],
                                    'market-access'     => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/[:id]/market-access.html',
                                            'defaults' => [
                                                'action' => 'market-access',
                                            ],
                                        ],
                                    ],
                                    'effort-spent'      => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/[:id]/effort/spent/report-[:report].html',
                                            'defaults' => [
                                                'action' => 'effort-spent',
                                            ],
                                        ],
                                    ],
                                ]
                            ],
                            'loi'            => [
                                'type'          => 'Segment',
                                'options'       => [
                                    'route'    => '/loi',
                                    'defaults' => [
                                        'controller' => Controller\Loi\ManagerController::class,
                                        'action'     => 'index',
                                    ],
                                ],
                                'may_terminate' => false,
                                'child_routes'  => [
                                    'approval' => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/approval.html',
                                            'defaults' => [
                                                'action' => 'approval',
                                            ],
                                        ],
                                    ],
                                    'missing'  => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/missing[/page-:page].html',
                                            'defaults' => [
                                                'action' => 'missing',
                                            ],
                                        ],
                                    ],
                                    'view'     => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'       => '/[:id].html',
                                            'constraints' => [
                                                'id' => '[0-9_-]+',
                                            ],
                                            'defaults'    => [
                                                'action' => 'view',
                                            ],
                                        ],
                                    ],
                                    'edit'     => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/edit/[:id].html',
                                            'defaults' => [
                                                'constraints' => [
                                                    'id' => '[0-9_-]+',
                                                ],
                                                'action'      => 'edit',
                                            ],
                                        ],
                                    ],
                                    'approve'  => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/approve.html',
                                            'defaults' => [
                                                'action' => 'approve',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'doa'            => [
                                'type'          => 'Segment',
                                'options'       => [
                                    'route'    => '/doa',
                                    'defaults' => [
                                        'controller' => Controller\Doa\ManagerController::class,
                                        'action'     => 'index',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes'  => [
                                    'approval'  => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/approval.html',
                                            'defaults' => [
                                                'action' => 'approval',
                                            ],
                                        ],
                                    ],
                                    'missing'   => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/missing[/page-:page].html',
                                            'defaults' => [
                                                'action' => 'missing',
                                            ],
                                        ],
                                    ],
                                    'view'      => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'       => '/[:id].html',
                                            'constraints' => [
                                                'id' => '[0-9_-]+',
                                            ],
                                            'defaults'    => [
                                                'action' => 'view',
                                            ],
                                        ],
                                    ],
                                    'edit'      => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/edit/[:id].html',
                                            'defaults' => [
                                                'constraints' => [
                                                    'id' => '[0-9_-]+',
                                                ],
                                                'action'      => 'edit',
                                            ],
                                        ],
                                    ],
                                    'remind'    => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/remind/[:affiliationId].html',
                                            'defaults' => [
                                                'constraints' => [
                                                    'affiliationId' => '[0-9_-]+',
                                                ],
                                                'action'      => 'remind',
                                            ],
                                        ],
                                    ],
                                    'reminders' => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/reminders/[:affiliationId].html',
                                            'defaults' => [
                                                'constraints' => [
                                                    'affiliationId' => '[0-9_-]+',
                                                ],
                                                'action'      => 'reminders',
                                            ],
                                        ],
                                    ],
                                    'approve'   => [
                                        'type'     => 'Segment',
                                        'priority' => 1000,
                                        'options'  => [
                                            'route'    => '/approve.html',
                                            'defaults' => [
                                                'action' => 'approve',
                                            ],
                                        ],
                                    ],
                                    'decline'   => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/decline.json',
                                            'defaults' => [
                                                'action' => 'decline',
                                            ],
                                        ],
                                    ],
                                ],
                            ],

                        ],
                    ],
                    'questionnaire' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/questionnaire',
                            'defaults' => [
                                'controller' => Controller\Questionnaire\QuestionnaireManagerController::class,
                                'action'     => 'list',
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes'  => [
                            'list'     => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'    => '/list[/f-:encodedFilter][/page-:page].html',
                                    'defaults' => [
                                        'action' => 'list',
                                    ],
                                ],
                            ],
                            'view'     => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'    => '/view/[:id].html',
                                    'defaults' => [
                                        'action' => 'view',

                                    ],
                                ],
                            ],
                            'edit'     => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'    => '/edit/[:id].html',
                                    'defaults' => [
                                        'action' => 'edit',
                                    ],
                                ],
                            ],
                            'copy'     => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'    => '/copy/[:id].html',
                                    'defaults' => [
                                        'action' => 'copy',
                                    ],
                                ],
                            ],
                            'new'      => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'    => '/new.html',
                                    'defaults' => [
                                        'action' => 'new',
                                    ],
                                ],
                            ],
                            'question' => [
                                'type'          => Segment::class,
                                'options'       => [
                                    'route'    => '/question',
                                    'defaults' => [
                                        'action'     => 'list',
                                        'controller' => Controller\Questionnaire\QuestionManagerController::class,
                                    ],
                                ],
                                'may_terminate' => false,
                                'child_routes'  => [
                                    'list' => [
                                        'type'     => Segment::class,
                                        'priority' => 1000,
                                        'options'  => [
                                            'route'    => '/list[/f-:encodedFilter][/page-:page].html',
                                            'defaults' => [
                                                'action' => 'list',
                                            ],
                                        ],
                                    ],
                                    'view' => [
                                        'type'    => Segment::class,
                                        'options' => [
                                            'route'    => '/view/[:id].html',
                                            'defaults' => [
                                                'action' => 'view',

                                            ],
                                        ],
                                    ],
                                    'edit' => [
                                        'type'    => Segment::class,
                                        'options' => [
                                            'route'    => '/edit/[:id].html',
                                            'defaults' => [
                                                'action' => 'edit',
                                            ],
                                        ],
                                    ],
                                    'new'  => [
                                        'type'    => Segment::class,
                                        'options' => [
                                            'route'    => '/new.html',
                                            'defaults' => [
                                                'action' => 'new',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'category' => [
                                'type'          => Segment::class,
                                'options'       => [
                                    'route'    => '/category',
                                    'defaults' => [
                                        'action'     => 'list',
                                        'controller' => Controller\Questionnaire\CategoryManagerController::class,
                                    ],
                                ],
                                'may_terminate' => false,
                                'child_routes'  => [
                                    'list' => [
                                        'type'     => Segment::class,
                                        'priority' => 1000,
                                        'options'  => [
                                            'route'    => '/list[/f-:encodedFilter][/page-:page].html',
                                            'defaults' => [
                                                'action' => 'list',
                                            ],
                                        ],
                                    ],
                                    'view' => [
                                        'type'    => Segment::class,
                                        'options' => [
                                            'route'    => '/view/[:id].html',
                                            'defaults' => [
                                                'action' => 'view',

                                            ],
                                        ],
                                    ],
                                    'edit' => [
                                        'type'    => Segment::class,
                                        'options' => [
                                            'route'    => '/edit/[:id].html',
                                            'defaults' => [
                                                'action' => 'edit',
                                            ],
                                        ],
                                    ],
                                    'new'  => [
                                        'type'    => Segment::class,
                                        'options' => [
                                            'route'    => '/new.html',
                                            'defaults' => [
                                                'action' => 'new',
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
