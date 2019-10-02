<?php
/**
 * ITEA Office all rights reserved
 *
 * @category  Content
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2019 ITEA Office (https://itea3.org)
 */
declare(strict_types=1);

namespace Affiliation\Form;

use Affiliation\Entity;
use Doctrine\ORM\EntityManager;
use Zend\Form\Element\File;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Class LoiFieldset
 *
 * @package Affiliation\Form
 */
final class LoiFieldset extends ObjectFieldset implements InputFilterProviderInterface
{
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager, new Entity\Loi());

        $this->add(
            [
                'type'    => File::class,
                'name'    => 'file',
                'options' => [
                    'label'      => 'txt-source-file',
                    'help-block' => _('txt-attachment-requirements'),
                ],
            ]
        );
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'dateApproved' => [
                'required' => false,
            ],
            'approver'     => [
                'required' => false,
            ],
            'contact'      => [
                'required' => true,
            ],
            'dateSigned'   => [
                'required'   => true,
                'validators' => [
                    [
                        'name'    => 'Date',
                        'options' => [
                            'pattern' => 'yyyy-mm-dd',
                        ],
                    ],
                ],
            ],
            'file'         => [
                'required' => false,
            ],
        ];
    }
}
