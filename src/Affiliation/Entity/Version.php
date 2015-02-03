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

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;

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
class Version extends EntityAbstract
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
     * @var Affiliation
     */
    private $affiliation;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", inversedBy="affiliationVersion")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id", nullable=false)
     * })
     * @var \Contact\Entity\Contact
     */
    private $contact;
    /**
     * @ORM\ManyToOne(targetEntity="Project\Entity\Version\Version", inversedBy="affiliationVersion")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="version_id", referencedColumnName="version_id", nullable=false)
     * })
     * @var \Project\Entity\Version\Version
     */
    private $version;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Cost\Version", cascade={"persist","remove"},  mappedBy="affiliationVersion")
     * @var \Project\Entity\Cost\Version
     */
    private $costVersion;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Effort\Version", cascade={"persist","remove"}, mappedBy="affiliationVersion")
     * @var \Project\Entity\Effort\Version
     */
    private $effortVersion;

    /**
     * @param $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        return $this->$property;
    }

    /**
     * @param $property
     * @param $value
     *
     * @return void
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    /**
     * @param InputFilterInterface $inputFilter
     *
     * @return void
     * @throws \Exception
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception(sprintf("This class %s is unused", __CLASS__));
    }

    /**
     * @return \Zend\InputFilter\InputFilter|\Zend\InputFilter\InputFilterInterface
     */
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    /**
     * Needed for the hydration of form elements
     * @return array
     */
    public function getArrayCopy()
    {
        return [];
    }

    /**
     * @return array
     */
    public function populate()
    {
        return $this->getArrayCopy();
    }

    /**
     * @param Affiliation $affiliation
     */
    public function setAffiliation($affiliation)
    {
        $this->affiliation = $affiliation;
    }

    /**
     * @return Affiliation
     */
    public function getAffiliation()
    {
        return $this->affiliation;
    }

    /**
     * @param \Contact\Entity\Contact $contact
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
    }

    /**
     * @return \Contact\Entity\Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param \Project\Entity\Cost\Version $costVersion
     */
    public function setCostVersion($costVersion)
    {
        $this->costVersion = $costVersion;
    }

    /**
     * @return \Project\Entity\Cost\Version
     */
    public function getCostVersion()
    {
        return $this->costVersion;
    }

    /**
     * @param \Project\Entity\Effort\Version $effortVersion
     */
    public function setEffortVersion($effortVersion)
    {
        $this->effortVersion = $effortVersion;
    }

    /**
     * @return \Project\Entity\Effort\Version
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
     * @param \Project\Entity\Version\Version $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return \Project\Entity\Version\Version
     */
    public function getVersion()
    {
        return $this->version;
    }
}
