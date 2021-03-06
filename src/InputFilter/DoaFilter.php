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
use Laminas\Validator\File\Extension;
use Laminas\Validator\File\Size;

/***
 * Class DoaFilter
 * @package Affiliation\InputFilter
 */
final class DoaFilter extends InputFilter
{
    public function __construct()
    {
        $inputFilter = new InputFilter();
        $inputFilter->add(
            [
                'name'     => 'dateSigned',
                'required' => false,
            ],
        );
        $inputFilter->add(
            [
                'name'     => 'dateApproved',
                'required' => false,
            ],
        );
        $inputFilter->add(
            [
                'name'     => 'groupName',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                    ['name' => 'ToNull'],
                ],
            ],
        );
        $inputFilter->add(
            [
                'name'     => 'chamberOfCommerceNumber',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                    ['name' => 'ToNull'],
                ],
            ],
        );
        $inputFilter->add(
            [
                'name'     => 'chamberOfCommerceLocation',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                    ['name' => 'ToNull'],
                ],
            ],
        );
        $this->add(
            [
                'name'       => 'file',
                'required'   => false,
                'validators' => [
                    new Size(
                        [
                            'min' => '1kB',
                            'max' => '16MB',
                        ]
                    ),
                    new Extension(
                        [
                            'extension' => ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'],
                        ]
                    ),
                ],
            ],
        );

        $this->add($inputFilter, 'affiliation_entity_doa');
    }
}
