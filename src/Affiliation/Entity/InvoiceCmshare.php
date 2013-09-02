<?php

namespace Affiliation\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AffiliationInvoiceCmshare
 *
 * @ORM\Table(name="affiliation_invoice_cmshare")
 * @ORM\Entity
 */
class InvoiceCmshare
{
    /**
     * @var integer
     *
     * @ORM\Column(name="cmshare_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="year", type="integer", nullable=false)
     */
    private $year;

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
