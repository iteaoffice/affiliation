<?php
/**
 * ITEA copyright message placeholder.
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/**
 * Entity for the Affiliation.
 *
 * @ORM\Table(name="affiliation_financial")
 * @ORM\Entity
 * @Annotation\Hydrator("Zend\Hydrator\ObjectProperty")
 * @Annotation\Name("affiliation_financial")
 *
 * @category    Affiliation
 */
class Financial extends AbstractEntity
{
    /**
     * @ORM\Column(name="affiliation_financial_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="branch", type="string", nullable=true)
     *
     * @var string
     */
    private $branch;
    /**
     * @ORM\OneToOne(targetEntity="\Affiliation\Entity\Affiliation", inversedBy="financial", cascade={"persist"})
     * @ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id", nullable=false)
     *
     * @var \Affiliation\Entity\Affiliation
     */
    private $affiliation;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", inversedBy="financial", cascade={"persist"})
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id", nullable=false)
     *
     * @var \Contact\Entity\Contact
     */
    private $contact;
    /**
     * @ORM\ManyToOne(targetEntity="Organisation\Entity\Organisation", inversedBy="affiliationFinancial", cascade={"persist"})
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="organisation_id", nullable=false)
     *
     * @var \Organisation\Entity\Organisation
     */
    private $organisation;
    /**
     * @ORM\Column(name="email_cc", type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $emailCC;

    public function __toString(): string
    {
        return \sprintf("%s financial", $this->affiliation);
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

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): Financial
    {
        $this->id = $id;

        return $this;
    }

    public function getBranch()
    {
        return $this->branch;
    }

    public function setBranch($branch): Financial
    {
        $this->branch = $branch;

        return $this;
    }

    public function getAffiliation()
    {
        return $this->affiliation;
    }

    public function setAffiliation($affiliation): Financial
    {
        $this->affiliation = $affiliation;

        return $this;
    }

    /**
     * @return \Contact\Entity\Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    public function setContact($contact): Financial
    {
        $this->contact = $contact;

        return $this;
    }

    public function getOrganisation()
    {
        return $this->organisation;
    }

    public function setOrganisation($organisation): Financial
    {
        $this->organisation = $organisation;

        return $this;
    }


    public function getEmailCC(): ?string
    {
        return $this->emailCC;
    }

    public function setEmailCC(string $emailCC): Financial
    {
        $this->emailCC = $emailCC;

        return $this;
    }
}
