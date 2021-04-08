<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Form;

use Affiliation\Entity\Affiliation;
use Contact\Entity\Contact;
use Contact\Service\ContactService;
use Doctrine\ORM\EntityManager;
use DoctrineORMModule\Form\Element\EntitySelect;
use DragonBe\Vies\Vies;
use DragonBe\Vies\ViesException;
use General\Entity\Country;
use Laminas\Filter\StringTrim;
use Laminas\Form\Element\Email;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\Callback;
use Laminas\Validator\NotEmpty;
use Organisation\Entity\Financial as FinancialOrganisation;

use function asort;

/**
 * Class FinancialForm
 * @package Affiliation\Form
 */
final class FinancialForm extends Form implements InputFilterProviderInterface
{
    private EntityManager $entityManager;

    public function __construct(Affiliation $affiliation, ContactService $contactService, EntityManager $entityManager)
    {
        parent::__construct();

        $this->entityManager = $entityManager;

        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');


        $this->add(
            [
                'type'    => Text::class,
                'name'    => 'organisation',
                'options' => [
                    'label' => _('txt-organisation-name'),
                ],
            ]
        );


        //All possible financial contacts are all the contacts in the affiliation and the other financial contacts
        //Throughout the application
        $contacts = $contactService->findContactsInAffiliation($affiliation);
        //Take only the contacts and sort them
        $financialContactValueOptions = [];
        /** @var Contact $contact */
        foreach ($contacts['contacts'] as $contact) {
            $financialContactValueOptions[$contact->getId()] = $contact->getFormName();
        }

        /**
         * Add all the financial contacts form other projects
         */
        foreach ($affiliation->getOrganisation()->getAffiliation() as $otherAffiliation) {
            if ($otherAffiliation->getFinancial() !== null) {
                $contact = $otherAffiliation->getFinancial()->getContact();

                //Skip any inactive contact
                if (! $contact->isActive()) {
                    continue;
                }

                $financialContactValueOptions[$contact->getId()] = $contact->getFormName();
            }
        }

        asort($financialContactValueOptions);

        $this->add(
            [
                'type'    => Select::class,
                'name'    => 'contact',
                'options' => [
                    'value_options' => $financialContactValueOptions,
                    'label'         => _('txt-affiliation-financial-contact-label'),
                    'help-block'    => _('txt-affiliation-financial-contact-help-block'),
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
                    'label' => _('txt-registered-country'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Text::class,
                'name'       => 'vat',
                'options'    => [
                    'label'      => _('txt-vat-number-label'),
                    'help-block' => _('txt-financial-vat-number-help-block'),
                ],
                'attributes' => [
                    'placeholder' => _('txt-financial-vat-number-placeholder'),

                ],
            ]
        );
        $this->add(
            [
                'type'       => Email::class,
                'name'       => 'emailCC',
                'options'    => [
                    'label'      => _('txt-affiliation-financial-email-cc'),
                    'help-block' => _('txt-affiliation-financial-email-cc-help-block'),
                ],
                'attributes' => [
                    'placeholder' => _('txt-affiliation-financial-email-cc-placeholder'),
                ],
            ]
        );

        $this->add(
            [
                'type'    => Radio::class,
                'name'    => 'omitContact',
                'options' => [
                    'value_options' => FinancialOrganisation::getOmitContactTemplates(),
                    'label'         => _('txt-affiliation-financial-omit-contact-label'),
                    'help-block'    => _('txt-affiliation-financial-omit-contact-help-block'),
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
                    'placeholder' => _('txt-city-placeholder'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => EntitySelect::class,
                'name'       => 'addressCountry',
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
                    'label' => _('txt-country'),
                ],
            ]
        );
        $this->add(
            [
                'type'    => Radio::class,
                'name'    => 'preferredDelivery',
                'options' => [
                    'value_options' => FinancialOrganisation::getEmailTemplates(),
                    'label'         => _('txt-preferred-delivery'),
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

    public function getInputFilterSpecification(): array
    {
        return [
            'organisation' => [
                'required' => true,
            ],
            'contact'      => [
                'required' => true,
            ],
            'emailCC'      => [
                'required' => false,
            ],
            'address'      => [
                'required' => true,
            ],
            'zipCode'      => [
                'required' => true,
            ],
            'city'         => [
                'required' => true,
            ],
            'vat'          => [
                'required'   => false,
                'filters'    => [
                    new StringTrim()
                ],
                'validators' => [
                    new NotEmpty(NotEmpty::NULL),
                    new Callback(
                        [
                            'messages' => [
                                Callback::INVALID_VALUE => 'The VAT number (%value%) is not a correct EU VAT number. This field can be left empty for NON-EU companies',
                            ],
                            'callback' => function (string $value, array $context) {

                                //Grab the country from the context
                                /** @var Country $country */
                                $country = $this->entityManager->find(\General\Entity\Country::class, (int)$context['registeredCountry']);

                                $countryCode = $country->getCd();
                                $vatNumber   = str_replace($countryCode, '', $value);

                                try {
                                    //Do an in-situ vat check
                                    $vies = new Vies();
                                    return $vies->validateVat($countryCode, $vatNumber);
                                } catch (ViesException $e) {
                                    return false;
                                }
                            },
                        ]
                    ),
                ],
            ],
        ];
    }
}
