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

use Contact\Service\ContactService;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Class DoaApproval
 *
 * @package Affiliation\Form
 */
class DoaApproval extends Form implements InputFilterProviderInterface
{
    public function __construct(ArrayCollection $doaList, ContactService $contactService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');

        /*
         * Create a fieldSet per DOA (and affiliation)
         */
        foreach ($doaList as $doa) {
            $affiliationFieldset = new Fieldset('affiliation_' . $doa->getAffiliation()->getId());

            $contacts = $contactService->findContactsInAffiliation($doa->getAffiliation());
            $affiliationFieldset->add(
                [
                    'type'       => 'Zend\Form\Element\Select',
                    'name'       => 'contact',
                    'options'    => [
                        'value_options' => $contactService->toFormValueOptions($contacts['contacts']),
                        'label'         => _("txt-contact-name"),
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
                    'type'       => 'Zend\Form\Element\Text',
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

        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'submit',
                'attributes' => [
                    'class' => "btn btn-primary",
                    'value' => _("txt-update"),
                ],
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'cancel',
                'attributes' => [
                    'class' => "btn btn-warning",
                    'value' => _("txt-cancel"),
                ],
            ]
        );
    }

    public function getInputFilterSpecification(): array
    {
        return [];
    }
}
