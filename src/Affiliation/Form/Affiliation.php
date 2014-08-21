<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Project
 * @package     Form
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Affiliation\Form;

use Affiliation\Service\AffiliationService;
use Organisation\Entity\Financial;
use Zend\Form\Form;

/**
 *
 */
class Affiliation extends Form
{
    /**
     * @param AffiliationService $affiliationService
     */
    public function __construct(AffiliationService $affiliationService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        $technicalContactValueOptions = [];
        $affiliationValueOptions      = [];
        foreach ($affiliationService->parseRenameOptions() as $country => $options) {
            $groupOptions = [];
            foreach ($options as $organisationId => $branchAndName) {
                foreach ($branchAndName as $branch => $organisationWithBranch) {
                    $groupOptions[sprintf("%s|%s", $organisationId, $branch)] = $organisationWithBranch;
                }
            }
            $affiliationValueOptions[$country] = [
                'label'   => $country,
                'options' => $groupOptions
            ];
        }
        /**
         * Collect the technical contacts
         */
        $technicalContactValueOptions[$affiliationService->getAffiliation()->getContact()->getId()] =
            $affiliationService->getAffiliation()->getContact()->getDisplayName();
        foreach ($affiliationService->getAffiliation()->getAssociate() as $contact) {
            $technicalContactValueOptions[$contact->getId()] = $contact->getDisplayName();
        }
        /**
         * Collect the financial contacts
         * This array starts from the technical contacts
         */
        $financialContactValueOptions = $technicalContactValueOptions;
        $organisation                 = $affiliationService->getAffiliation()->getOrganisation();
        foreach ($organisation->getAffiliation() as $affiliation) {
            if (!is_null($affiliation->getFinancial())) {
                $financialContactValueOptions[$affiliation->getFinancial()->getContact()->getId()] =
                    $affiliation->getFinancial()->getContact()->getDisplayName();
            }
        }
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Select',
                'name'       => 'affiliation',
                'options'    => [
                    'value_options' => $affiliationValueOptions,
                    'label'         => _("txt-change-affiliation"),
                ],
                'attributes' => [
                    'class'    => 'form-control',
                    'required' => true,
                ]
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Select',
                'name'       => 'technical',
                'options'    => [
                    'value_options' => $technicalContactValueOptions,
                    'label'         => _("txt-technical-contact"),
                ],
                'attributes' => [
                    'class'    => 'form-control',
                    'required' => true,
                ]
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Select',
                'name'       => 'financial',
                'options'    => [
                    'value_options' => $financialContactValueOptions,
                    'label'         => _("txt-financial-contact"),
                ],
                'attributes' => [
                    'class'    => 'form-control',
                    'required' => true,
                ]
            ]
        );
        $organisationFinancial = new Financial();
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Radio',
                'name'       => 'preferredDelivery',
                'options'    => [
                    'value_options' => $organisationFinancial->getEmailTemplates(),
                    'label'         => _("txt-preferred-delivery"),
                ],
                'attributes' => [
                    'class'    => 'form-control',
                    'required' => true,
                ]
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Text',
                'name'       => 'valueChain',
                'options'    => [
                    'label' => _("txt-position-on-value-chain"),
                ],
                'attributes' => [
                    'class'    => 'form-control',
                    'required' => true,
                ]
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'submit',
                'attributes' => [
                    'class' => "btn btn-primary",
                    'value' => _("txt-update")
                ]
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'cancel',
                'attributes' => [
                    'class' => "btn btn-warning",
                    'value' => _("txt-cancel")
                ]
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'deactivate',
                'attributes' => [
                    'class' => "btn btn-danger",
                    'value' => sprintf(
                        _("txt-deactivate-partner-%s"),
                        $affiliationService->getAffiliation()->getOrganisation()->getOrganisation()
                    )
                ]
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'reactivate',
                'attributes' => [
                    'class' => "btn btn-warning",
                    'value' => sprintf(
                        _("txt-reactivate-partner-%s"),
                        $affiliationService->getAffiliation()->getOrganisation()->getOrganisation()
                    )
                ]
            ]
        );
    }
}
