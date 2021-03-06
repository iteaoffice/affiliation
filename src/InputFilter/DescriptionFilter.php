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
 * Class DescriptionFilter
 *
 * @package Affiliation\InputFilter
 */
final class DescriptionFilter extends InputFilter
{
    public function __construct()
    {
        $inputFilter = new InputFilter();
        $inputFilter->add(
            [
                'name'     => 'description',
                'required' => true,
            ]
        );

        $this->add($inputFilter, 'affiliation_entity_description');
    }
}
