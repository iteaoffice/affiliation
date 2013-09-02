<?php

namespace Affiliation\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AffiliationVersion
 *
 * @ORM\Table(name="affiliation_version")
 * @ORM\Entity
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
     * @var \Affiliation
     *
     * @ORM\ManyToOne(targetEntity="Affiliation")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id")
     * })
     */
    private $affiliation;

    /**
     * @var \Contact
     *
     * @ORM\ManyToOne(targetEntity="Contact")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id")
     * })
     */
    private $contact;

    /**
     * @var \ProjectVersion
     *
     * @ORM\ManyToOne(targetEntity="ProjectVersion")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="version_id", referencedColumnName="version_id")
     * })
     */
    private $version;
}
