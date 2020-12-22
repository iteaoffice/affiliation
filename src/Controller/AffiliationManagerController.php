<?php

/**
 * ITEA Office all rights reserved
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Controller;

use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Description;
use Affiliation\Entity\Financial;
use Affiliation\Form\AddAssociate;
use Affiliation\Form\AdminAffiliation;
use Affiliation\Form\EditAssociate;
use Affiliation\Form\MarketAccess;
use Affiliation\Form\MissingAffiliationParentFilter;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\FormService;
use Application\Service\AssertionService;
use Contact\Service\ContactService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
use Invoice\Entity\Method;
use Invoice\Service\InvoiceService;
use Laminas\Http\Request;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Paginator\Paginator;
use Laminas\View\Model\ViewModel;
use Organisation\Entity\Name;
use Organisation\Entity\Parent\Organisation;
use Organisation\Service\OrganisationService;
use Organisation\Service\ParentService;
use Program\Service\CallService;
use Project\Acl\Assertion\Project;
use Project\Entity\Changelog;
use Project\Service\ContractService;
use Project\Service\ProjectService;
use Project\Service\ReportService;
use Project\Service\VersionService;
use Project\Service\WorkpackageService;

/**
 * Class AffiliationManagerController
 *
 * @package Affiliation\Controller
 */
final class AffiliationManagerController extends AffiliationAbstractController
{
    private AffiliationService $affiliationService;
    private TranslatorInterface $translator;
    private ProjectService $projectService;
    private VersionService $versionService;
    private ContractService $contractService;
    private ContactService $contactService;
    private OrganisationService $organisationService;
    private ReportService $reportService;
    private WorkpackageService $workpackageService;
    private InvoiceService $invoiceService;
    private ParentService $parentService;
    private CallService $callService;
    private AssertionService $assertionService;
    private FormService $formService;
    private EntityManager $entityManager;

    public function __construct(
        AffiliationService $affiliationService,
        TranslatorInterface $translator,
        ProjectService $projectService,
        VersionService $versionService,
        ContractService $contractService,
        ContactService $contactService,
        OrganisationService $organisationService,
        ReportService $reportService,
        WorkpackageService $workpackageService,
        InvoiceService $invoiceService,
        ParentService $parentService,
        CallService $callService,
        AssertionService $assertionService,
        FormService $formService,
        EntityManager $entityManager
    ) {
        $this->affiliationService  = $affiliationService;
        $this->translator          = $translator;
        $this->projectService      = $projectService;
        $this->versionService      = $versionService;
        $this->contractService     = $contractService;
        $this->contactService      = $contactService;
        $this->organisationService = $organisationService;
        $this->reportService       = $reportService;
        $this->workpackageService  = $workpackageService;
        $this->invoiceService      = $invoiceService;
        $this->parentService       = $parentService;
        $this->callService         = $callService;
        $this->assertionService    = $assertionService;
        $this->formService         = $formService;
        $this->entityManager       = $entityManager;
    }

    public function viewAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $this->assertionService->addResource($affiliation->getProject(), Project::class);

        return new ViewModel(
            [
                'affiliationService'    => $this->affiliationService,
                'affiliation'           => $affiliation,
                'contactsInAffiliation' => $this->contactService->findContactsInAffiliation($affiliation),
                'projectService'        => $this->projectService,
                'contactService'        => $this->contactService,
                'workpackageService'    => $this->workpackageService,
                'latestVersion'         => $this->projectService->getLatestNotRejectedProjectVersion(
                    $affiliation->getProject()
                ),
                'versionType'           => $this->projectService->getNextVersionType(
                    $affiliation->getProject()
                )->getVersionType(),
                'reportService'         => $this->reportService,
                'versionService'        => $this->versionService,
                'invoiceService'        => $this->invoiceService,
                'organisationService'   => $this->organisationService,
                'callService'           => $this->callService,
                'contractService'       => $this->contractService
            ]
        );
    }

    public function mergeAction()
    {
        /** @var Request $request */
        $request         = $this->getRequest();
        $mainAffiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $mainAffiliation) {
            return $this->notFoundAction();
        }

        $data = $request->getPost()->toArray();

        if (isset($data['merge'], $data['submit']) && $request->isPost()) {
            // Find the second affiliation
            $otherAffiliation  = $this->affiliationService->findAffiliationById((int)$data['merge']);
            $otherOrganisation = $otherAffiliation->getOrganisation();

            $result = $this->mergeAffiliation($mainAffiliation, $otherAffiliation);

            if ($result['success'] === true) {
                $this->flashMessenger()->setNamespace(FlashMessenger::NAMESPACE_SUCCESS)
                    ->addMessage(
                        sprintf(
                            $this->translator->translate(
                                'txt-merge-of-affiliation-%s-and-%s-in-project-%s-was-successful'
                            ),
                            $mainAffiliation->getOrganisation(),
                            $otherOrganisation,
                            $mainAffiliation->getProject()
                        )
                    );
            } else {
                $this->flashMessenger()->setNamespace(FlashMessenger::NAMESPACE_ERROR)
                    ->addMessage(
                        sprintf($this->translator->translate('txt-merge-failed:-%s'), $result['errorMessage'])
                    );
            }

            return $this->redirect()->toRoute(
                'zfcadmin/affiliation/view',
                ['id' => $mainAffiliation->getId()]
            );
        }

        return new ViewModel(
            [
                'affiliationService'  => $this->affiliationService,
                'affiliation'         => $mainAffiliation,
                'merge'               => $data['merge'] ?? null,
                'projectService'      => $this->projectService,
                'organisationService' => $this->organisationService,
            ]
        );
    }

    public function editAction()
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
        $formData['marketAccess']              = $affiliation->getMarketAccess();
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
        if (
            null !== $affiliation->getDateSelfFunded() || $affiliation->getSelfFunded() === Affiliation::SELF_FUNDED
        ) {
            if (null === $affiliation->getDateSelfFunded()) {
                $formData['dateSelfFunded'] = date('Y-m-d');
            } else {
                $formData['dateSelfFunded'] = $affiliation->getDateSelfFunded()->format('Y-m-d');
            }
        }

        // Only fill the formData of the finanicalOrganisation when this is known
        if (null !== ($financial = $affiliation->getFinancial())) {
            $formData['financialOrganisation'] = $financial->getOrganisation()->getId();
            $formData['financialBranch']       = $financial->getBranch();
            $formData['financialContact']      = $financial->getContact()->getId();
            $formData['emailCC']               = $financial->getEmailCC();
        }


        $form = new AdminAffiliation($affiliation, $this->parentService, $this->entityManager);
        $form->setData($formData);

        $form->get('contact')->injectContact($affiliation->getContact());
        $form->get('organisation')->injectOrganisation($affiliation->getOrganisation());
        if (null !== $affiliation->getFinancial()) {
            $form->get('financialOrganisation')->injectOrganisation($affiliation->getFinancial()->getOrganisation());
            $form->get('financialContact')->injectContact($affiliation->getFinancial()->getContact());
        }

        //Remove the delete when an affilation is active in a version
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
                $this->affiliationService->delete($affiliation);

                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/view',
                    ['id' => $affiliation->getId(),]
                );
            }

            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/view',
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
                    $affiliation->setSelfFunded(Affiliation::NOT_SELF_FUNDED);
                    $affiliation->setDateSelfFunded(null);
                } else {
                    $affiliation->setSelfFunded(Affiliation::SELF_FUNDED);
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
                $affiliation->setMarketAccess($formData['marketAccess']);

                $affiliation->setInvoiceMethod(null);
                if (! empty($formData['invoiceMethod'])) {
                    /** @var Method $method */
                    $method = $this->invoiceService->find(Method::class, (int)$formData['invoiceMethod']);
                    $affiliation->setInvoiceMethod($method);
                }

                $this->affiliationService->save($affiliation);

                // Only update the financial when an financial organisation is chosen
                if (! empty($formData['financialOrganisation'])) {
                    if (null === ($financial = $affiliation->getFinancial())) {
                        $financial = new Financial();
                        $financial->setAffiliation($affiliation);
                    }

                    $financial->setOrganisation(
                        $this->organisationService
                            ->findOrganisationById((int)$formData['financialOrganisation'])
                    );
                    $financial->setContact($this->contactService->findContactById((int)$formData['financialContact']));
                    $financial->setBranch($formData['financialBranch']);
                    if (! empty($formData['emailCC'])) {
                        $financial->setEmailCC($formData['emailCC']);
                    }


                    $this->affiliationService->save($financial);
                }

                $this->flashMessenger()->addSuccessMessage(
                    \sprintf(
                        $this->translator->translate('txt-affiliation-%s-has-successfully-been-updated'),
                        $affiliation
                    )
                );

                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/view',
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

    public function editAssociateAction()
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

        $form = new EditAssociate($affiliation, $this->contactService);
        $form->get('contact')->injectContact($contact);
        $form->setData($data);

        if ($request->isPost()) {
            if (! empty($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/view',
                    ['id' => $affiliation->getId()],
                    ['fragment' => 'associates']
                );
            }

            if (! empty($data['delete'])) {
                $affiliation->removeAssociate($contact);
                $this->affiliationService->save($affiliation);

                $this->flashMessenger()->addSuccessMessage(
                    sprintf(
                        $this->translator->translate('txt-associate-%s-has-successfully-been-removed'),
                        $contact->getDisplayName()
                    )
                );

                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/view',
                    ['id' => $affiliation->getId()],
                    ['fragment' => 'associates']
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
                        $this->translator->translate('txt-affiliation-%s-has-successfully-been-updated'),
                        $affiliation
                    )
                );

                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/view',
                    ['id' => $affiliation->getId()],
                    ['fragment' => 'associates']
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

    public function editDescriptionAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $description = new Description();
        if ($affiliation->hasDescription()) {
            /** @var Description $description */
            $description = $affiliation->getDescription();
        }

        $data = $this->getRequest()->getPost()->toArray();
        $form = $this->formService->prepare($description, $data);

        //This is not required in the office
        $form->getInputFilter()->get('affiliation_entity_description')->get('description')->setRequired(false);

        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/view',
                    ['id' => $affiliation->getId()]
                );
            }

            if ($form->isValid()) {

                /** @var Description $description */
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
                    'zfcadmin/affiliation/view',
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

    public function editMarketAccessAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $data = $this->getRequest()->getPost()->toArray();
        $form = new MarketAccess();
        $form->get('marketAccess')->setValue($affiliation->getMarketAccess());

        //This is not required in the office
        $form->getInputFilter()->get('marketAccess')->setRequired(false);

        $form->setData($data);


        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/view',
                    ['id' => $affiliation->getId()]
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
                    Changelog::SOURCE_OFFICE,
                    $changelogMessage
                );

                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/view',
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

    public function addAssociateAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $data = $this->getRequest()->getPost()->toArray();

        $form = new AddAssociate($affiliation, $this->contactService);
        $form->setData($data);

        if ($this->getRequest()->isPost() && $form->isValid()) {
            if (empty($form->getData()['cancel'])) {
                $affiliation->addAssociate($this->contactService->findContactById((int)$form->getData()['contact']));
                $this->affiliationService->save($affiliation);
            }

            $this->flashMessenger()->addSuccessMessage(
                sprintf(
                    $this->translator->translate('txt-affiliation-%s-has-successfully-been-updated'),
                    $affiliation
                )
            );

            return $this->redirect()->toRoute(
                'zfcadmin/affiliation/view',
                ['id' => $affiliation->getId()],
                ['fragment' => 'associates']
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

    public function missingAffiliationParentAction(): ViewModel
    {
        $page                     = $this->params()->fromRoute('page', 1);
        $filterPlugin             = $this->getAffiliationFilter();
        $missingAffiliationParent = $this->affiliationService->findMissingAffiliationParent();

        $paginator
            = new Paginator(new PaginatorAdapter(new ORMPaginator($missingAffiliationParent, false)));
        $paginator::setDefaultItemCountPerPage(($page === 'all') ? PHP_INT_MAX : 20);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(ceil($paginator->getTotalItemCount() / $paginator::getDefaultItemCountPerPage()));

        $form = new MissingAffiliationParentFilter();
        $form->setData(['filter' => $filterPlugin->getFilter()]);

        return new ViewModel(
            [
                'paginator'     => $paginator,
                'form'          => $form,
                'encodedFilter' => urlencode($filterPlugin->getHash()),
                'order'         => $filterPlugin->getOrder(),
                'direction'     => $filterPlugin->getDirection(),
            ]
        );
    }
}
