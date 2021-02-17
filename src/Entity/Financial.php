<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Entity;

use Contact\Entity\Contact;
use Doctrine\ORM\Mapping as ORM;
use Laminas\Form\Annotation;
use Organisation\Entity\Organisation;

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
     */
    private ?int $id = null;
    /**
     * @ORM\Column(name="branch", type="string", nullable=true)
     * @Annotation\Type("\Laminas\Form\Element\Text")
     * @Annotation\Options({"label":"txt-affiliation-financial-branch-label","help-block":"txt-affiliation-financial-branch-help-block"})
     * @Annotation\Options({"placeholder":"txt-affiliation-financial-branch-placeholder"})
     */
    private ?string $branch = null;
    /**
     * @ORM\OneToOne(targetEntity="Affiliation\Entity\Affiliation", inversedBy="financial", cascade={"persist"})
     * @ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id", nullable=false)
     * @Annotation\Exclude()
     */
    private ?Affiliation $affiliation = null;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", inversedBy="financial", cascade={"persist"})
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id", nullable=false)
     * @Annotation\Type("\Contact\Form\Element\Contact")
     * @Annotation\Attributes({"label":"txt-affiliation-financial-contact-label"})
     * @Annotation\Options({"help-block":"txt-affiliation-financial-contact-help-block"})
     */
    private ?Contact $contact = null;
    /**
     * @ORM\ManyToOne(targetEntity="Organisation\Entity\Organisation", inversedBy="affiliationFinancial", cascade={"persist"})
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="organisation_id", nullable=false)
     * @Annotation\Type("\Organisation\Form\Element\OrganisationElement")
     * @Annotation\Attributes({"label":"txt-affiliation-financial-organisation-label"})
     * @Annotation\Options({"help-block":"txt-affiliation-financial-organisation-help-block"})
     */
    private ?Organisation $organisation = null;
    /**
     * @ORM\Column(name="email_cc", type="string", nullable=true)
     * @Annotation\Type("\Laminas\Form\Element\Email")
     * @Annotation\Options({"label":"txt-affiliation-financial-email-cc-label","help-block":"txt-affiliation-financial-email-cc-help-block"})
     * @Annotation\Options({"placeholder":"txt-affiliation-financial-email-cc-placeholder"})
     */
    private ?string $emailCC = null;

    public function __toString(): string
    {
        return sprintf("%s financial", $this->affiliation->parseBranchedName());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id): Financial
    {
        $this->id = $id;

        return $this;
    }

    public function getBranch(): ?string
    {
        return $this->branch;
    }

    public function setBranch($branch): Financial
    {
        $this->branch = $branch;

        return $this;
    }

    public function getAffiliation(): ?Affiliation
    {
        return $this->affiliation;
    }

    public function setAffiliation(Affiliation $affiliation): Financial
    {
        $this->affiliation = $affiliation;

        return $this;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(Contact $contact): Financial
    {
        $this->contact = $contact;

        return $this;
    }

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(Organisation $organisation): Financial
    {
        $this->organisation = $organisation;

        return $this;
    }

    public function getEmailCC(): ?string
    {
        return $this->emailCC;
    }

    public function setEmailCC(?string $emailCC): Financial
    {
        $this->emailCC = $emailCC;

        return $this;
    }
}
