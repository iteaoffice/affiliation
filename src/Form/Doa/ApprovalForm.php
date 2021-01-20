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

use Contact\Service\ContactService;
use Doctrine\Common\Collections\ArrayCollection;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Select;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

/**
 * Class DoaApproval
 *
 * @package Affiliation\Form
 */
final class ApprovalForm extends Form
{
    public function __construct(ArrayCollection $doaList, ContactService $contactService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');

        $this->add(
            [
                'type'       => Checkbox::class,
                'name'       => 'sendMail',
                'attributes' => [
                    'id' => 'send-mail-checkbox',
                ]
            ]
        );

        foreach ($doaList as $doa) {
            $affiliationFieldset = new Fieldset('affiliation_' . $doa->getAffiliation()->getId());

            $contacts = $contactService->findContactsInAffiliation($doa->getAffiliation());
            $affiliationFieldset->add(
                [
                    'type'       => Select::class,
                    'name'       => 'contact',
                    'options'    => [
                        'value_options' => $contactService->toFormValueOptions($contacts['contacts']),
                        'label'         => _('txt-contact-name'),
                    ],
                    'attributes' => [
                        'class'    => 'form-control',
                        'id'       => 'contact-' . $doa->getId(),
                        'required' => true,
                    ],
                ]
            );

            $affiliationFieldset->add(
                [
                    'type'       => Date::class,
                    'name'       => 'dateSigned',
                    'attributes' => [
                        'class'    => 'form-control',
                        'id'       => 'dateSigned-' . $doa->getId(),
                        'required' => true,
                    ],
                ]
            );

            $this->add($affiliationFieldset);
        }
    }
}
