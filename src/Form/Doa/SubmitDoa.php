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

namespace Affiliation\Form\Doa;

use Zend\Form\Element\Checkbox;
use Zend\Form\Element\File;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\Callback;
use Zend\Validator\File\Extension;
use Zend\Validator\File\Size;

/**
 * Class SubmitDoa
 *
 * @package Affiliation\Form
 */
final class SubmitDoa extends Form implements InputFilterProviderInterface
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
                    'help-block' => _('txt-a-signed-loi-is-required'),
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
                            'min' => '5kB',
                            'max' => '8MB',
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
