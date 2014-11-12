<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category  Content
 * @package   Form
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Affiliation\Form;

use Affiliation\Entity;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Element\EntitySelect;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\File\Size;

/**
 * Class LoiFieldset
 * @package Affiliation\Form
 */
class LoiFieldset extends Fieldset implements InputFilterProviderInterface
{
    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct('loi');
        $loi = new Entity\Loi();
        $doctrineHydrator = new DoctrineHydrator($entityManager, 'Affiliation\Entity\Loi');
        $this->setHydrator($doctrineHydrator)->setObject($loi);
        $builder = new AnnotationBuilder();
        /**
         * Go over the different form elements and add them to the form
         */
        foreach ($builder->createForm($loi)->getElements() as $element) {
            /**
             * Go over each element to add the objectManager to the EntitySelect
             */
            if ($element instanceof EntitySelect) {
                $element->setOptions(
                    [
                        'object_manager' => $entityManager
                    ]
                );
            }
            //Add only when a type is provided
            if (array_key_exists('type', $element->getAttributes())) {
                $this->add($element);
            }
        }

        $this->add(
            [
                'type'    => '\Zend\Form\Element\Select',
                'name'    => 'contact',
                'options' => [
                    "label" => "txt-signer",
                ]
            ]
        );

        $this->add(
            [
                'type'    => '\Zend\Form\Element\File',
                'name'    => 'file',
                'options' => [
                    "label"      => "txt-source-file",
                    "help-block" => _("txt-attachment-requirements")
                ]
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
            'dateApproved' => [
                'required' => true,
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
                        ]
                    ]
                ]
            ],
            'file'         => [
                'required'   => false,
                'validators' => [
                    new Size(
                        [
                            'min' => '20kB',
                            'max' => '8MB',
                        ]
                    ),
                ]
            ]
        ];
    }
}
