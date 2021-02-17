<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
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
use Laminas\Form\Annotation;
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

/**
 * @ORM\Table(name="affiliation")
 * @ORM\Entity(repositoryClass="Affiliation\Repository\Affiliation")
 * @Annotation\Hydrator("Laminas\Hydrator\ObjectPropertyHydrator")
 * @Annotation\Name("affiliation")
 */
class Affiliation extends AbstractEntity
{
    public const NOT_SELF_FUNDED = 0;
    public const SELF_FUNDED     = 1;

    protected static array $selfFundedTemplates
        = [
            self::NOT_SELF_FUNDED => 'txt-not-self-funded',
            self::SELF_FUNDED     => 'txt-self-funded',
        ];

    /**
     * @ORM\Column(name="affiliation_id",type="integer",options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;
    /**
     * @ORM\Column(name="branch", type="string", nullable=true)
     *
     * @var string
     */
    private $branch;
    /**
     * @ORM\Column(name="note", type="text", nullable=true)
     *
     * @var string
     */
    private $note;
    /**
     * @ORM\Column(name="value_chain", type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $valueChain;
    /**
     * @ORM\Column(name="market_access", type="text", nullable=true)
     *
     * @var string
     */
    private $marketAccess;
    /**
     * @ORM\Column(name="strategic_importance", type="text", nullable=true)
     *
     * @var string
     */
    private $strategicImportance;
    /**
     * @ORM\Column(name="main_contribution", type="text", nullable=true)
     *
     * @var string
     */
    private $mainContribution;
    /**
     * @ORM\Column(name="tasks_and_added_value", type="text", nullable=true)
     *
     * @var string
     */
    private $tasksAndAddedValue;
    /**
     * @ORM\Column(name="self_funded", type="smallint", nullable=false)
     *
     * @var int
     */
    private $selfFunded;
    /**
     * @ORM\Column(name="date_created", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     *
     * @var DateTime
     */
    private $dateCreated;
    /**
     * @ORM\Column(name="date_end", type="datetime", nullable=true)
     *
     * @var DateTime
     */
    private $dateEnd;
    /**
     * @ORM\Column(name="date_self_funded", type="datetime", nullable=true)
     *
     * @var DateTime
     */
    private $dateSelfFunded;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", inversedBy="affiliation", cascade={"persist"})
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id", nullable=false)
     *
     * @var Contact
     */
    private $contact;
    /**
     * @ORM\ManyToMany(targetEntity="Contact\Entity\Contact", cascade="persist", inversedBy="proxyAffiliation", orphanRemoval=true)
     * @ORM\JoinTable(name="affiliation_contact_proxy",
     *            joinColumns={@ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id")},
     *            inverseJoinColumns={@ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id")}
     * )
     *
     * @var Contact[]|Collections\ArrayCollection
     */
    private $proxyContact;
    /**
     * @ORM\Column(name="communication_contact_name", type="string", nullable=true)
     *
     * @var string
     */
    private $communicationContactName;
    /**
     * @ORM\Column(name="communication_contact_email", type="string", nullable=true)
     * @Annotation\Type("\Laminas\Form\Element\Email")
     *
     * @var string
     */
    private $communicationContactEmail;
    /**
     * @ORM\ManyToOne(targetEntity="Organisation\Entity\Organisation", inversedBy="affiliation", cascade={"persist"})
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="organisation_id", nullable=false)
     *
     * @var \Organisation\Entity\Organisation
     */
    private $organisation;
    /**
     * @ORM\ManyToOne(targetEntity="Organisation\Entity\Parent\Organisation", inversedBy="affiliation", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_organisation_id", referencedColumnName="parent_organisation_id", nullable=true)
     *
     * @var \Organisation\Entity\Parent\Organisation
     */
    private $parentOrganisation;
    /**
     * @ORM\ManyToOne(targetEntity="Project\Entity\Project", inversedBy="affiliation", cascade={"persist"})
     * @ORM\JoinColumn(name="project_id", referencedColumnName="project_id", nullable=true)
     *
     * @var Project
     */
    private $project;
    /**
     * @ORM\OneToOne(targetEntity="Affiliation\Entity\Description", cascade={"persist","remove"}, mappedBy="affiliation")
     *
     * @var Description
     */
    private $description;
    /**
     * @ORM\OneToOne(targetEntity="Affiliation\Entity\Financial", cascade={"persist","remove"}, mappedBy="affiliation")
     *
     * @var Financial
     */
    private $financial;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Invoice", cascade={"persist"}, mappedBy="affiliation")
     *
     * @var Invoice[]|Collections\ArrayCollection()
     */
    private $invoice;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Log", cascade={"persist","remove"}, mappedBy="affiliation")
     *
     * @var Log[]|Collections\ArrayCollection()
     */
    private $log;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Version", cascade={"persist"}, mappedBy="affiliation")
     *
     * @var Version[]|Collections\ArrayCollection()
     */
    private $version;
    /**
     * @ORM\ManyToMany(targetEntity="Project\Entity\Contract", cascade={"persist"}, mappedBy="affiliation")
     *
     * @var Contract[]|Collections\ArrayCollection()
     */
    private $contract;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\ContractVersion", cascade={"persist"}, mappedBy="affiliation")
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
     *
     * @var Contact[]|Collections\ArrayCollection()
     */
    private $associate;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Funding\Funding", cascade={"persist","remove"}, mappedBy="affiliation")
     *
     * @var Funding[]|Collections\ArrayCollection()
     */
    private $funding;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Cost\Cost", cascade={"persist","remove"}, mappedBy="affiliation")
     *
     * @var \Project\Entity\Cost\Cost[]|Collections\ArrayCollection()
     */
    private $cost;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Contract\Cost", cascade={"persist"}, mappedBy="affiliation")
     *
     * @var Cost[]|Collections\ArrayCollection()
     */
    private $contractCost;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Effort\Effort", cascade={"persist","remove"}, mappedBy="affiliation")
     *
     * @var Effort[]|Collections\ArrayCollection()
     */
    private $effort;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Funding\Funded", cascade={"persist","remove"}, mappedBy="affiliation", orphanRemoval=true)
     *
     * @var Funded[]|Collections\ArrayCollection()
     */
    private $funded;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Effort\Spent", cascade={"persist"}, mappedBy="affiliation")
     *
     * @var Spent[]|Collections\ArrayCollection()
     */
    private $spent;
    /**
     * @ORM\OneToMany(targetEntity="Project\Entity\Report\EffortSpent", cascade={"persist"}, mappedBy="affiliation")
     *
     * @var EffortSpent[]|Collections\ArrayCollection()
     */
    private $projectReportEffortSpent;
    /**
     * @ORM\OneToOne(targetEntity="Affiliation\Entity\Loi", cascade={"persist","remove"}, mappedBy="affiliation")
     *
     * @var Loi
     */
    private $loi;
    /**
     * @ORM\OneToOne(targetEntity="Affiliation\Entity\Doa", cascade={"persist","remove"}, mappedBy="affiliation")
     *
     * @var Doa
     */
    private $doa;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Doa\Reminder", cascade={"persist","remove"}, mappedBy="affiliation")
     * @ORM\OrderBy=({"DateCreated"="DESC"})
     *
     * @var \Affiliation\Entity\Doa\Reminder[]|Collections\ArrayCollection
     */
    private $doaReminder;
    /**
     * @ORM\ManyToMany(targetEntity="Project\Entity\Achievement", cascade={"persist"}, mappedBy="affiliation")
     *
     * @var Achievement[]|Collections\ArrayCollection
     */
    private $achievement;
    /**
     * @ORM\ManyToMany(targetEntity="Project\Entity\ChangeRequest\CostChange", cascade={"persist"}, mappedBy="affiliation")
     *
     * @var CostChange[]|Collections\ArrayCollection
     */
    private $changeRequestCostChange;
    /**
     * @ORM\ManyToMany(targetEntity="Project\Entity\ChangeRequest\Country", cascade={"persist"}, mappedBy="affiliation")
     *
     * @var Country[]|Collections\ArrayCollection
     */
    private $changeRequestCountry;
    /**
     * @ORM\ManyToMany(targetEntity="Project\Entity\Log", cascade={"persist"}, mappedBy="affiliation")
     *
     * @var \Project\Entity\Log[]|Collections\ArrayCollection
     */
    private $projectLog;
    /**
     * @ORM\ManyToOne(targetEntity="Invoice\Entity\Method", inversedBy="affiliation", cascade={"persist"})
     * @ORM\JoinColumn(name="method_id", referencedColumnName="method_id", nullable=true)
     *
     * @var Method
     */
    private $invoiceMethod;
    /**
     * @ORM\OneToMany(targetEntity="Affiliation\Entity\Questionnaire\Answer", cascade={"persist","remove"}, mappedBy="affiliation")
     *
     * @var Answer[]|Collections\Collection
     */
    private $answers;

    public function __construct()
    {
        $this->invoice                  = new Collections\ArrayCollection();
        $this->log                      = new Collections\ArrayCollection();
        $this->version                  = new Collections\ArrayCollection();
        $this->contractVersion          = new Collections\ArrayCollection();
        $this->contract                 = new Collections\ArrayCollection();
        $this->associate                = new Collections\ArrayCollection();
        $this->funding                  = new Collections\ArrayCollection();
        $this->cost                     = new Collections\ArrayCollection();
        $this->contractCost             = new Collections\ArrayCollection();
        $this->funded                   = new Collections\ArrayCollection();
        $this->effort                   = new Collections\ArrayCollection();
        $this->spent                    = new Collections\ArrayCollection();
        $this->doaReminder              = new Collections\ArrayCollection();
        $this->achievement              = new Collections\ArrayCollection();
        $this->projectReportEffortSpent = new Collections\ArrayCollection();
        $this->changeRequestCostChange  = new Collections\ArrayCollection();
        $this->changeRequestCountry     = new Collections\ArrayCollection();
        $this->projectLog               = new Collections\ArrayCollection();
        $this->answers                  = new Collections\ArrayCollection();
        $this->proxyContact             = new Collections\ArrayCollection();
        /*
         * Self-funded is default NOT
         */
        $this->selfFunded = self::NOT_SELF_FUNDED;
    }

    public static function getSelfFundedTemplates(): array
    {
        return self::$selfFundedTemplates;
    }

    public function isSelfFunded(): bool
    {
        return $this->selfFunded === self::SELF_FUNDED && null !== $this->dateSelfFunded;
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

    public function setBranch(?string $branch): Affiliation
    {
        $this->branch = $branch;
        return $this;
    }

    public function getOrganisation(): ?\Organisation\Entity\Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(?\Organisation\Entity\Organisation $organisation): Affiliation
    {
        $this->organisation = $organisation;
        return $this;
    }

    public function hasMarketAccess(): bool
    {
        return null !== $this->marketAccess;
    }

    public function hasMainContribution(): bool
    {
        return null !== $this->mainContribution;
    }

    public function hasTasksAndAddedValue(): bool
    {
        return null !== $this->tasksAndAddedValue;
    }

    public function hasParentOrganisation(): bool
    {
        return null !== $this->parentOrganisation;
    }

    public function hasInvoiceMethodFPP(): bool
    {
        return null !== $this->invoiceMethod && $this->invoiceMethod->getId() === Method::METHOD_PERCENTAGE;
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

    public function hasDoa(): bool
    {
        return null !== $this->doa;
    }

    public function hasContractVersion(): bool
    {
        return null !== $this->contract && ! $this->contractVersion->isEmpty();
    }

    public function addAssociate(Contact $contact): void
    {
        if (! $this->associate->contains($contact)) {
            $this->associate->add($contact);
        }
    }

    public function removeAssociate(Contact $contact): void
    {
        $this->associate->removeElement($contact);
    }

    public function getSelfFundedText(): string
    {
        return self::$selfFundedTemplates[$this->selfFunded] ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Affiliation
    {
        $this->id = $id;
        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): Affiliation
    {
        $this->note = $note;
        return $this;
    }

    public function getValueChain(): ?string
    {
        return $this->valueChain;
    }

    public function setValueChain(?string $valueChain): Affiliation
    {
        $this->valueChain = $valueChain;
        return $this;
    }

    public function getMarketAccess(): ?string
    {
        return $this->marketAccess;
    }

    public function setMarketAccess(?string $marketAccess): Affiliation
    {
        $this->marketAccess = $marketAccess;
        return $this;
    }

    public function getStrategicImportance(): ?string
    {
        return $this->strategicImportance;
    }

    public function setStrategicImportance(?string $strategicImportance): Affiliation
    {
        $this->strategicImportance = $strategicImportance;
        return $this;
    }

    public function getMainContribution(): ?string
    {
        return $this->mainContribution;
    }

    public function setMainContribution(?string $mainContribution): Affiliation
    {
        $this->mainContribution = $mainContribution;
        return $this;
    }

    public function getTasksAndAddedValue(): ?string
    {
        return $this->tasksAndAddedValue;
    }

    public function setTasksAndAddedValue(?string $tasksAndAddedValue): Affiliation
    {
        $this->tasksAndAddedValue = $tasksAndAddedValue;
        return $this;
    }

    public function getSelfFunded(): ?int
    {
        return $this->selfFunded;
    }

    public function setSelfFunded(?int $selfFunded): Affiliation
    {
        $this->selfFunded = $selfFunded;
        return $this;
    }

    public function getDateCreated(): ?DateTime
    {
        return $this->dateCreated;
    }

    public function setDateCreated(?DateTime $dateCreated): Affiliation
    {
        $this->dateCreated = $dateCreated;
        return $this;
    }

    public function getDateEnd(): ?DateTime
    {
        return $this->dateEnd;
    }

    public function setDateEnd(?DateTime $dateEnd): Affiliation
    {
        $this->dateEnd = $dateEnd;
        return $this;
    }

    public function getDateSelfFunded(): ?DateTime
    {
        return $this->dateSelfFunded;
    }

    public function setDateSelfFunded(?DateTime $dateSelfFunded): Affiliation
    {
        $this->dateSelfFunded = $dateSelfFunded;
        return $this;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(?Contact $contact): Affiliation
    {
        $this->contact = $contact;
        return $this;
    }

    public function getProxyContact()
    {
        return $this->proxyContact;
    }

    public function setProxyContact($proxyContact): Affiliation
    {
        $this->proxyContact = $proxyContact;
        return $this;
    }

    public function getCommunicationContactName(): ?string
    {
        return $this->communicationContactName;
    }

    public function setCommunicationContactName(?string $communicationContactName): Affiliation
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

    public function getParentOrganisation(): ?Organisation
    {
        return $this->parentOrganisation;
    }

    public function setParentOrganisation(?Organisation $parentOrganisation): Affiliation
    {
        $this->parentOrganisation = $parentOrganisation;
        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): Affiliation
    {
        $this->project = $project;
        return $this;
    }

    public function getDescription(): ?Description
    {
        return $this->description;
    }

    public function setDescription(?Description $description): Affiliation
    {
        $this->description = $description;
        return $this;
    }

    public function getFinancial(): ?Financial
    {
        return $this->financial;
    }

    public function setFinancial(?Financial $financial): Affiliation
    {
        $this->financial = $financial;
        return $this;
    }

    public function getInvoice()
    {
        return $this->invoice;
    }

    public function setInvoice($invoice): Affiliation
    {
        $this->invoice = $invoice;
        return $this;
    }

    public function getLog()
    {
        return $this->log;
    }

    public function setLog($log): Affiliation
    {
        $this->log = $log;
        return $this;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version): Affiliation
    {
        $this->version = $version;
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

    public function getAssociate()
    {
        return $this->associate;
    }

    public function setAssociate($associate): Affiliation
    {
        $this->associate = $associate;
        return $this;
    }

    public function getFunding()
    {
        return $this->funding;
    }

    public function setFunding($funding): Affiliation
    {
        $this->funding = $funding;
        return $this;
    }

    public function getCost()
    {
        return $this->cost;
    }

    public function setCost($cost): Affiliation
    {
        $this->cost = $cost;
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

    public function getEffort()
    {
        return $this->effort;
    }

    public function setEffort($effort): Affiliation
    {
        $this->effort = $effort;
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

    public function getSpent()
    {
        return $this->spent;
    }

    public function setSpent($spent): Affiliation
    {
        $this->spent = $spent;
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

    public function getLoi(): ?Loi
    {
        return $this->loi;
    }

    public function setLoi(?Loi $loi): Affiliation
    {
        $this->loi = $loi;
        return $this;
    }

    public function getDoa(): ?Doa
    {
        return $this->doa;
    }

    public function setDoa(?Doa $doa): Affiliation
    {
        $this->doa = $doa;
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

    public function setChangeRequestCountry($changeRequestCountry): Affiliation
    {
        $this->changeRequestCountry = $changeRequestCountry;
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
}
