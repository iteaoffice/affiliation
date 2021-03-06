<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Form\Loi;

use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Submit;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\File\Extension;
use Laminas\Validator\File\Size;

/**
 * Class SubmitForm
 * @package Affiliation\Form\Loi
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
                'type'       => Submit::class,
                'name'       => 'submit',
                'attributes' => [
                    'class' => 'btn btn-primary',
                    'value' => _('txt-upload-loi'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Checkbox::class,
                'name'       => 'selfApprove',
                'options'    => [
                    'inline'     => true,
                    'help-block' => _('txt-self-approve-loi-checkbox-help-text'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Submit::class,
                'name'       => 'approve',
                'attributes' => [
                    'class' => 'btn btn-primary',
                    'value' => _('txt-approve-loi'),
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
            'file' => [
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
