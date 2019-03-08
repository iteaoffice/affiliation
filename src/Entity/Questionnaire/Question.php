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
 * Question
 *
 * @ORM\Table(name="affiliation_questionnaire_question")
 * @ORM\Entity(repositoryClass="Affiliation\Repository\Questionnaire\QuestionRepository")
 */
class Question extends AbstractEntity
{
    /** Input type is Yes/No */
    public const INPUT_TYPE_BOOL = 1;
    /** Input type is a single line textfield */
    public const INPUT_TYPE_STRING = 2;
    /** Input type is a multi-line textarea */
    public const INPUT_TYPE_TEXT = 3;
    /** Input type is select box */
    public const INPUT_TYPE_SELECT = 4;
    /** Input type is a number */
    public const INPUT_TYPE_NUMERIC = 5;
    /** Input type is a date */
    public const INPUT_TYPE_DATE = 6;

    /**
     * Templates for the input types.
     *
     * @var array
     */
    protected static $inputTypeTemplates = [
        self::INPUT_TYPE_BOOL => 'txt-input-type-bool',
        self::INPUT_TYPE_STRING => 'txt-input-type-string',
        self::INPUT_TYPE_TEXT => 'txt-input-type-text',
        //self::INPUT_TYPE_SELECT  => 'txt-input-type-select',
        self::INPUT_TYPE_NUMERIC => 'txt-input-type-numeric',
        self::INPUT_TYPE_DATE => 'txt-input-type-date',
    ];

    /**
     * @ORM\Column(name="question_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Annotation\Exclude()
     *
     * @var integer
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Affiliation\Entity\Questionnaire\Category", cascade={"persist"}, inversedBy="questions")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="category_id")
     * @Annotation\Type("DoctrineORMModule\Form\Element\EntitySelect")
     * @Annotation\Options({
     *     "target_class":"Affiliation\Entity\Questionnaire\Category",
     *     "help-block":"txt-question-category-help-block",
     *     "label":"txt-category"
     * })
     *
     * @var Category
     */
    private $category;

    /**
     * @ORM\Column(name="question", type="string", nullable=false)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({
     *     "label":"txt-question",
     *     "help-block":"txt-question-help-block",
     * })
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
     * @ORM\Column(name="placeholder", type="string", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({
     *     "label":"txt-placeholder",
     *     "help-block":"txt-placeholder-help-block"
     * })
     *
     * @var string
     */
    private $placeholder;

    /**
     * @ORM\Column(name="input_type", type="smallint", length=5, nullable=false)
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
     * @ORM\Column(name="is_required", type="boolean", length=1, nullable=false)
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
     * @ORM\Column(name="is_enabled", type="boolean", length=1, nullable=false)
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
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Questionnaire\QuestionnaireQuestion", cascade={"persist","remove"}, mappedBy="question")
     * @Annotation\Exclude()
     *
     * @var QuestionnaireQuestion[]|Collection
     */
    private $questionnaireQuestions;

    /**
     * Question constructor.
     */
    public function __construct()
    {
        $this->questionnaireQuestions = new ArrayCollection();
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
        return (string)$this->question;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Question
     */
    public function setId(int $id): Question
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Category
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     * @return Question
     */
    public function setCategory(Category $category): Question
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return string
     */
    public function getQuestion(): ?string
    {
        return $this->question;
    }

    /**
     * @param string $question
     * @return Question
     */
    public function setQuestion(string $question): Question
    {
        $this->question = $question;
        return $this;
    }

    /**
     * @return string
     */
    public function getHelpBlock(): ?string
    {
        return $this->helpBlock;
    }

    /**
     * @param string $helpBlock
     * @return Question
     */
    public function setHelpBlock(string $helpBlock): Question
    {
        $this->helpBlock = $helpBlock;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    /**
     * @param string $placeholder
     * @return Question
     */
    public function setPlaceholder(string $placeholder): Question
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * @param bool $textual
     * @return int|string
     */
    public function getInputType(bool $textual = false)
    {
        if ($textual) {
            return self::$inputTypeTemplates[$this->inputType];
        }

        return $this->inputType;
    }

    public function setInputType(int $inputType): Question
    {
        $this->inputType = $inputType;
        return $this;
    }

    public function getValues(): ?string
    {
        return $this->values;
    }

    public function setValues(string $values): Question
    {
        $this->values = $values;
        return $this;
    }

    public function getRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): Question
    {
        $this->required = $required;
        return $this;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): Question
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return QuestionnaireQuestion[]|Collection
     */
    public function getQuestionnaireQuestions()
    {
        return $this->questionnaireQuestions;
    }

    /**
     * @param QuestionnaireQuestion[]|Collection $questionnaireQuestions
     * @return Question
     */
    public function setQuestionnaireQuestions($questionnaireQuestions)
    {
        $this->questionnaireQuestions = $questionnaireQuestions;
        return $this;
    }
}
