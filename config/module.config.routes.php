<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
return array(
    'router' => array(
        'routes' => array(
            'community' => array(
                'child_routes' => array(
                    'affiliation' => array(
                        'type'          => 'Segment',
                        'priority'      => 1000,
                        'options'       => array(
                            'route'    => '/affiliation',
                            'defaults' => array(
                                'namespace'  => 'affiliation',
                                'controller' => 'affiliation-community',
                                'action'     => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes'  => array(
                            'affiliation' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/details/[:id].html',
                                    'defaults' => array(
                                        'action'    => 'affiliation',
                                        'privilege' => 'view-community'
                                    ),
                                ),
                            ),
                            'edit'        => array(
                                'type'          => 'Segment',
                                'options'       => array(
                                    'route'    => '/edit',
                                    'defaults' => array(
                                        'controller' => 'affiliation-edit',
                                        'action'     => 'edit',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes'  => [
                                    'affiliation' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/affiliation/[:id].html',
                                            'defaults' => array(
                                                'action'    => 'affiliation',
                                                'privilege' => 'edit-affiliation'
                                            ),
                                        ),
                                    ),
                                    'financial'   => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/financial/[:id].html',
                                            'defaults' => array(
                                                'action'    => 'financial',
                                                'privilege' => 'edit-financial'
                                            ),
                                        ),
                                    ),
                                    'description' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/description/[:id].html',
                                            'defaults' => array(
                                                'action'    => 'description',
                                                'privilege' => 'edit-description'
                                            ),
                                        ),
                                    ),
                                ]
                            ),
                            'doa'         => array(
                                'type'         => 'Segment',
                                'options'      => array(
                                    'route'    => '/doa',
                                    'defaults' => array(
                                        'controller' => 'affiliation-doa',
                                        'action'     => 'index',
                                    ),
                                ),
                                'child_routes' => array(
                                    'render'   => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/render/affiliation-[:affiliation-id].pdf',
                                            'defaults' => array(
                                                'action'    => 'render',
                                                'privilege' => 'render'
                                            ),
                                        ),
                                    ),
                                    'upload'   => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/upload/affiliation-[:affiliation-id].html',
                                            'defaults' => array(
                                                'action'    => 'upload',
                                                'privilege' => 'upload'
                                            ),
                                        ),
                                    ),
                                    'replace'  => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/replace/[:id].html',
                                            'defaults' => array(
                                                'action'    => 'replace',
                                                'privilege' => 'replace'
                                            ),
                                        ),
                                    ),
                                    'download' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/download/[:id].pdf',
                                            'defaults' => array(
                                                'action'    => 'download',
                                                'privilege' => 'download'
                                            ),
                                        ),
                                    ),
                                )
                            ),
                            'loi'         => array(
                                'type'         => 'Segment',
                                'options'      => array(
                                    'route'    => '/loi',
                                    'defaults' => array(
                                        'controller' => 'affiliation-loi',
                                        'action'     => 'index',
                                    ),
                                ),
                                'child_routes' => array(
                                    'render'   => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/render/affiliation-[:affiliation-id].pdf',
                                            'defaults' => array(
                                                'action'    => 'render',
                                                'privilege' => 'render'
                                            ),
                                        ),
                                    ),
                                    'upload'   => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/upload/affiliation-[:affiliation-id].html',
                                            'defaults' => array(
                                                'action'    => 'upload',
                                                'privilege' => 'upload'
                                            ),
                                        ),
                                    ),
                                    'replace'  => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/replace/[:id].html',
                                            'defaults' => array(
                                                'action'    => 'replace',
                                                'privilege' => 'replace'
                                            ),
                                        ),
                                    ),
                                    'download' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/download/[:id].pdf',
                                            'defaults' => array(
                                                'action'    => 'download',
                                                'privilege' => 'download'
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        )
    )
);
