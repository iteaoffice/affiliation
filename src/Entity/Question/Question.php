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
 * Question
 *
 * @ORM\Table(name="affiliation_question")
 * @ORM\Entity()
 */
class Question extends AbstractEntity
{
    /** Input type is Yes/No */
    public const INPUT_TYPE_BOOL   = 1;
    /** Input type is a single line textfield */
    public const INPUT_TYPE_STRING = 2;
    /** Input type is a multi-line textarea */
    public const INPUT_TYPE_TEXT   = 3;
    /** Input type is select box */
    public const INPUT_TYPE_SELECT = 4;
    /** Input type is a number */
    public const INPUT_TYPE_NUMERIC = 5;

    /**
     * Templates for the input types.
     *
     * @var array
     */
    protected static $inputTypeTemplates = [
        self::INPUT_TYPE_BOOL    => 'txt-input-type-bool',
        self::INPUT_TYPE_STRING  => 'txt-input-type-string',
        self::INPUT_TYPE_TEXT    => 'txt-input-type-text',
        self::INPUT_TYPE_SELECT  => 'txt-input-type-select',
        self::INPUT_TYPE_NUMERIC => 'txt-input-type-numeric',
    ];

    /**
     * @ORM\Column(name="question_id", type="integer", length=10, options={"unsigned"=true}, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Annotation\Exclude()
     *
     * @var integer
     */
    private $id;

    /**
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity="Affiliation\Entity\Question\Category", cascade={"persist"}, inversedBy="questions")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="category_id")
     * @Annotation\Type("DoctrineORMModule\Form\Element\EntitySelect")
     * @Annotation\Options({
     *     "target_class":"Affiliation\Entity\Question\Category",
     *     "help-block":"txt-question-category-help-block",
     *     "label":"txt-category"
     * })
     *
     * @var Category
     */
    private $category;

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
     * @ORM\Column(name="question", type="string", length=255, nullable=false)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-question"})
     *
     * @var string
     */
    private $question;

    /**
     * @ORM\Column(name="help_block", type="text", length=65535, nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Textarea")
     * @Annotation\Options({
     *     "label":"txt-help-block",
     *     "help-block":"txt-help-block-help-block"
     * })
     *
     * @var string
     */
    private $helpBlock;

    /**
     * @ORM\Column(name="placeholder", type="string", length=255, nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Textarea")
     * @Annotation\Options({
     *     "label":"txt-placeholder",
     *     "help-block":"txt-placeholder-help-block"
     * })
     *
     * @var string
     */
    private $placeholder;

    /**
     * @ORM\Column(name="input_type", type="smallint", length=5, options={"unsigned"=true}, nullable=false)
     * @Annotation\Type("Zend\Form\Element\Radio")
     * @Annotation\Attributes({"array":"inputTypeTemplates"})
     * @Annotation\Options({
     *     "label":"txt-input-type",
     *     "help-block":"txt-input-type-help-block"
     * })
     *
     * @var int
     */
    private $inputType;

    /**
     * @ORM\Column(name="`values`", type="text", length=65535, nullable=true)
     * @Annotation\Type("Zend\Form\Element\Textarea")
     * @Annotation\Options({
     *     "label":"txt-values",
     *     "help-block":"txt-values-help-block"
     * })
     *
     * @var string
     */
    private $values;

    /**
     * @ORM\Column(name="is_required", type="boolean", length=1, options={"unsigned"=true}, nullable=false)
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\Options({
     *     "label":"txt-required",
     *     "help-block":"txt-required-help-block"
     * })
     *
     * @var bool
     */
    private $required = true;

    /**
     * @ORM\Column(name="is_enabled", type="boolean", length=1, options={"unsigned"=true}, nullable=false)
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\Options({
     *     "label":"txt-enabled",
     *     "help-block":"txt-enabled-help-block"
     * })
     *
     * @var bool
     */
    private $enabled = true;

    /**
     * @ORM\ManyToMany(targetEntity="Affiliation\Entity\Question\Phase", cascade={"persist"}, inversedBy="questions")
     * @ORM\JoinTable(name="affiliation_question_phase_question",
     *    joinColumns={@ORM\JoinColumn(name="question_id", referencedColumnName="question_id")},
     *    inverseJoinColumns={@ORM\JoinColumn(name="phase_id", referencedColumnName="phase_id", nullable=false)}
     * )
     * @Annotation\Type("DoctrineORMModule\Form\Element\EntityMultiCheckbox")
     * @Annotation\Options({
     *     "target_class":"Affiliation\Entity\Question\Phase",
     *     "label":"txt-phases",
     *     "help-block":"txt-phases-type-block"
     * })
     *
     * @var Phase[]|Collection
     */
    private $phases;

    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Question\Answer", cascade={"persist"}, mappedBy="question")
     * @Annotation\Exclude()
     *
     * @var Answer[]|Collection
     */
    private $answers;

    /**
     * Question constructor.
     */
    public function __construct()
    {
        $this->phases  = new ArrayCollection();
        $this->answers = new ArrayCollection();
    }

    /**
     * @return array
     */
    public static function getInputTypeTemplates(): array
    {
        return self::$inputTypeTemplates;
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
        return $this->question;
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
    public function getQuestion(): ?string
    {
        return $this->question;
    }

    /**
     * @param string $question
     */
    public function setQuestion(string $question): void
    {
        $this->question = $question;
    }

    /**
     * @return string|null
     */
    public function getHelpBlock(): ?string
    {
        return $this->helpBlock;
    }

    /**
     * @param string $helpBlock
     */
    public function setHelpBlock(string $helpBlock): void
    {
        $this->helpBlock = $helpBlock;
    }

    /**
     * @return string|null
     */
    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    /**
     * @param string $placeholder
     */
    public function setPlaceholder(string $placeholder): void
    {
        $this->placeholder = $placeholder;
    }

    /**
     * @param bool $textual
     *
     * @return int|string
     */
    public function getInputType(bool $textual = false)
    {
        if ($textual) {
            return self::$inputTypeTemplates[$this->inputType];
        }

        return $this->inputType;
    }

    /**
     * @param int $inputType
     */
    public function setInputType(int $inputType): void
    {
        $this->inputType = $inputType;
    }

    /**
     * @return string|null
     */
    public function getValues(): ?string
    {
        return $this->values;
    }

    /**
     * @param string $values
     */
    public function setValues(string $values): void
    {
        $this->values = $values;
    }

    /**
     * @return bool
     */
    public function getRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     */
    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory(Category $category): void
    {
        $this->category = $category;
    }

    /**
     * @return Answer[]|Collection
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    /**
     * @param Answer[]|Collection $answers
     */
    public function setAnswers(Collection $answers): void
    {
        $this->answers = $answers;
    }
}
