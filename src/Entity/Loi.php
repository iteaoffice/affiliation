<?php
/**
 * ITEA copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Entity;

use Contact\Entity\Contact;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use General\Entity\ContentType;
use Zend\Form\Annotation;

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
     * @Annotation\Type("\Zend\Form\Element\Date")
     * @Annotation\Attributes({"step":"any"})
     * @Annotation\Options({"label":"txt-date-signed", "format":"Y-m-d"})
     *
     * @var DateTime
     */
    private $dateSigned;
    /**
     * @ORM\Column(name="date_approved", type="datetime", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Date")
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
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\LoiObject", cascade={"persist","remove"}, mappedBy="loi")
     * @Annotation\Exclude()
     *
     * @var LoiObject[]|ArrayCollection
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

    public function __construct()
    {
        $this->object = new ArrayCollection();
    }

    public function hasObject(): bool
    {
        return !$this->object->isEmpty();
    }

    public function __toString(): string
    {
        return sprintf('Loi: %s', $this->id);
    }

    public function parseFileName(): string
    {
        return sprintf('LOI_%s_%s', $this->getAffiliation()->getOrganisation(), $this->getAffiliation()->getProject());
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

    public function getId()
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

    public function getSize()
    {
        return $this->size;
    }

    public function setSize($size): Loi
    {
        $this->size = $size;

        return $this;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function setObject($object): Loi
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

    public function setContact($contact)
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
