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

namespace Affiliation\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Zend\Form\Annotation;

/**
 * ProjectLoi.
 *
 * @ORM\Table(name="project_loi")
 * @ORM\Entity(repositoryClass="Affiliation\Repository\Loi")
 */
class Loi extends AbstractEntity
{
    /**
     * @ORM\Column(name="loi_id",type="integer",options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="date_signed", type="date", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Date")
     * @Annotation\Attributes({"step":"any"})
     * @Annotation\Options({"label":"txt-date-signed", "format":"Y-m-d"})
     *
     * @var \DateTime
     */
    private $dateSigned;
    /**
     * @ORM\Column(name="date_approved", type="datetime", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Date")
     * @Annotation\Attributes({"step":"any"})
     * @Annotation\Options({"label":"txt-date-approved", "format":"Y-m-d"})
     *
     * @var \DateTime
     */
    private $dateApproved;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", cascade={"persist"}, inversedBy="loiApprover")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="approve_contact_id", referencedColumnName="contact_id")
     * })
     *
     * @var \Contact\Entity\Contact
     */
    private $approver;
    /**
     * @ORM\ManyToOne(targetEntity="General\Entity\ContentType", cascade={"persist"}, inversedBy="loi")
     * @ORM\JoinColumn(name="contenttype_id", referencedColumnName="contenttype_id", nullable=false)
     * @Annotation\Exclude()
     *
     * @var \General\Entity\ContentType
     */
    private $contentType;
    /**
     * @ORM\Column(name="size", type="integer", nullable=false)
     *
     * @var integer
     */
    private $size;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\LoiObject", cascade={"persist","remove"}, mappedBy="loi")
     * @Annotation\Exclude()
     *
     * @var \Affiliation\Entity\LoiObject[]|ArrayCollection
     */
    private $object;
    /**
     * @ORM\Column(name="date_updated", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     * @Annotation\Exclude()
     *
     * @var \DateTime
     */
    private $dateUpdated;
    /**
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     * @Annotation\Exclude()
     *
     * @var \DateTime
     */
    private $dateCreated;
    /**
     * @ORM\OneToOne(targetEntity="Affiliation\Entity\Affiliation", cascade="persist", inversedBy="loi")
     * @ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id", nullable=false)
     *
     * @var \Affiliation\Entity\Affiliation
     */
    private $affiliation;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", cascade="persist", inversedBy="loi")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id", nullable=false)
     * @Annotation\Exclude()
     *
     * @var \Contact\Entity\Contact
     */
    private $contact;

    public function __construct()
    {
        $this->object = new ArrayCollection();
    }

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

    public function setAffiliation($affiliation)
    {
        $this->affiliation = $affiliation;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Loi
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateSigned()
    {
        return $this->dateSigned;
    }

    /**
     * @param \DateTime $dateSigned
     *
     * @return Loi
     */
    public function setDateSigned($dateSigned)
    {
        $this->dateSigned = $dateSigned;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateApproved()
    {
        return $this->dateApproved;
    }

    /**
     * @param \DateTime $dateApproved
     *
     * @return Loi
     */
    public function setDateApproved($dateApproved)
    {
        $this->dateApproved = $dateApproved;

        return $this;
    }

    /**
     * @return \General\Entity\ContentType
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param \General\Entity\ContentType $contentType
     *
     * @return Loi
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     *
     * @return Loi
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return LoiObject[]|ArrayCollection
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param LoiObject[]|ArrayCollection $object
     *
     * @return Loi
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateUpdated()
    {
        return $this->dateUpdated;
    }

    /**
     * @param \DateTime $dateUpdated
     *
     * @return Loi
     */
    public function setDateUpdated($dateUpdated)
    {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     *
     * @return Loi
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * @return \Contact\Entity\Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param \Contact\Entity\Contact $contact
     *
     * @return Loi
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return \Contact\Entity\Contact
     */
    public function getApprover(): ?\Contact\Entity\Contact
    {
        return $this->approver;
    }

    /**
     * @param \Contact\Entity\Contact $approver
     *
     * @return Loi
     */
    public function setApprover(\Contact\Entity\Contact $approver): Loi
    {
        $this->approver = $approver;

        return $this;
    }
}
