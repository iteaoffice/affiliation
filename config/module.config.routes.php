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
                                    'route'    => '/payment-sheet/[:id]/year-[:year]/period-[:period][/:contract].html',
                                    'defaults' => [
                                        'action'    => 'payment-sheet',
                                        'privilege' => 'payment-sheet',
                                    ],
                                ],
                            ],
                            'payment-sheet-pdf' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/payment-sheet/[:id]/year-[:year]/period-[:period][/:contract].pdf',
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
                                    'manage-associate'    => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/manage-associate/[:id].html',
                                            'defaults' => [
                                                'action'    => 'manage-associate',
                                                'privilege' => 'manage-associate',
                                            ],
                                        ],
                                    ],
                                    'cost-and-effort'     => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/cost-and-effort/[:id].html',
                                            'defaults' => [
                                                'action'    => 'cost-and-effort',
                                                'privilege' => 'edit-cost-and-effort',
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
                                    'submit'   => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/submit/affiliation-[:affiliationId].html',
                                            'defaults' => [
                                                'action'    => 'submit',
                                                'privilege' => 'submit',
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
                                            'route'    => '/download/[:id].html',
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
                                    'submit'   => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/submit/affiliation-[:affiliationId].html',
                                            'defaults' => [
                                                'action'    => 'submit',
                                                'privilege' => 'submit',
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
                                            'route'    => '/download/[:id].html',
                                            'defaults' => [
                                                'action'    => 'download',
                                                'privilege' => 'download',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'questionnaire'     => [
                                'type'         => 'Segment',
                                'options'      => [
                                    'route'    => '/questionnaire',
                                    'defaults' => [
                                        'controller' => Controller\Questionnaire\QuestionnaireController::class,
                                        'action'     => 'view',
                                    ],
                                ],
                                'may_terminate' => false,
                                'child_routes' => [
                                    'view'   => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/view[/:affiliationId]/q-[:id].html',
                                            'defaults' => [
                                                'action'    => 'view',
                                                'privilege' => 'view-community',
                                            ],
                                        ],
                                    ],
                                    'edit'  => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/edit[/:affiliationId]/q-[:id].html',
                                            'defaults' => [
                                                'action'    => 'edit',
                                                'privilege' => 'edit-community',
                                            ],
                                        ],
                                    ],
                                    'overview'  => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/overview.html',
                                            'defaults' => [
                                                'action'    => 'overview',
                                                'privilege' => 'overview',
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
