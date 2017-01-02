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

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\Callback;

/**
 *
 */
class EffortSpent extends Form implements InputFilterProviderInterface
{
    /**
     * Local variable to have the effortPlanned available in the validator
     *
     * @var float
     */
    protected $effortPlanned;

    /**
     *
     */
    public function __construct($effortPlanned)
    {
        parent::__construct();
        $this->effortPlanned = $effortPlanned;
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');

        $this->add(
            [
                'type'    => 'Zend\Form\Element\Text',
                'name'    => 'effort',
                'options' => [
                    'label'      => _("txt-effort-spent"),
                    'help-block' => _("txt-effort-spent-effort-help-block"),
                ],
            ]
        );

        $this->add(
            [
                'type'       => 'Zend\Form\Element\Textarea',
                'name'       => 'marketAccess',
                'options'    => [
                    'label'      => _("txt-exploitation-prospects"),
                    'help-block' => _("txt-market-access-inline-help"),
                ],
                'attributes' => [
                    'id'    => 'marketAccess',
                    'rows'  => 8,
                    'class' => 'form-control',
                ],
            ]
        );

        $this->add(
            [
                'type'       => 'Zend\Form\Element\Textarea',
                'name'       => 'mainContribution',
                'options'    => [

                    'label'      => _("txt-main-contributions-role-and-added-value-within-the-project"),
                    'help-block' => _("txt--main-contribution-for-the-project-inline-help"),
                ],
                'attributes' => [
                    'id'    => 'mainContribution',
                    'class' => 'form-control',
                ],
            ]
        );

        $this->add(
            [
                'type'       => 'Zend\Form\Element\Textarea',
                'name'       => 'summary',
                'options'    => [

                    'label'      => _("txt-main-results-during-reporting-period"),
                    'help-block' => _("txt-brief-summary-of-partner-during-reporting-period"),
                ],
                'attributes' => [
                    'id'    => 'summary',
                    'rows'  => 4,
                    'class' => 'form-control',
                ],
            ]
        );

        $this->add(
            [
                'type'       => 'Zend\Form\Element\Textarea',
                'name'       => 'comment',
                'options'    => [

                    'label'      => _("txt-descrepancy-explanation"),
                    'help-block' => _("txt-effort-spent-comment-on-discrepancy-help-block"),
                ],
                'attributes' => [
                    'id'    => 'comment',
                    'rows'  => 4,
                    'class' => 'form-control',
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
            'effort'  => [
                'required'   => true,
                'validators' => [
                    [
                        'name' => 'IsFloat',
                    ],
                    [
                        'name'    => 'Between',
                        'options' => [
                            'min' => 0,
                            'max' => 1000,
                        ],
                    ],
                ],
            ],
            'comment' => [
                'required'   => true,
                'validators' => [
                    new \Zend\Validator\NotEmpty(\Zend\Validator\NotEmpty::NULL),
                    [
                        'name'    => 'Callback',
                        'options' => [
                            'messages' => [
                                Callback::INVALID_VALUE => sprintf(
                                    "Please give a comment to explain the strong descripency (> 20 percent) between the real and planned value (%s)",
                                    $this->effortPlanned
                                ),
                            ],
                            'callback' => function ($value, $context = []) {
                                if ($this->effortPlanned == 0
                                    && $context['effort'] == 0
                                ) {
                                    return true;
                                }

                                if ($this->effortPlanned == 0
                                    && $context['effort'] != 0
                                ) {
                                    return strlen($value) > 0;
                                }

                                if (abs(
                                        ($context['effort']
                                         - $this->effortPlanned)
                                        / $this->effortPlanned
                                    ) > 0.2
                                ) {
                                    return strlen($value) > 0;
                                }

                                return true;
                            },
                        ],
                    ],
                ],
            ],
        ];
    }
}
