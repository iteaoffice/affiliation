<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */
declare(strict_types=1);

namespace Affiliation\Form;

use Affiliation\Entity;
use Affiliation\Service\AffiliationService;
use Zend\Form\Element\Radio;
use Zend\Form\Element\Select;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Element\Textarea;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use function asort;
use function sprintf;

/**
 * Class Affiliation
 *
 * @package Affiliation\Form
 */
final class Affiliation extends Form implements InputFilterProviderInterface
{
    public function __construct(Entity\Affiliation $affiliation, AffiliationService $affiliationService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');
        $technicalContactValueOptions = [];
        $affiliationValueOptions = [];
        foreach ($affiliationService->parseRenameOptions($affiliation) as $country => $options) {
            $groupOptions = [];
            foreach ($options as $organisationId => $branchAndName) {
                foreach ($branchAndName as $branch => $organisationWithBranch) {
                    $groupOptions[sprintf('%s|%s', $organisationId, $branch)] = $organisationWithBranch;
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
        $organisation = $affiliation->getOrganisation();
        foreach ($organisation->getAffiliation() as $otherAffiliation) {
            if ((null !== $otherAffiliation->getFinancial())
                && null === $otherAffiliation->getFinancial()->getContact()->isActive()
            ) {
                $financialContactValueOptions[$otherAffiliation->getFinancial()->getContact()->getId()]
                    = $otherAffiliation->getFinancial()->getContact()->getFormName();
            }
        }
        asort($financialContactValueOptions);
        $communicationContactValueOptions = $financialContactValueOptions;
        $organisation = $affiliation->getOrganisation();
        foreach ($organisation->getAffiliation() as $otherAffiliation) {
            if ((null !== $otherAffiliation->getCommunication())
                && null === $otherAffiliation->getCommunication()->isActive()
            ) {
                $communicationContactValueOptions[$otherAffiliation->getCommunication()->getId()]
                    = $otherAffiliation->getCommunication()->getFormName();
            }
        }

        $this->add(
            [
                'type'       => Select::class,
                'name'       => 'affiliation',
                'options'    => [
                    'value_options' => $affiliationValueOptions,
                    'label'         => _('txt-change-affiliation'),
                    'help-block'    => _('txt-change-affiliation-help-block'),
                ],
                'attributes' => [
                    'class' => 'form-control',
                ],
            ]
        );
        $this->add(
            [
                'type'       => Select::class,
                'name'       => 'technical',
                'options'    => [
                    'value_options' => $technicalContactValueOptions,
                    'label'         => _('txt-technical-contact'),
                    'help-block'    => _('txt-technical-contact-help-block'),
                ],
                'attributes' => [

                    'class' => 'form-control',
                ],
            ]
        );
        $this->add(
            [
                'type'       => Select::class,
                'name'       => 'financial',
                'options'    => [
                    'value_options' => $technicalContactValueOptions,
                    'label'         => _('txt-financial-contact'),
                    'help-block'    => _('txt-financial-contact-help-block'),
                ],
                'attributes' => [

                    'class' => 'form-control',
                ],
            ]
        );
        $this->add(
            [
                'type'       => Select::class,
                'name'       => 'communication',
                'options'    => [
                    'value_options' => $communicationContactValueOptions,
                    'label'         => _('txt-communication-contact-label'),
                    'help-block'    => _('txt-communication-contact-help-block'),
                ],
                'attributes' => [

                    'class' => 'form-control',
                ],
            ]
        );
        $this->add(
            [
                'type'       => Text::class,
                'name'       => 'valueChain',
                'options'    => [
                    'label'      => _('txt-position-on-value-chain'),
                    'help-block' => _('txt-position-on-value-chain-inline-help'),
                ],
                'attributes' => [
                    'class' => 'form-control',
                ],
            ]
        );
        $this->add(
            [
                'type'       => Textarea::class,
                'name'       => 'mainContribution',
                'options'    => [
                    'label'      => _('txt-main-contributions-and-added-value'),
                    'help-block' => _('txt--main-contribution-for-the-project-inline-help'),
                ],
                'attributes' => [
                    'class' => 'form-control',
                ],
            ]
        );
        $this->add(
            [
                'type'       => Textarea::class,
                'name'       => 'strategicImportance',
                'options'    => [
                    'label'      => _('txt-strategic-importance'),
                    'help-block' => _('txt-strategic-importance-inline-help'),
                ],
                'attributes' => [
                    'class' => 'form-control',
                ],
            ]
        );
        $this->add(
            [
                'type'       => Textarea::class,
                'name'       => 'marketAccess',
                'options'    => [
                    'label'      => _('txt-market-access'),
                    'help-block' => _('txt-market-access-inline-help'),
                ],
                'attributes' => [
                    'cols'  => 8,
                    'class' => 'form-control',
                ],
            ]
        );
        $this->add(
            [
                'type'    => Radio::class,
                'name'    => 'selfFunded',
                'options' => [
                    'value_options' => Entity\Affiliation::getSelfFundedTemplates(),
                    'label'         => _('txt-self-funded'),
                    'help-block'    => _('txt-self-funded-inline-help'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Submit::class,
                'name'       => 'submit',
                'attributes' => [
                    'class' => 'btn btn-primary',
                    'value' => _('txt-update'),
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
        $this->add(
            [
                'type'       => Submit::class,
                'name'       => 'deactivate',
                'attributes' => [
                    'class' => 'btn btn-danger',
                    'value' => sprintf(_('Deactivate %s'), $affiliation->parseBranchedName()),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Submit::class,
                'name'       => 'reactivate',
                'attributes' => [
                    'class' => 'btn btn-warning',
                    'value' => sprintf(_('Reactivate %s'), $affiliation->parseBranchedName()),
                ],
            ]
        );
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'valueChain'  => [
                'required'   => false,
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 0,
                            'max'      => 255,
                        ],
                    ],
                ],
            ],
            'affiliation' => [
                'required' => true,
            ],
            'technical'   => [
                'required' => true,
            ],
            'financial'   => [
                'required' => true,
            ]
        ];
    }
}
