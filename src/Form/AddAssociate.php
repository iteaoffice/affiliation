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

use Affiliation\Entity\Affiliation;
use Contact\Service\ContactService;
use Zend\Form\Element\Email;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\Callback;
use Zend\Validator\EmailAddress;

/**
 * Class AddAssociate
 *
 * @package Affiliation\Form
 */
class AddAssociate extends Form implements InputFilterProviderInterface
{
    /**
     * @var array
     */
    protected $extensions = [];

    /**
     * @param Affiliation $affiliation
     * @param ContactService $contactService
     */
    public function __construct(Affiliation $affiliation, ContactService $contactService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');

        $contacts = [];
        foreach ($contactService->findContactsInOrganisation($affiliation->getOrganisation()) as $contact) {
            $contacts[$contact->getId()] = $contact->getFormName();
        }

        //Collect all the existing email addresses in the list
        foreach ($affiliation->getOrganisation()->getContactOrganisation() as $contactOrganisation) {
            $email = $contactOrganisation->getContact()->getEmail();
            $validator = new EmailAddress();
            if ($validator->isValid($email) && !\in_array($validator->hostname,
                    ['hotmail.com', 'gmail.com', 'yahoo.com', 'gmx.de'], true)) {
                $this->extensions[$validator->hostname] = $validator->hostname;
            }
        }

        natcasesort($contacts);

        $this->add(
            [
                'type'       => "Contact\Form\Element\Contact",
                'name'       => 'contact',
                'options'    => [
                    'value_options' => $contacts,
                    'allow_empty'   => true,
                    'empty_option'  => '--' . _("txt-add-email-via-known-contacts-in-organisation"),
                    'label'         => _("txt-add-associate-by-known-contact-label"),
                    'help-block'    => _("txt-add-associate-by-known-contact-help-block"),
                ],
                'attributes' => [
                    'class' => 'form-control',
                ],
            ]
        );

        $this->add(
            [
                'type'       => Email::class,
                'name'       => 'email',
                'options'    => [
                    'label'      => _("txt-email-address"),
                    'help-block' => sprintf(_("txt-add-associate-by-email-address-contact-help-block-extensions-%s"),
                        implode(', ', $this->extensions)),
                ],
                'attributes' => [
                    'class' => 'form-control',

                ],
            ]
        );

        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'submit',
                'attributes' => [
                    'class' => "btn btn-primary",
                    'value' => _("txt-submit"),
                ],
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'addKnownContact',
                'attributes' => [
                    'class' => "btn btn-primary",
                    'value' => _("txt-add-known-contact"),
                ],
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'addEmail',
                'attributes' => [
                    'class' => "btn btn-primary",
                    'value' => _("txt-add-via-company-email"),
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
    public function getInputFilterSpecification(): array
    {
        return [
            'contact' => [
                'required' => false,
                'validators' => [
                    new \Zend\Validator\NotEmpty(\Zend\Validator\NotEmpty::INTEGER),
                ],
            ],
            'email'   => [
                'required'   => false,
                'validators' => [
                    new \Zend\Validator\NotEmpty(\Zend\Validator\NotEmpty::NULL),
                    new Callback(
                        [
                            'messages' => [
                                Callback::INVALID_VALUE => 'The given email address (%value%) should have one of the domains ' . implode(', ',
                                        $this->extensions),
                            ],
                            'callback' => function ($value) {
                                $validator = new EmailAddress();
                                if (!$validator->isValid($value)) {
                                    return false;
                                }

                                return \in_array($validator->hostname, $this->extensions, true);
                            },
                        ]
                    ),
                ],
            ],
        ];
    }
}
