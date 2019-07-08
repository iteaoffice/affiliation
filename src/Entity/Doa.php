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

use Contact\Entity\Contact;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use General\Entity\ContentType;
use Zend\Form\Annotation;

/**
 * ProjectDoa.
 *
 * @ORM\Table(name="project_doa")
 * @ORM\Entity(repositoryClass="Affiliation\Repository\Doa")
 */
class Doa extends AbstractEntity
{
    /**
     * @ORM\Column(name="doa_id",type="integer",options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Annotation\Exclude()
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
     * @var DateTime
     */
    private $dateSigned;
    /**
     * @ORM\Column(name="date_approved", type="datetime", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Date")
     * @Annotation\Attributes({"step":"any"})
     * @Annotation\Options({"label":"txt-date-approved", "format":"Y-m-d"})
     *
     * @var DateTime
     */
    private $dateApproved;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", cascade={"persist"}, inversedBy="affiliationDoaApprover")
     * @ORM\JoinColumn(name="approve_contact_id", referencedColumnName="contact_id")
     * @Annotation\Type("\Contact\Form\Element\Contact")
     * @Annotation\Attributes({"label":"txt-affiliation-doa-approver-label"})
     * @Annotation\Options({"help-block":"txt-affiliation-doa-approver-help-block"})
     *
     * @var Contact
     */
    private $approver;
    /**
     * @ORM\ManyToOne(targetEntity="General\Entity\ContentType", cascade={"persist"}, inversedBy="affiliationDoa")
     * @ORM\JoinColumn(name="contenttype_id", referencedColumnName="contenttype_id", nullable=true)
     * @Annotation\Exclude()
     *
     * @var ContentType
     */
    private $contentType;
    /**
     * @ORM\Column(name="size", type="integer", nullable=false, nullable=true)
     * @Annotation\Exclude()
     *
     * @var integer
     */
    private $size;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\DoaObject", cascade={"persist","remove"}, mappedBy="doa")
     * @Annotation\Exclude()
     *
     * @var DoaObject[]|ArrayCollection
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
     * @ORM\Column(name="date_received", type="date", nullable=true)
     * @Gedmo\Timestampable(on="create")
     * @Annotation\Exclude()
     *
     * @var DateTime
     */
    private $dateCreated;
    /**
     * @ORM\OneToOne(targetEntity="Affiliation\Entity\Affiliation", cascade="persist", inversedBy="doa")
     * @ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id")
     * @Annotation\Exclude()
     *
     * @var Affiliation
     */
    private $affiliation;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", cascade="persist", inversedBy="affiliationDoa")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id", nullable=false)
     * @Annotation\Exclude()
     *
     * @var Contact
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
        return sprintf('Doa: %s', $this->id);
    }

    public function parseFileName(): string
    {
        return sprintf('DOA_%s_%s', $this->affiliation->getOrganisation(), $this->affiliation->getProject());
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): Doa
    {
        $this->id = $id;
        return $this;
    }

    public function getDateSigned(): ?DateTime
    {
        return $this->dateSigned;
    }

    public function setDateSigned(DateTime $dateSigned): Doa
    {
        $this->dateSigned = $dateSigned;
        return $this;
    }

    public function getDateApproved(): ?DateTime
    {
        return $this->dateApproved;
    }

    public function setDateApproved(?DateTime $dateApproved): Doa
    {
        $this->dateApproved = $dateApproved;
        return $this;
    }

    public function getApprover(): ?Contact
    {
        return $this->approver;
    }

    public function setApprover(Contact $approver): Doa
    {
        $this->approver = $approver;
        return $this;
    }

    public function getContentType(): ?ContentType
    {
        return $this->contentType;
    }

    public function setContentType(ContentType $contentType): Doa
    {
        $this->contentType = $contentType;
        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): Doa
    {
        $this->size = $size;
        return $this;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function setObject($object): Doa
    {
        $this->object = $object;
        return $this;
    }

    public function getDateUpdated(): ?DateTime
    {
        return $this->dateUpdated;
    }

    public function setDateUpdated(DateTime $dateUpdated): Doa
    {
        $this->dateUpdated = $dateUpdated;
        return $this;
    }

    public function getDateCreated(): ?DateTime
    {
        return $this->dateCreated;
    }

    public function setDateCreated(DateTime $dateCreated): Doa
    {
        $this->dateCreated = $dateCreated;
        return $this;
    }

    public function getAffiliation(): ?Affiliation
    {
        return $this->affiliation;
    }

    public function setAffiliation(Affiliation $affiliation): Doa
    {
        $this->affiliation = $affiliation;
        return $this;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(Contact $contact): Doa
    {
        $this->contact = $contact;
        return $this;
    }


}
