<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
use Affiliation\Form;
use Affiliation\Entity;
use Affiliation\Acl\Assertion;

return array(
    'factories' => array(
        'affiliation_affiliation_form'          => function ($sm) {
            return new Form\CreateObject($sm, new Entity\Affiliation());
        },
        'affiliation_acl_assertion_affiliation' => function ($sm) {
            return new Assertion\Affiliation($sm);
        },
        'affiliation_acl_assertion_doa'         => function ($sm) {
            return new Assertion\Doa($sm);
        },
        'affiliation_acl_assertion_loi'         => function ($sm) {
            return new Assertion\Loi($sm);
        },

    ),
);
