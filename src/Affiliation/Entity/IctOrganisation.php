<?php

namespace Affiliation\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AffiliationIctOrganisation
 *
 * @ORM\Table(name="affiliation_ict_organisation")
 * @ORM\Entity
 */
class IctOrganisation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="affiliation_ict_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \Affiliation
     *
     * @ORM\ManyToOne(targetEntity="Affiliation")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id")
     * })
     */
    private $affiliation;

    /**
     * @var \IctOrganisation
     *
     * @ORM\ManyToOne(targetEntity="IctOrganisation")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="ict_id", referencedColumnName="ict_id")
     * })
     */
    private $ict;
}
