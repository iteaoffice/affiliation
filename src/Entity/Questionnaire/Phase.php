<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Entity\Questionnaire;

use Affiliation\Entity\AbstractEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Laminas\Form\Annotation;

/**
 * Category
 *
 * @ORM\Table(name="affiliation_questionnaire_phase")
 * @ORM\Entity()
 */
class Phase extends AbstractEntity
{
    /** Start of project */
    public const PHASE_PROJECT_START = 1;
    /** End of project */
    public const PHASE_PROJECT_END = 2;

    /**
     * @ORM\Column(name="phase_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Annotation\Exclude()
     *
     * @var int
     */
    private $id;
    /**
     * @ORM\Column(name="phase", type="string", length=55, nullable=false)
     * @Annotation\Exclude()
     *
     * @var string
     */
    private $phase;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Questionnaire\Questionnaire", cascade={"persist"}, mappedBy="phase")
     * @Annotation\Exclude()
     *
     * @var Questionnaire[]|Collection
     */
    private $questionnaires;


    public function __construct()
    {
        $this->questionnaires = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string)$this->phase;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getPhase(): ?string
    {
        return $this->phase;
    }

    public function setPhase(string $phase): Phase
    {
        $this->phase = $phase;
        return $this;
    }

    /**
     * @return Questionnaire[]|Collection
     */
    public function getQuestionnaires(): Collection
    {
        return $this->questionnaires;
    }

    public function setQuestionnaires(Collection $questionnaires): void
    {
        $this->questionnaires = $questionnaires;
    }
}
