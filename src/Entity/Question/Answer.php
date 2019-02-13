<?php
/**
 * ITEA Office all rights reserved
 *
 * PHP Version 7
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */

declare(strict_types=1);

namespace Affiliation\Entity\Question;

use Affiliation\Entity\Affiliation;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Affiliation\Entity\AbstractEntity;
use Zend\Form\Annotation;

/**
 * Evaluation Report Project Report (This are the real reports)
 *
 * @ORM\Table(name="affiliation_question_answer")
 * @ORM\Entity
 */
class Answer extends AbstractEntity
{
    /**
     * @ORM\Column(name="result_id", length=10, type="integer", options={"unsigned"=true}, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Affiliation\Entity\Question\Question", cascade={"persist"}, inversedBy="answers")
     * @ORM\JoinColumn(name="question_id", referencedColumnName="question_id", nullable=false)
     * @Annotation\Exclude()
     *
     * @var Question
     */
    private $question;

    /**
     * @ORM\ManyToOne(targetEntity="Affiliation\Entity\Question\Phase", cascade={"persist"}, inversedBy="answers")
     * @ORM\JoinColumn(name="phase_id", referencedColumnName="phase_id", nullable=false)
     * @Annotation\Exclude()
     *
     * @var Phase
     */
    private $phase;

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
     * @Gedmo\Timestampable(on="change", field={"comment", "value", "score"})
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
     * @return Question
     */
    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    /**
     * @param Question $question
     */
    public function setQuestion(Question $question): void
    {
        $this->question = $question;
    }

    /**
     * @return Phase|null
     */
    public function getPhase(): ?Phase
    {
        return $this->phase;
    }

    /**
     * @param Phase $phase
     */
    public function setPhase(Phase $phase): void
    {
        $this->phase = $phase;
    }

    /**
     * @return Affiliation|null
     */
    public function getAffiliation(): ?Affiliation
    {
        return $this->affiliation;
    }

    /**
     * @param Affiliation $affiliation
     */
    public function setAffiliation(Affiliation $affiliation): void
    {
        $this->affiliation = $affiliation;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateCreated(): ?\DateTime
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     */
    public function setDateCreated(\DateTime $dateCreated): void
    {
        $this->dateCreated = $dateCreated;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateUpdated(): ?\DateTime
    {
        return $this->dateUpdated;
    }

    /**
     * @param \DateTime|null $dateUpdated
     */
    public function setDateUpdated(?\DateTime $dateUpdated): void
    {
        $this->dateUpdated = $dateUpdated;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateChanged(): ?\DateTime
    {
        return $this->dateChanged;
    }

    /**
     * @param \DateTime|null $dateChanged
     */
    public function setDateChanged(?\DateTime $dateChanged): void
    {
        $this->dateChanged = $dateChanged;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}
