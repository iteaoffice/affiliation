<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
use Affiliation\Entity;
use Affiliation\Form;

return [
    'factories' => [
        'affiliation_affiliation_form' => function ($sm) {
            return new Form\CreateObject($sm, new Entity\Affiliation());
        },
    ],
];
