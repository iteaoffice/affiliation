<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c] 2004-2014 ITEA Office (http://itea3.org]
 */
use Affiliation\Acl\Assertion\Affiliation as AffiliationAssertion;
use Affiliation\Acl\Assertion\Doa as DoaAssertion;
use Affiliation\Acl\Assertion\Loi as LoiAssertion;

return [
    'bjyauthorize' => [
        // resource providers provide a list of resources that will be tracked
        // in the ACL. like roles, they can be hierarchical
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'affiliation' => [],
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
                    'roles'     => [],
                    'assertion' => AffiliationAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/edit/affiliation',
                    'roles'     => [],
                    'assertion' => AffiliationAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/edit/add-associate',
                    'roles'     => [],
                    'assertion' => AffiliationAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/edit/financial',
                    'roles'     => [],
                    'assertion' => AffiliationAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/edit/description',
                    'roles'     => [],
                    'assertion' => AffiliationAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/doa/upload',
                    'roles'     => [],
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/doa/render',
                    'roles'     => [],
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/doa/replace',
                    'roles'     => [],
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/doa/download',
                    'roles'     => [],
                    'assertion' => DoaAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/loi/upload',
                    'roles'     => [],
                    'assertion' => LoiAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/loi/render',
                    'roles'     => [],
                    'assertion' => LoiAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/loi/replace',
                    'roles'     => [],
                    'assertion' => LoiAssertion::class
                ],
                [
                    'route'     => 'community/affiliation/loi/download',
                    'roles'     => [],
                    'assertion' => LoiAssertion::class
                ],
            ],
        ],
    ],
];
