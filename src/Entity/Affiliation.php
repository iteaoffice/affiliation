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

use Contact\Entity\Contact;
use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Organisation\Service\OrganisationService;
use Zend\Form\Annotation;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * Entity for the Affiliation.
 *
 * @ORM\Table(name="affiliation")
 * @ORM\Entity(repositoryClass="Affiliation\Repository\Affiliation")
 * @Annotation\Hydrator("Zend\Hydrator\ObjectProperty")
 * @Annotation\Name("affiliation")
 *
 * @category    Affiliation
 */
class Affiliation extends EntityAbstract implements ResourceInterface
{
    public const NOT_SELF_FUNDED = 0;
    public const SELF_FUNDED = 1;

    protected static $selfFundedTemplates
        = [
            self::NOT_SELF_FUNDED => 'txt-not-self-funded',
            self::SELF_FUNDED     => 'txt-self-funded',
        ];
    /**
     * @ORM\Column(name="affiliation_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Annotation\Exclude()
     *
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="branch", type="string", nullable=true)
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
     * @ORM\Column(name="strategic_importance", type="string", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-strategic-importance"})
     *
     * @var string
     */
    private $strategicImportance;
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
     * @ORM\ManyToOne(targetEntity="Organisation\Entity\Parent\Organisation", inversedBy="affiliation", cascade={"persist"})
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="parent_organisation_id", referencedColumnName="parent_organisation_id", nullable=true)
     * })
     * @Annotation\Exclude()
     *
     * @var \Organisation\Entity\Parent\Organisation|null
     */
    private $parentOrganisation;
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
     * @ORM\ManyToMany(targetEntity="Project\Entity\Contract", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Project\Entity\Contract[]|Collections\ArrayCollection()
     */
    private $contract;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\ContractVersion", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Affiliation\Entity\ContractVersion[]|Collections\ArrayCollection()
     */
    private $contractVersion;
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
     * @ORM\OneToMany(targetEntity="Project\Entity\Funding\Funding", cascade={"persist","remove"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Project\Entity\Funding\Funding[]|Collections\ArrayCollection()
     */
    private $funding;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Cost\Cost", cascade={"persist","remove"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Project\Entity\Cost\Cost[]|Collections\ArrayCollection()
     */
    private $cost;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Contract\Cost", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Project\Entity\Contract\Cost[]|Collections\ArrayCollection()
     */
    private $contractCost;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Effort\Effort", cascade={"persist","remove"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Project\Entity\Effort\Effort[]|Collections\ArrayCollection()
     */
    private $effort;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Funding\Funded", cascade={"persist","remove"}, mappedBy="affiliation", orphanRemoval=true)
     * @Annotation\Exclude()
     *
     * @var \Project\Entity\Funding\Funded[]|Collections\ArrayCollection()
     */
    private $funded;
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
     * @ORM\ManyToMany(targetEntity="Project\Entity\ChangeRequest\CostChange", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Project\Entity\ChangeRequest\CostChange[]|Collections\ArrayCollection
     */
    private $changeRequestCostChange;
    /**
     * @ORM\ManyToMany(targetEntity="Project\Entity\ChangeRequest\Country", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Project\Entity\ChangeRequest\Country[]|Collections\ArrayCollection
     */
    private $changeRequestCountry;
    /**
     * @ORM\ManyToMany(targetEntity="Project\Entity\Log", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var \Project\Entity\Log[]|Collections\ArrayCollection
     */
    private $projectLog;
    /**
     * @ORM\ManyToOne(targetEntity="Invoice\Entity\Method", inversedBy="affiliation", cascade={"persist"})
     * @ORM\JoinColumn(name="method_id", referencedColumnName="method_id", nullable=true)
     * @Annotation\Exclude()
     *
     * @var \Invoice\Entity\Method
     */
    private $invoiceMethod;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->ictOrganisation = new Collections\ArrayCollection();
        $this->description = new Collections\ArrayCollection();
        $this->invoice = new Collections\ArrayCollection();
        $this->log = new Collections\ArrayCollection();
        $this->version = new Collections\ArrayCollection();
        $this->contractVersion = new Collections\ArrayCollection();
        $this->contract = new Collections\ArrayCollection();
        $this->associate = new Collections\ArrayCollection();
        $this->funding = new Collections\ArrayCollection();
        $this->cost = new Collections\ArrayCollection();
        $this->contractCost = new Collections\ArrayCollection();
        $this->funded = new Collections\ArrayCollection();
        $this->effort = new Collections\ArrayCollection();
        $this->spent = new Collections\ArrayCollection();
        $this->doaReminder = new Collections\ArrayCollection();
        $this->loiReminder = new Collections\ArrayCollection();
        $this->achievement = new Collections\ArrayCollection();
        $this->projectReportEffortSpent = new Collections\ArrayCollection();
        $this->changeRequestCostChange = new Collections\ArrayCollection();
        $this->changeRequestCountry = new Collections\ArrayCollection();
        $this->projectLog = new Collections\ArrayCollection();
        /*
         * Self-funded is default NOT
         */
        $this->selfFunded = self::NOT_SELF_FUNDED;
    }

    public static function getSelfFundedTemplates(): array
    {
        return self::$selfFundedTemplates;
    }

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
        return $this->parseBranchedName();
    }

    /**
     * @return string
     */
    public function parseBranchedName(): string
    {
        //Todo, fix this
        if (false && !\is_null($this->getParentOrganisation())) {
            return OrganisationService::parseBranch(
                $this->getBranch(),
                $this->getParentOrganisation()->getOrganisation()
            );
        }

        return OrganisationService::parseBranch($this->getBranch(), $this->getOrganisation());
    }

    /**
     * @return null|\Organisation\Entity\Parent\Organisation
     */
    public function getParentOrganisation(): ?\Organisation\Entity\Parent\Organisation
    {
        return $this->parentOrganisation;
    }

    /**
     * @param null|\Organisation\Entity\Parent\Organisation $parentOrganisation
     *
     * @return Affiliation
     */
    public function setParentOrganisation($parentOrganisation): Affiliation
    {
        $this->parentOrganisation = $parentOrganisation;

        return $this;
    }

    /**
     * @return string
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * @param string $branch
     */
    public function setBranch($branch)
    {
        $this->branch = $branch;
    }

    /**
     * @return \Organisation\Entity\Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @param \Organisation\Entity\Organisation $organisation
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;
    }

    public function isActive(): bool
    {
        return null === $this->dateEnd;
    }

    public function hasFinancial(): bool
    {
        return null !== $this->financial;
    }

    public function isSelfFunded(): bool
    {
        return null === $this->dateSelfFunded;
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
     * @return \Contact\Entity\Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param \Contact\Entity\Contact $contact
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
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
    public function getDateEnd()
    {
        return $this->dateEnd;
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
    public function getDateSelfFunded()
    {
        return $this->dateSelfFunded;
    }

    /**
     * @param \DateTime $dateSelfFunded
     */
    public function setDateSelfFunded($dateSelfFunded)
    {
        $this->dateSelfFunded = $dateSelfFunded;
    }

    /**
     * @return \Affiliation\Entity\Description[]|Collections\ArrayCollection()
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param \Affiliation\Entity\Description[]|Collections\ArrayCollection() $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return \Affiliation\Entity\Financial
     */
    public function getFinancial()
    {
        return $this->financial;
    }

    /**
     * @param \Affiliation\Entity\Financial $financial
     */
    public function setFinancial($financial)
    {
        $this->financial = $financial;
    }

    /**
     * @return \Organisation\Entity\IctOrganisation[]|Collections\ArrayCollection()
     */
    public function getIctOrganisation()
    {
        return $this->ictOrganisation;
    }

    /**
     * @param \Organisation\Entity\IctOrganisation[]|Collections\ArrayCollection() $ictOrganisation
     */
    public function setIctOrganisation($ictOrganisation)
    {
        $this->ictOrganisation = $ictOrganisation;
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
     * @return \Affiliation\Entity\Invoice[]|Collections\ArrayCollection()
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param \Affiliation\Entity\Invoice[]|Collections\ArrayCollection() $invoice
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * @return \Affiliation\Entity\Log[]|Collections\ArrayCollection()
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param \Affiliation\Entity\Log[]|Collections\ArrayCollection() $log
     */
    public function setLog($log)
    {
        $this->log = $log;
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param string $note
     */
    public function setNote($note)
    {
        $this->note = $note;
    }

    /**
     * @return \Project\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param \Project\Entity\Project $project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }

    /**
     * @param bool|true $textual
     *
     * @return int
     */
    public function getSelfFunded(bool $textual = false)
    {
        if ($textual) {
            return self::$selfFundedTemplates[$this->selfFunded];
        }

        return $this->selfFunded;
    }

    /**
     * @param int $selfFunded
     */
    public function setSelfFunded($selfFunded)
    {
        $this->selfFunded = $selfFunded;
    }

    /**
     * @return string
     */
    public function getValueChain()
    {
        return $this->valueChain;
    }

    /**
     * @param string $valueChain
     */
    public function setValueChain($valueChain)
    {
        $this->valueChain = $valueChain;
    }

    /**
     * @return \Affiliation\Entity\Version[]|Collections\ArrayCollection()
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param \Affiliation\Entity\Version[]|Collections\ArrayCollection() $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return \Contact\Entity\Contact[]|Collections\ArrayCollection()
     */
    public function getAssociate()
    {
        return $this->associate;
    }

    /**
     * @param \Contact\Entity\Contact[]|Collections\ArrayCollection() $associate
     */
    public function setAssociate($associate)
    {
        $this->associate = $associate;
    }

    /**
     * @return \Project\Entity\Cost\Cost[]|Collections\ArrayCollection()
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param \Project\Entity\Cost\Cost[]|Collections\ArrayCollection() $cost
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
    }

    /**
     * @return \Project\Entity\Funding\Funding[]|Collections\ArrayCollection()
     */
    public function getFunding()
    {
        return $this->funding;
    }

    /**
     * @param \Project\Entity\Funding\Funding[]|Collections\ArrayCollection() $funding
     */
    public function setFunding($funding)
    {
        $this->funding = $funding;
    }

    /**
     * @return \Project\Entity\Effort\Spent[]|Collections\ArrayCollection()
     */
    public function getSpent()
    {
        return $this->spent;
    }

    /**
     * @param \Project\Entity\Effort\Spent[]|Collections\ArrayCollection() $spent
     */
    public function setSpent($spent)
    {
        $this->spent = $spent;
    }

    /**
     * @return \Project\Entity\Effort\Effort[]|Collections\ArrayCollection()
     */
    public function getEffort()
    {
        return $this->effort;
    }

    /**
     * @param \Project\Entity\Effort\Effort[]|Collections\ArrayCollection() $effort
     */
    public function setEffort($effort)
    {
        $this->effort = $effort;
    }

    /**
     * @return \Affiliation\Entity\Loi
     */
    public function getLoi()
    {
        return $this->loi;
    }

    /**
     * @param \Affiliation\Entity\Loi $loi
     */
    public function setLoi($loi)
    {
        $this->loi = $loi;
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
     * @return Collections\ArrayCollection|\Project\Entity\Achievement[]
     */
    public function getAchievement()
    {
        return $this->achievement;
    }

    /**
     * @param Collections\ArrayCollection|\Project\Entity\Achievement[] $achievement
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
    public function setProjectReportEffortSpent($projectReportEffortSpent): Affiliation
    {
        $this->projectReportEffortSpent = $projectReportEffortSpent;

        return $this;
    }

    /**
     * @return Collections\ArrayCollection|\Project\Entity\ChangeRequest\CostChange[]
     */
    public function getChangeRequestCostChange()
    {
        return $this->changeRequestCostChange;
    }

    /**
     * @param Collections\ArrayCollection|\Project\Entity\ChangeRequest\CostChange[] $changerequestCostChange
     *
     * @return Affiliation
     */
    public function setChangeRequestCostChange($changerequestCostChange): Affiliation
    {
        $this->changeRequestCostChange = $changerequestCostChange;

        return $this;
    }

    /**
     * @return Collections\ArrayCollection|\Project\Entity\ChangeRequest\Country[]
     */
    public function getChangeRequestCountry()
    {
        return $this->changeRequestCountry;
    }

    /**
     * @param Collections\ArrayCollection|\Project\Entity\ChangeRequest\Country[] $changerequestCountry
     *
     * @return Affiliation
     */
    public function setChangeRequestCountry($changerequestCountry): Affiliation
    {
        $this->changeRequestCountry = $changerequestCountry;

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

    /**
     * @return string
     */
    public function getStrategicImportance()
    {
        return $this->strategicImportance;
    }

    /**
     * @param string $strategicImportance
     *
     * @return Affiliation
     */
    public function setStrategicImportance($strategicImportance)
    {
        $this->strategicImportance = $strategicImportance;

        return $this;
    }

    /**
     * @return Collections\ArrayCollection|\Project\Entity\Funding\Funded[]
     */
    public function getFunded()
    {
        return $this->funded;
    }

    /**
     * @param Collections\ArrayCollection|\Project\Entity\Funding\Funded[] $funded
     *
     * @return Affiliation
     */
    public function setFunded($funded): Affiliation
    {
        $this->funded = $funded;

        return $this;
    }

    /**
     * @return Collections\ArrayCollection|\Project\Entity\Contract[]
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * @param Collections\ArrayCollection|\Project\Entity\Contract[] $contract
     *
     * @return Affiliation
     */
    public function setContract($contract): Affiliation
    {
        $this->contract = $contract;

        return $this;
    }

    /**
     * @return ContractVersion[]|Collections\ArrayCollection
     */
    public function getContractVersion()
    {
        return $this->contractVersion;
    }

    /**
     * @param ContractVersion[]|Collections\ArrayCollection $contractVersion
     *
     * @return Affiliation
     */
    public function setContractVersion($contractVersion): Affiliation
    {
        $this->contractVersion = $contractVersion;

        return $this;
    }

    /**
     * @return Collections\ArrayCollection|\Project\Entity\Contract\Cost[]
     */
    public function getContractCost()
    {
        return $this->contractCost;
    }

    /**
     * @param Collections\ArrayCollection|\Project\Entity\Contract\Cost[] $contractCost
     *
     * @return Affiliation
     */
    public function setContractCost($contractCost): Affiliation
    {
        $this->contractCost = $contractCost;

        return $this;
    }

    /**
     * @return \Invoice\Entity\Method
     */
    public function getInvoiceMethod(): ?\Invoice\Entity\Method
    {
        return $this->invoiceMethod;
    }

    /**
     * @param \Invoice\Entity\Method $invoiceMethod
     *
     * @return Affiliation
     */
    public function setInvoiceMethod(?\Invoice\Entity\Method $invoiceMethod): Affiliation
    {
        $this->invoiceMethod = $invoiceMethod;

        return $this;
    }
}
