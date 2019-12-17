<?php
/**
 * ITEA copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="project_loi_object")
 * @ORM\Entity
 */
class LoiObject extends AbstractEntity
{
    /**
     * @ORM\Column(name="object_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;
    /**
     * @ORM\Column(name="object", type="blob", nullable=false)
     *
     * @var resource
     */
    private $object;
    /**
     * @ORM\ManyToOne(targetEntity="Affiliation\Entity\Loi", cascade="persist", inversedBy="object")
     * @ORM\JoinColumn(name="loi_id", referencedColumnName="loi_id", nullable=false)
     *
     * @var \Affiliation\Entity\Loi
     */
    private $loi;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getLoi()
    {
        return $this->loi;
    }

    public function setLoi($loi)
    {
        $this->loi = $loi;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function setObject($object)
    {
        $this->object = $object;
    }
}
