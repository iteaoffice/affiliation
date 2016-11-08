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
            'community' => [
                'child_routes' => [
                    'affiliation' => [
                        'type'          => 'Segment',
                        'priority'      => 1000,
                        'options'       => [
                            'route'    => '/affiliation',
                            'defaults' => [
                                'namespace'  => 'affiliation',
                                'controller' => Controller\CommunityController::class,
                                'action'     => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'affiliation'       => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/details/[:id].html',
                                    'defaults' => [
                                        'action'    => 'affiliation',
                                        'privilege' => 'view-community',
                                    ],
                                ],
                            ],
                            'payment-sheet'     => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/payment-sheet/[:id]/year-[:year]/period-[:period].html',
                                    'defaults' => [
                                        'action'    => 'payment-sheet',
                                        'privilege' => 'payment-sheet',
                                    ],
                                ],
                            ],
                            'payment-sheet-pdf' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/payment-sheet/[:id]/year-[:year]/period-[:period].pdf',
                                    'defaults' => [
                                        'action'    => 'payment-sheet-pdf',
                                        'privilege' => 'payment-sheet',
                                    ],
                                ],
                            ],
                            'edit'              => [
                                'type'          => 'Segment',
                                'options'       => [
                                    'route'    => '/edit',
                                    'defaults' => [
                                        'controller' => Controller\EditController::class,
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
                                                'privilege' => 'edit-affiliation',
                                            ],
                                        ],
                                    ],
                                    'add-associate'       => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/add-associate/[:id].html',
                                            'defaults' => [
                                                'action'    => 'add-associate',
                                                'privilege' => 'add-associate',
                                            ],
                                        ],
                                    ],
                                    'financial'           => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/financial/[:id].html',
                                            'defaults' => [
                                                'action'    => 'financial',
                                                'privilege' => 'edit-financial',
                                            ],
                                        ],
                                    ],
                                    'description'         => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/description/[:id].html',
                                            'defaults' => [
                                                'action'    => 'description',
                                                'privilege' => 'edit-description',
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
                                ],
                            ],
                            'doa'               => [
                                'type'         => 'Segment',
                                'options'      => [
                                    'route'    => '/doa',
                                    'defaults' => [
                                        'controller' => Controller\DoaController::class,
                                        'action'     => 'index',
                                    ],
                                ],
                                'child_routes' => [
                                    'render'   => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/render/affiliation-[:affiliationId].pdf',
                                            'defaults' => [
                                                'action'    => 'render',
                                                'privilege' => 'render',
                                            ],
                                        ],
                                    ],
                                    'upload'   => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/upload/affiliation-[:affiliationId].html',
                                            'defaults' => [
                                                'action'    => 'upload',
                                                'privilege' => 'upload',
                                            ],
                                        ],
                                    ],
                                    'replace'  => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/replace/[:id].html',
                                            'defaults' => [
                                                'action'    => 'replace',
                                                'privilege' => 'replace',
                                            ],
                                        ],
                                    ],
                                    'download' => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/download/[:id].[:ext]',
                                            'defaults' => [
                                                'action'    => 'download',
                                                'privilege' => 'download',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'loi'               => [
                                'type'         => 'Segment',
                                'options'      => [
                                    'route'    => '/loi',
                                    'defaults' => [
                                        'controller' => Controller\LoiController::class,
                                        'action'     => 'index',
                                    ],
                                ],
                                'child_routes' => [
                                    'render'   => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/render/affiliation-[:affiliationId].pdf',
                                            'defaults' => [
                                                'action'    => 'render',
                                                'privilege' => 'render',
                                            ],
                                        ],
                                    ],
                                    'upload'   => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/upload/affiliation-[:affiliationId].html',
                                            'defaults' => [
                                                'action'    => 'upload',
                                                'privilege' => 'upload',
                                            ],
                                        ],
                                    ],
                                    'replace'  => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/replace/[:id].html',
                                            'defaults' => [
                                                'action'    => 'replace',
                                                'privilege' => 'replace',
                                            ],
                                        ],
                                    ],
                                    'download' => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/download/[:id].[:ext]',
                                            'defaults' => [
                                                'action'    => 'download',
                                                'privilege' => 'download',
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
