<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Form;

use Affiliation\Entity\Affiliation;
use Doctrine\ORM\EntityManager;
use DoctrineORMModule\Form\Element\EntitySelect;
use Organisation\Entity\Parent\Organisation;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 *
 */
class AdminAffiliation extends Form implements InputFilterProviderInterface
{
    /**
     * AdminAffiliation constructor.
     *
     * @param Affiliation   $affiliation
     * @param EntityManager $entityManager
     */
    public function __construct(Affiliation $affiliation, EntityManager $entityManager)
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
                    'label' => _("txt-organisation"),
                ],
            ]
        );

        $this->add(
            [
                'type'       => EntitySelect::class,
                'name'       => 'parentOrganisation',
                'attributes' => [
                    'label' => _("txt-parent-organisation"),

                ],
                'options'    => [
                    'object_manager'  => $entityManager,
                    'target_class'    => Organisation::class,
                    'find_method'     => [
                        'name'   => 'findBy',
                        'params' => [
                            'criteria' => [],
                            'orderBy'  => [
                                'parent' => 'ASC',
                            ],
                        ],
                    ],
                    'empty_option'    => '--' . "Find a parent-organisation",
                    'allow_empty'     => true,
                    'label_generator' => function (Organisation $organisation) {
                        return sprintf("%s (Parent: %s)", $organisation->getOrganisation(),
                            $organisation->getParent()->getOrganisation());
                    },
                    'help-block'      => _("txt-affiliation-parent-organisation-help-block"),
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
                    'label' => _("txt-technical-contact"),
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
    public function getInputFilterSpecification()
    {
        return [
            'parentOrganisation'    => [
                'required' => false,
            ],
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
