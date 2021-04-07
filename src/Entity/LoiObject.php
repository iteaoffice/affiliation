<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
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
     */
    private ?int $id = null;
    /**
     * @ORM\Column(name="object", type="blob", nullable=false)
     *
     * @var resource
     */
    private $object;
    /**
     * @ORM\OneToOne(targetEntity="Affiliation\Entity\Loi", cascade={"persist"}, inversedBy="object")
     * @ORM\JoinColumn(name="loi_id", referencedColumnName="loi_id", nullable=false)
     */
    private \Affiliation\Entity\Loi $loi;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getLoi(): \Affiliation\Entity\Loi
    {
        return $this->loi;
    }

    public function setLoi(\Affiliation\Entity\Loi $loi)
    {
        $this->loi = $loi;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function setObject(string $object)
    {
        $this->object = $object;
    }
}
