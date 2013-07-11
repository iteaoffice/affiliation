<?php


use Doctrine\ORM\Mapping as ORM;

/**
 * AffiliationInvoicePostcalc
 *
 * @ORM\Table(name="affiliation_invoice_postcalc")
 * @ORM\Entity
 */
class AffiliationInvoicePostcalc
{
    /**
     * @var integer
     *
     * @ORM\Column(name="postcalc_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $postcalcId;

    /**
     * @var float
     *
     * @ORM\Column(name="amount_invoiced", type="decimal", nullable=false)
     */
    private $amountInvoiced;

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
     * @var \Invoice
     *
     * @ORM\ManyToOne(targetEntity="Invoice")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="invoice_id", referencedColumnName="invoice_id")
     * })
     */
    private $invoice;
}
