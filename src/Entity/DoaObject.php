<?php
/**
 * ITEA copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProjectDoaObject.
 *
 * @ORM\Table(name="project_doa_object")
 * @ORM\Entity
 */
class DoaObject extends AbstractEntity
{
    /**
     * @ORM\Column(name="object_id",type="integer",options={"unsigned":true})
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
     * @ORM\JoinColumn(name="doa_id", referencedColumnName="doa_id", nullable=false)
     *
     * @var \Affiliation\Entity\Doa
     */
    private $doa;


    public function __get($property)
    {
        return $this->$property;
    }

    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    public function __isset($property)
    {
        return isset($this->$property);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    public function getDoa()
    {
        return $this->doa;
    }

    public function setDoa($doa)
    {
        $this->doa = $doa;

        return $this;
    }
}
