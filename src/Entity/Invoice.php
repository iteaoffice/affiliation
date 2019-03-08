<?php
/**
 * ITEA copyright message placeholder.
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Entity;

use Doctrine\ORM\Mapping as ORM;
use General\Entity\ExchangeRate;
use Zend\Form\Annotation;

/**
 * Entity for the Affiliation.
 *
 * @ORM\Table(name="affiliation_invoice")
 * @ORM\Entity
 * @Annotation\Hydrator("Zend\Hydrator\ObjectProperty")
 * @Annotation\Name("affiliation_invoice")
 *
 * @category    Affiliation
 */
class Invoice extends AbstractEntity
{
    /**
     * @ORM\Column(name="affiliation_invoice_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="period", type="integer", options={"unsigned":true})
     * @Annotation\Type("\Zend\Form\Element\Number")
     * @Annotation\Options({"label":"txt-affiliation-invoice-period-label","help-block":"txt-affiliation-invoice-period-help-block"})
     * @Annotation\Options({"placeholder":"txt-affiliation-invoice-period-placeholder"})
     *
     * @var integer
     */
    private $period;
    /**
     * @ORM\Column(name="year", type="integer", options={"unsigned":true})
     * @Annotation\Type("\Zend\Form\Element\Number")
     * @Annotation\Options({"label":"txt-affiliation-invoice-year-label","help-block":"txt-affiliation-invoice-year-help-block"})
     * @Annotation\Options({"placeholder":"txt-affiliation-invoice-year-placeholder"})
     *
     * @var integer
     */
    private $year;
    /**
     * @ORM\Column(name="years", type="array", nullable=false)
     * @Annotation\Type("\Zend\Form\Element\Text")
     *
     * @var array
     */
    private $years;
    /**
     * @ORM\Column(name="amount_invoiced", type="decimal", precision=10, scale=2, nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-affiliation-invoice-amount-invoiced-label","help-block":"txt-affiliation-invoice-amount-invoiced-help-block"})
     * @Annotation\Options({"placeholder":"txt-affiliation-invoice-amount-invoiced-placeholder"})
     *
     * @var float
     */
    private $amountInvoiced;
    /**
     * @ORM\ManyToOne(targetEntity="Project\Entity\Version\Version", inversedBy="affiliationInvoice", cascade={"persist"})
     * @ORM\JoinColumn(name="version_id", referencedColumnName="version_id", nullable=true)
     *
     * @var \Project\Entity\Version\Version
     */
    private $version;
    /**
     * @ORM\ManyToOne(targetEntity="Project\Entity\Contract\Version", inversedBy="affiliationInvoice", cascade={"persist"})
     * @ORM\JoinColumn(name="contract_version_id", referencedColumnName="version_id", nullable=true)
     *
     * @var \Project\Entity\Contract\Version
     */
    private $contractVersion;
    /**
     * @ORM\ManyToOne(targetEntity="Affiliation\Entity\Affiliation", inversedBy="invoice", cascade={"persist"})
     * @ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id", nullable=false)
     *
     * @var \Affiliation\Entity\Affiliation
     */
    private $affiliation;
    /**
     * @ORM\OneToOne(targetEntity="Invoice\Entity\Invoice", inversedBy="affiliationInvoice", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="invoice_id", referencedColumnName="invoice_id", nullable=false)
     * @var \Invoice\Entity\Invoice
     */
    private $invoice;
    /**
     * @ORM\ManyToOne(targetEntity="General\Entity\ExchangeRate", inversedBy="affiliationInvoice", cascade={"persist"})
     * @ORM\JoinColumn(name="exchange_rate_id", referencedColumnName="exchange_rate_id", nullable=true)
     * @var \General\Entity\ExchangeRate|null
     */
    private $exchangeRate;

    public function __get($property)
    {
        return $this->$property;
    }

    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    public function __isset($property)
    {
        return isset($this->$property);
    }

    public function __toString(): string
    {
        return (string)$this->getInvoice();
    }

    /**
     * @return \Invoice\Entity\Invoice
     */
    public function getInvoice(): ?\Invoice\Entity\Invoice
    {
        return $this->invoice;
    }

    /**
     * @param \Invoice\Entity\Invoice $invoice
     *
     * @return Invoice
     */
    public function setInvoice($invoice): ?Invoice
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function hasYearAndPeriod(int $year, int $period): bool
    {
        if (!$this->hasYear($year)) {
            return false;
        }

        return \in_array($period, $this->years[$year], true);
    }

    public function hasYear(int $year): bool
    {
        return \array_key_exists($year, $this->years);
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
     *
     * @return Invoice
     */
    public function setId($id): ?Invoice
    {
        $this->id = $id;

        return $this;
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
     *
     * @return Invoice
     */
    public function setPeriod($period): ?Invoice
    {
        $this->period = $period;

        return $this;
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
     *
     * @return Invoice
     */
    public function setYear($year): ?Invoice
    {
        $this->year = $year;

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
     * @param float $amountInvoiced
     *
     * @return Invoice
     */
    public function setAmountInvoiced($amountInvoiced): ?Invoice
    {
        $this->amountInvoiced = $amountInvoiced;

        return $this;
    }

    /**
     * @return \Project\Entity\Version\Version
     */
    public function getVersion(): ?\Project\Entity\Version\Version
    {
        return $this->version;
    }

    /**
     * @param \Project\Entity\Version\Version $version
     *
     * @return Invoice
     */
    public function setVersion($version): ?Invoice
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return \Project\Entity\Contract\Version|null
     */
    public function getContractVersion(): ?\Project\Entity\Contract\Version
    {
        return $this->contractVersion;
    }

    /**
     * @param \Project\Entity\Contract\Version $contractVersion
     *
     * @return Invoice
     */
    public function setContractVersion(\Project\Entity\Contract\Version $contractVersion): Invoice
    {
        $this->contractVersion = $contractVersion;

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
     * @param Affiliation $affiliation
     *
     * @return Invoice
     */
    public function setAffiliation($affiliation): ?Invoice
    {
        $this->affiliation = $affiliation;

        return $this;
    }

    /**
     * @return ExchangeRate|null
     */
    public function getExchangeRate(): ?ExchangeRate
    {
        return $this->exchangeRate;
    }

    /**
     * @param ExchangeRate|null $exchangeRate
     *
     * @return Invoice
     */
    public function setExchangeRate(?ExchangeRate $exchangeRate): Invoice
    {
        $this->exchangeRate = $exchangeRate;

        return $this;
    }

    public function getYears(): ?array
    {
        return $this->years;
    }

    public function setYears(array $years): Invoice
    {
        $this->years = $years;
        return $this;
    }
}
