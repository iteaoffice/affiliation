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
 * @ORM\Table(name="project_doa_object")
 * @ORM\Entity
 */
class DoaObject extends AbstractEntity
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
     * @ORM\OneToOne(targetEntity="Affiliation\Entity\Doa", cascade={"persist"}, inversedBy="object")
     * @ORM\JoinColumn(name="doa_id", referencedColumnName="doa_id", nullable=false)
     */
    private \Affiliation\Entity\Doa $doa;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id): DoaObject
    {
        $this->id = $id;

        return $this;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function setObject($object): DoaObject
    {
        $this->object = $object;

        return $this;
    }

    public function getDoa(): Doa
    {
        return $this->doa;
    }

    public function setDoa($doa): DoaObject
    {
        $this->doa = $doa;

        return $this;
    }
}
