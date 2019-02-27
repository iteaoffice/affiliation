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
 * @ORM\Table(name="affiliation_questionnaire")
 * @ORM\Entity(repositoryClass="Affiliation\Repository\Questionnaire\QuestionnaireRepository")
 */
class Questionnaire extends AbstractEntity
{
    /**
     * @ORM\Column(name="questionnaire_id", type="integer", length=10, options={"unsigned"=true}, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Annotation\Exclude()
     *
     * @var integer
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Affiliation\Entity\Questionnaire\Phase", cascade={"persist"}, inversedBy="questionnaires")
     * @ORM\JoinColumn(name="phase_id", referencedColumnName="phase_id")
     * @Annotation\Type("DoctrineORMModule\Form\Element\EntitySelect")
     * @Annotation\Options({
     *     "target_class":"Affiliation\Entity\Questionnaire\Phase",
     *     "help-block":"txt-questionnaire-phase-help-block",
     *     "label":"txt-phase"
     * })
     *
     * @var Phase
     */
    private $phase;

    /**
     * @ORM\Column(name="questionnaire", type="string", length=55, nullable=false)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({
     *     "label":"txt-questionnaire",
     *     "help-block":"txt-questionnaire-help-block"
     * })
     *
     * @var string
     */
    private $questionnaire;

    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Questionnaire\QuestionnaireQuestion", cascade={"persist", "remove"}, mappedBy="questionnaire", orphanRemoval=true)
     * @ORM\OrderBy({"sequence"="ASC"})
     * @Annotation\ComposedObject({
     *     "target_object":"Affiliation\Entity\Questionnaire\QuestionnaireQuestion",
     *     "is_collection":"true"
     * })
     * @Annotation\Options({
     *     "allow_add":"true",
     *     "allow_remove":"true",
     *     "count":0,
     *     "label":"txt-questions",
     *     "help-block":"txt-questionnaire-questions-help-block"
     * })
     *
     * @var QuestionnaireQuestion[]|Collection
     */
    private $questionnaireQuestions;

    /**
     * Category constructor.
     */
    public function __construct()
    {
        $this->questionnaireQuestions = new ArrayCollection();
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
        return (string) $this->questionnaire;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): Questionnaire
    {
        $this->id = $id;
        return $this;
    }

    public function getPhase(): ?Phase
    {
        return $this->phase;
    }

    public function setPhase(Phase $phase): Questionnaire
    {
        $this->phase = $phase;
        return $this;
    }

    public function getQuestionnaire(): ?string
    {
        return $this->questionnaire;
    }

    public function setQuestionnaire(string $questionnaire): Questionnaire
    {
        $this->questionnaire = $questionnaire;
        return $this;
    }

    public function getQuestionnaireQuestions(): Collection
    {
        return $this->questionnaireQuestions;
    }

    public function setQuestionnaireQuestions(Collection $questionnaireQuestions): Questionnaire
    {
        $this->questionnaireQuestions = $questionnaireQuestions;
        return $this;
    }
}
