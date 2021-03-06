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
use Organisation\Entity\Type;
use Program\Entity\Call\Call;

/**
 * @ORM\Table(name="affiliation_questionnaire")
 * @ORM\Entity(repositoryClass="Affiliation\Repository\Questionnaire\QuestionnaireRepository")
 */
class Questionnaire extends AbstractEntity
{
    /**
     * @ORM\Column(name="questionnaire_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Annotation\Exclude()
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Organisation\Entity\Type", cascade={"persist"}, inversedBy="questionnaires")
     * @ORM\JoinColumn(name="organisation_type_id", referencedColumnName="type_id", nullable=false)
     * @Annotation\Type("DoctrineORMModule\Form\Element\EntitySelect")
     * @Annotation\Options({
     *     "target_class":"Organisation\Entity\Type",
     *     "help-block":"txt-questionnaire-organisation-type-help-block",
     *     "label":"txt-organisation-type"
     * })
     *
     * @var Type
     */
    private $organisationType;

    /**
     * @ORM\ManyToOne(targetEntity="Program\Entity\Call\Call", cascade={"persist"}, inversedBy="questionnaires")
     * @ORM\JoinColumn(name="programcall_id", referencedColumnName="programcall_id", nullable=false)
     * @Annotation\Type("DoctrineORMModule\Form\Element\EntitySelect")
     * @Annotation\Options({
     *     "target_class":"Program\Entity\Call\Call",
     *     "help-block":"txt-questionnaire-program-call-help-block",
     *     "label":"txt-program-call"
     * })
     *
     * @var Call
     */
    private $programCall;

    /**
     * @ORM\ManyToOne(targetEntity="Affiliation\Entity\Questionnaire\Phase", cascade={"persist"}, inversedBy="questionnaires")
     * @ORM\JoinColumn(name="phase_id", referencedColumnName="phase_id", nullable=false)
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
     * @Annotation\Type("\Laminas\Form\Element\Text")
     * @Annotation\Options({
     *     "label":"txt-questionnaire",
     *     "help-block":"txt-questionnaire-help-block"
     * })
     *
     * @var string
     */
    private $questionnaire;

    /**
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     * @Annotation\Type("\Laminas\Form\Element\Textarea")
     * @Annotation\Options({
     *     "label":"txt-description",
     *     "help-block":"txt-description-help-block"
     * })
     *
     * @var string
     */
    private $description;

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
     *     "label":"txt-questions"
     * })
     *
     * @var QuestionnaireQuestion[]|Collection
     */
    private $questionnaireQuestions;

    public function __construct()
    {
        $this->questionnaireQuestions = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string)$this->questionnaire;
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

    public function getOrganisationType(): ?Type
    {
        return $this->organisationType;
    }

    public function setOrganisationType(Type $organisationType): Questionnaire
    {
        $this->organisationType = $organisationType;
        return $this;
    }

    public function getProgramCall(): ?Call
    {
        return $this->programCall;
    }

    public function setProgramCall(Call $programCall): Questionnaire
    {
        $this->programCall = $programCall;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): Questionnaire
    {
        $this->description = $description;
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

    public function addQuestionnaireQuestions(Collection $questionnaireQuestions): void
    {
        foreach ($questionnaireQuestions as $questionnaireQuestion) {
            $this->questionnaireQuestions->add($questionnaireQuestion);
        }
    }

    public function removeQuestionnaireQuestions(Collection $questionnaireQuestions): void
    {
        foreach ($questionnaireQuestions as $questionnaireQuestion) {
            $this->questionnaireQuestions->removeElement($questionnaireQuestion);
        }
    }
}
