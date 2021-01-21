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
            'community' => [
                'child_routes' => [
                    'affiliation'   => [
                        'type'          => 'Segment',
                        'priority'      => 1000,
                        'options'       => [
                            'route'    => '/affiliation',
                            'defaults' => [
                                'namespace'  => 'affiliation',
                                'controller' => Controller\AffiliationController::class,
                                'action'     => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'affiliation'      => [ //Keep a legacy affiliation link
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/[:id]/details.html',
                                    'defaults' => [
                                        'action' => 'details',
                                    ],
                                ],
                            ],
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
                                    'route'    => '/[:id]/payment-sheet/year-[:year]/period-[:period][/:contract].html',
                                    'defaults' => [
                                        'action' => 'payment-sheet',
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
                                    'affiliation'       => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/[:id]/affiliation.html',
                                            'defaults' => [
                                                'action'    => 'affiliation',
                                                'privilege' => 'edit',
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
                                            'route'    => '/[:id]/costs-and-effort.html',
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
                        ],
                    ],
                    'questionnaire' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'    => '/questionnaire',
                            'defaults' => [
                                'controller' => Controller\Questionnaire\QuestionnaireController::class,
                                'action'     => 'view',
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes'  => [
                            'view'     => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/view[/:affiliationId]/q-[:id].html',
                                    'defaults' => [
                                        'action'    => 'view',
                                        'privilege' => 'view-community',
                                    ],
                                ],
                            ],
                            'edit'     => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/edit[/:affiliationId]/q-[:id].html',
                                    'defaults' => [
                                        'action'    => 'edit',
                                        'privilege' => 'edit-community',
                                    ],
                                ],
                            ],
                            'overview' => [
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
];
