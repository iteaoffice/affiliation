<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Form;

use Affiliation\Entity\Affiliation;
use Contact\Service\ContactService;
use Deeplink\Entity\Target;
use Doctrine\ORM\EntityManager;
use DoctrineORMModule\Form\Element\EntitySelect;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 *
 */
class LoiReminder extends Form implements InputFilterProviderInterface
{
    /**
     * LoiReminder constructor.
     *
     * @param Affiliation    $affiliation
     * @param ContactService $contactService
     * @param EntityManager  $entityManager
     */
    public function __construct(
        Affiliation $affiliation,
        ContactService $contactService,
        EntityManager $entityManager
    ) {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');

        $contacts = $contactService->findContactsInAffiliation($affiliation);

        $this->add([
            'type'       => 'Zend\Form\Element\Select',
            'name'       => 'receiver',
            'options'    => [
                'value_options' => $contactService->toFormValueOptions($contacts['contacts']),
                'label'         => _("txt-contact-name"),
            ],
            'attributes' => [
                'class'    => 'form-control',
                'id'       => 'receiver',
                'required' => true,
            ],
        ]);

        $this->add([
            'type'       => EntitySelect::class,
            'name'       => 'deeplinkTarget',
            'attributes' => [
                'label' => _("txt-deeplink-target"),
            ],
            'options'    => [
                'object_manager'  => $entityManager,
                'target_class'    => Target::class,
                'find_method'     => [
                    'name'   => 'findBy',
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
        ]);

        $this->add([
            'type'       => 'Zend\Form\Element\Text',
            'name'       => 'subject',
            'attributes' => [
                'label' => _("txt-subject"),
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'type'       => 'Zend\Form\Element\Textarea',
            'name'       => 'message',
            'attributes' => [
                'label' => _("txt-message"),
                'rows'  => 15,
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'type'       => 'Zend\Form\Element\Submit',
            'name'       => 'submit',
            'attributes' => [
                'class' => "btn btn-primary",
                'value' => _("txt-send"),
            ],
        ]);
        $this->add([
            'type'       => 'Zend\Form\Element\Submit',
            'name'       => 'cancel',
            'attributes' => [
                'class' => "btn btn-warning",
                'value' => _("txt-cancel"),
            ],
        ]);
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