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

use Contact\Entity\Contact;
use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Zend\Form\Annotation;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * Entity for the Affiliation.
 *
 * @ORM\Table(name="affiliation")
 * @ORM\Entity(repositoryClass="Affiliation\Repository\Affiliation")
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Name("affiliation")
 *
 * @category    Affiliation
 */
class Affiliation extends EntityAbstract implements ResourceInterface
{
    /**
     * Constant for mode = 0 (not self funded).
     */
    const NOT_SELF_FUNDED = 0;
    /**
     * Constant for mode = 1 (self funded).
     */
    const SELF_FUNDED = 1;
    /**
     * Templates for the self funded parameter.
     *
     * @var array
     */
    protected static $selfFundedTemplates
        = [
            self::NOT_SELF_FUNDED => 'txt-not-self-funded',
            self::SELF_FUNDED     => 'txt-self-funded',
        ];
    /**
     * @ORM\Column(name="affiliation_id", length=10, type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Annotation\Exclude()
     *
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="branch", type="string", length=40, nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-branch"})
     *
     * @var string
     */
    private $branch;
    /**
     * @ORM\Column(name="note", type="text", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-note"})
     *
     * @var string
     */
    private $note;
    /**
     * @ORM\Column(name="value_chain", type="string", length=60, nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-value-chain"})
     *
     * @var string
     */
    private $valueChain;
    /**
     * @ORM\Column(name="market_access", type="string", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-market-access"})
     *
     * @var string
     */
    private $marketAccess;
    /**
     * @ORM\Column(name="main_contribution", type="string", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-main-contribution"})
     *
     * @var string
     */
    private $mainContribution;
    /**
     * @ORM\Column(name="self_funded", type="smallint", nullable=false)
     * @Annotation\Type("Zend\Form\Element\Radio")
     * @Annotation\Attributes({"array":"selfFundedTemplates"})
     * @Annotation\Attributes({"label":"txt-self-funded"})
     * @Annotation\Required(true)
     *
     * @var integer
     */
    private $selfFunded;
    /**
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     * @Annotation\Exclude()
     *
     * @var \DateTime
     */
    private $dateCreated;
    /**
     * @ORM\Column(name="date_end", type="datetime", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\DateTime")
     * @Annotation\Options({"label":"txt-date-end"})
     *
     * @var \DateTime
     */
    private $dateEnd;
    /**
     * @ORM\Column(name="date_self_funded", type="datetime", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\DateTime")
     * @Annotation\Options({"label":"txt-date-self-funded"})
     *
     * @var \DateTime
     */
    private $dateSelfFunded;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", inversedBy="affiliation", cascade={"persist"})
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id", nullable=false)
     * })
     * @Annotation\Type("DoctrineORMModule\Form\Element\EntitySelect")
     * @Annotation\Options({
     *      "target_class":"Contact\Entity\Contact",
     *      "find_method":{
     *          "name":"findBy",
     *          "params": {
     *              "criteria":{},
     *              "orderBy":{
     *                  "lastname":"ASC"}
     *              }
     *          }
     *      }
     * )
     * @Annotation\Attributes({"label":"txt-project-leader"})
     *
     * @var \Contact\Entity\Contact
     */
    private $contact;
    /**
     * @ORM\ManyToOne(targetEntity="Organisation\Entity\Organisation", inversedBy="affiliation", cascade={"persist"})
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="organisation_id", nullable=false)
     * })
     * @Annotation\Exclude()
     *
     * @var \Organisation\Entity\Organisation
     */
    private $organisation;
    /**
     * @ORM\ManyToOne(targetEntity="Project\Entity\Project", inversedBy="affiliation", cascade={"persist"})
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="project_id", referencedColumnName="project_id", nullable=true)
     * })
     * @Annotation\Exclude()
     *
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
     *
     * @var \Organisation\Entity\IctOrganisation[]|Collections\ArrayCollection()
     */
    private $ictOrganisation;
    /**
     * @ORM\ManyToMany(targetEntity="Affiliation\Entity\Description", cascade={"persist","remove"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Affiliation\Entity\Description[]|Collections\ArrayCollection()
     */
    private $description;
    /**
     * @ORM\OneToOne(targetEntity="Affiliation\Entity\Financial", cascade={"persist","remove"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Affiliation\Entity\Financial
     */
    private $financial;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Invoice", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Affiliation\Entity\Invoice[]|Collections\ArrayCollection()
     */
    private $invoice;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\InvoiceCmShare", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Affiliation\Entity\InvoiceCmShare[]|Collections\ArrayCollection()
     */
    private $invoiceCmShare;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\InvoicePostCalc", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Affiliation\Entity\Invoice[]|Collections\ArrayCollection()
     */
    private $invoicePostCalc;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Log", cascade={"persist","remove"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Affiliation\Entity\Log[]|Collections\ArrayCollection()
     */
    private $log;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Version", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Affiliation\Entity\Version[]|Collections\ArrayCollection()
     */
    private $version;
    /**
     * @ORM\ManyToMany(targetEntity="Contact\Entity\Contact", inversedBy="associate", cascade={"persist"})
     * @ORM\OrderBy=({"Lastname"="ASC"})
     * @ORM\JoinTable(name="associate",
     *    joinColumns={@ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id")},
     *    inverseJoinColumns={@ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id")}
     * )
     * @Annotation\Type("DoctrineORMModule\Form\Element\EntityMultiCheckbox")
     * @Annotation\Options({
     *      "target_class":"Contact\Entity\Contact",
     *      "find_method":{
     *          "name":"findBy",
     *          "params": {
     *              "criteria":{},
     *              "orderBy":{
     *                  "lastname":"ASC"}
     *              }
     *          }
     *      }
     * )
     * @Annotation\Attributes({"label":"txt-associates"})
     *
     * @var \Contact\Entity\Contact[]|Collections\ArrayCollection()
     */
    private $associate;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Cost\Cost", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Project\Entity\Cost\Cost[]|Collections\ArrayCollection()
     */
    private $cost;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Funding\Funding", cascade={"persist","remove"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Project\Entity\Funding\Funding[]|Collections\ArrayCollection()
     */
    private $funding;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Effort\Effort", cascade={"persist","remove"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Project\Entity\Effort\Effort[]|Collections\ArrayCollection()
     */
    private $effort;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Effort\Spent", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Project\Entity\Effort\Spent[]|Collections\ArrayCollection()
     */
    private $spent;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Report\EffortSpent", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Project\Entity\Report\EffortSpent[]|Collections\ArrayCollection()
     */
    private $projectReportEffortSpent;
    /**
     * @ORM\OneToOne(targetEntity="Affiliation\Entity\Loi", cascade={"persist","remove"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Affiliation\Entity\Loi
     */
    private $loi;
    /**
     * @ORM\OneToOne(targetEntity="Affiliation\Entity\Doa", cascade={"persist","remove"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Affiliation\Entity\Doa
     */
    private $doa;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\DoaReminder", cascade={"persist","remove"}, mappedBy="affiliation")
     * @ORM\OrderBy=({"DateCreated"="DESC"})
     * @Annotation\Exclude();
     *
     * @var \Affiliation\Entity\DoaReminder[]|Collections\ArrayCollection
     */
    private $doaReminder;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\LoiReminder", cascade={"persist","remove"}, mappedBy="affiliation")
     * @ORM\OrderBy=({"DateCreated"="DESC"})
     * @Annotation\Exclude();
     *
     * @var \Affiliation\Entity\DoaReminder[]|Collections\ArrayCollection
     */
    private $loiReminder;
    /**
     * @ORM\ManyToMany(targetEntity="Project\Entity\Achievement", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Project\Entity\Achievement[]|Collections\ArrayCollection
     */
    private $achievement;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Changerequest\CostChange", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Project\Entity\Changerequest\CostChange[]|Collections\ArrayCollection
     */
    private $changerequestCostChange;
    /**
     * @ORM\ManyToMany(targetEntity="Project\Entity\Log", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Project\Entity\Log[]|Collections\ArrayCollection
     */
    private $projectLog;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->ictOrganisation = new Collections\ArrayCollection();
        $this->description = new Collections\ArrayCollection();
        $this->invoice = new Collections\ArrayCollection();
        $this->invoiceCmShare = new Collections\ArrayCollection();
        $this->invoicePostCalc = new Collections\ArrayCollection();
        $this->log = new Collections\ArrayCollection();
        $this->version = new Collections\ArrayCollection();
        $this->associate = new Collections\ArrayCollection();
        $this->cost = new Collections\ArrayCollection();
        $this->funding = new Collections\ArrayCollection();
        $this->effort = new Collections\ArrayCollection();
        $this->spent = new Collections\ArrayCollection();
        $this->doaReminder = new Collections\ArrayCollection();
        $this->loiReminder = new Collections\ArrayCollection();
        $this->achievement = new Collections\ArrayCollection();
        $this->projectReportEffortSpent = new Collections\ArrayCollection();
        $this->changerequestCostChange = new Collections\ArrayCollection();
        $this->projectLog = new Collections\ArrayCollection();
        /*
         * Self-funded is default NOT
         */
        $this->selfFunded = self::NOT_SELF_FUNDED;
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
     * ToString.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getOrganisation()->getOrganisation();
    }

    /**
     * @return string
     */
    public function parseBranchedName()
    {
        return trim(preg_replace(
            '/^(([^\~]*)\~\s?)?\s?(.*)$/',
            '${2}' . $this->getOrganisation()->getOrganisation() . ' ${3}',
            $this->getBranch()
        ));
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
     * @param InputFilterInterface $inputFilter
     *
     * @throws \Exception
     * @return void
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception(sprintf("This class %s is unused", __CLASS__));
    }

    /**
     * @return \Zend\InputFilter\InputFilter|\Zend\InputFilter\InputFilterInterface
     */
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();
            $inputFilter->add($factory->createInput([
                'name'       => 'branch',
                'required'   => false,
                'filters'    => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 40,
                        ],
                    ],
                ],
            ]));
            $inputFilter->add($factory->createInput([
                'name'     => 'note',
                'required' => false,
            ]));
            $inputFilter->add($factory->createInput([
                'name'     => 'valueChain',
                'required' => false,
            ]));
            $inputFilter->add($factory->createInput([
                'name'     => 'mainContribution',
                'required' => false,
            ]));
            $inputFilter->add($factory->createInput([
                'name'     => 'marketAccess',
                'required' => false,
            ]));
            $inputFilter->add($factory->createInput([
                'name'     => 'dateEnd',
                'required' => false,
            ]));
            $inputFilter->add($factory->createInput([
                'name'     => 'dateEnd',
                'required' => false,
            ]));
            $inputFilter->add($factory->createInput([
                'name'     => 'dateSelfFunded',
                'required' => false,
            ]));
            $inputFilter->add($factory->createInput([
                'name'     => 'contact',
                'required' => false,
            ]));
            $inputFilter->add($factory->createInput([
                'name'       => 'selfFunded',
                'required'   => true,
                'validators' => [
                    [
                        'name'    => 'InArray',
                        'options' => [
                            'haystack' => array_keys($this->getSelfFundedTemplates()),
                        ],
                    ],
                ],
            ]));
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    /**
     * Needed for the hydration of form elements.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return [
            'branch'           => $this->branch,
            'note'             => $this->note,
            'valueChain'       => $this->valueChain,
            'selfFunded'       => $this->selfFunded,
            'dateEnd'          => $this->dateEnd,
            'dateSelfFunded'   => $this->dateSelfFunded,
            'contact'          => $this->contact,
            'marketAccess'     => $this->marketAccess,
            'mainContribution' => $this->mainContribution,
        ];
    }

    /**
     * @return array
     */
    public static function getSelfFundedTemplates()
    {
        return self::$selfFundedTemplates;
    }


    /**
     * @return array
     */
    public function populate()
    {
        return $this->getArrayCopy();
    }

    /**
     * @param Contact $contact
     */
    public function addAssociate(Contact $contact)
    {
        if (!$this->associate->contains($contact)) {
            $this->associate->add($contact);
        }
    }

    /**
     * @param Contact $contact
     */
    public function removeAssociate(Contact $contact)
    {
        $this->associate->removeElement($contact);
    }

    /**
     * @param string $branch
     */
    public function setBranch($branch)
    {
        $this->branch = $branch;
    }

    /**
     * @return string
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * @param \Contact\Entity\Contact $contact
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
    }

    /**
     * @return \Contact\Entity\Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param \DateTime $dateCreated
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateEnd
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;
    }

    /**
     * @return \DateTime
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * @param \DateTime $dateSelfFunded
     */
    public function setDateSelfFunded($dateSelfFunded)
    {
        $this->dateSelfFunded = $dateSelfFunded;
    }

    /**
     * @return \DateTime
     */
    public function getDateSelfFunded()
    {
        return $this->dateSelfFunded;
    }

    /**
     * @param \Affiliation\Entity\Description[]|Collections\ArrayCollection() $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return \Affiliation\Entity\Description[]|Collections\ArrayCollection()
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param \Affiliation\Entity\Financial $financial
     */
    public function setFinancial($financial)
    {
        $this->financial = $financial;
    }

    /**
     * @return \Affiliation\Entity\Financial
     */
    public function getFinancial()
    {
        return $this->financial;
    }

    /**
     * @param \Organisation\Entity\IctOrganisation[]|Collections\ArrayCollection() $ictOrganisation
     */
    public function setIctOrganisation($ictOrganisation)
    {
        $this->ictOrganisation = $ictOrganisation;
    }

    /**
     * @return \Organisation\Entity\IctOrganisation[]|Collections\ArrayCollection()
     */
    public function getIctOrganisation()
    {
        return $this->ictOrganisation;
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Affiliation\Entity\Invoice[]|Collections\ArrayCollection() $invoice
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * @return \Affiliation\Entity\Invoice[]|Collections\ArrayCollection()
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param \Affiliation\Entity\InvoiceCmShare[]|Collections\ArrayCollection() $invoiceCmShare
     */
    public function setInvoiceCmShare($invoiceCmShare)
    {
        $this->invoiceCmShare = $invoiceCmShare;
    }

    /**
     * @return \Affiliation\Entity\InvoiceCmShare[]|Collections\ArrayCollection()
     */
    public function getInvoiceCmShare()
    {
        return $this->invoiceCmShare;
    }

    /**
     * @param \Affiliation\Entity\Invoice[]|Collections\ArrayCollection() $invoicePostCalc
     */
    public function setInvoicePostCalc($invoicePostCalc)
    {
        $this->invoicePostCalc = $invoicePostCalc;
    }

    /**
     * @return \Affiliation\Entity\Invoice[]|Collections\ArrayCollection()
     */
    public function getInvoicePostCalc()
    {
        return $this->invoicePostCalc;
    }

    /**
     * @param \Affiliation\Entity\Log[]|Collections\ArrayCollection() $log
     */
    public function setLog($log)
    {
        $this->log = $log;
    }

    /**
     * @return \Affiliation\Entity\Log[]|Collections\ArrayCollection()
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param string $note
     */
    public function setNote($note)
    {
        $this->note = $note;
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param \Organisation\Entity\Organisation $organisation
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;
    }

    /**
     * @return \Organisation\Entity\Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @param \Project\Entity\Project $project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }

    /**
     * @return \Project\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param int $selfFunded
     */
    public function setSelfFunded($selfFunded)
    {
        $this->selfFunded = $selfFunded;
    }

    /**
     * @param bool|true $textual
     *
     * @return int
     */
    public function getSelfFunded($textual = false)
    {
        if ($textual) {
            return self::$selfFundedTemplates[$this->selfFunded];
        }

        return $this->selfFunded;
    }

    /**
     * @param string $valueChain
     */
    public function setValueChain($valueChain)
    {
        $this->valueChain = $valueChain;
    }

    /**
     * @return string
     */
    public function getValueChain()
    {
        return $this->valueChain;
    }

    /**
     * @param \Affiliation\Entity\Version[]|Collections\ArrayCollection() $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return \Affiliation\Entity\Version[]|Collections\ArrayCollection()
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param \Contact\Entity\Contact[]|Collections\ArrayCollection() $associate
     */
    public function setAssociate($associate)
    {
        $this->associate = $associate;
    }

    /**
     * @return \Contact\Entity\Contact[]|Collections\ArrayCollection()
     */
    public function getAssociate()
    {
        return $this->associate;
    }

    /**
     * @param \Project\Entity\Cost\Cost[]|Collections\ArrayCollection() $cost
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
    }

    /**
     * @return \Project\Entity\Cost\Cost[]|Collections\ArrayCollection()
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param \Project\Entity\Funding\Funding[]|Collections\ArrayCollection() $funding
     */
    public function setFunding($funding)
    {
        $this->funding = $funding;
    }

    /**
     * @return \Project\Entity\Funding\Funding[]|Collections\ArrayCollection()
     */
    public function getFunding()
    {
        return $this->funding;
    }

    /**
     * @param \Project\Entity\Effort\Spent[]|Collections\ArrayCollection() $spent
     */
    public function setSpent($spent)
    {
        $this->spent = $spent;
    }

    /**
     * @return \Project\Entity\Effort\Spent[]|Collections\ArrayCollection()
     */
    public function getSpent()
    {
        return $this->spent;
    }

    /**
     * @param \Project\Entity\Effort\Effort[]|Collections\ArrayCollection() $effort
     */
    public function setEffort($effort)
    {
        $this->effort = $effort;
    }

    /**
     * @return \Project\Entity\Effort\Effort[]|Collections\ArrayCollection()
     */
    public function getEffort()
    {
        return $this->effort;
    }

    /**
     * @param \Affiliation\Entity\Loi $loi
     */
    public function setLoi($loi)
    {
        $this->loi = $loi;
    }

    /**
     * @return \Affiliation\Entity\Loi
     */
    public function getLoi()
    {
        return $this->loi;
    }

    /**
     * @return Doa
     */
    public function getDoa()
    {
        return $this->doa;
    }

    /**
     * @param Doa $doa
     */
    public function setDoa($doa)
    {
        $this->doa = $doa;
    }

    /**
     * @return string
     */
    public function getMainContribution()
    {
        return $this->mainContribution;
    }

    /**
     * @param string $mainContribution
     */
    public function setMainContribution($mainContribution)
    {
        $this->mainContribution = $mainContribution;
    }

    /**
     * @return string
     */
    public function getMarketAccess()
    {
        return $this->marketAccess;
    }

    /**
     * @param string $marketAccess
     */
    public function setMarketAccess($marketAccess)
    {
        $this->marketAccess = $marketAccess;
    }

    /**
     * @return DoaReminder[]|Collections\ArrayCollection
     */
    public function getDoaReminder()
    {
        return $this->doaReminder;
    }

    /**
     * @param DoaReminder[]|Collections\ArrayCollection $doaReminder
     */
    public function setDoaReminder($doaReminder)
    {
        $this->doaReminder = $doaReminder;
    }

    /**
     * @return DoaReminder[]|Collections\ArrayCollection
     */
    public function getLoiReminder()
    {
        return $this->loiReminder;
    }

    /**
     * @param DoaReminder[]|Collections\ArrayCollection $loiReminder
     */
    public function setLoiReminder($loiReminder)
    {
        $this->loiReminder = $loiReminder;
    }

    /**
     * @return \Project\Entity\Achievement[]
     */
    public function getAchievement()
    {
        return $this->achievement;
    }

    /**
     * @param \Project\Entity\Achievement[] $achievement
     */
    public function setAchievement($achievement)
    {
        $this->achievement = $achievement;
    }

    /**
     * @return Collections\ArrayCollection|\Project\Entity\Report\EffortSpent[]
     */
    public function getProjectReportEffortSpent()
    {
        return $this->projectReportEffortSpent;
    }

    /**
     * @param  Collections\ArrayCollection|\Project\Entity\Report\EffortSpent[] $projectReportEffortSpent
     *
     * @return Affiliation
     */
    public function setProjectReportEffortSpent($projectReportEffortSpent)
    {
        $this->projectReportEffortSpent = $projectReportEffortSpent;

        return $this;
    }

    /**
     * @return Collections\ArrayCollection|\Project\Entity\Changerequest\CostChange[]
     */
    public function getChangerequestCostChange()
    {
        return $this->changerequestCostChange;
    }

    /**
     * @param Collections\ArrayCollection|\Project\Entity\Changerequest\CostChange[] $changerequestCostChange
     *
     * @return Affiliation
     */
    public function setChangerequestCostChange($changerequestCostChange)
    {
        $this->changerequestCostChange = $changerequestCostChange;

        return $this;
    }

    /**
     * @return Collections\ArrayCollection|\Project\Entity\Log[]
     */
    public function getProjectLog()
    {
        return $this->projectLog;
    }

    /**
     * @param Collections\ArrayCollection|\Project\Entity\Log[] $projectLog
     *
     * @return Affiliation
     */
    public function setProjectLog($projectLog)
    {
        $this->projectLog = $projectLog;

        return $this;
    }
}
