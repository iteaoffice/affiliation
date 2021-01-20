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

use Affiliation\Entity;
use Affiliation\Service\AffiliationService;
use Contact\Entity\Contact;
use Contact\Service\ContactService;
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
final class AffiliationForm extends Form implements InputFilterProviderInterface
{
    public function __construct(Entity\Affiliation $affiliation, AffiliationService $affiliationService, ContactService $contactService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');

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

        $this->add(
            [
                'type'    => Select::class,
                'name'    => 'affiliation',
                'options' => [
                    'value_options' => $affiliationValueOptions,
                    'label'         => _('txt-change-affiliation'),
                    'help-block'    => _('txt-change-affiliation-help-block'),
                ],
            ]
        );

        $contacts = $contactService->findContactsInAffiliation($affiliation);
        //Take only the contacts and sort them
        $technicalContactValueOptions = [];
        /** @var Contact $contact */
        foreach ($contacts['contacts'] as $contact) {
            $technicalContactValueOptions[$contact->getId()] = $contact->getFormName();
        }
        asort($technicalContactValueOptions);

        $this->add(
            [
                'type'    => Select::class,
                'name'    => 'technical',
                'options' => [
                    'value_options' => $technicalContactValueOptions,
                    'label'         => _('txt-technical-contact'),
                    'help-block'    => _('txt-technical-contact-help-block'),
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
                    'label'      => _('txt-role-in-the-project'),
                    'help-block' => _('txt-role-in-the-project-inline-help'),
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

                    'help-block' => _('txt-affiliation-main-contribution-for-the-project-help-block'),
                ],
                'attributes' => [
                    'placeholder' => _('txt-affiliation-main-contributions-placeholder'),
                    'label'       => _('txt-affiliation-main-contributions-label'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Textarea::class,
                'name'       => 'tasksAndAddedValue',
                'options'    => [

                    'help-block' => _('txt-affiliation-tasks-and-added-value-help-block'),
                ],
                'attributes' => [
                    'placeholder' => _('txt-affiliation-tasks-and-added-value-placeholder'),
                    'label'       => _('txt-affiliation-tasks-and-added-value-label'),
                ],
            ]
        );
        $this->add(
            [
                'type'    => Textarea::class,
                'name'    => 'strategicImportance',
                'options' => [
                    'label'      => _('txt-strategic-importance'),
                    'help-block' => _('txt-strategic-importance-inline-help'),
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
