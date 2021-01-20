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
 * @ORM\Table(name="affiliation_questionnaire_question_questionnaire")
 * @ORM\Entity(repositoryClass="Affiliation\Repository\Questionnaire\QuestionnaireQuestionRepository")
 * @Annotation\Instance("Affiliation\Entity\Questionnaire\QuestionnaireQuestion")
 */
class QuestionnaireQuestion extends AbstractEntity
{
    /**
     * @ORM\Column(name="question_questionnaire_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Annotation\Exclude()
     *
     * @var int
     */
    private $id;

    /**
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
     * @ORM\ManyToOne(targetEntity="Affiliation\Entity\Questionnaire\Questionnaire", cascade={"persist"}, inversedBy="questionnaireQuestions")
     * @ORM\JoinColumn(name="questionnaire_id", referencedColumnName="questionnaire_id", nullable=false)
     * @Annotation\Type("\Laminas\Form\Element\Hidden")
     * @Gedmo\SortableGroup
     *
     * @var Questionnaire
     */
    private $questionnaire;

    /**
     * @ORM\ManyToOne(targetEntity="Affiliation\Entity\Questionnaire\Question", cascade={"persist"}, inversedBy="questionnaireQuestions")
     * @ORM\JoinColumn(name="question_id", referencedColumnName="question_id", nullable=false)
     * @Annotation\Type("DoctrineORMModule\Form\Element\EntitySelect")
     * @Annotation\Options({
     *     "target_class":"Affiliation\Entity\Questionnaire\Question",
     *     "help-block":"txt-question-help-block",
     *     "label":"txt-question",
     *     "inline":true
     * })
     *
     * @var Question
     */
    private $question;

    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Questionnaire\Answer", cascade={"persist"}, mappedBy="questionnaireQuestion")
     * @Annotation\Exclude()
     *
     * @var Answer[]|Collection
     */
    private $answers;

    /**
     * QuestionnaireQuestion constructor.
     */
    public function __construct()
    {
        $this->answers = new ArrayCollection();
    }

    public function __toString(): string
    {
        return \sprintf('%d: %s', (int)$this->sequence, (string)$this->question->getQuestion());
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): QuestionnaireQuestion
    {
        $this->id = $id;
        return $this;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function setSequence(int $sequence): QuestionnaireQuestion
    {
        $this->sequence = $sequence;
        return $this;
    }

    public function getQuestionnaire(): ?Questionnaire
    {
        return $this->questionnaire;
    }

    public function setQuestionnaire(Questionnaire $questionnaire): QuestionnaireQuestion
    {
        $this->questionnaire = $questionnaire;
        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(Question $question): QuestionnaireQuestion
    {
        $this->question = $question;
        return $this;
    }

    public function getAnswers(): ?Collection
    {
        return $this->answers;
    }

    public function setAnswers($answers): QuestionnaireQuestion
    {
        $this->answers = $answers;
        return $this;
    }
}
