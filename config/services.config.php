<?php
/**
 * Japaveh Webdesign copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <info@japaveh.nl>
 * @copyright   Copyright (c) 2004-2013 Japaveh Webdesign (http://japaveh.nl)
 */
use Affiliation\Form;
use Affiliation\Entity;

return array(
    'factories' => array(
        'affiliation_affiliation_form' => function ($sm) {
            return new Form\CreateObject($sm, new Entity\Affiliation());
        },

    ),
);
