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
 * @ORM\Table(name="affiliation")
 * @ORM\Entity(repositoryClass="Affiliation\Repository\Affiliation")
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Name("affiliation")
 *
 * @category    Affiliation
 * @package     Entity
 */
class Affiliation extends EntityAbstract //implements ResourceInterface
{
    /**
     * Constant for mode = 0 (not self funded)
     */
    const NOT_SELF_FUNDED = 0;
    /**
     * Constant for mode = 1 (self funded)
     */
    const SELF_FUNDED = 1;

    /**
     * Templates for the self funded parameter
     * @var array
     */
    protected $selfFundedTemplates = array(
        self::NOT_SELF_FUNDED => 'txt-not-self-funded',
        self::SELF_FUNDED     => 'txt-self-funded',
    );

    /**
     * @ORM\Column(name="affiliation_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Annotation\Exclude()
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="branch", type="string", length=40, nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-branch"})
     * @var string
     */
    private $branch;
    /**
     * @ORM\Column(name="note", type="text", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-note"})
     * @var string
     */
    private $note;
    /**
     * @ORM\Column(name="value_chain", type="string", length=60, nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-value-chain"})
     * @var string
     */
    private $valueChain;
    /**
     * @ORM\Column(name="self_funded", type="smallint", nullable=false)
     * @Annotation\Type("Zend\Form\Element\Radio")
     * @Annotation\Attributes({"array":"selfFundedTemplates"})
     * @Annotation\Attributes({"label":"txt-self-funded"})
     * @Annotation\Required(true)
     * @var integer
     */
    private $selfFunded;
    /**
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     * @Annotation\Exclude()
     * @var \DateTime
     */
    private $dateCreated;
    /**
     * @ORM\Column(name="date_end", type="datetime", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\DateTime")
     * @Annotation\Options({"label":"txt-date-end"})
     * @var \DateTime
     */
    private $dateEnd;
    /**
     * @ORM\Column(name="date_self_funded", type="datetime", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\DateTime")
     * @Annotation\Options({"label":"txt-date-self-funded"})
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
     * @var \Contact\Entity\Contact
     */
    private $contact;
    /**
     * @ORM\ManyToOne(targetEntity="Organisation\Entity\Organisation", inversedBy="affiliation", cascade={"persist"})
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="organisation_id", nullable=false)
     * })
     * @Annotation\Exclude()
     * @var \Organisation\Entity\Organisation
     */
    private $organisation;
    /**
     * @ORM\ManyToOne(targetEntity="Project\Entity\Project", inversedBy="affiliation", cascade={"persist"})
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="project_id", referencedColumnName="project_id", nullable=true)
     * })
     * @Annotation\Exclude()
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
     * @var \Contact\Entity\Contact[]
     */
    private $associate;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->ictOrganisation = new Collections\ArrayCollection();
        $this->description     = new Collections\ArrayCollection();
        $this->financial       = new Collections\ArrayCollection();
        $this->invoice         = new Collections\ArrayCollection();
        $this->invoiceCmShare  = new Collections\ArrayCollection();
        $this->invoicePostCalc = new Collections\ArrayCollection();
        $this->log             = new Collections\ArrayCollection();
        $this->version         = new Collections\ArrayCollection();
        $this->associate       = new Collections\ArrayCollection();
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
     *
     * @return void
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    /**
     * ToString
     * Return the id here for form population
     * @return string
     */
    public function __toString()
    {
        return (string)$this->contact->getFirstName() . ' ' .
        $this->contact->getLastName() . ' ' . $this->getOrganisation()->getOrganisation();
    }

    /**
     * @return array
     */
    public function getSelfFundedTemplates()
    {
        return $this->selfFundedTemplates;
    }

    /**
     * @param InputFilterInterface $inputFilter
     *
     * @return void
     * @throws \Exception
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
            $factory     = new InputFactory();

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'       => 'branch',
                        'required'   => false,
                        'filters'    => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                        ),
                        'validators' => array(
                            array(
                                'name'    => 'StringLength',
                                'options' => array(
                                    'encoding' => 'UTF-8',
                                    'min'      => 1,
                                    'max'      => 40,
                                ),
                            ),
                        ),
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'note',
                        'required' => false,
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'valueChain',
                        'required' => false,
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'dateEnd',
                        'required' => false,
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'dateEnd',
                        'required' => false,
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'dateSelfFunded',
                        'required' => false,
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'contact',
                        'required' => false,
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'       => 'selfFunded',
                        'required'   => true,
                        'validators' => array(
                            array(
                                'name'    => 'InArray',
                                'options' => array(
                                    'haystack' => array_keys($this->getSelfFundedTemplates())
                                )
                            )
                        )
                    )
                )
            );

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    /**
     * Needed for the hydration of form elements
     * @return array
     */
    public function getArrayCopy()
    {
        return array(
            'branch'         => $this->branch,
            'note'           => $this->note,
            'valueChain'     => $this->valueChain,
            'selfFunded'     => $this->selfFunded,
            'dateEnd'        => $this->dateEnd,
            'dateSelfFunded' => $this->dateSelfFunded,
            'contact'        => $this->contact,
        );
    }

    /**
     * @return array
     */
    public function populate()
    {
        return $this->getArrayCopy();
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
     * @param \Affiliation\Entity\Description[] $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return \Affiliation\Entity\Description[]
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param \Affiliation\Entity\Financial[] $financial
     */
    public function setFinancial($financial)
    {
        $this->financial = $financial;
    }

    /**
     * @return \Affiliation\Entity\Financial[]
     */
    public function getFinancial()
    {
        return $this->financial;
    }

    /**
     * @param \Organisation\Entity\IctOrganisation[] $ictOrganisation
     */
    public function setIctOrganisation($ictOrganisation)
    {
        $this->ictOrganisation = $ictOrganisation;
    }

    /**
     * @return \Organisation\Entity\IctOrganisation[]
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
     * @param \Affiliation\Entity\Invoice[] $invoice
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * @return \Affiliation\Entity\Invoice[]
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param \Affiliation\Entity\InvoiceCmShare[] $invoiceCmShare
     */
    public function setInvoiceCmShare($invoiceCmShare)
    {
        $this->invoiceCmShare = $invoiceCmShare;
    }

    /**
     * @return \Affiliation\Entity\InvoiceCmShare[]
     */
    public function getInvoiceCmShare()
    {
        return $this->invoiceCmShare;
    }

    /**
     * @param \Affiliation\Entity\Invoice[] $invoicePostCalc
     */
    public function setInvoicePostCalc($invoicePostCalc)
    {
        $this->invoicePostCalc = $invoicePostCalc;
    }

    /**
     * @return \Affiliation\Entity\Invoice[]
     */
    public function getInvoicePostCalc()
    {
        return $this->invoicePostCalc;
    }

    /**
     * @param \Affiliation\Entity\Log[] $log
     */
    public function setLog($log)
    {
        $this->log = $log;
    }

    /**
     * @return \Affiliation\Entity\Log[]
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
     * @return int
     */
    public function getSelfFunded()
    {
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
     * @param \Affiliation\Entity\Version[] $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return \Affiliation\Entity\Version[]
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param \Contact\Entity\Contact[] $associate
     */
    public function setAssociate($associate)
    {
        $this->associate = $associate;
    }

    /**
     * @return \Contact\Entity\Contact[]
     */
    public function getAssociate()
    {
        return $this->associate;
    }
}
