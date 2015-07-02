<?php
/**
 * ITEA copyright message placeholder.
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */

namespace Affiliation\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/**
 * Entity for the Affiliation.
 *
 * @ORM\Table(name="affiliation_invoice_postcalc")
 * @ORM\Entity
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Name("affiliation_invoice_postcalc")
 *
 * @category    Affiliation
 */
class InvoicePostCalc extends EntityAbstract
{
    /**
     * @ORM\Column(name="postcalc_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="amount_invoiced", type="decimal", nullable=false)
     *
     * @var float
     */
    private $amountInvoiced;
    /**
     * @ORM\ManyToOne(targetEntity="\Affiliation\Entity\Affiliation", inversedBy="invoicePostCalc", cascade={"persist"})
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id", nullable=false)
     * })
     *
     * @var \Affiliation\Entity\Affiliation
     */
    private $affiliation;
    /**
     * @ORM\OneToOne(targetEntity="Invoice\Entity\Invoice", inversedBy="affiliationInvoicePostCalc", cascade={"persist"})
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="invoice_id", referencedColumnName="invoice_id", nullable=false)
     * })
     * @var \Invoice\Entity\Invoice
     */
    private $invoice;

    /**
     * Class constructor.
     */
    public function __construct()
    {
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
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  int             $id
     * @return InvoicePostCalc
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmountInvoiced()
    {
        return $this->amountInvoiced;
    }

    /**
     * @param  float           $amountInvoiced
     * @return InvoicePostCalc
     */
    public function setAmountInvoiced($amountInvoiced)
    {
        $this->amountInvoiced = $amountInvoiced;

        return $this;
    }

    /**
     * @return Affiliation
     */
    public function getAffiliation()
    {
        return $this->affiliation;
    }

    /**
     * @param  Affiliation     $affiliation
     * @return InvoicePostCalc
     */
    public function setAffiliation($affiliation)
    {
        $this->affiliation = $affiliation;

        return $this;
    }

    /**
     * @return \Invoice\Entity\Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param  \Invoice\Entity\Invoice $invoice
     * @return InvoicePostCalc
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;

        return $this;
    }
}
