<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
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
                                    'affiliation'   => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/affiliation/[:id].html',
                                            'defaults' => [
                                                'action'    => 'affiliation',
                                                'privilege' => 'edit-affiliation'
                                            ],
                                        ],
                                    ],
                                    'add-associate' => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/add-associate/[:id].html',
                                            'defaults' => [
                                                'action'    => 'add-associate',
                                                'privilege' => 'add-associate'
                                            ],
                                        ],
                                    ],
                                    'financial'     => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/financial/[:id].html',
                                            'defaults' => [
                                                'action'    => 'financial',
                                                'privilege' => 'edit-financial'
                                            ],
                                        ],
                                    ],
                                    'description'   => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/description/[:id].html',
                                            'defaults' => [
                                                'action'    => 'description',
                                                'privilege' => 'edit-description'
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
                                            'route'    => '/download/[:id].pdf',
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
                                            'route'    => '/download/[:id].pdf',
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
            ]
        ]
    ]
];
