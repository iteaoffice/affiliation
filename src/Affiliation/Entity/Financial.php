<?php


use Doctrine\ORM\Mapping as ORM;

/**
 * AffiliationFinancial
 *
 * @ORM\Table(name="affiliation_financial")
 * @ORM\Entity
 */
class AffiliationFinancial
{
    /**
     * @var integer
     *
     * @ORM\Column(name="affiliation_financial_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $affiliationFinancialId;

    /**
     * @var string
     *
     * @ORM\Column(name="branch", type="string", length=40, nullable=true)
     */
    private $branch;

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
     * @var \Organisation
     *
     * @ORM\ManyToOne(targetEntity="Organisation")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="organisation_id")
     * })
     */
    private $organisation;
}
