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

use Affiliation\Entity\Affiliation;
use Contact\Service\ContactService;
use Deeplink\Entity\Target;
use DoctrineORMModule\Form\Element\EntitySelect;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 *
 */
class DoaReminder extends Form implements InputFilterProviderInterface
{
    /**
     * @param Affiliation    $affiliation
     * @param ContactService $contactService
     */
    public function __construct(
        Affiliation $affiliation,
        ContactService $contactService
    ) {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');

        $contactService->findContactsInAffiliation($affiliation);

        $this->add(
            [
                'type'       => 'Zend\Form\Element\Select',
                'name'       => 'receiver',
                'options'    => [
                    'value_options' => $contactService->toFormValueOptions(),
                    'label'         => _("txt-contact-name"),
                ],
                'attributes' => [
                    'class'    => 'form-control',
                    'id'       => 'receiver',
                    'required' => true,
                ],
            ]
        );

        $this->add(
            [
                'type'       => 'Zend\Form\Element\Text',
                'name'       => 'subject',
                'attributes' => [
                    'label' => _("txt-subject"),
                    'class' => 'form-control',
                ],
            ]
        );

        $this->add(
            [
                'type'       => 'Zend\Form\Element\Textarea',
                'name'       => 'message',
                'attributes' => [
                    'label' => _("txt-message"),
                    'rows'  => 15,
                    'class' => 'form-control',
                ],
            ]
        );

        $this->add(
            [
                'type'       => EntitySelect::class,
                'name'       => 'deeplinkTarget',
                'attributes' => [
                    'label' => _("txt-deeplink-target"),
                ],
                'options'    => [
                    'object_manager'  => $contactService->getEntityManager(),
                    'target_class'    => "Deeplink\Entity\Target",
                    'find_method'     => [
                        'name'   => 'findTargetsWithRoute',
                        'params' => [
                            'criteria' => [],
                            'orderBy'  => [
                                'route' => 'ASC',
                            ],
                        ],
                    ],
                    'label_generator' => function (Target $targetEntity) {
                        return sprintf("%s (%s)", $targetEntity->getTarget(), $targetEntity->getRoute());
                    },
                ],
            ]
        );

        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'submit',
                'attributes' => [
                    'class' => "btn btn-primary",
                    'value' => _("txt-send"),
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
