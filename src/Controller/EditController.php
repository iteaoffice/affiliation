<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Controller;

use Affiliation\Entity;
use Affiliation\Form;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\FormService;
use Contact\Entity\Contact;
use Contact\Service\ContactService;
use DateTime;
use Doctrine\ORM\EntityManager;
use General\Service\CountryService;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\View\Model\ViewModel;
use Organisation\Service\OrganisationService;
use Project\Entity\Changelog;
use Project\Entity\Cost\Cost;
use Project\Entity\Effort\Effort;
use Project\Entity\Project;
use Project\Entity\Report\EffortSpent as ReportEffortSpent;
use Project\Service\ContractService;
use Project\Service\ProjectService;
use Project\Service\ReportService;
use Project\Service\VersionService;
use Project\Service\WorkpackageService;

use function array_key_exists;
use function count;
use function explode;
use function str_replace;

/**
 * Class EditController
 *
 * @package Affiliation\Controller
 */
final class EditController extends AffiliationAbstractController
{
    private AffiliationService $affiliationService;
    private ProjectService $projectService;
    private VersionService $versionService;
    private ContactService $contactService;
    private OrganisationService $organisationService;
    private CountryService $countryService;
    private ReportService $reportService;
    private ContractService $contractService;
    private WorkpackageService $workpackageService;
    private FormService $formService;
    private EntityManager $entityManager;
    private TranslatorInterface $translator;

    public function __construct(
        AffiliationService $affiliationService,
        ProjectService $projectService,
        VersionService $versionService,
        ContactService $contactService,
        OrganisationService $organisationService,
        CountryService $countryService,
        ReportService $reportService,
        ContractService $contractService,
        WorkpackageService $workpackageService,
        FormService $formService,
        EntityManager $entityManager,
        TranslatorInterface $translator
    )
    {
        $this->affiliationService  = $affiliationService;
        $this->projectService      = $projectService;
        $this->versionService      = $versionService;
        $this->contactService      = $contactService;
        $this->organisationService = $organisationService;
        $this->countryService      = $countryService;
        $this->reportService       = $reportService;
        $this->contractService     = $contractService;
        $this->workpackageService  = $workpackageService;
        $this->formService         = $formService;
        $this->entityManager       = $entityManager;
        $this->translator          = $translator;
    }


    public function affiliationAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $formData                              = $this->getRequest()->getPost()->toArray();
        $formData['affiliation']               = sprintf(
            '%s|%s',
            $affiliation->getOrganisation()->getId(),
            $affiliation->getBranch()
        );
        $formData['technical']                 = $affiliation->getContact()->getId();
        $formData['communicationContactName']  = $affiliation->getCommunicationContactName();
        $formData['communicationContactEmail'] = $affiliation->getCommunicationContactEmail();
        $formData['valueChain']                = $affiliation->getValueChain();
        $formData['mainContribution']          = $affiliation->getMainContribution();
        if ($this->projectService->hasTasksAndAddedValue($affiliation->getProject())) {
            $formData['tasksAndAddedValue'] = $affiliation->getTasksAndAddedValue();
        }
        $formData['strategicImportance'] = $affiliation->getStrategicImportance();
        $formData['selfFunded']          = $affiliation->getSelfFunded();

        $form = new Form\AffiliationForm($affiliation, $this->affiliationService, $this->contactService);
        $form->setData($formData);

        if (!$this->projectService->hasTasksAndAddedValue($affiliation->getProject())) {
            $form->remove('tasksAndAddedValue');
        }

        //Remove the de-activate-button when partner is not active
        if (!$affiliation->isActive()) {
            $form->remove('deactivate');
        }

        //Remove the re-activate-button when partner is active
        if ($affiliation->isActive()) {
            $form->remove('reactivate');
        }

        if ($this->affiliationService->affiliationHasCostOrEffortInDraft($affiliation)) {
            $form->remove('deactivate');
        }

        if ($this->getRequest()->isPost() && $form->setData($this->getRequest()->getPost()->toArray())) {
            /*
             * When the deactivate button is pressed, handle this in the service layer
             */
            if (isset($formData['deactivate'])) {
                $this->affiliationService->deactivateAffiliation($affiliation);

                //Update the rationale for public funding
                $this->affiliationService
                    ->updateCountryRationaleByAffiliation($affiliation, AffiliationService::AFFILIATION_DEACTIVATE);

                $changelogMessage = sprintf(
                    $this->translator->translate('txt-affiliation-%s-has-successfully-been-deactivated'),
                    $affiliation
                );

                $this->flashMessenger()->addSuccessMessage($changelogMessage);
                $this->projectService->addMessageToChangelog(
                    $affiliation->getProject(),
                    $this->identity(),
                    Changelog::TYPE_PARTNER,
                    Changelog::SOURCE_COMMUNITY,
                    $changelogMessage
                );

                return $this->redirect()->toRoute(
                    'community/project/project/partners',
                    [
                        'docRef' => $affiliation->getProject()->getDocRef(),
                    ]
                );
            }
            /*
             * When the deactivate button is pressed, handle this in the service layer
             */
            if (isset($formData['reactivate'])) {
                $this->affiliationService->reactivateAffiliation($affiliation);

                //Update the rationale for public funding
                $this->affiliationService
                    ->updateCountryRationaleByAffiliation($affiliation, AffiliationService::AFFILIATION_REACTIVATE);

                $changelogMessage = sprintf(
                    $this->translator->translate('txt-affiliation-%s-has-successfully-been-reactivated'),
                    $affiliation
                );

                $this->flashMessenger()->addSuccessMessage($changelogMessage);
                $this->projectService->addMessageToChangelog(
                    $affiliation->getProject(),
                    $this->identity(),
                    Changelog::TYPE_PARTNER,
                    Changelog::SOURCE_COMMUNITY,
                    $changelogMessage
                );

                return $this->redirect()->toRoute(
                    'community/affiliation/details',
                    [
                        'id' => $affiliation->getId(),
                    ]
                );
            }

            /*
             * When the deactivate button is pressed, handle this in the service layer
             */
            if (isset($formData['cancel'])) {
                return $this->redirect()->toRoute(
                    'community/affiliation/details',
                    ['id' => $affiliation->getId(),]
                );
            }

            if ($form->isValid()) {
                $formData = $form->getData();

                /*
                 * Parse the organisation and branch
                 */
                [$organisationId, $branch] = explode('|', $formData['affiliation']);
                $organisation = $this->organisationService->findOrganisationById((int)$organisationId);
                $affiliation->setOrganisation($organisation);
                $affiliation->setContact($this->contactService->findContactById((int)$formData['technical']));
                $affiliation->setCommunicationContactName($formData['communicationContactName']);
                $affiliation->setCommunicationContactEmail($formData['communicationContactEmail']);
                $affiliation->setBranch($branch);
                $affiliation->setValueChain($formData['valueChain']);
                $affiliation->setMainContribution($formData['mainContribution']);

                if ($this->projectService->hasTasksAndAddedValue($affiliation->getProject())) {
                    $affiliation->setTasksAndAddedValue($formData['tasksAndAddedValue']);
                }

                $affiliation->setStrategicImportance($formData['strategicImportance']);
                $affiliation->setSelfFunded($formData['selfFunded']);

                $this->affiliationService->save($affiliation);

                $changelogMessage = sprintf(
                    $this->translator->translate('txt-affiliation-%s-has-successfully-been-updated'),
                    $affiliation
                );

                $this->flashMessenger()->addSuccessMessage($changelogMessage);
                $this->projectService->addMessageToChangelog(
                    $affiliation->getProject(),
                    $this->identity(),
                    Changelog::TYPE_PARTNER,
                    Changelog::SOURCE_COMMUNITY,
                    $changelogMessage
                );

                return $this->redirect()->toRoute(
                    'community/affiliation/details',
                    [
                        'id' => $affiliation->getId(),
                    ]
                );
            }
        }

        return new ViewModel(
            [
                'affiliation'                       => $affiliation,
                'affiliationHasCostOrEffortInDraft' => $this->affiliationService
                    ->affiliationHasCostOrEffortInDraft($affiliation),
                'affiliationService'                => $this->affiliationService,
                'projectService'                    => $this->projectService,
                'form'                              => $form,
            ]
        );
    }

    public function financialAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $formData = $this->affiliationService->getFinancialFormData($affiliation);
        $form     = new Form\FinancialForm($affiliation, $this->contactService, $this->entityManager);
        $data     = array_merge($formData, $this->getRequest()->getPost()->toArray());
        $form->setData($data);

        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'community/affiliation/details',
                    [
                        'id' => $affiliation->getId(),
                    ],
                    [
                        'fragment' => 'invoicing',
                    ]
                );
            }


            if ($form->isValid()) {
                $formData = $form->getData();

                $this->affiliationService->saveFinancial(
                    $affiliation,
                    $formData['vat'],
                    (int)$formData['country'],
                    trim($formData['organisation']),
                    (int)$formData['contact'],
                    (int)$formData['preferredDelivery'],
                    (int)$formData['omitContact'],
                    (string)$formData['address'],
                    (string)$formData['zipCode'],
                    (string)$formData['city'],
                );

                $changelogMessage = sprintf(
                    $this->translator->translate(
                        'txt-affiliation-financial-information-%s-has-been-updated-successfully'
                    ),
                    $affiliation->parseBranchedName()
                );

                $this->flashMessenger()->addSuccessMessage($changelogMessage);
                $this->projectService->addMessageToChangelog(
                    $affiliation->getProject(),
                    $this->identity(),
                    Changelog::TYPE_PARTNER,
                    Changelog::SOURCE_COMMUNITY,
                    $changelogMessage
                );

                return $this->redirect()->toRoute(
                    'community/affiliation/financial',
                    [
                        'id' => $affiliation->getId(),
                    ],
                );
            }
        }

        return new ViewModel(
            [
                'affiliation'        => $affiliation,
                'affiliationService' => $this->affiliationService,
                'projectService'     => $this->projectService,
                'form'               => $form,
            ]
        );
    }

    public function manageAssociatesAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $data = $this->getRequest()->getPost()->toArray();


        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                $this->flashMessenger()->addSuccessMessage(
                    sprintf(
                        $this->translator->translate('txt-manage-associates-of-affiliation-%s-cancelled'),
                        $affiliation
                    )
                );

                return $this->redirect()->toRoute(
                    'community/affiliation/contacts',
                    ['id' => $affiliation->getId()]
                );
            }

            $removedContacts = [];
            if (array_key_exists('contact', $data)) {
                //Find the contact
                foreach ($data['contact'] as $contactId) {
                    $contact = $this->contactService->findContactById((int)$contactId);
                    if (null === $contact) {
                        continue;
                    }

                    $affiliation->getAssociate()->removeElement($contact);

                    $removedContacts[] = $contact;
                }

                $this->affiliationService->save($affiliation);
            }

            $changelogMessage = sprintf(
                $this->translator->translate('txt-%s-associates-were-removed-from-affiliation'),
                count($removedContacts)
            );

            $this->flashMessenger()->addSuccessMessage($changelogMessage);
            $this->projectService->addMessageToChangelog(
                $affiliation->getProject(),
                $this->identity(),
                Changelog::TYPE_PARTNER,
                Changelog::SOURCE_COMMUNITY,
                $changelogMessage
            );

            return $this->redirect()->toRoute(
                'community/affiliation/contacts',
                ['id' => $affiliation->getId()],
            );
        }

        return new ViewModel(
            [
                'affiliation'           => $affiliation,
                'contactsInAffiliation' => $this->contactService->findContactsInAffiliation($affiliation),
            ]
        );
    }

    public function addAssociateAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $data = $this->getRequest()->getPost()->toArray();

        $form = new Form\AddAssociateForm($affiliation, $this->contactService);
        $form->setData($data);

        if ($this->getRequest()->isPost() && $form->isValid()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'community/affiliation/contacts',
                    ['id' => $affiliation->getId()]
                );
            }

            if (empty($data['contact']) && empty($data['email'])) {
                $this->flashMessenger()->addInfoMessage(
                    sprintf(
                        $this->translator->translate('txt-no-contact-has-been-added-affiliation-%s'),
                        $affiliation->parseBranchedName()
                    )
                );

                return $this->redirect()->toRoute(
                    'community/affiliation/edit/add-associate',
                    ['id' => $affiliation->getId()]
                );
            }


            if (isset($data['addKnownContact']) && !empty($data['contact'])) {

                /** @var Contact $contact */
                $contact = $this->contactService->findContactById((int)$data['contact']);

                $this->affiliationService->addAssociate($affiliation, $contact);

                $changelogMessage = sprintf(
                    $this->translator->translate('txt-contact-%s-has-been-added-as-associate-to-affiliation-%s'),
                    $contact->parseFullName(),
                    $affiliation
                );

                $this->flashMessenger()->addSuccessMessage($changelogMessage);

                $this->projectService->addMessageToChangelog(
                    $affiliation->getProject(),
                    $this->identity(),
                    Changelog::TYPE_PARTNER,
                    Changelog::SOURCE_COMMUNITY,
                    $changelogMessage
                );
            }

            if (isset($data['addEmail']) && !empty($data['email'])) {
                $this->affiliationService->addAssociate($affiliation, null, $data['email']);

                $changelogMessage = sprintf(
                    $this->translator->translate('txt-contact-%s-has-been-added-as-associate-to-affiliation-%s'),
                    $data['email'],
                    $affiliation
                );

                $this->flashMessenger()->addSuccessMessage($changelogMessage);
                $this->projectService->addMessageToChangelog(
                    $affiliation->getProject(),
                    $this->identity(),
                    Changelog::TYPE_PARTNER,
                    Changelog::SOURCE_COMMUNITY,
                    $changelogMessage
                );
            }

            return $this->redirect()->toRoute(
                'community/affiliation/contacts',
                ['id' => $affiliation->getId()]
            );
        }

        return new ViewModel(
            [
                'affiliation'        => $affiliation,
                'affiliationService' => $this->affiliationService,
                'projectService'     => $this->projectService,
                'form'               => $form,
            ]
        );
    }

    public function descriptionAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $description = new Entity\Description();
        if ($affiliation->hasDescription()) {
            /** @var Entity\Description $description */
            $description = $affiliation->getDescription();
        }

        $data = $this->getRequest()->getPost()->toArray();
        $form = $this->formService->prepare($description, $data);
        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'community/affiliation/details',
                    ['id' => $affiliation->getId()],
                    ['fragment' => 'description']
                );
            }

            if ($form->isValid()) {

                /** @var Entity\Description $description */
                $description = $form->getData();
                $description->setAffiliation($affiliation);
                $description->setContact($this->identity());
                $this->affiliationService->save($description);

                $changelogMessage = sprintf(
                    $this->translator->translate('txt-description-of-affiliation-%s-has-successfully-been-updated'),
                    $affiliation
                );

                $this->flashMessenger()->addSuccessMessage($changelogMessage);
                $this->projectService->addMessageToChangelog(
                    $affiliation->getProject(),
                    $this->identity(),
                    Changelog::TYPE_PARTNER,
                    Changelog::SOURCE_COMMUNITY,
                    $changelogMessage
                );

                return $this->redirect()->toRoute(
                    'community/affiliation/details',
                    ['id' => $affiliation->getId()],
                    ['fragment' => 'description']
                );
            }
        }

        return new ViewModel(
            [
                'affiliation'        => $affiliation,
                'affiliationService' => $this->affiliationService,
                'projectService'     => $this->projectService,
                'form'               => $form,
            ]
        );
    }

    public function marketAccessAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $data = $this->getRequest()->getPost()->toArray();
        $form = new Form\MarketAccessForm();
        $form->get('marketAccess')->setValue($affiliation->getMarketAccess());

        $form->setData($data);


        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'community/affiliation/details',
                    ['id' => $affiliation->getId()],
                    ['fragment' => 'market-access']
                );
            }

            if ($form->isValid()) {
                $affiliation->setMarketAccess($form->getData()['marketAccess']);
                $this->affiliationService->save($affiliation);

                $changelogMessage = sprintf(
                    $this->translator->translate('txt-description-of-affiliation-%s-has-successfully-been-updated'),
                    $affiliation
                );

                $this->flashMessenger()->addSuccessMessage($changelogMessage);
                $this->projectService->addMessageToChangelog(
                    $affiliation->getProject(),
                    $this->identity(),
                    Changelog::TYPE_PARTNER,
                    Changelog::SOURCE_COMMUNITY,
                    $changelogMessage
                );

                return $this->redirect()->toRoute(
                    'community/affiliation/details',
                    ['id' => $affiliation->getId()],
                    ['fragment' => 'market-access']
                );
            }
        }

        return new ViewModel(
            [
                'affiliation'        => $affiliation,
                'affiliationService' => $this->affiliationService,
                'projectService'     => $this->projectService,
                'form'               => $form,
            ]
        );
    }

    public function effortSpentAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $report = $this->reportService->findReportById((int)$this->params('report'));
        if (null === $report) {
            return $this->notFoundAction();
        }

        $latestVersion = $this->projectService->getLatestApprovedProjectVersion($affiliation->getProject());

        if (null === $latestVersion) {
            return $this->notFoundAction();
        }

        $totalPlannedEffort = $this->versionService
            ->findTotalEffortByAffiliationAndVersionUpToReportingPeriod(
                $affiliation,
                $latestVersion,
                $report
            );

        if (
        !$effortSpent
            = $this->reportService->findEffortSpentByReportAndAffiliation($report, $affiliation)
        ) {
            $effortSpent = new ReportEffortSpent();
            $effortSpent->setAffiliation($affiliation);
            $effortSpent->setReport($report);
        }

        /**
         * Inject the known data form the object into the data array for form population
         */
        $data = array_merge(
            [
                'effort'           => $effortSpent->getEffort(),
                'comment'          => $effortSpent->getComment(),
                'summary'          => $effortSpent->getSummary(),
                'marketAccess'     => $affiliation->getMarketAccess(),
                'mainContribution' => $affiliation->getMainContribution(),
            ],
            $this->getRequest()->getPost()->toArray()
        );

        $form = new Form\EffortSpentForm($totalPlannedEffort);
        $form->setData($data);

        if ($this->getRequest()->isPost()) {
            /**
             * Handle the cancel request
             */
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'community/affiliation/reporting',
                    [
                        'id' => $affiliation->getId(),
                    ],
                );
            }

            if ($form->isValid()) {
                $effortSpent->setEffort($data['effort']);
                $effortSpent->setComment($data['comment']);
                $effortSpent->setSummary($data['summary']);
                //Force the technical contact
                $effortSpent->setContact($affiliation->getContact());
                $this->projectService->save($effortSpent);

                //Update the marketAccess
                $affiliation->setMarketAccess($data['marketAccess']);
                $affiliation->setMainContribution($data['mainContribution']);
                $this->affiliationService->save($affiliation);

                $changelogMessage = sprintf(
                    $this->translator->translate('txt-effort-spent-of-affiliation-%s-has-successfully-been-updated'),
                    $affiliation
                );

                $this->flashMessenger()->addSuccessMessage($changelogMessage);
                $this->projectService->addMessageToChangelog(
                    $affiliation->getProject(),
                    $this->identity(),
                    Changelog::TYPE_PARTNER,
                    Changelog::SOURCE_COMMUNITY,
                    $changelogMessage
                );

                return $this->redirect()->toRoute(
                    'community/affiliation/reporting',
                    [
                        'id' => $affiliation->getId(),
                    ],
                );
            }
        }

        return new ViewModel(
            [
                'affiliation'        => $affiliation,
                'affiliationService' => $this->affiliationService,
                'projectService'     => $this->projectService,
                'reportService'      => $this->reportService,
                'report'             => $report,
                'form'               => $form,
                'totalPlannedEffort' => $totalPlannedEffort,
            ]
        );
    }

    public function costsAndEffortAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        //Prepare the formData
        /** @var Project $project */
        $project = $affiliation->getProject();

        $formData = [];
        foreach ($this->projectService->parseEditYearRange($project) as $year) {
            $costPerYear = $this->projectService->findTotalCostByAffiliationPerYear($affiliation);
            if (!array_key_exists($year, $costPerYear)) {
                $costPerYear[$year] = 0;
            }
            $formData['costPerAffiliationAndYear']
            [$affiliation->getId()]
            [$year]
                = ['cost' => $costPerYear[$year] / 1000];

            if (!$this->projectService->hasWorkPackages($project)) {
                $effortPerYear = $this->projectService->findTotalEffortByAffiliationPerYear($affiliation);
                if (!array_key_exists($year, $effortPerYear)) {
                    $effortPerYear[$year] = 0;
                }
                $formData['effortPerAffiliationAndYear']
                [$affiliation->getId()]
                [$year]
                    = ['effort' => $effortPerYear[$year]];
            }

            if ($this->projectService->hasWorkPackages($project)) {
                /*
                 * Sum over the effort, this is grouped per workpackage
                 */
                foreach ($this->workpackageService->findWorkpackageByProjectAndWhich($project) as $workpackage) {
                    $effortPerWorkpackageAndYear
                        = $this->projectService->findTotalEffortByWorkpackageAndAffiliationPerYear(
                        $workpackage,
                        $affiliation
                    );
                    if (!array_key_exists($year, $effortPerWorkpackageAndYear)) {
                        $effortPerWorkpackageAndYear[$year] = 0;
                    }
                    $formData['effortPerAffiliationAndYear']
                    [$workpackage->getId()]
                    [$affiliation->getId()]
                    [$year]
                        = ['effort' => $effortPerWorkpackageAndYear[$year]];
                }
            }
        }

        $data = array_merge(
            $formData,
            $this->getRequest()->getPost()->toArray()
        );

        $form = new Form\CostsAndEffortForm($affiliation, $this->projectService, $this->workpackageService);
        $form->setData($data);

        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'community/affiliation/costs-and-effort',
                    ['id' => $affiliation->getId()]
                );
            }

            if ($form->isValid()) {

                $formData = $form->getData();

                /*
                 * Update the cost
                 */
                foreach ($formData['costPerAffiliationAndYear'][$affiliation->getId()] as $year => $costValue) {
                    $cost = $this->projectService->findCostByAffiliationAndYear($affiliation, $year);

                    $setCostValue = (float)str_replace(',', '.', $costValue['cost']);

                    if (null !== $cost && $setCostValue === 0.0) {
                        $this->projectService->delete($cost);
                    }

                    if ($setCostValue > 0) {
                        /*
                         * Create a new if not set yet
                         */
                        if (null === $cost) {
                            $cost = new Cost();
                            $cost->setAffiliation($affiliation);
                            $dateStart = new DateTime();
                            $cost->setDateStart($dateStart->modify('first day of january ' . $year));
                            $dateEnd = new DateTime();
                            $cost->setDateEnd($dateEnd->modify('last day of december ' . $year));
                        }
                        $cost->setCosts($setCostValue * 1000);
                        $this->projectService->save($cost);
                    }
                }

                if (!$this->projectService->hasWorkPackages($project)) {
                    /*
                     * Update the cost
                     */
                    foreach ($formData['effortPerAffiliationAndYear'][$affiliation->getId()] as $year => $effortValue) {
                        $effort = $this->projectService->findEffortByAffiliationAndYear($affiliation, $year);

                        $setEffortValue = (float)str_replace(',', '.', $effortValue['effort']);

                        if (null !== $effort && $setEffortValue === 0.0) {
                            $this->projectService->delete($effort);
                        }

                        if ($setEffortValue > 0) {
                            /*
                             * Create a new if not set yet
                             */
                            if (null === $effort) {
                                $effort = new Effort();
                                $effort->setAffiliation($affiliation);
                                $dateStart = new DateTime();
                                $effort->setDateStart($dateStart->modify('first day of january ' . $year));
                                $dateEnd = new DateTime();
                                $effort->setDateEnd($dateEnd->modify('last day of december ' . $year));
                            }
                            $effort->setEffort($setEffortValue);
                            $this->projectService->save($effort);
                        }
                    }
                }

                if ($this->projectService->hasWorkPackages($project)) {
                    /*
                     * Update the effort
                     */
                    foreach ($formData['effortPerAffiliationAndYear'] as $workpackageId => $effortPerAffiliationAndYear) {
                        $workpackage = $this->workpackageService->findWorkpackageById($workpackageId);

                        if (null === $workpackage) {
                            continue;
                        }

                        foreach ($effortPerAffiliationAndYear[$affiliation->getId()] as $year => $effortValue) {
                            $effort = $this->projectService
                                ->findEffortByAffiliationAndWorkpackageAndYear(
                                    $affiliation,
                                    $workpackage,
                                    $year
                                );

                            $setEffortValue = (float)str_replace(',', '.', $effortValue['effort']);

                            if (null !== $effort && $setEffortValue === 0.0) {
                                $this->projectService->delete($effort);
                            }

                            if ($setEffortValue > 0) {
                                /*
                                 * Create a new if not set yet
                                 */
                                if (null === $effort) {
                                    $effort = new Effort();
                                    $effort->setAffiliation($affiliation);
                                    $effort->setWorkpackage($workpackage);
                                    $dateStart = new DateTime();
                                    $effort->setDateStart($dateStart->modify('first day of january ' . $year));
                                    $dateEnd = new DateTime();
                                    $effort->setDateEnd($dateEnd->modify('last day of december ' . $year));
                                }
                                $effort->setEffort($setEffortValue);
                                $this->projectService->save($effort);
                            }
                        }
                    }
                }

                $changelogMessage = sprintf(
                    $this->translator->translate(
                        'txt-cost-and-effort-of-partner-%s-in-project-%s-has-successfully-been-updated'
                    ),
                    $affiliation->parseBranchedName(),
                    $project->parseFullName()
                );

                $this->flashMessenger()->addSuccessMessage($changelogMessage);
                $this->projectService->addMessageToChangelog(
                    $affiliation->getProject(),
                    $this->identity(),
                    Changelog::TYPE_PARTNER,
                    Changelog::SOURCE_COMMUNITY,
                    $changelogMessage
                );

                return $this->redirect()->toRoute(
                    'community/affiliation/costs-and-effort',
                    ['id' => $affiliation->getId()]
                );
            }
        }

        return new ViewModel(
            [
                'affiliation'        => $affiliation,
                'project'            => $project,
                'projectService'     => $this->projectService,
                'workpackageService' => $this->workpackageService,
                'contractService'    => $this->contractService,
                'affiliationService' => $this->affiliationService,
                'yearRange'          => $this->projectService->parseYearRange($project),
                'editYearRange'      => $this->projectService->parseEditYearRange($project),
                'hasWorkPackages'    => $this->projectService->hasWorkPackages($project),
                'form'               => $form
            ]
        );
    }

    public function technicalContactAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        //Create an array for the proxies, but not on submission
        $proxyContacts = [];
        if (!$this->getRequest()->isPost()) {
            foreach ($affiliation->getProxyContact() as $contact) {
                $proxyContacts[] = $contact->getId();
            }
        }

        $data = array_merge(
            [
                'technicalContact'      => $affiliation->getContact()->getId(),
                'proxyTechnicalContact' => $proxyContacts,
            ],
            $this->getRequest()->getPost()->toArray()
        );

        $form = new Form\TechnicalContactForm($this->contactService, $affiliation, $this->identity());
        $form->setData($data);

        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'community/affiliation/details',
                    ['id' => $affiliation->getId()]
                );
            }

            if ($form->isValid()) {
                //Save the technical contact
                $technicalContact = $this->contactService->findContactById((int)$data['technicalContact']);
                if (null !== $technicalContact) {
                    $affiliation->setContact($technicalContact);
                }

                //Save the proxies
                $proxies    = [];
                $proxyNames = [];
                if (isset($data['proxyTechnicalContact'])) {
                    foreach ((array)$data['proxyTechnicalContact'] as $proxyTechnicalContact) {
                        $proxyTechnicalContact = $this->contactService->findContactById((int)$proxyTechnicalContact);
                        if (null !== $proxyTechnicalContact) {
                            $proxies[]    = $proxyTechnicalContact;
                            $proxyNames[] = $proxyTechnicalContact->parseFullName();
                        }
                    }
                }
                $affiliation->setProxyContact($proxies);

                $this->affiliationService->save($affiliation);
                $this->projectService->save($affiliation->getProject());

                if (count($proxyNames) > 0) {
                    $changelogMessage = sprintf(
                        $this->translator->translate(
                            'txt-technical-contact-and-proxy-technical-contacts-have-been-updated-project-leader-is-%s-proxy-are-%s'
                        ),
                        $affiliation->getContact()->parseFullName(),
                        implode(', ', $proxyNames)
                    );
                } else {
                    $changelogMessage = sprintf(
                        $this->translator->translate(
                            'txt-technical-contact-and-proxy-technical-contact-have-been-updated-project-leader-is-%s-and-there-are-no-proxies'
                        ),
                        $affiliation->getContact()->parseFullName()
                    );
                }

                $this->flashMessenger()->addSuccessMessage($changelogMessage);
                $this->projectService->addMessageToChangelog(
                    $affiliation->getProject(),
                    $this->identity(),
                    Changelog::TYPE_PARTNER,
                    Changelog::SOURCE_COMMUNITY,
                    $changelogMessage
                );

                return $this->redirect()->toRoute(
                    'community/affiliation/details',
                    ['id' => $affiliation->getId()]
                );
            }
        }

        return new ViewModel(['form' => $form, 'affiliation' => $affiliation]);
    }
}
