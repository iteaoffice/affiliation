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
use Contact\Form\Element\Contact;
use Contact\Service\ContactService;
use Laminas\Form\Element\Email;
use Laminas\Form\Element\Submit;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Uri\Uri;
use Laminas\Validator\Callback;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\NotEmpty;
use function in_array;

/**
 * Class AddAssociate
 *
 * @package Affiliation\Form
 */
final class AddAssociate extends Form implements InputFilterProviderInterface
{
    protected $extensions = [];

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
            if ($validator->isValid($email)
                && ! in_array(
                    $validator->hostname,
                    ['hotmail.com', 'gmail.com', 'yahoo.com', 'gmx.de'],
                    true
                )
            ) {
                $this->extensions[$validator->hostname] = $validator->hostname;
            }
        }

        foreach ($affiliation->getOrganisation()->getWeb() as $web) {
            $validator = new Uri();

            //Strip any www.
            $web = str_replace('www.', '', $web->getWeb());

            $parse = $validator->parse($web);

            $this->extensions[$parse->getHost()] = $parse->getHost();
        }

        natcasesort($contacts);

        $this->add(
            [
                'type'       => Contact::class,
                'name'       => 'contact',
                'options'    => [
                    'value_options' => $contacts,
                    'allow_empty'   => true,
                    'empty_option'  => '-- ' . ('Select here the known contact'),
                    'label'         => _('txt-add-associate-by-known-contact-label'),
                    'help-block'    => _('txt-add-associate-by-known-contact-help-block'),
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
                    'label'      => _('txt-company-email-address'),
                    'help-block' => sprintf(
                        'Here you can add an associate via the company email address. The email address should have (one of) the following extension(s): %s',
                        implode(', ', $this->extensions)
                    ),
                ],
                'attributes' => [
                    'class' => 'form-control',

                ],
            ]
        );

        $this->add(
            [
                'type'       => Submit::class,
                'name'       => 'submit',
                'attributes' => [
                    'class' => 'btn btn-primary',
                    'value' => _('txt-submit'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Submit::class,
                'name'       => 'addKnownContact',
                'attributes' => [
                    'class' => 'btn btn-primary',
                    'value' => _('txt-add-known-contact'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Submit::class,
                'name'       => 'addEmail',
                'attributes' => [
                    'class' => 'btn btn-primary',
                    'value' => _('txt-add-via-company-email'),
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
            'contact' => [
                'required'   => false,
                'validators' => [
                    new NotEmpty(NotEmpty::INTEGER),
                ],
            ],
            'email'   => [
                'required'   => false,
                'validators' => [
                    new NotEmpty(NotEmpty::NULL),
                    new Callback(
                        [
                            'messages' => [
                                Callback::INVALID_VALUE => 'The given email address (%value%) should have (one of) the extension(s): '
                                    . implode(
                                        ', ',
                                        $this->extensions
                                    ),
                            ],
                            'callback' => function ($value) {
                                $validator = new EmailAddress();
                                if (! $validator->isValid($value)) {
                                    return false;
                                }

                                return in_array($validator->hostname, $this->extensions, true);
                            },
                        ]
                    ),
                ],
            ],
        ];
    }
}
