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
                                        'action' => 'affiliation',
                                    ),
                                ),
                            ),
                            'edit'        => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/edit/[:id].html',
                                    'defaults' => array(
                                        'action' => 'edit',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        )
    )
);
