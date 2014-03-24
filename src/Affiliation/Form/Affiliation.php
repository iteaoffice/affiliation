<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Project
 * @package     Form
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Affiliation\Form;

use Zend\Form\Form;
use Affiliation\Service\AffiliationService;

/**
 *
 */
class Affiliation extends Form
{
    /**
     * Class constructor
     */
    public function __construct(AffiliationService $affiliationService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');

        $technicalContactValueOptions = array();
        $affiliationValueOptions      = array();

        foreach ($affiliationService->parseRenameOptions() as $country => $options) {

            $groupOptions = array();
            foreach ($options as $organisationId => $branchAndName) {
                foreach ($branchAndName as $branch => $organisationWithBranch) {
                    $groupOptions[sprintf("%s|%s", $organisationId, $branch)] = $organisationWithBranch;
                }
            }

            $affiliationValueOptions[$country] = array(
                'label'   => $country,
                'options' => $groupOptions
            );
        }

        /**
         * Collect the technical contacts
         */
        $technicalContactValueOptions[$affiliationService->getAffiliation()->getContact()->getId()] =
            $affiliationService->getAffiliation()->getContact()->getDisplayName();
        foreach ($affiliationService->getAffiliation()->getAssociate() as $contact) {
            $technicalContactValueOptions[$contact->getId()] = $contact->getDisplayName();
        }

        /**
         * Collect the financial contacts
         * This array starts from the technical contacts
         */
        $financialContactValueOptions = $technicalContactValueOptions;
        $organisation                 = $affiliationService->getAffiliation()->getOrganisation();
        foreach ($organisation->getAffiliation() as $affiliation) {
            if (!is_null($affiliation->getFinancial())) {
                $financialContactValueOptions[$affiliation->getFinancial()->getContact()->getId()] =
                    $affiliation->getFinancial()->getContact()->getDisplayName();
            }
        }

        $this->add(
            array(
                'type'       => 'Zend\Form\Element\Select',
                'name'       => 'affiliation',

                'options'    => array(
                    'value_options' => $affiliationValueOptions,
                    'label'         => _("txt-change-affiliation"),
                ),
                'attributes' => array(
                    'class'    => 'form-control',
                    'required' => true,
                )
            )
        );

        $this->add(
            array(
                'type'       => 'Zend\Form\Element\Select',
                'name'       => 'technical',

                'options'    => array(
                    'value_options' => $technicalContactValueOptions,
                    'label'         => _("txt-technical-contact"),
                ),
                'attributes' => array(
                    'class'    => 'form-control',
                    'required' => true,
                )
            )
        );

        $this->add(
            array(
                'type'       => 'Zend\Form\Element\Select',
                'name'       => 'financial',

                'options'    => array(
                    'value_options' => $financialContactValueOptions,
                    'label'         => _("txt-financial-contact"),
                ),
                'attributes' => array(
                    'class'    => 'form-control',
                    'required' => true,
                )
            )
        );

        $this->add(
            array(
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'submit',
                'attributes' => array(
                    'class' => "btn btn-primary",
                    'value' => _("txt-update")
                )
            )
        );

        $this->add(
            array(
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'deactivate',
                'attributes' => array(
                    'class' => "btn btn-danger",
                    'value' => sprintf(_("txt-deactivate-partner-%s"), $affiliationService->getAffiliation()->getOrganisation()->getOrganisation())
                )
            )
        );

        $this->add(
            array(
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'reactivate',
                'attributes' => array(
                    'class' => "btn btn-warning",
                    'value' => sprintf(_("txt-reactivate-partner-%s"), $affiliationService->getAffiliation()->getOrganisation()->getOrganisation())
                )
            )
        );
    }
}
