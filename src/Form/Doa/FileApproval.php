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

namespace Affiliation\Form\Doa;

use Contact\Service\ContactService;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Date;
use Zend\Form\Element\Select;
use Zend\Form\Fieldset;
use Zend\Form\Form;

/**
 * Class DoaApproval
 *
 * @package Affiliation\Form
 */
final class FileApproval extends Form
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