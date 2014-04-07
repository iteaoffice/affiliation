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
    'bjyauthorize' => array(
        // resource providers provide a list of resources that will be tracked
        // in the ACL. like roles, they can be hierarchical
        'resource_providers' => array(
            'BjyAuthorize\Provider\Resource\Config' => array(
                'affiliation' => array(),
            ),
        ),
        /* rules can be specified here with the format:
         * array(roles (array) , resource, [privilege (array|string), assertion])
         * assertions will be loaded using the service manager and must implement
         * Zend\Acl\Assertion\AssertionInterface.
         * *if you use assertions, define them using the service manager!*
         */
        'rule_providers'     => array(
            'BjyAuthorize\Provider\Rule\Config' => array(
                'allow' => array(
                    // allow guests and users (and admins, through inheritance)
                    // the "wear" privilege on the resource "pants"d
                    array(array('public'), 'affiliation', array('listings', 'view')),
                    array(array(), 'affiliation', array('edit', 'new', 'delete'))
                ),
                // Don't mix allow/deny rules if you are using role inheritance.
                // There are some weird bugs.
                'deny'  => array( // ...
                ),
            ),
        ),
        /* Currently, only controller and route guards exist
         */
        'guards'             => array(
            /* If this guard is specified here (i.e. it is enabled), it will block
             * access to all routes unless they are specified here.
             */
            'BjyAuthorize\Guard\Route' => array(
                array('route' => 'community/affiliation/affiliation', 'roles' => array('office')),
                array('route' => 'community/affiliation/upload-program-doa', 'roles' => array('office')),
                array('route' => 'community/affiliation/render-program-doa', 'roles' => array('office')),
                array('route' => 'community/affiliation/doa/upload', 'roles' => array('office')),
                array('route' => 'community/affiliation/doa/render', 'roles' => array('office')),
                array('route' => 'community/affiliation/doa/download', 'roles' => array('office')),
                array('route' => 'community/affiliation/loi/upload', 'roles' => array('office')),
                array('route' => 'community/affiliation/loi/render', 'roles' => array('office')),
                array('route' => 'community/affiliation/loi/download', 'roles' => array('office')),
                array('route' => 'community/affiliation/edit', 'roles' => array('office')),
            ),
        ),
    ),
);
