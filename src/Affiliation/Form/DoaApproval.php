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

use Affiliation\Entity\Doa;
use Contact\Service\ContactService;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 *
 */
class DoaApproval extends Form implements InputFilterProviderInterface
{
    /**
     * @param Doa[] $doa
     */
    public function __construct(array $doa, ContactService $contactService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');

        /*
         * Create a fieldSet per DOA (and affiliation)
         */
        foreach ($doa as $doa) {
            $affiliationFieldset = new Fieldset('affiliation_'.$doa->getAffiliation()->getId());

            $contactService->findContactsInAffiliation($doa->getAffiliation());
            $affiliationFieldset->add(
                [
                    'type'       => 'Zend\Form\Element\Select',
                    'name'       => 'contact',
                    'options'    => [
                        'value_options' => $contactService->toFormValueOptions(),
                        'label'         => _("txt-contact-name"),
                    ],
                    'attributes' => [
                        'class'    => 'form-control',
                        'id'       => 'contact-'.$doa->getId(),
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
                        'id'       => 'dateSigned-'.$doa->getId(),
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

    /**
     * Should return an array specification compatible with
     * {@link Zend\InputFilter\Factory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [];
    }
}
