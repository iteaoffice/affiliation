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
 * @ORM\Table(name="affiliation_invoice")
 * @ORM\Entity
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Name("affiliation_invoice")
 *
 * @category    Affiliation
 */
class Invoice //extends EntityAbstract
{
    /**
     * @ORM\Column(name="affiliation_invoice_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="period", type="integer", nullable=false)
     *
     * @var integer
     */
    private $period;
    /**
     * @ORM\Column(name="year", type="integer", nullable=false)
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
     * @param $property
     * @param $value
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    /**
     * Returns the string identifier of the Resource.
     *
     * @return string
     */
    public function getResourceId()
    {
        return sprintf("%s:%s", __CLASS__, $this->id);
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
     * @return int
     */
    public function getVersionId()
    {
        return $this->versionId;
    }

    /**
     * @param int $versionId
     */
    public function setVersionId($versionId)
    {
        $this->versionId = $versionId;
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
     * @return \Project\Entity\Version\Version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param \Project\Entity\Version\Version $version
     * @return Invoice
     */
    public function setVersion($version)
    {
        $this->version = $version;

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
     * @param \Invoice\Entity\Invoice $invoice
     * @return Invoice
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;

        return $this;
    }
}
