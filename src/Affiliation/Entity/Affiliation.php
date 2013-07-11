<?php

namespace Affiliation\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Affiliation
 *
 * @ORM\Table(name="affiliation")
 * @ORM\Entity
 */
class Affiliation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="affiliation_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $affiliationId;

    /**
     * @var string
     *
     * @ORM\Column(name="branch", type="string", length=40, nullable=true)
     */
    private $branch;

    /**
     * @var string
     *
     * @ORM\Column(name="note", type="text", nullable=true)
     */
    private $note;

    /**
     * @var string
     *
     * @ORM\Column(name="value_chain", type="string", length=60, nullable=true)
     */
    private $valueChain;

    /**
     * @var integer
     *
     * @ORM\Column(name="self_funded", type="smallint", nullable=false)
     */
    private $selfFunded;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     */
    private $dateCreated;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_end", type="datetime", nullable=true)
     */
    private $dateEnd;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_self_funded", type="datetime", nullable=true)
     */
    private $dateSelfFunded;

    /**
     * @var \Contact
     *
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id")
     * })
     */
    private $contact;

//    /**
//     * @var \Organisation
//     *
//     * @ORM\ManyToOne(targetEntity="Organisation")
//     * @ORM\JoinColumns({
//     *   @ORM\JoinColumn(name="organisation_id", referencedColumnName="organisation_id")
//     * })
//     */
//    private $organisation;

    /**
     * @var \Project
     *
     * @ORM\ManyToOne(targetEntity="Project\Entity\Project")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="project_id", referencedColumnName="project_id")
     * })
     */
    private $project;
}
