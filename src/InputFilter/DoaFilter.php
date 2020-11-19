<?php

/**
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
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
