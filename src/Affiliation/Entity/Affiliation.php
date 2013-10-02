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

use Zend\Form\Annotation;
use Zend\Permissions\Acl\Resource\ResourceInterface;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity for the Affiliation
 *
 * @ORM\Table(name="affiliation")
 * @ORM\Entity
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Name("affiliation")
 *
 * @category    Affiliation
 * @package     Entity
 */
class Affiliation //extends EntityAbstract implements ResourceInterface
{
    /**
     * @ORM\Column(name="affiliation_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="branch", type="string", length=40, nullable=true)
     * @var string
     */
    private $branch;
    /**
     * @ORM\Column(name="note", type="text", nullable=true)
     * @var string
     */
    private $note;
    /**
     * @ORM\Column(name="value_chain", type="string", length=60, nullable=true)
     * @var string
     */
    private $valueChain;
    /**
     * @ORM\Column(name="self_funded", type="smallint", nullable=false)
     * @var integer
     */
    private $selfFunded;
    /**
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     * @var \DateTime
     */
    private $dateCreated;
    /**
     * @ORM\Column(name="date_end", type="datetime", nullable=true)
     * @var \DateTime
     */
    private $dateEnd;
    /**
     * @ORM\Column(name="date_self_funded", type="datetime", nullable=true)
     * @var \DateTime
     */
    private $dateSelfFunded;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", inversedBy="affiliation", cascade={"persist"})
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id", nullable=false)
     * })
     * @var \Contact\Entity\Contact
     */
    private $contact;
    /**
     * @ORM\ManyToOne(targetEntity="Organisation\Entity\Organisation", inversedBy="affiliation", cascade={"persist"})
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="organisation_id", nullable=false)
     * })
     * @var \Organisation\Entity\Organisation
     */
    private $organisation;
    /**
     * @ORM\ManyToOne(targetEntity="Project\Entity\Project", inversedBy="affiliation", cascade={"persist"})
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="project_id", referencedColumnName="project_id", nullable=true)
     * })
     * @var \Project\Entity\Project
     */
    private $project;
    /**
     * @ORM\ManyToMany(targetEntity="Organisation\Entity\IctOrganisation", inversedBy="affiliation", cascade={"persist"})
     * @ORM\OrderBy=({"Organisation"="ASC"})
     * @ORM\JoinTable(name="affiliation_ict_organisation",
     *    joinColumns={@ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id")},
     *    inverseJoinColumns={@ORM\JoinColumn(name="ict_id", referencedColumnName="ict_id")}
     * )
     * @Annotation\Type("DoctrineORMModule\Form\Element\EntityMultiCheckbox")
     * @Annotation\Options({
     *      "target_class":"Organisation\Entity\IctOrganisation",
     *      "find_method":{
     *          "name":"findBy",
     *          "params": {
     *              "criteria":{},
     *              "orderBy":{
     *                  "organisation":"ASC"}
     *              }
     *          }
     *      }
     * )
     * @Annotation\Attributes({"label":"txt-ict-organisation"})
     * @var \Organisation\Entity\IctOrganisation[]
     */
    private $ictOrganisation;
    /**
     * @ORM\ManyToMany(targetEntity="Affiliation\Entity\Description", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     * @var \Affiliation\Entity\Description[]
     */
    private $description;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Financial", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     * @var \Affiliation\Entity\Financial[]
     */
    private $financial;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Invoice", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     * @var \Affiliation\Entity\Invoice[]
     */
    private $invoice;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\InvoiceCmShare", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     * @var \Affiliation\Entity\InvoiceCmShare[]
     */
    private $invoiceCmShare;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\InvoicePostCalc", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     * @var \Affiliation\Entity\Invoice[]
     */
    private $invoicePostCalc;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Log", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     * @var \Affiliation\Entity\Log[]
     */
    private $log;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Version", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     * @var \Affiliation\Entity\Version[]
     */
    private $version;
}
