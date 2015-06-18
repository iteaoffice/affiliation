<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Affiliation;

use Affiliation\Controller\AffiliationController;
use Affiliation\Controller\AffiliationManagerController;
use Affiliation\Controller\DoaManagerController;
use Affiliation\Controller\LoiManagerController;

return [
    'router' => [
        'routes' => [
            'community' => [
                'child_routes' => [
                    'affiliation' => [
                        'type'          => 'Segment',
                        'priority'      => 1000,
                        'options'       => [
                            'route'    => '/affiliation',
                            'defaults' => [
                                'namespace'  => 'affiliation',
                                'controller' => 'affiliation-community',
                                'action'     => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'affiliation' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/details/[:id].html',
                                    'defaults' => [
                                        'action'    => 'affiliation',
                                        'privilege' => 'view-community'
                                    ],
                                ],
                            ],
                            'edit'        => [
                                'type'          => 'Segment',
                                'options'       => [
                                    'route'    => '/edit',
                                    'defaults' => [
                                        'controller' => 'affiliation-edit',
                                        'action'     => 'edit',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes'  => [
                                    'affiliation'         => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/affiliation/[:id].html',
                                            'defaults' => [
                                                'action'    => 'affiliation',
                                                'privilege' => 'edit-affiliation'
                                            ],
                                        ],
                                    ],
                                    'add-associate'       => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/add-associate/[:id].html',
                                            'defaults' => [
                                                'action'    => 'add-associate',
                                                'privilege' => 'add-associate'
                                            ],
                                        ],
                                    ],
                                    'financial'           => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/financial/[:id].html',
                                            'defaults' => [
                                                'action'    => 'financial',
                                                'privilege' => 'edit-financial'
                                            ],
                                        ],
                                    ],
                                    'description'         => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/description/[:id].html',
                                            'defaults' => [
                                                'action'    => 'description',
                                                'privilege' => 'edit-description'
                                            ],
                                        ],
                                    ],
                                    'update-effort-spent' => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/update-effort-spent/affiliation-[:id]/report-[:report].html',
                                            'defaults' => [
                                                'action'    => 'update-effort-spent',
                                                'privilege' => 'update-effort-spent',
                                            ],
                                        ],
                                    ],
                                ]
                            ],
                            'doa'         => [
                                'type'         => 'Segment',
                                'options'      => [
                                    'route'    => '/doa',
                                    'defaults' => [
                                        'controller' => 'affiliation-doa',
                                        'action'     => 'index',
                                    ],
                                ],
                                'child_routes' => [
                                    'render'   => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/render/affiliation-[:affiliation-id].pdf',
                                            'defaults' => [
                                                'action'    => 'render',
                                                'privilege' => 'render'
                                            ],
                                        ],
                                    ],
                                    'upload'   => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/upload/affiliation-[:affiliation-id].html',
                                            'defaults' => [
                                                'action'    => 'upload',
                                                'privilege' => 'upload'
                                            ],
                                        ],
                                    ],
                                    'replace'  => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/replace/[:id].html',
                                            'defaults' => [
                                                'action'    => 'replace',
                                                'privilege' => 'replace'
                                            ],
                                        ],
                                    ],
                                    'download' => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/download/[:id].[:ext]',
                                            'defaults' => [
                                                'action'    => 'download',
                                                'privilege' => 'download'
                                            ],
                                        ],
                                    ],
                                ]
                            ],
                            'loi'         => [
                                'type'         => 'Segment',
                                'options'      => [
                                    'route'    => '/loi',
                                    'defaults' => [
                                        'controller' => 'affiliation-loi',
                                        'action'     => 'index',
                                    ],
                                ],
                                'child_routes' => [
                                    'render'   => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/render/affiliation-[:affiliation-id].pdf',
                                            'defaults' => [
                                                'action'    => 'render',
                                                'privilege' => 'render'
                                            ],
                                        ],
                                    ],
                                    'upload'   => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/upload/affiliation-[:affiliation-id].html',
                                            'defaults' => [
                                                'action'    => 'upload',
                                                'privilege' => 'upload'
                                            ],
                                        ],
                                    ],
                                    'replace'  => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/replace/[:id].html',
                                            'defaults' => [
                                                'action'    => 'replace',
                                                'privilege' => 'replace'
                                            ],
                                        ],
                                    ],
                                    'download' => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/download/[:id].[:ext]',
                                            'defaults' => [
                                                'action'    => 'download',
                                                'privilege' => 'download'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'zfcadmin'  => [
                'child_routes' => [
                    'affiliation'         => [
                        'type'          => 'Segment',
                        'priority'      => 1000,
                        'options'       => [
                            'route'    => '/affiliation',
                            'defaults' => [
                                'namespace'  => __NAMESPACE__,
                                'controller' => AffiliationController::class,
                                'action'     => 'index',
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes'  => [
                            'affiliation' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/[:id].html',
                                    'defaults' => [
                                        'action' => 'affiliation',
                                    ]
                                ]
                            ],
                            'list'        => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/list[/f-:encodedFilter][/page-:page].html',
                                    'defaults' => [
                                        'action' => 'list',
                                    ]
                                ]
                            ],
                        ]
                    ],
                    'affiliation-manager' => [
                        'type'          => 'Segment',
                        'priority'      => 1000,
                        'options'       => [
                            'route'    => '/affiliation',
                            'defaults' => [
                                'namespace'  => __NAMESPACE__,
                                'controller' => AffiliationManagerController::class,
                                'action'     => 'index',
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes'  => [
                            'affiliation' => [
                                'type'          => 'Segment',
                                'priority'      => 1000,
                                'options'       => [
                                    'route'    => '/',
                                    'defaults' => [
                                        'namespace'  => __NAMESPACE__,
                                        'controller' => AffiliationManagerController::class,
                                        'action'     => 'index',
                                    ],
                                ],
                                'may_terminate' => false,
                                'child_routes'  => [
                                    'view'              => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => 'view/[:id].html',
                                            'defaults' => [
                                                'action'    => 'view',
                                                'privilege' => 'view-admin',
                                            ]
                                        ]
                                    ],
                                    'payment-sheet'     => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => 'payment-sheet/[:id]/year-[:year]/period-[:period].html',
                                            'defaults' => [
                                                'action'    => 'payment-sheet',
                                                'privilege' => 'payment-sheet-admin',
                                            ]
                                        ]
                                    ],
                                    'payment-sheet-pdf' => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => 'payment-sheet/[:id]/year-[:year]/period-[:period].pdf',
                                            'defaults' => [
                                                'action'    => 'payment-sheet-pdf',
                                                'privilege' => 'payment-sheet-admin',
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            'loi'         => [
                                'type'          => 'Segment',
                                'priority'      => 1000,
                                'options'       => [
                                    'route'    => '/loi',
                                    'defaults' => [
                                        'namespace'  => __NAMESPACE__,
                                        'controller' => LoiManagerController::class,
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
                                            'route'    => '/remind/[:affiliation-id].html',
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
                                            'route'    => '/reminders/[:affiliation-id].html',
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
                            'doa'         => [
                                'type'          => 'Segment',
                                'priority'      => 1000,
                                'options'       => [
                                    'route'    => '/doa',
                                    'defaults' => [
                                        'namespace'  => __NAMESPACE__,
                                        'controller' => DoaManagerController::class,
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
                                            'route'    => '/remind/[:affiliation-id].html',
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
                                            'route'    => '/reminders/[:affiliation-id].html',
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
                    ]
                ]
            ]
        ]
    ]
];
