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
use Zend\Form\Annotation;

/**
 * Entity for the Affiliation.
 *
 * @ORM\Table(name="affiliation_version")
 * @ORM\Entity
 * @Annotation\Hydrator("Zend\Hydrator\ObjectProperty")
 * @Annotation\Name("affiliation_version")
 *
 * @category    Affiliation
 */
class Version extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="affiliation_version_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    /**
     * @ORM\ManyToOne(targetEntity="Affiliation\Entity\Affiliation", inversedBy="version")
     * @ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id", nullable=false)
     *
     * @var Affiliation
     */
    private $affiliation;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", inversedBy="affiliationVersion")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id", nullable=false)
     *
     * @var \Contact\Entity\Contact
     */
    private $contact;
    /**
     * @ORM\ManyToOne(targetEntity="Project\Entity\Version\Version", inversedBy="affiliationVersion")
     * @ORM\JoinColumn(name="version_id", referencedColumnName="version_id", nullable=false)
     *
     * @var \Project\Entity\Version\Version
     */
    private $version;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Cost\Version", cascade={"persist","remove"},  mappedBy="affiliationVersion")
     *
     * @var \Project\Entity\Cost\Version[]|ArrayCollection
     */
    private $costVersion;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Effort\Version", cascade={"persist","remove"}, mappedBy="affiliationVersion")
     *
     * @var \Project\Entity\Effort\Version[]|ArrayCollection
     */
    private $effortVersion;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Funding\Version", cascade={"persist","remove"}, mappedBy="affiliationVersion")
     *
     * @var \Project\Entity\Funding\Version[]|ArrayCollection
     */
    private $fundingVersion;

    public function __construct()
    {
        $this->costVersion = new ArrayCollection();
        $this->effortVersion = new ArrayCollection();
        $this->fundingVersion = new ArrayCollection();
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
        return \sprintf(
            '%s in %s (%s)',
            $this->getAffiliation()->getOrganisation(),
            $this->getAffiliation()->getProject(),
            $this->getVersion()->getVersionType()->getDescription()
        );
    }

    public function getAffiliation()
    {
        return $this->affiliation;
    }

    public function setAffiliation($affiliation)
    {
        $this->affiliation = $affiliation;

        return $this;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getContact()
    {
        return $this->contact;
    }

    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return ArrayCollection|\Project\Entity\Cost\Version[]
     */
    public function getCostVersion()
    {
        return $this->costVersion;
    }

    /**
     * @param ArrayCollection|\Project\Entity\Cost\Version[] $costVersion
     *
     * @return Version
     */
    public function setCostVersion($costVersion)
    {
        $this->costVersion = $costVersion;

        return $this;
    }

    /**
     * @return ArrayCollection|\Project\Entity\Effort\Version[]
     */
    public function getEffortVersion()
    {
        return $this->effortVersion;
    }

    /**
     * @param ArrayCollection|\Project\Entity\Effort\Version[] $effortVersion
     *
     * @return Version
     */
    public function setEffortVersion($effortVersion)
    {
        $this->effortVersion = $effortVersion;

        return $this;
    }

    /**
     * @return ArrayCollection|\Project\Entity\Funding\Version[]
     */
    public function getFundingVersion()
    {
        return $this->fundingVersion;
    }

    /**
     * @param ArrayCollection|\Project\Entity\Funding\Version[] $fundingVersion
     *
     * @return Version
     */
    public function setFundingVersion($fundingVersion)
    {
        $this->fundingVersion = $fundingVersion;

        return $this;
    }
}
