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
                    array(array('office'), 'affiliation', array(
                        'edit-community',
                    ), 'affiliation_acl_assertion_affiliation'),
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
                array('route' => 'community/affiliation/affiliation', 'roles' => array('office'), 'assertion' => 'affiliation_acl_assertion_affiliation'),
                array('route' => 'community/affiliation/edit', 'roles' => array('office'), 'assertion' => 'affiliation_acl_assertion_affiliation'),
                array('route' => 'community/affiliation/edit-financial', 'roles' => array('office'), 'assertion' => 'affiliation_acl_assertion_affiliation'),

                array('route' => 'community/affiliation/doa/upload', 'roles' => array('office'), 'assertion' => 'affiliation_acl_assertion_doa'),
                array('route' => 'community/affiliation/doa/render', 'roles' => array('office'), 'assertion' => 'affiliation_acl_assertion_doa'),
                array('route' => 'community/affiliation/doa/download', 'roles' => array('office'), 'assertion' => 'affiliation_acl_assertion_doa'),

                array('route' => 'community/affiliation/loi/upload', 'roles' => array('office'), 'assertion' => 'affiliation_acl_assertion_loi'),
                array('route' => 'community/affiliation/loi/render', 'roles' => array('office'), 'assertion' => 'affiliation_acl_assertion_loi'),
                array('route' => 'community/affiliation/loi/download', 'roles' => array('office'), 'assertion' => 'affiliation_acl_assertion_loi'),
            ),
        ),
    ),
);
