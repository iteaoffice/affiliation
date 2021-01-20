<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Form\Doa;

use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\Callback;
use Laminas\Validator\File\Extension;
use Laminas\Validator\File\Size;

/**
 * Class SubmitForm
 * @package Affiliation\Form\Doa
 */
final class SubmitForm extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('action', '');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->add(
            [
                'type'       => Text::class,
                'name'       => 'group_name',
                'attributes' => [
                    'placeholder' => _('txt-name-of-business-group-/-department'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Text::class,
                'name'       => 'chamber_of_commerce_number',
                'attributes' => [
                    'placeholder' => _('txt-number-chamber-of-commerce'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Text::class,
                'name'       => 'chamber_of_commerce_location',
                'attributes' => [
                    'width'       => '60px',
                    'placeholder' => _('txt-location-chamber-of-commerce'),
                ],
            ]
        );
        $this->add(
            [
                'type'    => File::class,
                'name'    => 'file',
                'options' => [
                    'label'      => 'txt-file',
                    'help-block' => _('txt-a-signed-project-doa-is-required'),
                ],
            ]
        );

        $this->add(
            [
                'type'    => Checkbox::class,
                'name'    => 'selfApprove',
                'options' => [
                    'help-block' => _('txt-self-approve-loi-checkbox-help-text'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Submit::class,
                'name'       => 'sign',
                'attributes' => [
                    'class' => 'btn btn-primary',
                    'value' => _('txt-sign-digital-doa'),
                ],
            ]
        );

        $this->add(
            [
                'type'       => Submit::class,
                'name'       => 'upload',
                'attributes' => [
                    'class' => 'btn btn-primary',
                    'value' => _('txt-upload-paper-doa'),
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
            'group_name'                   => [
                'required' => true,
            ],
            'chamber_of_commerce_number'   => [
                'required' => true,
            ],
            'chamber_of_commerce_location' => [
                'required' => true,
            ],
            'selfApprove'                  => [
                'required'   => true,
                'validators' => [
                    [
                        'name'    => Callback::class,
                        'options' => [
                            'callback' => static function ($value) {
                                return $value === '1';
                            },
                        ],
                    ],
                ]
            ],
            'file'                         => [
                'required'   => true,
                'validators' => [
                    new Size(
                        [
                            'min' => '1kB',
                            'max' => '16MB',
                        ]
                    ),
                    new Extension(
                        [
                            'extension' => ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'],
                        ]
                    ),
                ],
            ],
        ];
    }
}
