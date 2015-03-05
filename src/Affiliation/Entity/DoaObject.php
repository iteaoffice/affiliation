<?php
/**
 * ITEA copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */

namespace Affiliation\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;

/**
 * ProjectDoaObject.
 *
 * @ORM\Table(name="project_doa_object")
 * @ORM\Entity
 */
class DoaObject extends EntityAbstract
{
    /**
     * @ORM\Column(name="object_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="object", type="blob", nullable=false)
     *
     * @var resource
     */
    private $object;
    /**
     * @ORM\ManyToOne(targetEntity="Affiliation\Entity\Doa", cascade="persist", inversedBy="object")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="doa_id", referencedColumnName="doa_id")
     * })
     *
     * @var \Affiliation\Entity\Doa
     */
    private $doa;

    /**
     * Magic Getter.
     *
     * @param $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        return $this->$property;
    }

    /**
     * Magic Setter.
     *
     * @param $property
     * @param $value
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    /**
     * Set input filter.
     *
     * @param InputFilterInterface $inputFilter
     *
     * @throws \Exception
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Setting an inputFilter is currently not supported");
    }

    /**
     * @return \Zend\InputFilter\InputFilter|\Zend\InputFilter\InputFilterInterface
     */
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter       = new InputFilter();
            $factory           = new InputFactory();
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    /**
     * Needed for the hydration of form elements.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return [];
    }

    public function populate()
    {
        return $this->getArrayCopy();
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Affiliation\Entity\Doa $doa
     */
    public function setDoa($doa)
    {
        $this->doa = $doa;
    }

    /**
     * @return \Affiliation\Entity\Doa
     */
    public function getDoa()
    {
        return $this->doa;
    }

    /**
     * @param string $object
     */
    public function setObject($object)
    {
        $this->object = $object;
    }

    /**
     * @return resource
     */
    public function getObject()
    {
        return $this->object;
    }
}
