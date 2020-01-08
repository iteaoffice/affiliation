<?php

/**
 * ITEA Office all rights reserved
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Form;

use Affiliation\Entity;
use Affiliation\Service\AffiliationService;
use Contact\Entity\Contact;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\Callback;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\NotEmpty;

use function asort;
use function sprintf;

/**
 * Class Affiliation
 *
 * @package Affiliation\Form
 */
final class Affiliation extends Form implements InputFilterProviderInterface
{
    public function __construct(Entity\Affiliation $affiliation, AffiliationService $affiliationService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');
        $technicalContactValueOptions = [];
        $affiliationValueOptions = [];
        foreach ($affiliationService->parseRenameOptions($affiliation) as $country => $options) {
            $groupOptions = [];
            foreach ($options as $organisationId => $branchAndName) {
                foreach ($branchAndName as $branch => $organisationWithBranch) {
                    $groupOptions[sprintf('%s|%s', $organisationId, $branch)] = $organisationWithBranch;
                }
            }
            $affiliationValueOptions[$country] = [
                'label'   => $country,
                'options' => $groupOptions,
            ];
        }
        /*
         * Collect the technical contacts
         */
        $technicalContactValueOptions[$affiliation->getContact()->getId()]
            = $affiliation->getContact()->getFormName();
        foreach ($affiliation->getAssociate() as $contact) {
            $technicalContactValueOptions[$contact->getId()] = $contact->getFormName();
        }
        asort($technicalContactValueOptions);

        //Go over the TC/PTC to add them
        if ($this->isContactInAffiliationOrganisation($affiliation->getProject()->getContact(), $affiliation)) {
            $contact = $affiliation->getProject()->getContact();
            $technicalContactValueOptions[$contact->getId()] = $contact->getFormName();
        }

        foreach ($affiliation->getProject()->getProxyContact() as $proxyContact) {
            if ($this->isContactInAffiliationOrganisation($proxyContact, $affiliation)) {
                $contact = $affiliation->getProject()->getContact();
                $technicalContactValueOptions[$contact->getId()] = $contact->getFormName();
            }
        }

        /*
         * Collect the financial contacts
         * This array starts from the technical contacts
         */
        $financialContactValueOptions = $technicalContactValueOptions;
        $organisation = $affiliation->getOrganisation();
        foreach ($organisation->getAffiliation() as $otherAffiliation) {
            if (
                (null !== $otherAffiliation->getFinancial())
                && null === $otherAffiliation->getFinancial()->getContact()->isActive()
            ) {
                $financialContactValueOptions[$otherAffiliation->getFinancial()->getContact()->getId()]
                    = $otherAffiliation->getFinancial()->getContact()->getFormName();
            }
        }

        //Add the current financial contact
        if (null !== $affiliation->getFinancial()) {
            $financialContactValueOptions[$affiliation->getFinancial()->getContact()->getId()]
                = $affiliation->getFinancial()->getContact()->getFormName();
        }

        asort($financialContactValueOptions);

        $this->add(
            [
                'type'       => Select::class,
                'name'       => 'affiliation',
                'options'    => [
                    'value_options' => $affiliationValueOptions,
                    'label'         => _('txt-change-affiliation'),
                    'help-block'    => _('txt-change-affiliation-help-block'),
                ],
                'attributes' => [
                    'class' => 'form-control',
                ],
            ]
        );
        $this->add(
            [
                'type'       => Select::class,
                'name'       => 'technical',
                'options'    => [
                    'value_options' => $technicalContactValueOptions,
                    'label'         => _('txt-technical-contact'),
                    'help-block'    => _('txt-technical-contact-help-block'),
                ],
                'attributes' => [

                    'class' => 'form-control',
                ],
            ]
        );
        $this->add(
            [
                'type'       => Select::class,
                'name'       => 'financial',
                'options'    => [
                    'value_options' => $financialContactValueOptions,
                    'label'         => _('txt-financial-contact'),
                    'help-block'    => _('txt-financial-contact-help-block'),
                ],
                'attributes' => [

                    'class' => 'form-control',
                ],
            ]
        );
        $this->add(
            [
                'type'       => Text::class,
                'name'       => 'communicationContactName',
                'options'    => [
                    'label'      => _('txt-communication-contact-name'),
                    'help-block' => _('txt-communication-contact-name-help-block'),
                ],
                'attributes' => [
                    'placeholder' => _('txt-communication-contact-name-placeholder'),
                ]
            ]
        );
        $this->add(
            [
                'type'       => Text::class,
                'name'       => 'communicationContactEmail',
                'options'    => [
                    'label'      => _('txt-communication-contact-email'),
                    'help-block' => _('txt-communication-contact-email-help-block'),
                ],
                'attributes' => [
                    'placeholder' => _('txt-communication-contact-email-placeholder'),
                ]
            ]
        );
        $this->add(
            [
                'type'       => Text::class,
                'name'       => 'valueChain',
                'options'    => [
                    'label'      => _('txt-position-on-value-chain'),
                    'help-block' => _('txt-position-on-value-chain-inline-help'),
                ],
                'attributes' => [
                    'class' => 'form-control',
                ],
            ]
        );
        $this->add(
            [
                'type'       => Textarea::class,
                'name'       => 'mainContribution',
                'options'    => [
                    'label'      => _('txt-main-contributions-and-added-value'),
                    'help-block' => _('txt--main-contribution-for-the-project-inline-help'),
                ],
                'attributes' => [
                    'class' => 'form-control',
                ],
            ]
        );
        $this->add(
            [
                'type'       => Textarea::class,
                'name'       => 'strategicImportance',
                'options'    => [
                    'label'      => _('txt-strategic-importance'),
                    'help-block' => _('txt-strategic-importance-inline-help'),
                ],
                'attributes' => [
                    'class' => 'form-control',
                ],
            ]
        );
        $this->add(
            [
                'type'       => Textarea::class,
                'name'       => 'marketAccess',
                'options'    => [
                    'label'      => _('txt-market-access'),
                    'help-block' => _('txt-market-access-inline-help'),
                ],
                'attributes' => [
                    'cols'  => 8,
                    'class' => 'form-control',
                ],
            ]
        );
        $this->add(
            [
                'type'    => Radio::class,
                'name'    => 'selfFunded',
                'options' => [
                    'value_options' => Entity\Affiliation::getSelfFundedTemplates(),
                    'label'         => _('txt-self-funded'),
                    'help-block'    => _('txt-self-funded-inline-help'),
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
        $this->add(
            [
                'type'       => Submit::class,
                'name'       => 'deactivate',
                'attributes' => [
                    'class' => 'btn btn-danger',
                    'value' => sprintf(_('Deactivate %s'), $affiliation->parseBranchedName()),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Submit::class,
                'name'       => 'reactivate',
                'attributes' => [
                    'class' => 'btn btn-warning',
                    'value' => sprintf(_('Reactivate %s'), $affiliation->parseBranchedName()),
                ],
            ]
        );
    }

    private function isContactInAffiliationOrganisation(Contact $contact, Entity\Affiliation $affiliation): bool
    {
        if (! $contact->hasOrganisation()) {
            return false;
        }
        return $contact->getContactOrganisation()->getOrganisation() === $affiliation->getOrganisation();
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'valueChain'                => [
                'required'   => false,
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 0,
                            'max'      => 255,
                        ],
                    ],
                ],
            ],
            'affiliation'               => [
                'required' => true,
            ],
            'technical'                 => [
                'required' => true,
            ],
            'financial'                 => [
                'required' => true,
            ],
            'communicationContactName'  => [
                'required'   => false,
                'validators' => [
                    new NotEmpty(NotEmpty::NULL),
                    [
                        'name'    => 'Callback',
                        'options' => [
                            'messages' => [
                                Callback::INVALID_VALUE => sprintf(
                                    'No email provided for the communication contact'
                                ),
                            ],
                            'callback' => static function ($value, $context = []) {
                                return '' !== $context['communicationContactEmail'];
                            },
                        ],
                    ],
                ],
            ],
            'communicationContactEmail' => [
                'required'   => false,
                'validators' => [
                    new EmailAddress(),
                    new NotEmpty(NotEmpty::NULL),
                    [
                        'name'    => 'Callback',
                        'options' => [
                            'messages' => [
                                Callback::INVALID_VALUE => sprintf(
                                    'No name provided for the communication contact'
                                ),
                            ],
                            'callback' => static function ($value, $context = []) {
                                return '' !== $context['communicationContactName'];
                            },
                        ],
                    ],
                ],
            ],
        ];
    }
}
