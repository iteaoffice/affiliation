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

use Affiliation\Entity;
use Affiliation\Service\AffiliationService;
use Zend\Form\Form;

/**
 * Class Affiliation
 *
 * @package Affiliation\Form
 */
class Affiliation extends Form
{
    /**
     * Affiliation constructor.
     *
     * @param Entity\Affiliation $affiliation
     * @param AffiliationService $affiliationService
     */
    public function __construct(Entity\Affiliation $affiliation, AffiliationService $affiliationService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');
        $technicalContactValueOptions = [];
        $affiliationValueOptions      = [];
        foreach ($affiliationService->parseRenameOptions($affiliation) as $country => $options) {
            $groupOptions = [];
            foreach ($options as $organisationId => $branchAndName) {
                foreach ($branchAndName as $branch => $organisationWithBranch) {
                    $groupOptions[sprintf("%s|%s", $organisationId, $branch)] = $organisationWithBranch;
                }
            }
            $affiliationValueOptions[$country] = [
                'label'   => $country,
                'options' => $groupOptions,
            ];
        }
        /*
         * Collect the technical contacts
         */
        $technicalContactValueOptions[$affiliation->getContact()->getId()]
            = $affiliation->getContact()->getFormName();
        foreach ($affiliation->getAssociate() as $contact) {
            $technicalContactValueOptions[$contact->getId()] = $contact->getFormName();
        }
        asort($technicalContactValueOptions);
        /*
         * Collect the financial contacts
         * This array starts from the technical contacts
         */
        $financialContactValueOptions = $technicalContactValueOptions;
        $organisation                 = $affiliation->getOrganisation();
        foreach ($organisation->getAffiliation() as $affiliation) {
            if (! is_null($affiliation->getFinancial())) {
                $financialContactValueOptions[$affiliation->getFinancial()->getContact()->getId()]
                    = $affiliation->getFinancial()->getContact()->getFormName();
            }
        }

        asort($financialContactValueOptions);

        $this->add(
            [
                'type'       => 'Zend\Form\Element\Select',
                'name'       => 'affiliation',
                'options'    => [
                    'value_options' => $affiliationValueOptions,
                    'label'         => _("txt-change-affiliation"),
                ],
                'attributes' => [
                    'class' => 'form-control',
                ],
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
                    'class' => 'form-control',
                ],
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
                    'class' => 'form-control',
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
                    'label'      => _("txt-main-contributions-and-added-value"),
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
                'name'       => 'strategicImportance',
                'options'    => [
                    'label'      => _("txt-strategic-importance"),
                    'help-block' => _("txt-strategic-importance-inline-help"),
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
                'type'    => 'Zend\Form\Element\Radio',
                'name'    => 'selfFunded',
                'options' => [
                    'value_options' => Entity\Affiliation::getSelfFundedTemplates(),
                    'label'         => _("txt-self-funded"),
                    'help-block'    => _("txt-self-funded-inline-help"),
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
                    'value' => sprintf(_("Deactivate %s"), $affiliation->parseBranchedName()),
                ],
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'reactivate',
                'attributes' => [
                    'class' => "btn btn-warning",
                    'value' => sprintf(_("Reactivate %s"), $affiliation->parseBranchedName()),
                ],
            ]
        );
    }
}
