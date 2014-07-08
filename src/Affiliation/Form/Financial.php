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
     * Class constructor
     */
    public function __construct(AffiliationService $affiliationService, GeneralService $generalService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        $countries = [];
        foreach ($generalService->findAll('country') as $country) {
            $countries[$country->getId()] = $country->getCountry();
        }
        asort($countries);
        $this->add(
            array(
                'type'       => 'Zend\Form\Element\Text',
                'name'       => 'organisation',
                'options'    => array(
                    'label' => _("txt-organisation-name"),
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
                'name'       => 'registeredCountry',
                'options'    => array(
                    'value_options' => $countries,
                    'label'         => _("txt-registered-country"),
                ),
                'attributes' => array(
                    'class'    => 'form-control',
                    'required' => true,
                )
            )
        );
        $this->add(
            array(
                'type'       => 'Zend\Form\Element\Text',
                'name'       => 'vat',
                'options'    => array(
                    'label' => _("txt-vat-number"),
                ),
                'attributes' => array(
                    'class'    => 'form-control',
                    'required' => true,
                )
            )
        );
        $this->add(
            array(
                'type'       => 'Zend\Form\Element\Text',
                'name'       => 'attention',
                'options'    => array(
                    'label'      => _("txt-attention"),
                    'help-block' => _("txt-financial-attention-form-element-explanation")
                ),
                'attributes' => array(
                    'class'       => 'form-control',
                    'placeholder' => _("txt-financial-attention-placeholder")
                )
            )
        );
        $organisationFinancial = new FinancialOrganisation();
        $this->add(
            array(
                'type'       => 'Zend\Form\Element\Radio',
                'name'       => 'omitContact',
                'options'    => array(
                    'value_options' => $organisationFinancial->getOmitContactTemplates(),
                    'label'         => _("txt-omit-contact"),
                ),
                'attributes' => array(
                    'class'    => 'form-control',
                    'required' => true,
                )
            )
        );
        $this->add(
            array(
                'type'       => 'Zend\Form\Element\Text',
                'name'       => 'address',
                'options'    => array(
                    'label' => _("txt-address"),
                ),
                'attributes' => array(
                    'class'       => 'form-control',
                    'placeholder' => _("txt-address-placeholder"),
                    'required'    => true,
                )
            )
        );
        $this->add(
            array(
                'type'       => 'Zend\Form\Element\Text',
                'name'       => 'zipCode',
                'options'    => array(
                    'label' => _("txt-zip-code"),
                ),
                'attributes' => array(
                    'class'       => 'form-control',
                    'placeholder' => _("txt-zip-code-placeholder"),
                    'required'    => true,
                )
            )
        );
        $this->add(
            array(
                'type'       => 'Zend\Form\Element\Text',
                'name'       => 'city',
                'options'    => array(
                    'label' => _("txt-city"),
                ),
                'attributes' => array(
                    'class'       => 'form-control',
                    'placeholder' => _("txt-city-placeholder"),
                    'required'    => true,
                )
            )
        );
        $this->add(
            array(
                'type'       => 'Zend\Form\Element\Select',
                'name'       => 'country',
                'options'    => array(
                    'value_options' => $countries,
                    'label'         => _("txt-country"),
                ),
                'attributes' => array(
                    'class'    => 'form-control',
                    'required' => true,
                )
            )
        );
        $this->add(
            array(
                'type'       => 'Zend\Form\Element\Radio',
                'name'       => 'preferredDelivery',
                'options'    => array(
                    'value_options' => $organisationFinancial->getEmailTemplates(),
                    'label'         => _("txt-preferred-delivery"),
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
                'name'       => 'cancel',
                'attributes' => array(
                    'class' => "btn btn-warning",
                    'value' => _("txt-cancel")
                )
            )
        );
    }
}
