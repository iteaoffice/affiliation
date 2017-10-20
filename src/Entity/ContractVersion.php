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
 * @ORM\Table(name="affiliation_contract_version")
 * @ORM\Entity
 * @Annotation\Hydrator("Zend\Hydrator\ObjectProperty")
 * @Annotation\Name("affiliation_contract_version")
 *
 * @category    Affiliation
 */
class ContractVersion extends EntityAbstract
{
    /**
     * @var integer
     *
     * @ORM\Column(name="affiliation_contract_version_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    /**
     * @ORM\ManyToOne(targetEntity="Affiliation\Entity\Affiliation", inversedBy="contractVersion")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id", nullable=false)
     * })
     *
     * @var Affiliation
     */
    private $affiliation;
    /**
     * @ORM\ManyToOne(targetEntity="Project\Entity\Contract\Version", inversedBy="affiliationVersion", cascade={"persist"})
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="version_id", referencedColumnName="version_id", nullable=false)
     * })
     *
     * @var \Project\Entity\Contract\Version
     */
    private $version;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Contract\CostVersion", cascade={"persist","remove"},  mappedBy="affiliationVersion")
     *
     * @var \Project\Entity\Contract\CostVersion[]|ArrayCollection
     */
    private $costVersion;

    /**
     * Version constructor.
     */
    public function __construct()
    {
        $this->costVersion = new ArrayCollection();
    }

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
     * @return void;
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    /**
     * @param $property
     *
     * @return bool
     */
    public function __isset($property)
    {
        return isset($this->$property);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            "%s in %s (%s)",
            $this->getAffiliation()->getOrganisation(),
            $this->getAffiliation()->getProject(),
            $this->getVersion()->getVersionType()->getDescription()
        );
    }

    /**
     * @return Affiliation
     */
    public function getAffiliation(): Affiliation
    {
        return $this->affiliation;
    }

    /**
     * @param Affiliation $affiliation
     * @return ContractVersion
     */
    public function setAffiliation(Affiliation $affiliation): ContractVersion
    {
        $this->affiliation = $affiliation;

        return $this;
    }

    /**
     * @return \Project\Entity\Contract\Version
     */
    public function getVersion(): \Project\Entity\Contract\Version
    {
        return $this->version;
    }

    /**
     * @param \Project\Entity\Contract\Version $version
     * @return ContractVersion
     */
    public function setVersion(\Project\Entity\Contract\Version $version): ContractVersion
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ContractVersion
     */
    public function setId(int $id): ContractVersion
    {
        $this->id = $id;

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
     * @return ContractVersion
     */
    public function setCostVersion($costVersion): ContractVersion
    {
        $this->costVersion = $costVersion;

        return $this;
    }
}
