<?php
/**
 * ITEA copyright message placeholder
 *
 * @category    Affiliation
 * @package     Entity
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Affiliation\Entity;

use Zend\Form\Annotation;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity for the Affiliation
 *
 * @ORM\Table(name="affiliation_version")
 * @ORM\Entity
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Name("affiliation_version")
 *
 * @category    Affiliation
 * @package     Entity
 */
class Version
{
    /**
     * @var integer
     *
     * @ORM\Column(name="affiliation_version_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    /**
     * @ORM\ManyToOne(targetEntity="Affiliation\Entity\Affiliation", inversedBy="version")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id", nullable=false)
     * })
     * @var \Contact\Entity\Contact
     */
    private $affiliation;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", inversedBy="affiliationVersion")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id", nullable=false)
     * })
     * @var \Project\Entity\Version\Version
     */
    private $contact;
    /**
     * @ORM\ManyToOne(targetEntity="Project\Entity\Version\Version", inversedBy="affiliationVersion")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="version_id", referencedColumnName="version_id", nullable=false)
     * })
     * @var \Contact\Entity\Contact
     */
    private $version;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Cost\Version", cascade="persist", mappedBy="affiliationVersion")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="version_id", referencedColumnName="version_id", nullable=false)
     * })
     * @var \Contact\Entity\Contact
     */
    private $costVersion;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Effort\Version", cascade="persist", mappedBy="affiliationVersion")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="version_id", referencedColumnName="version_id", nullable=false)
     * })
     * @var \Contact\Entity\Contact
     */
    private $effortVersion;

    /**
     * @param \Contact\Entity\Contact $affiliation
     */
    public function setAffiliation($affiliation)
    {
        $this->affiliation = $affiliation;
    }

    /**
     * @return \Contact\Entity\Contact
     */
    public function getAffiliation()
    {
        return $this->affiliation;
    }

    /**
     * @param \Project\Entity\Version\Version $contact
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
    }

    /**
     * @return \Project\Entity\Version\Version
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param \Contact\Entity\Contact $costVersion
     */
    public function setCostVersion($costVersion)
    {
        $this->costVersion = $costVersion;
    }

    /**
     * @return \Contact\Entity\Contact
     */
    public function getCostVersion()
    {
        return $this->costVersion;
    }

    /**
     * @param \Contact\Entity\Contact $effortVersion
     */
    public function setEffortVersion($effortVersion)
    {
        $this->effortVersion = $effortVersion;
    }

    /**
     * @return \Contact\Entity\Contact
     */
    public function getEffortVersion()
    {
        return $this->effortVersion;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Contact\Entity\Contact $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return \Contact\Entity\Contact
     */
    public function getVersion()
    {
        return $this->version;
    }
}
