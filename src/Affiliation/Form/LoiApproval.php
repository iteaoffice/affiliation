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

use Affiliation\Entity\Loi;
use Contact\Service\ContactService;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 *
 */
class LoiApproval extends Form implements InputFilterProviderInterface
{
    /**
     * @param Loi[] $lois
     * @param ContactService $contactService
     */
    public function __construct(array $lois, ContactService $contactService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');

        /*
         * Create a fieldSet per LOI (and affiliation)
         */
        foreach ($lois as $loi) {
            $affiliationFieldset = new Fieldset('affiliation_' . $loi->getAffiliation()->getId());

            $contactService->findContactsInAffiliation($loi->getAffiliation());
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
                        'id'       => 'contact-' . $loi->getId(),
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
                        'id'       => 'dateSigned-' . $loi->getId(),
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
