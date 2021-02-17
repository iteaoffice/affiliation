<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Entity;

use Contact\Entity\Contact;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use General\Entity\ContentType;
use Laminas\Form\Annotation;

/**
 * @ORM\Table(name="project_loi")
 * @ORM\Entity(repositoryClass="Affiliation\Repository\Loi")
 */
class Loi extends AbstractEntity
{
    /**
     * @ORM\Column(name="loi_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;
    /**
     * @ORM\Column(name="date_signed", type="date", nullable=true)
     * @Annotation\Type("\Laminas\Form\Element\Date")
     * @Annotation\Attributes({"step":"any"})
     * @Annotation\Options({"label":"txt-date-signed", "format":"Y-m-d"})
     *
     * @var DateTime
     */
    private $dateSigned;
    /**
     * @ORM\Column(name="date_approved", type="datetime", nullable=true)
     * @Annotation\Type("\Laminas\Form\Element\Date")
     * @Annotation\Attributes({"step":"any"})
     * @Annotation\Options({"label":"txt-date-approved", "format":"Y-m-d"})
     * @Annotation\Options({"help-block":"txt-affiliation-loi-date-approved-help-block"})
     *
     * @var DateTime
     */
    private $dateApproved;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", cascade={"persist"}, inversedBy="loiApprover")
     * @ORM\JoinColumn(name="approve_contact_id", referencedColumnName="contact_id")
     * @Annotation\Type("\Contact\Form\Element\Contact")
     * @Annotation\Attributes({"label":"txt-affiliation-loi-approver-label"})
     * @Annotation\Options({"help-block":"txt-affiliation-loi-approver-help-block"})
     *
     * @var Contact
     */
    private $approver;
    /**
     * @ORM\ManyToOne(targetEntity="General\Entity\ContentType", cascade={"persist"}, inversedBy="loi")
     * @ORM\JoinColumn(name="contenttype_id", referencedColumnName="contenttype_id", nullable=true)
     * @Annotation\Exclude()
     *
     * @var ContentType
     */
    private $contentType;
    /**
     * @ORM\Column(name="size", type="integer", options={"unsigned":true}, nullable=true)
     *
     * @var int
     */
    private $size;
    /**
     * @ORM\OneToOne(targetEntity="Affiliation\Entity\LoiObject", cascade={"persist","remove"}, mappedBy="loi")
     * @Annotation\Exclude()
     *
     * @var \Affiliation\Entity\LoiObject|null
     */
    private $object;
    /**
     * @ORM\Column(name="date_updated", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     * @Annotation\Exclude()
     *
     * @var DateTime
     */
    private $dateUpdated;
    /**
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     * @Annotation\Exclude()
     *
     * @var DateTime
     */
    private $dateCreated;
    /**
     * @ORM\OneToOne(targetEntity="Affiliation\Entity\Affiliation", cascade="persist", inversedBy="loi")
     * @ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id", nullable=false)
     *
     * @var Affiliation
     */
    private $affiliation;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", cascade="persist", inversedBy="loi")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id", nullable=false)
     * @Annotation\Type("\Contact\Form\Element\Contact")
     * @Annotation\Attributes({"label":"txt-affiliation-loi-contact-label"})
     * @Annotation\Options({"help-block":"txt-affiliation-loi-contact-help-block"})
     *
     * @var Contact
     */
    private $contact;

    public function __toString(): string
    {
        return $this->parseFileName();
    }

    public function parseFileName(): string
    {
        return sprintf('LOI_%s_%s', $this->affiliation->parseBranchedName(), $this->affiliation->getProject());
    }

    public function hasObject(): bool
    {
        return null !== $this->object;
    }

    public function isSigned(): bool
    {
        return null !== $this->dateSigned;
    }

    public function isApproved(): bool
    {
        return null !== $this->dateApproved;
    }

    public function getAffiliation(): Affiliation
    {
        return $this->affiliation;
    }

    public function setAffiliation($affiliation): Loi
    {
        $this->affiliation = $affiliation;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id): Loi
    {
        $this->id = $id;

        return $this;
    }

    public function getDateSigned(): ?DateTime
    {
        return $this->dateSigned;
    }

    public function setDateSigned($dateSigned): Loi
    {
        $this->dateSigned = $dateSigned;

        return $this;
    }

    public function getDateApproved(): ?DateTime
    {
        return $this->dateApproved;
    }

    public function setDateApproved($dateApproved): Loi
    {
        $this->dateApproved = $dateApproved;

        return $this;
    }

    public function getContentType(): ?ContentType
    {
        return $this->contentType;
    }

    public function setContentType($contentType): Loi
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize($size): Loi
    {
        $this->size = $size;

        return $this;
    }

    public function getObject(): ?LoiObject
    {
        return $this->object;
    }

    public function setObject(?LoiObject $object): Loi
    {
        $this->object = $object;

        return $this;
    }

    public function getDateUpdated(): ?DateTime
    {
        return $this->dateUpdated;
    }

    public function setDateUpdated($dateUpdated): Loi
    {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    public function getDateCreated(): ?DateTime
    {
        return $this->dateCreated;
    }

    public function setDateCreated($dateCreated): Loi
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact($contact): Loi
    {
        $this->contact = $contact;

        return $this;
    }

    public function getApprover(): ?Contact
    {
        return $this->approver;
    }

    public function setApprover(?Contact $approver): Loi
    {
        $this->approver = $approver;

        return $this;
    }
}
