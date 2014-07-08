<?php
/**
 * ITEA copyright message placeholder
 *
 * @category    Project
 * @package     Entity
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Affiliation\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/**
 * Entity for the Affiliation
 *
 * @ORM\Table(name="affiliation_invoice_postcalc")
 * @ORM\Entity
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Name("affiliation_invoice_postcalc")
 *
 * @category    Affiliation
 * @package     Entity
 */
class InvoicePostCalc
{
    /**
     * @ORM\Column(name="postcalc_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="amount_invoiced", type="decimal", nullable=false)
     * @var float
     */
    private $amountInvoiced;
    /**
     * @ORM\ManyToOne(targetEntity="\Affiliation\Entity\Affiliation", inversedBy="invoicePostCalc", cascade={"persist"})
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id", nullable=false)
     * })
     * @var \Affiliation\Entity\Affiliation
     */
    private $affiliation;
//
//    /**
//     * @var \Invoice
//     *
//     * @ORM\ManyToOne(targetEntity="Invoice")
//     * @ORM\JoinColumns({
//     * @ORM\JoinColumn(name="invoice_id", referencedColumnName="invoice_id")
//     * })
//     */
//    private $invoice;
}
