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

use Affiliation\Entity\Affiliation;
use Contact\Service\ContactService;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Select;
use Contact\Form\Element\Contact;

/**
 * Class EditAssociate
 *
 * @package Affiliation\Form
 */
final class EditAssociate extends Form implements InputFilterProviderInterface
{
    public function __construct(Affiliation $affiliation, ContactService $contactService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');

        //We can transfer the assocate to other affiliations in the project
        $affiliations = [];

        foreach ($affiliation->getProject()->getAffiliation() as $otherAffiliation) {
            $affiliations[$otherAffiliation->getId()] = sprintf(
                '%s (%s) %s',
                $otherAffiliation->getOrganisation()->getOrganisation(),
                $otherAffiliation->getOrganisation()->getCountry(),
                $otherAffiliation->getDateEnd() === null ? '' : ' (deactivated)'
            );
        }

        natcasesort($affiliations);

        $contacts = [];
        foreach ($contactService->findContactsInOrganisation($affiliation->getOrganisation()) as $contact) {
            $contacts[$contact->getId()] = $contact->getFormName();
        }

        $this->add(
            [
                'type'       => Contact::class,
                'name'       => 'contact',
                'options'    => [
                    'value_options' => $contacts,
                    'label'         => _('txt-contact-name'),
                ],
                'attributes' => [
                    'class'    => 'form-control',
                    'required' => true,
                ],
            ]
        );

        $this->add(
            [
                'type'    => Select::class,
                'name'    => 'affiliation',
                'options' => [
                    'value_options' => $affiliations,
                    'label'         => _('txt-partner-name'),
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
                'name'       => 'delete',
                'attributes' => [
                    'class' => 'btn btn-danger',
                    'value' => _('txt-delete'),
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
            'affiliation' => [
                'required' => true,
            ],
        ];
    }
}
