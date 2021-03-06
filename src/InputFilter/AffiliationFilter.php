<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\InputFilter;

use Laminas\InputFilter\InputFilter;

/**
 * Class AffiliationFilter
 *
 * @package Affiliation\InputFilter
 */
final class AffiliationFilter extends InputFilter
{
    public function __construct()
    {
        $inputFilter = new InputFilter();
        $inputFilter->add(
            [
                'name'       => 'branch',
                'required'   => false,
                'filters'    => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 40,
                        ],
                    ],
                ],
            ]
        );
        $inputFilter->add(
            [
                'name'     => 'note',
                'required' => false,
            ]
        );
        $inputFilter->add(
            [
                'name'       => 'valueChain',
                'required'   => false,
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 0,
                            'max'      => 255,
                        ],
                    ],
                ],
            ]
        );
        $inputFilter->add(
            [
                'name'     => 'mainContribution',
                'required' => false,
            ]
        );
        $inputFilter->add(
            [
                'name'     => 'marketAccess',
                'required' => false,
            ]
        );
        $inputFilter->add(
            [
                'name'     => 'dateEnd',
                'required' => false,
            ]
        );
        $inputFilter->add(
            [
                'name'     => 'dateSelfFunded',
                'required' => false,
            ]
        );
        $inputFilter->add(
            [
                'name'     => 'contact',
                'required' => false,
            ]
        );
        $inputFilter->add(
            [
                'name'     => 'selfFunded',
                'required' => true,
            ]
        );

        $this->add($inputFilter, 'affiliation_entity_affiliation');
    }
}
