<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 */
declare(strict_types=1);

namespace Affiliation\Form;

use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\Callback;
use Laminas\Validator\NotEmpty;

/**
 * Class EffortSpent
 *
 * @package Affiliation\Form
 */
final class EffortSpent extends Form implements InputFilterProviderInterface
{
    private $effortPlanned;

    public function __construct(float $effortPlanned = null)
    {
        parent::__construct();
        $this->effortPlanned = $effortPlanned;
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');

        $this->add(
            [
                'type'    => Text::class,
                'name'    => 'effort',
                'options' => [
                    'label'      => _('txt-effort-spent'),
                    'help-block' => _('txt-effort-spent-effort-help-block'),
                ],
            ]
        );

        $this->add(
            [
                'type'       => Textarea::class,
                'name'       => 'marketAccess',
                'options'    => [
                    'label'      => _('txt-exploitation-prospects'),
                    'help-block' => _('txt-market-access-inline-help'),
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
                'type'       => Textarea::class,
                'name'       => 'mainContribution',
                'options'    => [

                    'label'      => _('txt-main-contributions-role-and-added-value-within-the-project'),
                    'help-block' => _('txt--main-contribution-for-the-project-inline-help'),
                ],
                'attributes' => [
                    'id'    => 'mainContribution',
                    'rows'  => 8,
                    'class' => 'form-control',
                ],
            ]
        );

        $this->add(
            [
                'type'       => Textarea::class,
                'name'       => 'summary',
                'options'    => [

                    'label'      => _('txt-main-results-during-reporting-period'),
                    'help-block' => _('txt-brief-summary-of-partner-during-reporting-period'),
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
                'type'       => Textarea::class,
                'name'       => 'comment',
                'options'    => [

                    'label'      => _('txt-descrepancy-explanation'),
                    'help-block' => _('txt-effort-spent-comment-on-discrepancy-help-block'),
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
    }

    public function getInputFilterSpecification(): array
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
                    new NotEmpty(NotEmpty::NULL),
                    [
                        'name'    => 'Callback',
                        'options' => [
                            'messages' => [
                                Callback::INVALID_VALUE => sprintf(
                                    'Please give a comment to explain the strong descripency (> 20 percent) between the real and planned value (%s)',
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
