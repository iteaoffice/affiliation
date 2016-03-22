<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Form;

use Affiliation\Service\AffiliationService;
use General\Service\GeneralService;
use Organisation\Entity\Financial as FinancialOrganisation;
use Zend\Form\Form;

/**
 *
 */
class Financial extends Form
{
    /**
     * @param AffiliationService $affiliationService
     * @param GeneralService     $generalService
     */
    public function __construct(AffiliationService $affiliationService, GeneralService $generalService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');
        $countries = [];
        foreach ($generalService->findAll('country') as $country) {
            $countries[$country->getId()] = $country->getCountry();
        }
        asort($countries);
        $this->add([
            'type'       => 'Zend\Form\Element\Text',
            'name'       => 'organisation',
            'options'    => [
                'label' => _("txt-organisation-name"),
            ],
            'attributes' => [
                'class'    => 'form-control',
                'required' => true,
            ],
        ]);
        $this->add([
            'type'       => 'Zend\Form\Element\Select',
            'name'       => 'registeredCountry',
            'options'    => [
                'value_options' => $countries,
                'label'         => _("txt-registered-country"),
            ],
            'attributes' => [
                'class'    => 'form-control',
                'required' => true,
            ],
        ]);
        $this->add([
            'type'       => 'Zend\Form\Element\Text',
            'name'       => 'vat',
            'options'    => [
                'label' => _("txt-vat-number"),
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);
        $this->add([
            'type'       => 'Zend\Form\Element\Text',
            'name'       => 'attention',
            'options'    => [
                'label'      => _("txt-attention"),
                'help-block' => _("txt-financial-attention-form-element-explanation"),
            ],
            'attributes' => [
                'class'       => 'form-control',
                'placeholder' => _("txt-financial-attention-placeholder"),
            ],
        ]);
        /*
         * Collect the financial contacts
         */
        $financialContactValueOptions = [];

        $financialContactValueOptions[$affiliationService->getAffiliation()->getContact()->getId()]
            = $affiliationService->getAffiliation()->getContact()->getFormName();
        /**
         * Add the associates
         */
        foreach ($affiliationService->getAffiliation()->getAssociate() as $contact) {
            $financialContactValueOptions[$contact->getId()] = $contact->getFormName();
        }
        $organisation = $affiliationService->getAffiliation()->getOrganisation();
        /**
         * Add the contacts in the organisation
         */
        foreach ($organisation->getContactOrganisation() as $contactOrganisation) {
            $financialContactValueOptions[$contactOrganisation->getContact()->getId()]
                = $contactOrganisation->getContact()->getFormName();
        }
        /**
         * Add all the financial contacts form other projects
         */
        foreach ($organisation->getAffiliation() as $affiliation) {
            if (!is_null($affiliation->getFinancial())) {
                $financialContactValueOptions[$affiliation->getFinancial()->getContact()->getId()]
                    = $affiliation->getFinancial()->getContact()->getFormName();
            }
        }

        asort($financialContactValueOptions);

        $this->add([
            'type'       => 'Zend\Form\Element\Select',
            'name'       => 'contact',
            'options'    => [
                'value_options' => $financialContactValueOptions,
                'label'         => _("txt-financial-contact"),
            ],
            'attributes' => [
                'class'    => 'form-control',
                'required' => true,
            ],
        ]);
        $organisationFinancial = new FinancialOrganisation();
        $this->add([
            'type'       => 'Zend\Form\Element\Radio',
            'name'       => 'omitContact',
            'options'    => [
                'value_options' => $organisationFinancial->getOmitContactTemplates(),
                'label'         => _("txt-omit-contact"),
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);
        $this->add([
            'type'       => 'Zend\Form\Element\Textarea',
            'name'       => 'address',
            'options'    => [
                'label' => _("txt-address"),
            ],
            'attributes' => [
                'class'       => 'form-control',
                'placeholder' => _("txt-address-placeholder"),
                'required'    => true,
            ],
        ]);
        $this->add([
            'type'       => 'Zend\Form\Element\Text',
            'name'       => 'zipCode',
            'options'    => [
                'label' => _("txt-zip-code"),
            ],
            'attributes' => [
                'class'       => 'form-control',
                'placeholder' => _("txt-zip-code-placeholder"),
                'required'    => true,
            ],
        ]);
        $this->add([
            'type'       => 'Zend\Form\Element\Text',
            'name'       => 'city',
            'options'    => [
                'label' => _("txt-city"),
            ],
            'attributes' => [
                'class'       => 'form-control',
                'placeholder' => _("txt-city-placeholder"),
                'required'    => true,
            ],
        ]);
        $this->add([
            'type'       => 'Zend\Form\Element\Select',
            'name'       => 'country',
            'options'    => [
                'value_options' => $countries,
                'label'         => _("txt-country"),
            ],
            'attributes' => [
                'class'    => 'form-control',
                'required' => true,
            ],
        ]);
        $this->add([
            'type'       => 'Zend\Form\Element\Radio',
            'name'       => 'preferredDelivery',
            'options'    => [
                'value_options' => $organisationFinancial->getEmailTemplates(),
                'label'         => _("txt-preferred-delivery"),
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);
        $this->add([
            'type'       => 'Zend\Form\Element\Submit',
            'name'       => 'submit',
            'attributes' => [
                'class' => "btn btn-primary",
                'value' => _("txt-update"),
            ],
        ]);
        $this->add([
            'type'       => 'Zend\Form\Element\Submit',
            'name'       => 'cancel',
            'attributes' => [
                'class' => "btn btn-warning",
                'value' => _("txt-cancel"),
            ],
        ]);
    }
}
