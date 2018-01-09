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

use Affiliation\Entity\Affiliation;
use DoctrineORMModule\Form\Element\EntitySelect;
use Invoice\Entity\Method;
use Organisation\Entity\OParent;
use Organisation\Service\ParentService;
use Zend\Form\Element\Radio;
use Zend\Form\Element\Select;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 *
 */
class AdminAffiliation extends Form implements InputFilterProviderInterface
{
    /**
     * AdminAffiliation constructor.
     * @param Affiliation $affiliation
     * @param ParentService $parentService
     */
    public function __construct(Affiliation $affiliation, ParentService $parentService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');


        $this->add(
            [
                'type'    => 'Organisation\Form\Element\Organisation',
                'name'    => 'organisation',
                'options' => [
                    'label'      => _("txt-organisation"),
                    'help-block' => _("txt-edit-affiliation-organisation-help-block"),
                ],
            ]
        );

        // Try to find parentOrganisations based on the 'name' of the original organisation
        $parentOrganisationLike = $parentService->findParentOrganisationByNameLike($affiliation->getOrganisation()->getOrganisation());
        $parentOrganisations = ['' => '-- None of the options'];
        foreach ($parentOrganisationLike as $parentOrganisation) {
            $parentOrganisations[$parentOrganisation->getId()] = sprintf(
                '%s (%s)',
                $parentOrganisation->getOrganisation(),
                $parentOrganisation->getOrganisation()->getCountry()->getIso3()
            );
        }

        $this->add(
            [
                'type'       => Radio::class,
                'name'       => 'parentOrganisationLike',
                'attributes' => [
                    'label' => _("txt-suggested-parent-organisation"),

                ],
                'options'    => [
                    'empty_option'  => '--' . "Find a parent organisation",
                    'allow_empty'   => true,
                    'value_options' => $parentOrganisations,
                    'help-block'    => _("txt-suggested-affiliation-parent-organisation-help-block"),
                ],
            ]
        );

        // Create a list of all parentOrganisations
        /** @var OParent[] $parents */
        $parents = $parentService->findAll(OParent::class);

        $parentsAndOrganisations = [];
        $parentOptions = [];

        foreach ($parents as $parent) {
            $parentOrganisations = [];
            foreach ($parent->getParentOrganisation() as $parentOrganisation) {
                $parentOrganisations[$parentOrganisation->getId()] = sprintf(
                    '%s (%s)',
                    $parentOrganisation->getOrganisation(),
                    $parentOrganisation->getOrganisation()->getCountry()->getIso3()
                );
            }
            asort($parentOrganisations);

            // Only add the parent to the array if there are 1 or more organisations in the parent
            if (\count($parentOrganisations) > 0) {
                $parentsAndOrganisations[$parent->getOrganisation()->getOrganisation()] = [
                    'label' => $parent->getOrganisation()->getOrganisation()
                ];
                $parentsAndOrganisations[$parent->getOrganisation()->getOrganisation()]['options'] = $parentOrganisations;
            }

            $parentOptions[$parent->getId()] = sprintf(
                '%s (%s)',
                $parent->getOrganisation(),
                $parent->getOrganisation()->getCountry()->getIso3()
            );
        }

        ksort($parentsAndOrganisations);
        asort($parentOptions);

        $this->add(
            [
                'type'       => Select::class,
                'name'       => 'parentOrganisation',
                'attributes' => [
                    'label' => _("txt-parent-organisation"),

                ],
                'options'    => [
                    'empty_option'  => '--' . "Find a parent organisation",
                    'allow_empty'   => true,
                    'value_options' => $parentsAndOrganisations,
                    'help-block'    => _("txt-affiliation-parent-organisation-help-block"),
                ],
            ]
        );

        $this->add(
            [
                'type'       => Select::class,
                'name'       => 'parent',
                'attributes' => [
                    'label' => _("txt-parent"),

                ],
                'options'    => [
                    'empty_option'  => '--' . "Find a parent",
                    'allow_empty'   => true,
                    'value_options' => $parentOptions,
                    'help-block'    => _("txt-affiliation-parent-help-block"),
                ],
            ]
        );

        $this->add(
            [
                'type'    => 'Zend\Form\Element\Checkbox',
                'name'    => 'createParentFromOrganisation',
                'options' => [
                    'label'      => _("txt-create-parent-from-organisation-label"),
                    'help-block' => _("txt-create-parent-from-organisation-help-block"),
                ],
            ]
        );

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
                'type'    => 'Contact\Form\Element\Contact',
                'name'    => 'contact',
                'options' => [
                    'label'      => _("txt-technical-contact"),
                    'help-block' => _("txt-edit-affiliation-technical-contact-help-block"),
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
                'type'       => 'Contact\Form\Element\Contact',
                'name'       => 'financialContact',
                'options'    => [
                    'label' => _("txt-financial-contact"),
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

        $this->add(
            [
                'type'    => 'Organisation\Form\Element\Organisation',
                'name'    => 'financialOrganisation',
                'options' => [
                    'label'      => _("txt-financial-organisation"),
                    'help-block' => _("txt-financial-organisation-help"),
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
                'type'    => EntitySelect::class,
                'name'    => 'invoiceMethod',
                'options' => [
                    'target_class'   => Method::class,
                    'allow_empty'    => true,
                    'empty_option'   => _("txt-choose-an-invoice-method"),
                    'object_manager' => $parentService->getEntityManager(),
                    'label'          => _("txt-invoice-method"),
                    'find_method'    => [
                        'name'   => 'findAll',
                        'params' => [
                            'criteria' => [],
                            'orderBy'  => ['method' => 'ASC'],
                        ],
                    ],
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
                'name'       => 'delete',
                'attributes' => [
                    'class' => "btn btn-danger",
                    'value' => sprintf(_("Delete %s"), $affiliation->parseBranchedName()),
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
    public function getInputFilterSpecification(): array
    {
        return [
            'parent'                       => [
                'required' => false,
            ],
            'parentOrganisation'           => [
                'required' => false,
            ],
            'parentOrganisationLike'       => [
                'required' => false,
            ],
            'createParentFromOrganisation' => [
                'required' => false,
            ],
            'dateEnd'                      => [
                'required' => false,
            ],
            'dateSelfFunded'               => [
                'required' => false,
            ],
            'financialOrganisation'        => [
                'required' => false,
            ],
            'financialContact'             => [
                'required' => false,
            ],
            'emailCC'                      => [
                'required' => false,
            ],
            'invoiceMethod'                => [
                'required' => false,
            ],
        ];
    }
}
