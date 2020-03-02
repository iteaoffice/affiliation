<?php

/**
 * ITEA copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Project\Entity\Contract\CostVersion;
use Laminas\Form\Annotation;

use function sprintf;

/**
 * @ORM\Table(name="affiliation_contract_version")
 * @ORM\Entity
 * @Annotation\Hydrator("Laminas\Hydrator\ObjectPropertyHydrator")
 * @Annotation\Name("affiliation_contract_version")
 */
class ContractVersion extends AbstractEntity
{
    /**
     * @ORM\Column(name="affiliation_contract_version_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    /**
     * @ORM\ManyToOne(targetEntity="Affiliation\Entity\Affiliation", inversedBy="contractVersion")
     * @ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id", nullable=false)
     *
     * @var Affiliation
     */
    private $affiliation;
    /**
     * @ORM\ManyToOne(targetEntity="Project\Entity\Contract\Version", inversedBy="affiliationVersion", cascade={"persist"})
     * @ORM\JoinColumn(name="version_id", referencedColumnName="version_id", nullable=false)
     *
     * @var \Project\Entity\Contract\Version
     */
    private $version;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Contract\CostVersion", cascade={"persist","remove"},  mappedBy="affiliationVersion")
     *
     * @var CostVersion[]|ArrayCollection
     */
    private $costVersion;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Version", cascade="persist", mappedBy="contractVersion")
     * @Annotation\Exclude()
     *
     * @var Version[]|ArrayCollection
     */
    private $affiliationVersion;

    public function __construct()
    {
        $this->costVersion = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf(
            '%s in %s (%s)',
            $this->getAffiliation()->getOrganisation(),
            $this->getAffiliation()->getProject(),
            $this->getVersion()->getContract()->getCountry()
        );
    }

    public function getAffiliation(): Affiliation
    {
        return $this->affiliation;
    }

    public function setAffiliation(Affiliation $affiliation): ContractVersion
    {
        $this->affiliation = $affiliation;

        return $this;
    }

    public function getVersion(): \Project\Entity\Contract\Version
    {
        return $this->version;
    }

    public function setVersion(\Project\Entity\Contract\Version $version): ContractVersion
    {
        $this->version = $version;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): ContractVersion
    {
        $this->id = $id;

        return $this;
    }

    public function getCostVersion()
    {
        return $this->costVersion;
    }

    public function setCostVersion($costVersion): ContractVersion
    {
        $this->costVersion = $costVersion;

        return $this;
    }

    public function getAffiliationVersion()
    {
        return $this->affiliationVersion;
    }

    public function setAffiliationVersion($affiliationVersion): ContractVersion
    {
        $this->affiliationVersion = $affiliationVersion;
        return $this;
    }
}
