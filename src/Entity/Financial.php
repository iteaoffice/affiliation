<?php

/**
 * ITEA copyright message placeholder.
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Entity;

use Contact\Entity\Contact;
use Doctrine\ORM\Mapping as ORM;
use Organisation\Entity\Organisation;
use Laminas\Form\Annotation;

use function sprintf;

/**
 * @ORM\Table(name="affiliation_financial")
 * @ORM\Entity
 * @Annotation\Hydrator("Laminas\Hydrator\ObjectPropertyHydrator")
 * @Annotation\Name("affiliation_financial")
 */
class Financial extends AbstractEntity
{
    /**
     * @ORM\Column(name="affiliation_financial_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;
    /**
     * @ORM\Column(name="branch", type="string", nullable=true)
     *
     * @var string
     */
    private $branch;
    /**
     * @ORM\OneToOne(targetEntity="Affiliation\Entity\Affiliation", inversedBy="financial", cascade={"persist"})
     * @ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id", nullable=false)
     *
     * @var Affiliation
     */
    private $affiliation;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", inversedBy="financial", cascade={"persist"})
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id", nullable=false)
     *
     * @var Contact
     */
    private $contact;
    /**
     * @ORM\ManyToOne(targetEntity="Organisation\Entity\Organisation", inversedBy="affiliationFinancial", cascade={"persist"})
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="organisation_id", nullable=false)
     *
     * @var Organisation
     */
    private $organisation;
    /**
     * @ORM\Column(name="email_cc", type="string", nullable=true)
     *
     * @var string
     */
    private $emailCC;

    public function __toString(): string
    {
        return sprintf("%s financial", $this->affiliation);
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
     * @return Contact
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
