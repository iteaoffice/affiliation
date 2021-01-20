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

return [
    'router' => [
        'routes' => [
            'json' => [
                'child_routes' => [
                    'affiliation' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route' => '/affiliation',
                        ],
                        'may_terminate' => false,
                        'child_routes'  => [
                            'loi' => [
                                'type'          => 'Segment',
                                'options'       => [
                                    'route'    => '/loi',
                                    'defaults' => [
                                        'controller' => Controller\Json\LoiController::class,
                                        'action'     => 'index',
                                    ],
                                ],
                                'may_terminate' => false,
                                'child_routes'  => [
                                    'approve' => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/approve.json',
                                            'defaults' => [
                                                'action' => 'approve',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'doa' => [
                                'type'          => 'Segment',
                                'options'       => [
                                    'route'    => '/doa',
                                    'defaults' => [
                                        'controller' => Controller\Json\DoaController::class,
                                        'action'     => 'index',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes'  => [

                                    'approve' => [
                                        'type'     => 'Segment',
                                        'priority' => 1000,
                                        'options'  => [
                                            'route'    => '/approve.json',
                                            'defaults' => [
                                                'action' => 'approve',
                                            ],
                                        ],
                                    ],
                                    'decline' => [
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
                ],
            ],
        ],
    ],
];
