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
 * ProjectDoa.
 *
 * @ORM\Table(name="project_doa")
 * @ORM\Entity(repositoryClass="Affiliation\Repository\Doa")
 */
class Doa extends AbstractEntity
{
    /**
     * @ORM\Column(name="doa_id", type="integer", options={"unsigned":true})
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
     * @ORM\ManyToOne(targetEntity="General\Entity\ContentType", cascade={"persist"}, inversedBy="affiliationDoa")
     * @ORM\JoinColumn(name="contenttype_id", referencedColumnName="contenttype_id", nullable=true)
     * @Annotation\Exclude()
     *
     * @var \General\Entity\ContentType
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
     * @var \Affiliation\Entity\DoaObject[]|ArrayCollection
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
     * @ORM\Column(name="date_received", type="date", nullable=true)
     * @Gedmo\Timestampable(on="create")
     * @Annotation\Exclude()
     *
     * @var \DateTime
     */
    private $dateCreated;
    /**
     * @ORM\OneToOne(targetEntity="Affiliation\Entity\Affiliation", cascade="persist", inversedBy="doa")
     * @ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id")
     * @Annotation\Exclude()
     *
     * @var \Affiliation\Entity\Affiliation
     */
    private $affiliation;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", cascade="persist", inversedBy="affiliationDoa")
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
        return sprintf("Doa: %s", $this->id);
    }

    public function parseFileName(): string
    {
        return sprintf('DOA_%s_%s', $this->getAffiliation()->getOrganisation(), $this->getAffiliation()->getProject());
    }

    /**
     * @return Affiliation
     */
    public function getAffiliation()
    {
        return $this->affiliation;
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return Doa
     */
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
     * @return Doa
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
     * @return Doa
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
     * @return Doa
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
     * @return Doa
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
     * @return Doa
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return DoaObject[]|ArrayCollection
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param DoaObject[]|ArrayCollection $object
     *
     * @return Doa
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
     * @return Doa
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
     * @return Doa
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
     * @return Doa
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }
}
