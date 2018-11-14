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

use Zend\Form\Element\Checkbox;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\File\Extension;
use Zend\Validator\File\Size;

/**
 *
 */
class SubmitLoi extends Form implements InputFilterProviderInterface
{
    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('action', '');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->add(
            [
                'type'    => '\Zend\Form\Element\File',
                'name'    => 'file',
                'options' => [
                    "label"      => "txt-file",
                    "help-block" => _("txt-a-signed-loi-is-required"),
                ],
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'submit',
                'attributes' => [
                    'class' => "btn btn-primary",
                    'value' => _("txt-upload-loi"),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Checkbox::class,
                'name'       => 'selfApprove',
                'options'    => [
                    'inline'     => true,
                    "help-block" => _("txt-self-approve-loi-checkbox-help-text"),
                ],
                'attributes' => [

                ]
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'approve',
                'attributes' => [
                    'class' => "btn btn-primary",
                    'value' => _("txt-approve-loi"),
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
    public function getInputFilterSpecification(): array
    {
        return [
            'file' => [
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
