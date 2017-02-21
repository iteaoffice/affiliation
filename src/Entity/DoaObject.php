<?php
/**
 * ITEA copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     *
     * @return void
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return DoaObject
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return resource
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param resource $object
     *
     * @return DoaObject
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @return Doa
     */
    public function getDoa()
    {
        return $this->doa;
    }

    /**
     * @param Doa $doa
     *
     * @return DoaObject
     */
    public function setDoa($doa)
    {
        $this->doa = $doa;

        return $this;
    }
}
