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
use Gedmo\Mapping\Annotation as Gedmo;
use Laminas\Form\Annotation;

/**
 * Category
 *
 * @ORM\Table(name="affiliation_questionnaire_category")
 * @ORM\Entity(repositoryClass="Affiliation\Repository\Questionnaire\CategoryRepository")
 */
class Category extends AbstractEntity
{
    /**
     * @ORM\Column(name="category_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Annotation\Exclude()
     *
     * @var int
     */
    private $id;

    /**
     *
     * @ORM\Column(name="sequence", type="integer", options={"unsigned":true})
     * @Annotation\Type("\Laminas\Form\Element\Number")
     * @Annotation\Options({
     *     "label":"txt-sequence",
     *     "help-block":"txt-sequence-help-block"
     * })
     * @Gedmo\SortablePosition
     *
     * @var int
     */
    private $sequence = 0;

    /**
     * @ORM\Column(name="category", type="string", length=55, nullable=false)
     * @Annotation\Type("\Laminas\Form\Element\Text")
     * @Annotation\Options({
     *     "label":"txt-category",
     *     "help-block":"txt-category-help-block"
     * })
     *
     * @var string
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Questionnaire\Question", cascade={"persist"}, mappedBy="category")
     * @Annotation\Exclude()
     *
     * @var Question[]|Collection
     */
    private $questions;

    /**
     * Category constructor.
     */
    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

    /**
     * @param $property
     *
     * @return mixed
     */
    public function __toString(): string
    {
        return (string) $this->category;
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
     * @return int
     */
    public function getSequence(): int
    {
        return $this->sequence;
    }

    /**
     * @param int $sequence
     */
    public function setSequence(int $sequence): void
    {
        $this->sequence = $sequence;
    }

    /**
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    /**
     * @return Question[]|Collection
     */
    public function getQuestions()
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
}
