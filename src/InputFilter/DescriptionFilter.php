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
 * Class DescriptionFilter
 *
 * @package Affiliation\InputFilter
 */
class DescriptionFilter extends InputFilter
{
    /**
     * DescriptionFilter constructor.
     */
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
