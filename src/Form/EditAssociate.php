<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */
declare(strict_types = 1);

namespace Affiliation\Form;

use Affiliation\Entity\Affiliation;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Class EditAssociate
 *
 * @package Affiliation\Form
 */
class EditAssociate extends Form implements InputFilterProviderInterface
{
    /**
     * EditAssociate constructor.
     *
     * @param Affiliation $affiliation
     */
    public function __construct(Affiliation $affiliation)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');

        //We can transfer the assocate to other affiliations in the project
        $affiliations = [];

        foreach ($affiliation->getProject()->getAffiliation() as $otherAffiliation) {
            $affiliations[$otherAffiliation->getId()] = sprintf(
                "%s (%s) %s",
                $otherAffiliation->getOrganisation()->getOrganisation(),
                $otherAffiliation->getOrganisation()->getCountry(),
                is_null($otherAffiliation->getDateEnd()) ? '' : ' (deactivated)'
            );
        }

        asort($affiliations);

        $this->add(
            [
                'type'    => 'Zend\Form\Element\Select',
                'name'    => 'affiliation',
                'options' => [
                    'value_options' => $affiliations,
                    'label'         => _("txt-partner-name"),
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
                'name'       => 'delete',
                'attributes' => [
                    'class' => "btn btn-danger",
                    'value' => _("txt-delete"),
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
            'affiliation' => [
                'required' => true,
            ],
        ];
    }
}
