<?php

/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
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
                    'affiliation' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'    => '/affiliation',
                            'defaults' => [
                                'controller' => Controller\AffiliationManagerController::class,
                                'action'     => 'index',
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes'  => [
                            'view'                       => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/view/[:id].html',
                                    'defaults' => [
                                        'action'    => 'view',
                                        'privilege' => 'view-admin',
                                    ],
                                ],
                            ],
                            'edit'                       => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/edit/[:id].html',
                                    'defaults' => [
                                        'action'    => 'edit',
                                        'privilege' => 'edit-admin',
                                    ],
                                ],
                            ],
                            'merge'                      => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/merge/[:id].html',
                                    'defaults' => [
                                        'action'    => 'merge',
                                        'privilege' => 'merge-admin',
                                    ],
                                ],
                            ],
                            'missing-affiliation-parent' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/missing-affiliation-parent[/f-:encodedFilter][/page-:page].html',
                                    'defaults' => [
                                        'action' => 'missing-affiliation-parent',
                                    ],
                                ],
                            ],
                            'edit-associate'             => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/edit-associate/affiliation-[:id]/contact-[:contact].html',
                                    'defaults' => [
                                        'action'    => 'edit-associate',
                                        'privilege' => 'edit-admin',
                                    ],
                                ],
                            ],
                            'add-associate'              => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/add-associate/affiliation-[:id].html',
                                    'defaults' => [
                                        'action'    => 'add-associate',
                                        'privilege' => 'edit-admin',
                                    ],
                                ],
                            ],
                            'edit-description'           => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/edit-description/[:id].html',
                                    'defaults' => [
                                        'action' => 'edit-description',
                                    ],
                                ],
                            ],
                            'edit-market-access'         => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/edit-market-access/[:id].html',
                                    'defaults' => [
                                        'action' => 'edit-market-access',
                                    ],
                                ],
                            ],
                            'loi'                        => [
                                'type'          => 'Segment',
                                'priority'      => 1000,
                                'options'       => [
                                    'route'    => '/loi',
                                    'defaults' => [
                                        'controller' => Controller\LoiManagerController::class,
                                        'action'     => 'index',
                                    ],
                                ],
                                'may_terminate' => false,
                                'child_routes'  => [
                                    'approval' => [
                                        'type'     => 'Segment',
                                        'priority' => 1000,
                                        'options'  => [
                                            'route'    => '/approval.html',
                                            'defaults' => [
                                                'action'    => 'approval',
                                                'privilege' => 'approval-admin',
                                            ],
                                        ],
                                    ],
                                    'missing'  => [
                                        'type'     => 'Segment',
                                        'priority' => 1000,
                                        'options'  => [
                                            'route'    => '/missing[/page-:page].html',
                                            'defaults' => [
                                                'action'    => 'missing',
                                                'privilege' => 'missing-admin',
                                            ],
                                        ],
                                    ],
                                    'view'     => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'priority'    => 100,
                                            'route'       => '/[:id].html',
                                            'constraints' => [
                                                'id' => '[0-9_-]+',
                                            ],
                                            'defaults'    => [
                                                'action'    => 'view',
                                                'privilege' => 'view-admin',
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
                                                'privilege'   => 'edit-admin',
                                            ],
                                        ],
                                    ],
                                    'approve'  => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/approve.html',
                                            'defaults' => [
                                                'action'    => 'approve',
                                                'privilege' => 'edit-admin',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'doa'                        => [
                                'type'          => 'Segment',
                                'options'       => [
                                    'route'    => '/doa',
                                    'defaults' => [
                                        'controller' => Controller\DoaManagerController::class,
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
                                                'action'    => 'approval',
                                                'privilege' => 'approval-admin',
                                            ],
                                        ],
                                    ],
                                    'missing'   => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/missing[/page-:page].html',
                                            'defaults' => [
                                                'action'    => 'missing',
                                                'privilege' => 'missing-admin',
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
                                                'action'    => 'view',
                                                'privilege' => 'view-admin',
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
                                                'privilege'   => 'edit-admin',
                                            ],
                                        ],
                                    ],
                                    'remind'    => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/remind/[:affiliationId].html',
                                            'defaults' => [
                                                'constraints' => [
                                                    'id' => '[0-9_-]+',
                                                ],
                                                'action'      => 'remind',
                                                'privilege'   => 'remind-admin',
                                            ],
                                        ],
                                    ],
                                    'reminders' => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/reminders/[:affiliationId].html',
                                            'defaults' => [
                                                'constraints' => [
                                                    'id' => '[0-9_-]+',
                                                ],
                                                'action'      => 'reminders',
                                                'privilege'   => 'reminders-admin',
                                            ],
                                        ],
                                    ],
                                    'approve'   => [
                                        'type'     => 'Segment',
                                        'priority' => 1000,
                                        'options'  => [
                                            'route'    => '/finalise.html',
                                            'defaults' => [
                                                'action'    => 'approve',
                                                'privilege' => 'edit-admin',
                                            ],
                                        ],
                                    ],
                                    'decline'   => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/decline.json',
                                            'defaults' => [
                                                'action'    => 'decline',
                                                'privilege' => 'edit-admin',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'questionnaire'              => [
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
        ],
    ],
];
