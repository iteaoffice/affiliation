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

use Affiliation\Service\AffiliationService;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 *
 */
class EditAssociate extends Form implements InputFilterProviderInterface
{
    /**
     * EditAssociate constructor.
     * @param AffiliationService $affiliationService
     */
    public function __construct(AffiliationService $affiliationService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');

        //We can transfer the assocate to ohter affiliations in the project
        $affiliations = [];

        foreach ($affiliationService->getAffiliation()->getProject()->getAffiliation() as $affiliation) {
            $affiliations[$affiliation->getId()] = sprintf(
                "%s (%s) %s",
                $affiliation->getOrganisation()->getOrganisation(),
                $affiliation->getOrganisation()->getCountry(),
                is_null($affiliation->getDateEnd()) ? '': ' (deactivated)'
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
