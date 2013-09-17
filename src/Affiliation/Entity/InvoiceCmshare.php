<?php
/**
 * ITEA copyright message placeholder
 *
 * @category    Project
 * @package     Entity
 * @author      Johan van der Heide <info@japaveh.nl>
 * @copyright   Copyright (c) 2004-2013 ITEA
 */
namespace Affiliation\Entity;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\Factory as InputFactory;
use Zend\Form\Annotation;

use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;

use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Entity for the Affiliation
 *
 * @ORM\Table(name="affiliation_invoice_cmshare")
 * @ORM\Entity
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Name("affiliation_invoice_cmshare")
 *
 * @category    Affiliation
 * @package     Entity
 */
class InvoiceCmShare //extends EntityAbstract
{
    /**
     * @ORM\Column(name="cmshare_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="year", type="integer", nullable=false)
     * @var integer
     */
    private $year;
    /**
     * @ORM\Column(name="amount_invoiced", type="decimal", nullable=false)
     * @var float
     */
    private $amountInvoiced;
    /**
     * @ORM\ManyToOne(targetEntity="\Affiliation\Entity\Affiliation", inversedBy="invoiceCmShare", cascade={"persist"})
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
