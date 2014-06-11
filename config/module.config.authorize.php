<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c] 2004-2014 ITEA Office (http://itea3.org]
 */
return [
    'bjyauthorize' => [
        // resource providers provide a list of resources that will be tracked
        // in the ACL. like roles, they can be hierarchical
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'affiliation' => [],
            ],
        ],
        /* rules can be specified here with the format:
         * [roles (array] , resource, [privilege (array|string], assertion]]
         * assertions will be loaded using the service manager and must implement
         * Zend\Acl\Assertion\AssertionInterface.
         * *if you use assertions, define them using the service manager!*
         */
        'rule_providers'     => [
            'BjyAuthorize\Provider\Rule\Config' => [
                'allow' => [
                    [
                        ['office'],
                        'affiliation',
                        [
                            'edit-community',
                        ],
                        'affiliation_acl_assertion_affiliation'
                    ],
                ],
            ],
        ],
        /* Currently, only controller and route guards exist
         */
        'guards'             => [
            /* If this guard is specified here (i.e. it is enabled], it will block
             * access to all routes unless they are specified here.
             */
            'BjyAuthorize\Guard\Route' => [
                [
                    'route'     => 'community/affiliation/affiliation',
                    'roles'     => ['office'],
                    'assertion' => 'affiliation_acl_assertion_affiliation'
                ],
                [
                    'route'     => 'community/affiliation/edit',
                    'roles'     => ['office'],
                    'assertion' => 'affiliation_acl_assertion_affiliation'
                ],
                [
                    'route'     => 'community/affiliation/edit-financial',
                    'roles'     => ['office'],
                    'assertion' => 'affiliation_acl_assertion_affiliation'
                ],
                [
                    'route'     => 'community/affiliation/doa/upload',
                    'roles'     => ['office'],
                    'assertion' => 'affiliation_acl_assertion_doa'
                ],
                [
                    'route'     => 'community/affiliation/doa/render',
                    'roles'     => ['office'],
                    'assertion' => 'affiliation_acl_assertion_doa'
                ],
                [
                    'route'     => 'community/affiliation/doa/replace',
                    'roles'     => ['office'],
                    'assertion' => 'affiliation_acl_assertion_doa'
                ],
                [
                    'route'     => 'community/affiliation/doa/download',
                    'roles'     => ['office'],
                    'assertion' => 'affiliation_acl_assertion_doa'
                ],
                [
                    'route'     => 'community/affiliation/loi/upload',
                    'roles'     => ['office'],
                    'assertion' => 'affiliation_acl_assertion_loi'
                ],
                [
                    'route'     => 'community/affiliation/loi/render',
                    'roles'     => ['office'],
                    'assertion' => 'affiliation_acl_assertion_loi'
                ],
                [
                    'route'     => 'community/affiliation/loi/replace',
                    'roles'     => ['office'],
                    'assertion' => 'affiliation_acl_assertion_loi'
                ],
                [
                    'route'     => 'community/affiliation/loi/download',
                    'roles'     => ['office'],
                    'assertion' => 'affiliation_acl_assertion_loi'
                ],
            ],
        ],
    ],
];
