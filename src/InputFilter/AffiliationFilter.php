<?php
/**
 * ITEA Office all rights reserved
 *
 * PHP Version 7
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */

namespace Affiliation\InputFilter;

use Zend\InputFilter\InputFilter;

/**
 * Class AffiliationFilter
 *
 * @package Affiliation\InputFilter
 */
class AffiliationFilter extends InputFilter
{
    /**
     * AffiliationFilter constructor.
     */
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
                'name'     => 'valueChain',
                'required' => false,
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
