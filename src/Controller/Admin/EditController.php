<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

namespace Affiliation\Controller\Admin;

use Affiliation\Controller\AffiliationAbstractController;
use Affiliation\Entity;
use Affiliation\Form;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\FormService;
use Contact\Entity\Contact;
use Contact\Service\ContactService;
use Doctrine\ORM\EntityManager;
use Invoice\Entity\Method;
use Invoice\Service\InvoiceService;
use Laminas\Http\Request;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\View\Model\ViewModel;
use Organisation\Entity\Name;
use Organisation\Entity\Parent\Organisation;
use Organisation\Service\OrganisationService;
use Organisation\Service\ParentService;
use Project\Entity\Changelog;
use Project\Service\ProjectService;
use Project\Service\ReportService;
use Project\Service\VersionService;

/**
 * Class EditController
 * @package Affiliation\Controller\Admin
 */
final class EditController extends AffiliationAbstractController
{
    private AffiliationService $affiliationService;
    private TranslatorInterface $translator;
    private ProjectService $projectService;
    private VersionService $versionService;
    private ReportService $reportService;
    private ContactService $contactService;
    private OrganisationService $organisationService;
    private InvoiceService $invoiceService;
    private ParentService $parentService;
    private FormService $formService;
    private EntityManager $entityManager;

    public function __construct(AffiliationService $affiliationService, TranslatorInterface $translator, ProjectService $projectService, VersionService $versionService, ReportService $reportService, ContactService $contactService, OrganisationService $organisationService, InvoiceService $invoiceService, ParentService $parentService, FormService $formService, EntityManager $entityManager)
    {
        $this->affiliationService  = $affiliationService;
        $this->translator          = $translator;
        $this->projectService      = $projectService;
        $this->versionService      = $versionService;
        $this->reportService       = $reportService;
        $this->contactService      = $contactService;
        $this->organisationService = $organisationService;
        $this->invoiceService      = $invoiceService;
        $this->parentService       = $parentService;
        $this->formService         = $formService;
        $this->entityManager       = $entityManager;
    }

    public function affiliationAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));
        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $data = $this->getRequest()->getPost()->toArray();

        $formData                              = [];
        $formData['affiliation']               = sprintf(
            '%s|%s',
            $affiliation->getOrganisation()->getId(),
            $affiliation->getBranch()
        );
        $formData['contact']                   = $affiliation->getContact()->getId();
        $formData['communicationContactName']  = $affiliation->getCommunicationContactName();
        $formData['communicationContactEmail'] = $affiliation->getCommunicationContactEmail();
        $formData['branch']                    = $affiliation->getBranch();
        $formData['valueChain']                = $affiliation->getValueChain();
        if ($this->projectService->hasTasksAndAddedValue($affiliation->getProject())) {
            $formData['tasksAndAddedValue'] = $affiliation->getTasksAndAddedValue();
        }
        $formData['mainContribution'] = $affiliation->getMainContribution();
        $formData['invoiceMethod']    = null === $affiliation->getInvoiceMethod() ? null
            : $affiliation->getInvoiceMethod()->getId();

        // Try to populate the form based on the organisation known already
        if (null === $affiliation->getParentOrganisation()) {
            $organisation = $affiliation->getOrganisation();
            if (null !== $organisation->getParent()) {
                $formData['parent'] = $organisation->getParent()->getId();
            }
            if (null !== $organisation->getParentOrganisation()) {
                $formData['parentOrganisation']     = $organisation->getParentOrganisation()->getId();
                $formData['parentOrganisationLike'] = $organisation->getParentOrganisation()->getId();
            }
        } else {
            $formData['parent']                 = $affiliation->getParentOrganisation()->getParent()->getId();
            $formData['parentOrganisation']     = $affiliation->getParentOrganisation()->getId();
            $formData['parentOrganisationLike'] = $affiliation->getParentOrganisation()->getId();
        }

        if (null !== $affiliation->getDateEnd()) {
            $formData['dateEnd'] = $affiliation->getDateEnd()->format('Y-m-d');
        }
        if ($affiliation->isSelfFunded()) {
            if (null === $affiliation->getDateSelfFunded()) {
                $formData['dateSelfFunded'] = date('Y-m-d');
            } else {
                $formData['dateSelfFunded'] = $affiliation->getDateSelfFunded()->format('Y-m-d');
            }
        }

        $form = new Form\Admin\AffiliationForm($affiliation, $this->parentService, $this->entityManager);
        $form->setData($formData);

        $form->get('contact')->injectContact($affiliation->getContact());
        $form->get('organisation')->injectOrganisation($affiliation->getOrganisation());

        //Remove the delete when an affiliation is active in a version
        if ($this->affiliationService->isActiveInVersion($affiliation)) {
            $form->remove('delete');
        }


        if ($this->getRequest()->isPost() && $form->setData($data)) {
            if (isset($data['delete']) && $this->affiliationService->isActiveInVersion($affiliation)) {
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
                    Changelog::SOURCE_OFFICE,
                    $changelogMessage
                );

                return $this->redirect()->toRoute(
                    'zfcadmin/project/project/affiliation',
                    ['id' => $affiliation->getProject()->getId(),]
                );
            }

            if (isset($data['delete']) && ! $this->affiliationService->isActiveInVersion($affiliation)) {
                $changelogMessage = sprintf(
                    $this->translator->translate('txt-affiliation-%s-has-successfully-been-deleted'),
                    $affiliation->parseBranchedName()
                );

                $this->flashMessenger()->addSuccessMessage($changelogMessage);
                $this->projectService->addMessageToChangelog(
                    $affiliation->getProject(),
                    $this->identity(),
                    Changelog::TYPE_PARTNER,
                    Changelog::SOURCE_OFFICE,
                    $changelogMessage
                );

                $this->affiliationService->delete($affiliation);

                return $this->redirect()->toRoute(
                    'zfcadmin/project/project/affiliation',
                    ['id' => $affiliation->getProject()->getId(),]
                );
            }

            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/details',
                    ['id' => $affiliation->getId(),]
                );
            }

            if ($form->isValid()) {
                $formData = $form->getData();

                //Find the selected organisation
                $organisation = $this->organisationService
                    ->findOrganisationById((int)$formData['organisation']);
                $contact      = $this->contactService->findContactById((int)$formData['contact']);

                switch (true) {
                    case ! empty($formData['parentOrganisationLike']):
                        /** @var Organisation $parentOrganisation */
                        $parentOrganisation = $this->parentService->find(
                            Organisation::class,
                            (int)$formData['parentOrganisationLike']
                        );
                        $affiliation->setParentOrganisation($parentOrganisation);
                        $affiliation->setOrganisation($parentOrganisation->getOrganisation());
                        break;
                    case ! empty($formData['parentOrganisation']):
                        /** @var Organisation $parentOrganisation */
                        $parentOrganisation = $this->parentService->find(
                            Organisation::class,
                            (int)$formData['parentOrganisation']
                        );
                        $affiliation->setParentOrganisation($parentOrganisation);
                        $affiliation->setOrganisation($parentOrganisation->getOrganisation());
                        break;
                    case ! empty($formData['parent']):
                        // When a parent is selected, use that to find the $parent
                        $parent             = $this->parentService->findParentById($formData['parent']);
                        $parentOrganisation = $this->parentService->findParentOrganisationInParentByOrganisation(
                            $parent,
                            $organisation
                        );

                        if (null === $parentOrganisation) {
                            $parentOrganisation = new Organisation();
                            $parentOrganisation->setOrganisation($organisation);
                            $parentOrganisation->setParent($parent);
                            $parentOrganisation->setContact(
                                $this->contactService
                                    ->findContactById((int)$formData['contact'])
                            );
                            $this->parentService->save($parentOrganisation);
                        }
                        $affiliation->setParentOrganisation($parentOrganisation);
                        $affiliation->setOrganisation($organisation);
                        break;
                    case $formData['createParentFromOrganisation'] === '1':
                        // Find first the organisation
                        $organisation       = $this->organisationService
                            ->findOrganisationById((int)$formData['organisation']);
                        $parentOrganisation = $this->parentService
                            ->createParentAndParentOrganisationFromOrganisation(
                                $organisation,
                                $affiliation->getContact()
                            );

                        $affiliation->setParentOrganisation($parentOrganisation);
                        $affiliation->setOrganisation($organisation);
                        break;
                    default:
                        $parentOrganisation = $affiliation->getParentOrganisation();
                        $affiliation->setOrganisation($organisation);
                        break;
                }

                // The partner has been updated now, so we need to store the name of the organiation and the project
                if (
                    null !== $parentOrganisation
                    && null === $this->organisationService
                        ->findOrganisationNameByNameAndProject(
                            $parentOrganisation->getOrganisation(),
                            $organisation->getOrganisation(),
                            $affiliation->getProject()
                        )
                ) {
                    $name = new Name();
                    $name->setOrganisation($parentOrganisation->getOrganisation());
                    $name->setName($organisation->getOrganisation());
                    $name->setProject($affiliation->getProject());
                    $this->organisationService->save($name);
                }

                // Update the affiliation based on the form information
                $affiliation->setContact($contact);

                $affiliation->setCommunicationContactName($formData['communicationContactName']);
                $affiliation->setCommunicationContactEmail($formData['communicationContactEmail']);

                $affiliation->setBranch($formData['branch']);
                if (empty($formData['dateSelfFunded'])) {
                    $affiliation->setSelfFunded(Entity\Affiliation::NOT_SELF_FUNDED);
                    $affiliation->setDateSelfFunded(null);
                } else {
                    $affiliation->setSelfFunded(Entity\Affiliation::SELF_FUNDED);
                    $affiliation->setDateSelfFunded(\DateTime::createFromFormat('Y-m-d', $formData['dateSelfFunded']));
                }
                if (empty($formData['dateEnd'])) {
                    $affiliation->setDateEnd(null);
                } else {
                    $affiliation->setDateEnd(\DateTime::createFromFormat('Y-m-d', $formData['dateEnd']));
                }

                if ($this->projectService->hasTasksAndAddedValue($affiliation->getProject())) {
                    $affiliation->setTasksAndAddedValue($formData['tasksAndAddedValue']);
                }

                $affiliation->setValueChain($formData['valueChain']);
                $affiliation->setMainContribution($formData['mainContribution']);

                $affiliation->setInvoiceMethod(null);
                if (! empty($formData['invoiceMethod'])) {
                    /** @var Method $method */
                    $method = $this->invoiceService->find(Method::class, (int)$formData['invoiceMethod']);
                    $affiliation->setInvoiceMethod($method);
                }

                $this->affiliationService->save($affiliation);

                $this->flashMessenger()->addSuccessMessage(
                    \sprintf(
                        $this->translator->translate('txt-affiliation-%s-has-successfully-been-updated'),
                        $affiliation
                    )
                );

                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/details',
                    ['id' => $affiliation->getId()]
                );
            }
        }

        return new ViewModel(
            [
                'affiliation'    => $affiliation,
                'projectService' => $this->projectService,
                'form'           => $form,
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

        //This is not required in the office
        $form->getInputFilter()->get('affiliation_entity_description')->get('description')->setRequired(false);

        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/details',
                    ['id' => $affiliation->getId()]
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
                    Changelog::SOURCE_OFFICE,
                    $changelogMessage
                );

                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/description',
                    ['id' => $affiliation->getId()]
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

        //This is not required in the office
        $form->getInputFilter()->get('marketAccess')->setRequired(false);

        $form->setData($data);


        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/market-access',
                    ['id' => $affiliation->getId()]
                );
            }

            if ($form->isValid()) {
                $affiliation->setMarketAccess($form->getData()['marketAccess']);
                $this->affiliationService->save($affiliation);

                $changelogMessage = sprintf(
                    $this->translator->translate('txt-market-access-of-affiliation-%s-has-successfully-been-updated'),
                    $affiliation
                );

                $this->flashMessenger()->addSuccessMessage($changelogMessage);
                $this->projectService->addMessageToChangelog(
                    $affiliation->getProject(),
                    $this->identity(),
                    Changelog::TYPE_PARTNER,
                    Changelog::SOURCE_OFFICE,
                    $changelogMessage
                );

                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/market-access',
                    ['id' => $affiliation->getId()]
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
                Changelog::SOURCE_OFFICE,
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

        $form = new Form\Admin\AddAssociateForm();
        $form->setData($data);

        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/contacts',
                    ['id' => $affiliation->getId()]
                );
            }

            if ($form->isValid()) {

                /** @var Contact $contact */
                $contact = $this->contactService->findContactById((int)$form->getData()['contact']);

                $affiliation->addAssociate($contact);
                $this->affiliationService->save($affiliation);

                $this->flashMessenger()->addSuccessMessage(
                    sprintf(
                        $this->translator->translate('txt-contact-%s-in-affiliation-%s-has-been-created-successfully'),
                        $contact->parseFullName(),
                        $affiliation
                    )
                );

                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/contacts',
                    ['id' => $affiliation->getId()]
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

    public function associateAction()
    {
        /** @var Request $request */
        $request     = $this->getRequest();
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $contact = $this->contactService->findContactById((int)$this->params('contact'));
        if (null === $contact) {
            return $this->notFoundAction();
        }

        $data = array_merge(
            ['affiliation' => $affiliation->getId(), 'contact' => $contact->getId()],
            $request->getPost()->toArray()
        );

        $form = new Form\AssociateForm($affiliation, $this->contactService);
        $form->get('contact')->injectContact($contact);
        $form->setData($data);

        if ($request->isPost()) {
            if (! empty($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/contacts',
                    ['id' => $affiliation->getId()],
                );
            }

            if (! empty($data['delete'])) {
                $affiliation->removeAssociate($contact);
                $this->affiliationService->save($affiliation);

                $this->flashMessenger()->addSuccessMessage(
                    sprintf(
                        $this->translator->translate('txt-contact-%s-in-affiliation-%s-has-been-deleted-successfully'),
                        $contact->parseFullName(),
                        $affiliation
                    )
                );

                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/contacts',
                    ['id' => $affiliation->getId()]
                );
            }


            if ($form->isValid()) {
                $formData = $form->getData();

                $affiliation->removeAssociate($contact);
                $this->affiliationService->save($affiliation);

                //Define the new affiliation
                $affiliation = $this->affiliationService->findAffiliationById((int)$formData['affiliation']);
                $contact     = $this->contactService->findContactById((int)$formData['contact']);
                $affiliation->addAssociate($contact);

                $this->affiliationService->save($affiliation);

                $this->flashMessenger()->addSuccessMessage(
                    sprintf(
                        $this->translator->translate('txt-contact-%s-in-affiliation-%s-has-been-updated-successfully'),
                        $contact->parseFullName(),
                        $affiliation
                    )
                );

                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/contacts',
                    ['id' => $affiliation->getId()]
                );
            }
        }

        return new ViewModel(
            [
                'affiliation'    => $affiliation,
                'projectService' => $this->projectService,
                'contact'        => $contact,
                'form'           => $form,
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
            ! $effortSpent
            = $this->reportService->findEffortSpentByReportAndAffiliation($report, $affiliation)
        ) {
            $effortSpent = new \Project\Entity\Report\EffortSpent();
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
                    'zfcadmin/affiliation/reporting',
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

    public function technicalContactAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        //Create an array for the proxies, but not on submission
        $proxyContacts = [];
        if (! $this->getRequest()->isPost()) {
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
                    'zfcadmin/affiliation/details',
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
                    Changelog::SOURCE_OFFICE,
                    $changelogMessage
                );

                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/details',
                    ['id' => $affiliation->getId()]
                );
            }
        }

        return new ViewModel(['form' => $form, 'affiliation' => $affiliation]);
    }

    public function financialAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $affiliationFinancial = $affiliation->getFinancial() ?? (new Entity\Financial())->setAffiliation($affiliation);
        $data = $this->getRequest()->getPost()->toArray();
        $form = $this->formService->prepare($affiliationFinancial, $data);

        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/financial',
                    [
                        'id' => $affiliation->getId(),
                    ],
                );
            }

            if (isset($data['delete']) && ! $affiliationFinancial->isEmpty()) {
                $this->affiliationService->delete($affiliationFinancial);

                $changelogMessage = sprintf(
                    $this->translator->translate(
                        'txt-affiliation-financial-information-of-%s-has-been-deleted-successfully'
                    ),
                    $affiliation->parseBranchedName()
                );

                $this->projectService->addMessageToChangelog(
                    $affiliation->getProject(),
                    $this->identity(),
                    Changelog::TYPE_PARTNER,
                    Changelog::SOURCE_OFFICE,
                    $changelogMessage
                );
            }


            if ($form->isValid()) {
                /** @var Entity\Financial $affiliationFinancial */
                $affiliationFinancial = $form->getData();
                $this->affiliationService->save($affiliationFinancial);

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
                    Changelog::SOURCE_OFFICE,
                    $changelogMessage
                );

                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/financial',
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
}
