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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;
use function sprintf;

/**
 * @ORM\Table(name="affiliation_version")
 * @ORM\Entity
 * @Annotation\Hydrator("Zend\Hydrator\ObjectProperty")
 * @Annotation\Name("affiliation_version")
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
     * @var Contact
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
    /**
     * @ORM\OneToOne(targetEntity="Project\Entity\Contract\Version", inversedBy="projectAffiliationVersion")
     * @ORM\JoinColumn(name="contract_version_id", referencedColumnName="version_id", nullable=true)
     *
     * @var \Project\Entity\Contract\Version
     */
    private $contractVersion;

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
        return sprintf(
            '%s in %s (%s)',
            $this->affiliation->getOrganisation(),
            $this->affiliation->getProject(),
            $this->version->getVersionType()->getDescription()
        );
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): Version
    {
        $this->id = $id;
        return $this;
    }

    public function getAffiliation(): ?Affiliation
    {
        return $this->affiliation;
    }

    public function setAffiliation(?Affiliation $affiliation): Version
    {
        $this->affiliation = $affiliation;
        return $this;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(?Contact $contact): Version
    {
        $this->contact = $contact;
        return $this;
    }

    public function getVersion(): ?\Project\Entity\Version\Version
    {
        return $this->version;
    }

    public function setVersion(?\Project\Entity\Version\Version $version): Version
    {
        $this->version = $version;
        return $this;
    }

    public function getCostVersion()
    {
        return $this->costVersion;
    }

    public function setCostVersion($costVersion): Version
    {
        $this->costVersion = $costVersion;
        return $this;
    }

    public function getEffortVersion()
    {
        return $this->effortVersion;
    }

    public function setEffortVersion($effortVersion): Version
    {
        $this->effortVersion = $effortVersion;
        return $this;
    }

    public function getFundingVersion()
    {
        return $this->fundingVersion;
    }

    public function setFundingVersion($fundingVersion): Version
    {
        $this->fundingVersion = $fundingVersion;
        return $this;
    }

    public function getContractVersion(): ?\Project\Entity\Contract\Version
    {
        return $this->contractVersion;
    }

    public function setContractVersion(?\Project\Entity\Contract\Version $contractVersion): Version
    {
        $this->contractVersion = $contractVersion;
        return $this;
    }


}
