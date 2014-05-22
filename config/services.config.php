<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
use Affiliation\Acl\Assertion;
use Affiliation\Entity;
use Affiliation\Form;

return array(
    'invokables' => array(
        'affiliation_affiliation_service'     => 'Affiliation\Service\AffiliationService',
        'affiliation_form_service'            => 'Affiliation\Service\FormService',
        'affiliation_affiliation_form_filter' => 'Affiliation\Form\FilterCreateAffiliation',
    ),
    'factories'  => array(
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
        'affiliation_assertion'                 => 'Affiliation\Acl\Assertion\Affiliation',
        'affiliation_module_options'            => 'Affiliation\Service\OptionServiceFactory',
    ),
);
