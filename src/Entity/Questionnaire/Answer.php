<?php
/**
*
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */

declare(strict_types=1);

namespace Affiliation\Entity\Questionnaire;

use Affiliation\Entity\Affiliation;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Affiliation\Entity\AbstractEntity;
use Zend\Form\Annotation;

/**
 * @ORM\Table(name="affiliation_questionnaire_answer")
 * @ORM\Entity(repositoryClass="Affiliation\Repository\Questionnaire\AnswerRepository")
 */
class Answer extends AbstractEntity
{
    /**
     * @ORM\Column(name="answer_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Annotation\Exclude()
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Affiliation\Entity\Questionnaire\QuestionnaireQuestion", cascade={"persist"}, inversedBy="answers")
     * @ORM\JoinColumn(name="question_questionnaire_id", referencedColumnName="question_questionnaire_id", nullable=false)
     * @Annotation\Exclude()
     *
     * @var QuestionnaireQuestion
     */
    private $questionnaireQuestion;

    /**
     * @ORM\ManyToOne(targetEntity="Affiliation\Entity\Affiliation", cascade={"persist"}, inversedBy="answers")
     * @ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id", nullable=false)
     * @Annotation\Exclude()
     *
     * @var Affiliation
     */
    private $affiliation;

    /**
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     * @Annotation\Exclude()
     *
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @ORM\Column(name="date_updated", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     * @Annotation\Exclude()
     *
     * @var \DateTime
     */
    private $dateUpdated;

    /**
     * @ORM\Column(name="date_changed", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="change", field={"value"})
     * @Annotation\Exclude()
     *
     * @var \DateTime
     */
    private $dateChanged;

    /**
     * @ORM\Column(name="value", length=65535, type="text", nullable=true)
     * @Annotation\Exclude()
     *
     * @var string
     */
    private $value;

    /**
     * Magic Getter.
     *
     * @param $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        return $this->$property;
    }

    /**
     * Magic Setter.
     *
     * @param $property
     * @param $value
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    /**
     * @param $property
     *
     * @return bool
     */
    public function __isset($property): bool
    {
        return isset($this->$property);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): Answer
    {
        $this->id = $id;
        return $this;
    }

    public function getQuestionnaireQuestion(): ?QuestionnaireQuestion
    {
        return $this->questionnaireQuestion;
    }

    public function setQuestionnaireQuestion(QuestionnaireQuestion $questionnaireQuestion): Answer
    {
        $this->questionnaireQuestion = $questionnaireQuestion;
        return $this;
    }

    public function getAffiliation(): ?Affiliation
    {
        return $this->affiliation;
    }

    public function setAffiliation(Affiliation $affiliation): Answer
    {
        $this->affiliation = $affiliation;
        return $this;
    }

    public function getDateCreated(): ?\DateTime
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTime $dateCreated): Answer
    {
        $this->dateCreated = $dateCreated;
        return $this;
    }

    public function getDateUpdated(): ?\DateTime
    {
        return $this->dateUpdated;
    }

    public function setDateUpdated(\DateTime $dateUpdated): Answer
    {
        $this->dateUpdated = $dateUpdated;
        return $this;
    }

    public function getDateChanged(): ?\DateTime
    {
        return $this->dateChanged;
    }

    public function setDateChanged(\DateTime $dateChanged): Answer
    {
        $this->dateChanged = $dateChanged;
        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): Answer
    {
        $this->value = $value;
        return $this;
    }
}
