<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */
declare(strict_types=1);


namespace Affiliation\Form;

use Affiliation\Entity\Affiliation;
use function asort;
use Doctrine\ORM\EntityManager;
use Organisation\Entity\Financial as FinancialOrganisation;
use Zend\Form\Form;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Radio;
use General\Entity\Country;
use DoctrineORMModule\Form\Element\EntitySelect;
use Zend\Form\Element\Text;
use Zend\Form\Element\Textarea;
use Zend\Form\Element\Select;

final class Financial extends Form
{
    public function __construct(Affiliation $affiliation, EntityManager $entityManager)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');

        $this->add(
            [
                'type'       => Text::class,
                'name'       => 'organisation',
                'options'    => [
                    'label' => _('txt-organisation-name'),
                ],
                'attributes' => [
                    'class' => 'form-control',
                ],
            ]
        );

        $this->add(
            [
                'type'       => EntitySelect::class,
                'name'       => 'registeredCountry',
                'options'    => [
                    'target_class'   => Country::class,
                    'object_manager' => $entityManager,
                    'find_method'    => [
                        'name'   => 'findForForm',
                        'params' => [
                            'criteria' => [],
                            'orderBy'  => [],
                        ],
                    ],
                ],
                'attributes' => [
                    'class' => 'form-control',
                    'label' => _('txt-registered-country'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Text::class,
                'name'       => 'vat',
                'options'    => [
                    'label' => _('txt-vat-number'),
                ],
                'attributes' => [
                    'class'       => 'form-control',
                    'placeholder' => _('txt-financial-vat-number-placeholder'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Text::class,
                'name'       => 'attention',
                'options'    => [
                    'label'      => _('txt-attention'),
                    'help-block' => _('txt-financial-attention-form-element-explanation'),
                ],
                'attributes' => [
                    'class'       => 'form-control',
                    'placeholder' => _('txt-financial-attention-placeholder'),
                ],
            ]
        );
        /*
         * Collect the financial contacts
         */
        $financialContactValueOptions = [];

        $financialContactValueOptions[$affiliation->getContact()->getId()]
            = $affiliation->getContact()->getFormName();
        /**
         * Add the associates
         */
        foreach ($affiliation->getAssociate() as $contact) {
            $financialContactValueOptions[$contact->getId()] = $contact->getFormName();
        }
        $organisation = $affiliation->getOrganisation();
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
        foreach ($organisation->getAffiliation() as $otherAffiliation) {
            if ($otherAffiliation->getFinancial() !== null) {
                $financialContactValueOptions[$otherAffiliation->getFinancial()->getContact()->getId()]
                    = $otherAffiliation->getFinancial()->getContact()->getFormName();
            }
        }

        asort($financialContactValueOptions);

        $this->add(
            [
                'type'       => Select::class,
                'name'       => 'contact',
                'options'    => [
                    'value_options' => $financialContactValueOptions,
                    'label'         => _('txt-financial-contact'),
                ],
                'attributes' => [
                    'class' => 'form-control',
                ],
            ]
        );

        $this->add(
            [
                'type'       => Radio::class,
                'name'       => 'omitContact',
                'options'    => [
                    'value_options' => FinancialOrganisation::getOmitContactTemplates(),
                    'label'         => _('txt-omit-contact'),
                ],
                'attributes' => [

                ],
            ]
        );
        $this->add(
            [
                'type'       => Textarea::class,
                'name'       => 'address',
                'options'    => [
                    'label' => _('txt-address'),
                ],
                'attributes' => [
                    'class'       => 'form-control',
                    'placeholder' => _('txt-address-placeholder'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Text::class,
                'name'       => 'zipCode',
                'options'    => [
                    'label' => _('txt-zip-code'),
                ],
                'attributes' => [
                    'class'       => 'form-control',
                    'placeholder' => _('txt-zip-code-placeholder'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Text::class,
                'name'       => 'city',
                'options'    => [
                    'label' => _('txt-city'),
                ],
                'attributes' => [
                    'class'       => 'form-control',
                    'placeholder' => _('txt-city-placeholder'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => EntitySelect::class,
                'name'       => 'country',
                'options'    => [
                    'target_class'   => Country::class,
                    'object_manager' => $entityManager,
                    'find_method'    => [
                        'name'   => 'findForForm',
                        'params' => [
                            'criteria' => [],
                            'orderBy'  => [],
                        ],
                    ],
                ],
                'attributes' => [
                    'class' => 'form-control',
                    'label' => _('txt-country'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Radio::class,
                'name'       => 'preferredDelivery',
                'options'    => [
                    'value_options' => FinancialOrganisation::getEmailTemplates(),
                    'label'         => _('txt-preferred-delivery'),
                ],
                'attributes' => [
                ],
            ]
        );
        $this->add(
            [
                'type'       => Submit::class,
                'name'       => 'submit',
                'attributes' => [
                    'class' => 'btn btn-primary',
                    'value' => _('txt-update'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Submit::class,
                'name'       => 'cancel',
                'attributes' => [
                    'class' => 'btn btn-warning',
                    'value' => _('txt-cancel'),
                ],
            ]
        );
    }
}
