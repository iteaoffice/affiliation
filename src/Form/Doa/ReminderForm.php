<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Form\Doa;

use Affiliation\Entity\Affiliation;
use Contact\Service\ContactService;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Submit;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

/**
 * Class ReminderForm
 * @package Affiliation\Form\Doa
 */
final class ReminderForm extends Form implements InputFilterProviderInterface
{
    public function __construct(Affiliation $affiliation, ContactService $contactService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');

        $contacts = $contactService->findContactsInAffiliation($affiliation);

        $this->add(
            [
                'type'    => Select::class,
                'name'    => 'receiver',
                'options' => [
                    'value_options' => $contactService->toFormValueOptions($contacts['contacts']),
                    'label'         => _('txt-doa-reminder-receiver-label'),
                    'help-block'    => _('txt-doa-reminder-receiver-help-block'),
                ],
            ]
        );

        $this->add(
            [
                'type'       => Submit::class,
                'name'       => 'submit',
                'attributes' => [
                    'class' => 'btn btn-primary',
                    'value' => _('txt-send'),
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
            'receiver' => [
                'required' => true
            ]
        ];
    }
}
