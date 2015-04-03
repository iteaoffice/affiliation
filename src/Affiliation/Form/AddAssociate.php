<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */

namespace Affiliation\Form;

use Affiliation\Service\AffiliationService;
use Contact\Service\ContactService;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 *
 */
class AddAssociate extends Form implements InputFilterProviderInterface
{
    /**
     * @param AffiliationService $affiliationService
     * @param ContactService     $contactService
     */
    public function __construct(AffiliationService $affiliationService, ContactService $contactService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');

        $contacts = [];
        foreach ($contactService->findContactsInOrganisation(
            $affiliationService->getAffiliation()->getOrganisation()
        ) as $contact) {
            $contacts[$contact->getId()] = $contact->getFormName();
        }

        $this->add(
            [
                'type'       => 'Zend\Form\Element\Select',
                'name'       => 'contact',
                'options'    => [
                    'value_options' => $contacts,
                    'label'         => _("txt-contact-name"),
                ],
                'attributes' => [
                    'class'    => 'form-control',
                    'required' => true,
                ],
            ]
        );

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

    /**
     * Should return an array specification compatible with
     * {@link Zend\InputFilter\Factory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [
            'contact' => [
                'required' => true,
            ],
        ];
    }
}
