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

namespace Affiliation\Entity\Question;

use Affiliation\Entity\AbstractEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/**
 * Category
 *
 * @ORM\Table(name="affiliation_question_phase")
 * @ORM\Entity()
 */
class Phase extends AbstractEntity
{
    /** Start of project */
    public const PHASE_PROJECT_START = 1;
    /** End of project */
    public const PHASE_PROJECT_END   = 2;

    /**
     * @ORM\Column(name="phase_id", type="integer", length=10, options={"unsigned"=true}, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Annotation\Exclude()
     *
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(name="phase", type="string", length=55, nullable=false)
     *
     * @var string
     */
    private $phase;

    /**
     * @ORM\ManyToMany(targetEntity="Affiliation\Entity\Question\Question", cascade={"persist"}, mappedBy="phases")
     * @Annotation\Exclude()
     *
     * @var Question[]|Collection
     */
    private $questions;

    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Question\Answer", cascade={"persist"}, mappedBy="phase")
     * @Annotation\Exclude()
     *
     * @var Question[]|Collection
     */
    private $answers;

    /**
     * Category constructor.
     */
    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->answers   = new ArrayCollection();
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
        return $this->phase;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getPhase(): ?string
    {
        return $this->phase;
    }

    /**
     * @param string $phase
     */
    public function setPhase(string $phase): void
    {
        $this->phase = $phase;
    }

    /**
     * @return Question[]|Collection
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    /**
     * @param Question[]|Collection $questions
     */
    public function setQuestions(Collection $questions): void
    {
        $this->questions = $questions;
    }

    /**
     * @return Question[]|Collection
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    /**
     * @param Question[]|Collection $answers
     */
    public function setAnswers(Collection $answers): void
    {
        $this->answers = $answers;
    }
}
