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

return array(
    'factories' => array(
        'affiliation_affiliation_form'       => function ($sm) {
            return new Form\CreateAffiliation($sm);
        },
        'affiliation_facility_form'      => function ($sm) {
            return new Form\CreateFacility($sm);
        },
        'affiliation_area_form'          => function ($sm) {
            return new Form\CreateArea($sm);
        },
        'affiliation_area2_form'         => function ($sm) {
            return new Form\CreateArea2($sm);
        },
        'affiliation_sub_area_form'      => function ($sm) {
            return new Form\CreateSubArea($sm);
        },
        'affiliation_oper_area_form'     => function ($sm) {
            return new Form\CreateOperArea($sm);
        },
        'affiliation_oper_sub_area_form' => function ($sm) {
            return new Form\CreateOperSubArea($sm);
        },
        'affiliation_message_form'       => function ($sm) {
            return new Form\CreateMessage($sm);
        },
    ),
);
