<?php
/**
 * ITEA copyright message placeholder.
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/**
 * Entity for the Affiliation.
 *
 * @ORM\Table(name="affiliation_invoice")
 * @ORM\Entity
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Name("affiliation_invoice")
 *
 * @category    Affiliation
 */
class Invoice extends EntityAbstract
{
    /**
     * @ORM\Column(name="affiliation_invoice_id", length=10, type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="period", length=10, type="integer", nullable=false)
     *
     * @var integer
     */
    private $period;
    /**
     * @ORM\Column(name="year", length=10, type="integer", nullable=false)
     *
     * @var integer
     */
    private $year;
    /**
     * @ORM\Column(name="amount_invoiced", type="decimal", nullable=true)
     *
     * @var float
     */
    private $amountInvoiced;
    /**
     * @ORM\ManyToOne(targetEntity="Project\Entity\Version\Version", inversedBy="invoice", cascade={"persist"})
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="version_id", referencedColumnName="version_id", nullable=true)
     * })
     *
     * @var \Project\Entity\Version\Version
     */
    private $version;
    /**
     * @ORM\ManyToOne(targetEntity="Affiliation\Entity\Affiliation", inversedBy="invoice", cascade={"persist"})
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id", nullable=false)
     * })
     *
     * @var \Affiliation\Entity\Affiliation
     */
    private $affiliation;
    /**
     * @ORM\OneToOne(targetEntity="Invoice\Entity\Invoice", inversedBy="affiliationInvoice", cascade={"persist"})
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
     * @inheritDoc
     */
    public function __toString()
    {
        return (string)$this->getInvoice();
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * @param int $period
     */
    public function setPeriod($period)
    {
        $this->period = $period;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param int $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * @return float
     */
    public function getAmountInvoiced()
    {
        return $this->amountInvoiced;
    }

    /**
     * @param float $amountInvoiced
     */
    public function setAmountInvoiced($amountInvoiced)
    {
        $this->amountInvoiced = $amountInvoiced;
    }

    /**
     * @return \Project\Entity\Version\Version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param \Project\Entity\Version\Version $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return Affiliation
     */
    public function getAffiliation()
    {
        return $this->affiliation;
    }

    /**
     * @param Affiliation $affiliation
     */
    public function setAffiliation($affiliation)
    {
        $this->affiliation = $affiliation;
    }

    /**
     * @return \Invoice\Entity\Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param \Invoice\Entity\Invoice $invoice
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
    }
}