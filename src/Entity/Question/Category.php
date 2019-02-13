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
use Gedmo\Mapping\Annotation as Gedmo;
use Zend\Form\Annotation;

/**
 * Category
 *
 * @ORM\Table(name="affiliation_question_category")
 * @ORM\Entity(repositoryClass="Affiliation\Repository\Question\CategoryRepository")
 */
class Category extends AbstractEntity
{
    /**
     * @ORM\Column(name="category_id", type="integer", length=10, options={"unsigned"=true}, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Annotation\Exclude()
     *
     * @var integer
     */
    private $id;

    /**
     *
     * @ORM\Column(name="sequence", length=10, options={"unsigned"=true}, type="integer", nullable=false)
     * @Annotation\Type("\Zend\Form\Element\Number")
     * @Annotation\Options({
     *     "label":"txt-sequence",
     *     "help-block":"txt-sequence-help-block"
     * })
     * @Gedmo\SortablePosition
     *
     * @var integer
     */
    private $sequence = 0;

    /**
     * @ORM\Column(name="category", type="string", length=55, nullable=false)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-category"})
     *
     * @var string
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Question\Question", cascade={"persist"}, mappedBy="category")
     * @ORM\OrderBy({"sequence" = "ASC"})
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
        return $this->category;
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
