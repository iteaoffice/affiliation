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

use Affiliation\Entity\Questionnaire\Answer;
use Contact\Entity\Contact;
use DateTime;
use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Invoice\Entity\Method;
use Organisation\Entity\Parent\Organisation;
use Organisation\Service\OrganisationService;
use Project\Entity\Achievement;
use Project\Entity\ChangeRequest\CostChange;
use Project\Entity\ChangeRequest\Country;
use Project\Entity\Contract;
use Project\Entity\Contract\Cost;
use Project\Entity\Effort\Effort;
use Project\Entity\Effort\Spent;
use Project\Entity\Funding\Funded;
use Project\Entity\Funding\Funding;
use Project\Entity\Project;
use Project\Entity\Report\EffortSpent;
use Zend\Form\Annotation;

/**
 * @ORM\Table(name="affiliation")
 * @ORM\Entity(repositoryClass="Affiliation\Repository\Affiliation")
 * @Annotation\Hydrator("Zend\Hydrator\ObjectProperty")
 * @Annotation\Name("affiliation")
 */
class Affiliation extends AbstractEntity
{
    public const NOT_SELF_FUNDED = 0;
    public const SELF_FUNDED = 1;

    protected static $selfFundedTemplates
        = [
            self::NOT_SELF_FUNDED => 'txt-not-self-funded',
            self::SELF_FUNDED     => 'txt-self-funded',
        ];

    /**
     * @ORM\Column(name="affiliation_id",type="integer",options={"unsigned":true})
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
     * @ORM\Column(name="value_chain", type="string", length=255, nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-value-chain"})
     *
     * @var string
     */
    private $valueChain;
    /**
     * @ORM\Column(name="market_access", type="text", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-market-access"})
     *
     * @var string
     */
    private $marketAccess;
    /**
     * @ORM\Column(name="strategic_importance", type="text", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-strategic-importance"})
     *
     * @var string
     */
    private $strategicImportance;
    /**
     * @ORM\Column(name="main_contribution", type="text", nullable=true)
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
     * @ORM\Column(name="date_created", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     * @Annotation\Exclude()
     *
     * @var DateTime
     */
    private $dateCreated;
    /**
     * @ORM\Column(name="date_end", type="datetime", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\DateTime")
     * @Annotation\Options({"label":"txt-date-end"})
     *
     * @var DateTime
     */
    private $dateEnd;
    /**
     * @ORM\Column(name="date_self_funded", type="datetime", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\DateTime")
     * @Annotation\Options({"label":"txt-date-self-funded"})
     *
     * @var DateTime
     */
    private $dateSelfFunded;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", inversedBy="affiliation", cascade={"persist"})
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id", nullable=false)
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
     * @Annotation\Attributes({"label":"txt-technical-contact"})
     *
     * @var Contact
     */
    private $contact;
    /**
     * @ORM\Column(name="communication_contact_name", type="string", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Text")
     * @Annotation\Options({"label":"txt-affiliation-communication-contact-name-label","help-block":"txt-affiliation-communication-contact-name-help-block"})
     * @Annotation\Attributes({"placeholder":"txt-affiliation-communication-contact-name-placeholder"})
     *
     * @var string
     */
    private $communicationContactName;
    /**
     * @ORM\Column(name="communication_contact_email", type="string", nullable=true)
     * @Annotation\Type("\Zend\Form\Element\Email")
     * @Annotation\Options({"label":"txt-affiliation-communication-contact-email-label","help-block":"txt-affiliation-communication-contact-email-help-block"})
     * @Annotation\Attributes({"placeholder":"txt-affiliation-communication-contact-email-placeholder"})
     *
     * @var string
     */
    private $communicationContactEmail;
    /**
     * @ORM\ManyToOne(targetEntity="Organisation\Entity\Organisation", inversedBy="affiliation", cascade={"persist"})
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="organisation_id", nullable=false)
     * @Annotation\Exclude()
     *
     * @var \Organisation\Entity\Organisation
     */
    private $organisation;
    /**
     * @ORM\ManyToOne(targetEntity="Organisation\Entity\Parent\Organisation", inversedBy="affiliation", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_organisation_id", referencedColumnName="parent_organisation_id", nullable=true)
     * @Annotation\Exclude()
     *
     * @var Organisation|null
     */
    private $parentOrganisation;
    /**
     * @ORM\ManyToOne(targetEntity="Project\Entity\Project", inversedBy="affiliation", cascade={"persist"})
     * @ORM\JoinColumn(name="project_id", referencedColumnName="project_id", nullable=true)
     * @Annotation\Exclude()
     *
     * @var Project
     */
    private $project;
    /**
     * @ORM\OneToOne(targetEntity="Affiliation\Entity\Description", cascade={"persist","remove"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var Description|null
     */
    private $description;
    /**
     * @ORM\OneToOne(targetEntity="Affiliation\Entity\Financial", cascade={"persist","remove"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var Financial
     */
    private $financial;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Invoice", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var Invoice[]|Collections\ArrayCollection()
     */
    private $invoice;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Log", cascade={"persist","remove"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var Log[]|Collections\ArrayCollection()
     */
    private $log;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Version", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var Version[]|Collections\ArrayCollection()
     */
    private $version;
    /**
     * @ORM\ManyToMany(targetEntity="Project\Entity\Contract", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var Contract[]|Collections\ArrayCollection()
     */
    private $contract;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\ContractVersion", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var ContractVersion[]|Collections\ArrayCollection()
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
     * @var Contact[]|Collections\ArrayCollection()
     */
    private $associate;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Funding\Funding", cascade={"persist","remove"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var Funding[]|Collections\ArrayCollection()
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
     * @var Cost[]|Collections\ArrayCollection()
     */
    private $contractCost;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Effort\Effort", cascade={"persist","remove"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var Effort[]|Collections\ArrayCollection()
     */
    private $effort;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Funding\Funded", cascade={"persist","remove"}, mappedBy="affiliation", orphanRemoval=true)
     * @Annotation\Exclude()
     *
     * @var Funded[]|Collections\ArrayCollection()
     */
    private $funded;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Effort\Spent", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var Spent[]|Collections\ArrayCollection()
     */
    private $spent;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Report\EffortSpent", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var EffortSpent[]|Collections\ArrayCollection()
     */
    private $projectReportEffortSpent;
    /**
     * @ORM\OneToOne(targetEntity="Affiliation\Entity\Loi", cascade={"persist","remove"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var Loi
     */
    private $loi;
    /**
     * @ORM\OneToOne(targetEntity="Affiliation\Entity\Doa", cascade={"persist","remove"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var Doa
     */
    private $doa;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\DoaReminder", cascade={"persist","remove"}, mappedBy="affiliation")
     * @ORM\OrderBy=({"DateCreated"="DESC"})
     * @Annotation\Exclude();
     *
     * @var DoaReminder[]|Collections\ArrayCollection
     */
    private $doaReminder;
    /**
     * @ORM\ManyToMany(targetEntity="Project\Entity\Achievement", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var Achievement[]|Collections\ArrayCollection
     */
    private $achievement;
    /**
     * @ORM\ManyToMany(targetEntity="Project\Entity\ChangeRequest\CostChange", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var CostChange[]|Collections\ArrayCollection
     */
    private $changeRequestCostChange;
    /**
     * @ORM\ManyToMany(targetEntity="Project\Entity\ChangeRequest\Country", cascade={"persist"}, mappedBy="affiliation")
     * @Annotation\Exclude()
     *
     * @var Country[]|Collections\ArrayCollection
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
     * @var Method
     */
    private $invoiceMethod;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Questionnaire\Answer", cascade={"persist","remove"}, mappedBy="affiliation")
     * @Annotation\Exclude();
     *
     * @var Answer[]|Collections\Collection
     */
    private $answers;

    public function __construct()
    {
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
        $this->achievement = new Collections\ArrayCollection();
        $this->projectReportEffortSpent = new Collections\ArrayCollection();
        $this->changeRequestCostChange = new Collections\ArrayCollection();
        $this->changeRequestCountry = new Collections\ArrayCollection();
        $this->projectLog = new Collections\ArrayCollection();
        $this->answers = new Collections\ArrayCollection();
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

    public function parseBranchedName(): string
    {
        return OrganisationService::parseBranch($this->getBranch(), $this->getOrganisation());
    }

    public function getBranch(): ?string
    {
        return $this->branch;
    }

    public function setBranch($branch)
    {
        $this->branch = $branch;
    }

    public function getOrganisation()
    {
        return $this->organisation;
    }

    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;
    }

    public function getParentOrganisation(): ?Organisation
    {
        return $this->parentOrganisation;
    }

    public function setParentOrganisation($parentOrganisation): Affiliation
    {
        $this->parentOrganisation = $parentOrganisation;

        return $this;
    }

    public function isActive(): bool
    {
        return null === $this->dateEnd;
    }

    public function hasFinancial(): bool
    {
        return null !== $this->financial;
    }

    public function hasDescription(): bool
    {
        return null !== $this->description;
    }

    public function isSelfFunded(): bool
    {
        return null === $this->dateSelfFunded;
    }

    public function hasContractVersion(): bool
    {
        return null !== $this->contract && !$this->contractVersion->isEmpty();
    }

    public function addAssociate(Contact $contact): void
    {
        if (!$this->associate->contains($contact)) {
            $this->associate->add($contact);
        }
    }

    public function removeAssociate(Contact $contact): void
    {
        $this->associate->removeElement($contact);
    }

    public function getContact()
    {
        return $this->contact;
    }

    public function setContact($contact)
    {
        $this->contact = $contact;
    }

    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }

    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;
    }

    public function getDateSelfFunded()
    {
        return $this->dateSelfFunded;
    }

    public function setDateSelfFunded($dateSelfFunded)
    {
        $this->dateSelfFunded = $dateSelfFunded;
    }

    public function getDescription(): ?Description
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getFinancial(): ?Financial
    {
        return $this->financial;
    }

    public function setFinancial($financial)
    {
        $this->financial = $financial;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getInvoice()
    {
        return $this->invoice;
    }

    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
    }

    public function getLog()
    {
        return $this->log;
    }

    public function setLog($log)
    {
        $this->log = $log;
    }

    public function getNote()
    {
        return $this->note;
    }

    public function setNote($note)
    {
        $this->note = $note;
    }

    public function getProject()
    {
        return $this->project;
    }

    public function setProject($project)
    {
        $this->project = $project;
    }

    public function getSelfFunded(bool $textual = false)
    {
        if ($textual) {
            return self::$selfFundedTemplates[$this->selfFunded];
        }

        return $this->selfFunded;
    }

    public function setSelfFunded($selfFunded)
    {
        $this->selfFunded = $selfFunded;
    }

    public function getValueChain()
    {
        return $this->valueChain;
    }

    public function setValueChain($valueChain)
    {
        $this->valueChain = $valueChain;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function getAssociate()
    {
        return $this->associate;
    }

    public function setAssociate($associate)
    {
        $this->associate = $associate;
    }

    public function getCost()
    {
        return $this->cost;
    }

    public function setCost($cost)
    {
        $this->cost = $cost;
    }

    public function getFunding()
    {
        return $this->funding;
    }

    public function setFunding($funding)
    {
        $this->funding = $funding;
    }

    public function getSpent()
    {
        return $this->spent;
    }

    public function setSpent($spent): Affiliation
    {
        $this->spent = $spent;

        return $this;
    }

    public function getEffort()
    {
        return $this->effort;
    }

    public function setEffort($effort): Affiliation
    {
        $this->effort = $effort;

        return $this;
    }

    public function getLoi()
    {
        return $this->loi;
    }

    public function setLoi($loi): Affiliation
    {
        $this->loi = $loi;

        return $this;
    }

    public function getDoa()
    {
        return $this->doa;
    }

    public function setDoa($doa): Affiliation
    {
        $this->doa = $doa;

        return $this;
    }

    public function getMainContribution(): ?string
    {
        return $this->mainContribution;
    }

    public function setMainContribution($mainContribution): Affiliation
    {
        $this->mainContribution = $mainContribution;

        return $this;
    }

    public function getMarketAccess(): ?string
    {
        return $this->marketAccess;
    }

    public function setMarketAccess($marketAccess): Affiliation
    {
        $this->marketAccess = $marketAccess;

        return $this;
    }

    public function getDoaReminder()
    {
        return $this->doaReminder;
    }

    public function setDoaReminder($doaReminder): Affiliation
    {
        $this->doaReminder = $doaReminder;

        return $this;
    }

    public function getAchievement()
    {
        return $this->achievement;
    }

    public function setAchievement($achievement): Affiliation
    {
        $this->achievement = $achievement;

        return $this;
    }

    public function getProjectReportEffortSpent()
    {
        return $this->projectReportEffortSpent;
    }

    public function setProjectReportEffortSpent($projectReportEffortSpent): Affiliation
    {
        $this->projectReportEffortSpent = $projectReportEffortSpent;

        return $this;
    }

    public function getChangeRequestCostChange()
    {
        return $this->changeRequestCostChange;
    }

    public function setChangeRequestCostChange($changeRequestCostChange): Affiliation
    {
        $this->changeRequestCostChange = $changeRequestCostChange;

        return $this;
    }

    public function getChangeRequestCountry()
    {
        return $this->changeRequestCountry;
    }

    public function setChangeRequestCountry($changerequestCountry): Affiliation
    {
        $this->changeRequestCountry = $changerequestCountry;

        return $this;
    }

    public function getProjectLog()
    {
        return $this->projectLog;
    }

    public function setProjectLog($projectLog): Affiliation
    {
        $this->projectLog = $projectLog;

        return $this;
    }

    public function getStrategicImportance(): ?string
    {
        return $this->strategicImportance;
    }

    public function setStrategicImportance($strategicImportance): Affiliation
    {
        $this->strategicImportance = $strategicImportance;

        return $this;
    }

    public function getFunded()
    {
        return $this->funded;
    }

    public function setFunded($funded): Affiliation
    {
        $this->funded = $funded;

        return $this;
    }

    public function getContract()
    {
        return $this->contract;
    }

    public function setContract($contract): Affiliation
    {
        $this->contract = $contract;

        return $this;
    }

    public function getContractVersion()
    {
        return $this->contractVersion;
    }

    public function setContractVersion($contractVersion): Affiliation
    {
        $this->contractVersion = $contractVersion;

        return $this;
    }

    public function getContractCost()
    {
        return $this->contractCost;
    }

    public function setContractCost($contractCost): Affiliation
    {
        $this->contractCost = $contractCost;

        return $this;
    }

    public function getInvoiceMethod(): ?Method
    {
        return $this->invoiceMethod;
    }

    public function setInvoiceMethod(?Method $invoiceMethod): Affiliation
    {
        $this->invoiceMethod = $invoiceMethod;

        return $this;
    }

    public function getAnswers()
    {
        return $this->answers;
    }

    public function setAnswers($answers): Affiliation
    {
        $this->answers = $answers;
        return $this;
    }

    public function getCommunicationContactName(): ?string
    {
        return $this->communicationContactName;
    }

    public function setCommunicationContactName(string $communicationContactName): Affiliation
    {
        $this->communicationContactName = $communicationContactName;
        return $this;
    }

    public function getCommunicationContactEmail(): ?string
    {
        return $this->communicationContactEmail;
    }

    public function setCommunicationContactEmail(?string $communicationContactEmail): Affiliation
    {
        $this->communicationContactEmail = $communicationContactEmail;
        return $this;
    }
}
