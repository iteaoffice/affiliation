<?php

/**
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */

declare(strict_types=1);

namespace Affiliation\Form;

use Contact\Entity\Contact;
use Contact\Service\ContactService;
use Laminas\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

/**
 * Class ManageTechnicalContact
 *
 * @package Project\Form
 */
final class ManageTechnicalContact extends Form\Form implements InputFilterProviderInterface
{
    public function __construct(ContactService $contactService, \Affiliation\Entity\Affiliation $affiliation, Contact $contact)
    {
        parent::__construct();

        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');

        $valueOptions                    = [];
        $valueOptions[$contact->getId()] = $contact->getFormName();

        foreach ($contactService->findContactsInAffiliation($affiliation)['contacts'] as $projectContact) {
            $valueOptions[$projectContact->getId()] = ucfirst($projectContact->getFormName());
        }

        // Perform the sort:
        asort($valueOptions);

        $this->add(
            [
                'type'       => Form\Element\Select::class,
                'name'       => 'technicalContact',
                'options'    => [
                    'value_options' => $valueOptions,
                    'label'         => _('txt-manage-technical-contact-technical-contact-label'),
                    'help-block'    => _('txt-manage-technical-contact-technical-contact-help-block'),
                ],
                'attributes' => [
                    'class' => 'form-control',
                ],
            ]
        );

        $this->add(
            [
                'type'    => Form\Element\MultiCheckbox::class,
                'name'    => 'proxyTechnicalContact',
                'options' => [
                    'value_options' => $valueOptions,
                    'label'         => _('txt-manage-technical-contact-proxy-technical-contact-label'),
                    'help-block'    => _('txt-manage-technical-contact-proxy-technical-contact-help-block'),
                ],
            ]
        );

        $this->add(
            [
                'type'       => Form\Element\Submit::class,
                'name'       => 'submit',
                'attributes' => [
                    'class' => 'btn btn-primary',
                    'value' => _('txt-save'),
                ],
            ]
        );

        $this->add(
            [
                'type'       => Form\Element\Submit::class,
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
            'proxyTechnicalContact' => [
                'required' => false
            ]
        ];
    }
}
