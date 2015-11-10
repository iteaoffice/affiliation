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
use Organisation\Service\OrganisationService;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 *
 */
class AdminAffiliation extends Form implements InputFilterProviderInterface
{
    /**
     * AdminAffiliation constructor.
     * @param AffiliationService $affiliationService
     * @param OrganisationService $organisationService
     */
    public function __construct(AffiliationService $affiliationService, OrganisationService $organisationService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');

        $technicalContactValueOptions = [];

        /*
         * Collect the technical contacts
         */
        $technicalContactValueOptions[$affiliationService->getAffiliation()->getContact()->getId()] =
            $affiliationService->getAffiliation()->getContact()->getFormName();
        foreach ($affiliationService->getAffiliation()->getAssociate() as $contact) {
            $technicalContactValueOptions[$contact->getId()] = $contact->getFormName();
        }
        asort($technicalContactValueOptions);
        /*
         * Collect the financial contacts
         * This array starts from the technical contacts
         */
        $financialContactValueOptions = $technicalContactValueOptions;
        $organisation = $affiliationService->getAffiliation()->getOrganisation();
        foreach ($organisation->getAffiliation() as $affiliation) {
            if (!is_null($affiliation->getFinancial())) {
                $financialContactValueOptions[$affiliation->getFinancial()->getContact()->getId()] =
                    $affiliation->getFinancial()->getContact()->getFormName();
            }
        }

        asort($financialContactValueOptions);

        $this->add(
            [
                'type'    => 'Zend\Form\Element\Text',
                'name'    => 'branch',
                'options' => [
                    'label'      => _("txt-branch"),
                    'help-block' => _("txt-branch-inline-help"),
                ],
            ]
        );

        $this->add(
            [
                'type'    => 'Zend\Form\Element\Date',
                'name'    => 'dateEnd',
                'options' => [
                    'label'      => _("txt-date-removed"),
                    'help-block' => _("txt-date-removed-inline-help"),
                ],
            ]
        );

        $this->add(
            [
                'type'    => 'Zend\Form\Element\Date',
                'name'    => 'dateSelfFunded',
                'options' => [
                    'label'      => _("txt-date-self-funded"),
                    'help-block' => _("txt-date-self-funded-inline-help"),
                ],
            ]
        );


        $this->add(
            [
                'type'    => 'Zend\Form\Element\Select',
                'name'    => 'contact',
                'options' => [
                    'value_options' => $technicalContactValueOptions,
                    'label'         => _("txt-technical-contact"),
                ],
            ]
        );


        $this->add(
            [
                'type'       => 'Zend\Form\Element\Text',
                'name'       => 'valueChain',
                'options'    => [
                    'label'      => _("txt-position-on-value-chain"),
                    'help-block' => _("txt-position-on-value-chain-inline-help"),
                ],
                'attributes' => [
                    'class' => 'form-control',
                ],
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Textarea',
                'name'       => 'mainContribution',
                'options'    => [
                    'label'      => _("txt-main-contribution-for-the-project"),
                    'help-block' => _("txt--main-contribution-for-the-project-inline-help"),
                ],
                'attributes' => [
                    'class' => 'form-control',
                ],
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Textarea',
                'name'       => 'marketAccess',
                'options'    => [
                    'label'      => _("txt-market-access"),
                    'help-block' => _("txt-market-access-inline-help"),
                ],
                'attributes' => [
                    'cols'  => 8,
                    'class' => 'form-control',
                ],
            ]
        );


        $this->add(
            [
                'type'       => 'Zend\Form\Element\Select',
                'name'       => 'financialContact',
                'options'    => [
                    'value_options' => $financialContactValueOptions,
                    'label'         => _("txt-financial-contact"),
                ],
                'attributes' => [
                    'class' => 'form-control',
                ],
            ]
        );

        $this->add(
            [
                'type'    => 'Zend\Form\Element\Text',
                'name'    => 'financialBranch',
                'options' => [
                    'label'      => _("txt-branch"),
                    'help-block' => _("txt-branch-inline-help"),
                ],
            ]
        );


        $financialOrganisation = [];
        if (!is_null($financial = $affiliationService->getAffiliation()->getFinancial())) {
            $financialOrganisation[$financial->getOrganisation()->getId()] = $financial->getOrganisation()->getOrganisation();
        }

        $this->add(
            [
                'type'    => 'Zend\Form\Element\Select',
                'name'    => 'financialOrganisation',
                'options' => [
                    'value_options' => $financialOrganisation,
                    'label'         => _("txt-financial-organisation"),
                    'help-block'    => _("txt-financial-organisation-help"),
                ],
            ]
        );

        $this->add(
            [
                'type'    => 'Zend\Form\Element\Email',
                'name'    => 'emailCC',
                'options' => [
                    'label'      => _("txt-email-cc"),
                    'help-block' => _("txt-email-cc-inline-help"),
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
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'deactivate',
                'attributes' => [
                    'class' => "btn btn-danger",
                    'value' => sprintf(
                        _("Deactivate %s"),
                        $affiliationService->getAffiliation()->getOrganisation()->getOrganisation()
                    ),
                ],
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'reactivate',
                'attributes' => [
                    'class' => "btn btn-warning",
                    'value' => sprintf(
                        _("Reactivate %s"),
                        $affiliationService->getAffiliation()->getOrganisation()->getOrganisation()
                    ),
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
            'dateEnd'               => [
                'required' => false,
            ],
            'dateSelfFunded'        => [
                'required' => false,
            ],
            'financialOrganisation' => [
                'required' => false,
            ],
            'financialContact'      => [
                'required' => false,
            ],
            'emailCC'               => [
                'required' => false,
            ],
        ];
    }
}
