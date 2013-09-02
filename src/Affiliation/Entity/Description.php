<?php

namespace Affiliation\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AffiliationDescription
 *
 * @ORM\Table(name="affiliation_description")
 * @ORM\Entity
 */
class Description
{
    /**
     * @var integer
     *
     * @ORM\Column(name="affiliation_description_id", type="integer", nullable=false)
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
     * @var \Description
     *
     * @ORM\ManyToOne(targetEntity="Description")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="description_id", referencedColumnName="description_id")
     * })
     */
    private $description;
}
