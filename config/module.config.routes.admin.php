<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */
namespace Affiliation;

use Affiliation\Controller;

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
                            'list'           => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/list[/f-:encodedFilter][/page-:page].html',
                                    'defaults' => [
                                        'format' => 'html',
                                        'action' => 'list',
                                    ]
                                ]
                            ],
                            'list-csv'       => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/list[/f-:encodedFilter].csv',
                                    'defaults' => [
                                        'format' => 'csv',
                                        'action' => 'list',
                                    ]
                                ]
                            ],
                            'view'           => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/view/[:id].html',
                                    'defaults' => [
                                        'action'    => 'view',
                                        'privilege' => 'view-admin',
                                    ]
                                ]
                            ],
                            'edit'           => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/edit/[:id].html',
                                    'defaults' => [
                                        'action'    => 'edit',
                                        'privilege' => 'edit-admin',
                                    ]
                                ]
                            ],
                            'merge'          => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/merge/[:id].html',
                                    'defaults' => [
                                        'action'    => 'merge',
                                        'privilege' => 'merge-admin',
                                    ]
                                ]
                            ],
                            'edit-associate' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/edit-associate/affiliation-[:affiliation]/contact-[:contact].html',
                                    'defaults' => [
                                        'action'    => 'edit-associate',
                                        'privilege' => 'edit-admin',
                                    ]
                                ]
                            ],
                            'loi'            => [
                                'type'          => 'Segment',
                                'priority'      => 1000,
                                'options'       => [
                                    'route'    => '/loi',
                                    'defaults' => [
                                        'namespace'  => __NAMESPACE__,
                                        'controller' => Controller\LoiManagerController::class,
                                        'action'     => 'index',
                                    ],
                                ],
                                'may_terminate' => false,
                                'child_routes'  => [
                                    'list'      => [
                                        'type'     => 'Segment',
                                        'priority' => 1000,
                                        'options'  => [
                                            'route'    => '/list.html',
                                            'defaults' => [
                                                'action'    => 'list',
                                                'privilege' => 'list-admin',
                                            ],
                                        ],
                                    ],
                                    'approval'  => [
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
                                    'missing'   => [
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
                                    'view'      => [
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
                                            ]
                                        ]
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
                                            ]
                                        ]
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
                                    'approve'   => [
                                        'type'     => 'Segment',
                                        'priority' => 1000,
                                        'options'  => [
                                            'route'    => '/approve.html',
                                            'defaults' => [
                                                'action'    => 'approve',
                                                'privilege' => 'edit-admin',
                                            ],
                                        ],
                                    ],
                                ]
                            ],
                            'doa'            => [
                                'type'          => 'Segment',
                                'priority'      => 1000,
                                'options'       => [
                                    'route'    => '/doa',
                                    'defaults' => [
                                        'namespace'  => __NAMESPACE__,
                                        'controller' => Controller\DoaManagerController::class,
                                        'action'     => 'index',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes'  => [
                                    'list'      => [
                                        'type'     => 'Segment',
                                        'priority' => 1000,
                                        'options'  => [
                                            'route'    => '/list.html',
                                            'defaults' => [
                                                'action'    => 'list',
                                                'privilege' => 'list-admin',
                                            ],
                                        ],
                                    ],
                                    'approval'  => [
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
                                    'missing'   => [
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
                                    'view'      => [
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
                                            ]
                                        ]
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
                                            ]
                                        ]
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
                                            ]
                                        ]
                                    ],
                                    'approve'   => [
                                        'type'     => 'Segment',
                                        'priority' => 1000,
                                        'options'  => [
                                            'route'    => '/approve.html',
                                            'defaults' => [
                                                'action'    => 'approve',
                                                'privilege' => 'edit-admin',
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                ]
            ]
        ]
    ]
];
