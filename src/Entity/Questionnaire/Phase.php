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

namespace Affiliation\Entity\Questionnaire;

use Affiliation\Entity\AbstractEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

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
    public const PHASE_PROJECT_END   = 2;

    /**
     * @ORM\Column(name="phase_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Annotation\Exclude()
     *
     * @var integer
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

    /**
     * @param $property
     *
     * @return mixed
     */
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

    public function __toString(): string
    {
        return (string) $this->phase;
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
