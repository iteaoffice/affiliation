<?php

/**
 * ITEA copyright message placeholder.
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Entity;

use Doctrine\ORM\Mapping as ORM;
use General\Entity\ExchangeRate;
use Laminas\Form\Annotation;

use function array_key_exists;
use function in_array;

/**
 * @ORM\Table(name="affiliation_invoice")
 * @ORM\Entity
 * @Annotation\Hydrator("Laminas\Hydrator\ObjectProperty")
 * @Annotation\Name("affiliation_invoice")
 */
class Invoice extends AbstractEntity
{
    /**
     * @ORM\Column(name="affiliation_invoice_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;
    /**
     * @ORM\Column(name="period", type="integer", options={"unsigned":true})
     * @Annotation\Type("\Laminas\Form\Element\Number")
     * @Annotation\Options({"label":"txt-affiliation-invoice-period-label","help-block":"txt-affiliation-invoice-period-help-block"})
     * @Annotation\Options({"placeholder":"txt-affiliation-invoice-period-placeholder"})
     *
     * @var int
     */
    private $period;
    /**
     * @ORM\Column(name="year", type="integer", options={"unsigned":true})
     * @Annotation\Type("\Laminas\Form\Element\Number")
     * @Annotation\Options({"label":"txt-affiliation-invoice-year-label","help-block":"txt-affiliation-invoice-year-help-block"})
     * @Annotation\Options({"placeholder":"txt-affiliation-invoice-year-placeholder"})
     *
     * @var int
     */
    private $year;
    /**
     * @ORM\Column(name="years", type="array", nullable=false)
     * @Annotation\Type("\Laminas\Form\Element\Text")
     *
     * @var array
     */
    private $years;
    /**
     * @ORM\Column(name="amount_invoiced", type="decimal", precision=10, scale=2, nullable=true)
     * @Annotation\Type("\Laminas\Form\Element\Text")
     * @Annotation\Options({"label":"txt-affiliation-invoice-amount-invoiced-label","help-block":"txt-affiliation-invoice-amount-invoiced-help-block"})
     * @Annotation\Options({"placeholder":"txt-affiliation-invoice-amount-invoiced-placeholder"})
     *
     * @var string
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
     * @var Affiliation
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
     * @var ExchangeRate|null
     */
    private $exchangeRate;

    public function __toString(): string
    {
        return (string)$this->getInvoice();
    }

    public function getInvoice(): ?\Invoice\Entity\Invoice
    {
        return $this->invoice;
    }

    public function setInvoice($invoice): ?Invoice
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function hasYearAndPeriod(int $year, int $period): bool
    {
        if (! $this->hasYear($year)) {
            return false;
        }

        return in_array($period, $this->years[$year], true);
    }

    public function hasYear(int $year): bool
    {
        return array_key_exists($year, $this->years);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): ?Invoice
    {
        $this->id = $id;

        return $this;
    }

    public function getPeriod(): ?int
    {
        return $this->period;
    }

    public function setPeriod($period): ?Invoice
    {
        $this->period = $period;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear($year): ?Invoice
    {
        $this->year = $year;

        return $this;
    }

    public function getAmountInvoiced(): ?float
    {
        return (float) $this->amountInvoiced;
    }

    public function setAmountInvoiced($amountInvoiced): ?Invoice
    {
        $this->amountInvoiced = $amountInvoiced;

        return $this;
    }

    public function getVersion(): ?\Project\Entity\Version\Version
    {
        return $this->version;
    }

    public function setVersion($version): ?Invoice
    {
        $this->version = $version;

        return $this;
    }

    public function getContractVersion(): ?\Project\Entity\Contract\Version
    {
        return $this->contractVersion;
    }

    public function setContractVersion(\Project\Entity\Contract\Version $contractVersion): Invoice
    {
        $this->contractVersion = $contractVersion;

        return $this;
    }

    public function getAffiliation(): ?Affiliation
    {
        return $this->affiliation;
    }

    public function setAffiliation($affiliation): ?Invoice
    {
        $this->affiliation = $affiliation;

        return $this;
    }

    public function getExchangeRate(): ?ExchangeRate
    {
        return $this->exchangeRate;
    }

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
