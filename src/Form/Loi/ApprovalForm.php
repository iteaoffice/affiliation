<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Form\Loi;

use Contact\Service\ContactService;
use Doctrine\Common\Collections\ArrayCollection;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Submit;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

/**
 * Class ApprovalForm
 * @package Affiliation\Form\Loi
 */
final class ApprovalForm extends Form implements InputFilterProviderInterface
{
    public function __construct(ArrayCollection $lois, ContactService $contactService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');

        foreach ($lois as $loi) {
            $affiliationFieldset = new Fieldset('affiliation_' . $loi->getAffiliation()->getId());

            $contacts = $contactService->findContactsInAffiliation($loi->getAffiliation());
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
                        'id'       => 'contact-' . $loi->getId(),
                        'required' => true,
                    ],
                ]
            );

            $affiliationFieldset->add(
                [
                    'type'       => Date::class,
                    'name'       => 'dateSigned',
                    'attributes' => [
                        'id'       => 'dateSigned-' . $loi->getId(),
                        'class'    => 'form-control',
                        'required' => true,
                    ],
                ]
            );

            $this->add($affiliationFieldset);
        }

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
        return [];
    }
}
