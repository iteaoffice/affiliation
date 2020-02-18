<?php

/**
 * ITEA Office all rights reserved
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Controller;

use Affiliation\Entity\Description;
use Affiliation\Entity\Financial;
use Affiliation\Form\AddAssociate;
use Affiliation\Form\Affiliation as AffiliationForm;
use Affiliation\Form\CostAndEffort;
use Affiliation\Form\EffortSpent;
use Affiliation\Form\Financial as FinancialForm;
use Affiliation\Form\ManageTechnicalContact;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\FormService;
use Contact\Entity\Address;
use Contact\Entity\AddressType;
use Contact\Entity\Contact;
use Contact\Service\ContactService;
use DateTime;
use Doctrine\ORM\EntityManager;
use DragonBe\Vies\Vies;
use General\Entity\Country;
use General\Service\CountryService;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\View\Model\ViewModel;
use Organisation\Entity\Organisation;
use Organisation\Entity\Type;
use Organisation\Service\OrganisationService;
use Project\Entity\Changelog;
use Project\Entity\Cost\Cost;
use Project\Entity\Effort\Effort;
use Project\Entity\Report\EffortSpent as ReportEffortSpent;
use Project\Service\ContractService;
use Project\Service\ProjectService;
use Project\Service\ReportService;
use Project\Service\VersionService;
use Project\Service\WorkpackageService;
use Throwable;

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

        $formData                = $this->getRequest()->getPost()->toArray();
        $formData['affiliation'] = sprintf(
            '%s|%s',
            $affiliation->getOrganisation()->getId(),
            $affiliation->getBranch()
        );

        $formData['technical']                 = $affiliation->getContact()->getId();
        $formData['communicationContactName']  = $affiliation->getCommunicationContactName();
        $formData['communicationContactEmail'] = $affiliation->getCommunicationContactEmail();
        $formData['valueChain']                = $affiliation->getValueChain();
        $formData['marketAccess']              = $affiliation->getMarketAccess();
        $formData['mainContribution']          = $affiliation->getMainContribution();
        $formData['strategicImportance']       = $affiliation->getStrategicImportance();
        $formData['selfFunded']                = $affiliation->getSelfFunded();

        /*
         * Check if the organisation has a financial contact
         */
        if (null !== $affiliation->getFinancial()) {
            $formData['financial'] = $affiliation->getFinancial()->getContact()->getId();
        }
        $form = new AffiliationForm($affiliation, $this->affiliationService);
        $form->setData($formData);

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
                    'community/affiliation/affiliation',
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
                    'community/affiliation/affiliation',
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
                $this->affiliationService->save($affiliation);
                $affiliation->setValueChain($formData['valueChain']);
                $affiliation->setMainContribution($formData['mainContribution']);
                $affiliation->setMarketAccess($formData['marketAccess']);
                $affiliation->setStrategicImportance($formData['strategicImportance']);
                $affiliation->setSelfFunded($formData['selfFunded']);
                /*
                 * Handle the financial organisation
                 */
                if (null === ($financial = $affiliation->getFinancial())) {
                    $financial = new Financial();
                }
                $financial->setOrganisation($organisation);
                $financial->setAffiliation($affiliation);
                $financial->setBranch($branch);
                $financial->setContact($this->contactService->findContactById((int)$formData['financial']));
                $this->affiliationService->save($financial);

                //Update the mode of the project
                $project = $affiliation->getProject();
                $project->setMode($this->projectService->getNextMode($project)->getMode());
                //$this->projectService->save($project);

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
                    'community/affiliation/affiliation',
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

        $formData              = [
            'preferredDelivery' => \Organisation\Entity\Financial::EMAIL_DELIVERY,
            'omitContact'       => \Organisation\Entity\Financial::OMIT_CONTACT,
        ];
        $branch                = null;
        $financialAddress      = null;
        $organisationFinancial = null;

        if (null !== $affiliation->getFinancial()) {
            $organisationFinancial = $affiliation->getFinancial()->getOrganisation()->getFinancial();
            $branch                = $affiliation->getFinancial()->getBranch();

            $formData['attention'] = $affiliation->getFinancial()->getContact()->getDisplayName();

            /** @var ContactService $contactService */
            $formData['contact'] = $affiliation->getFinancial()->getContact()->getId();

            $financialAddress = $this->contactService->getFinancialAddress(
                $affiliation->getFinancial()->getContact()
            );

            if (null !== $financialAddress) {
                $formData['address'] = $financialAddress->getAddress();
                $formData['zipCode'] = $financialAddress->getZipCode();
                $formData['city']    = $financialAddress->getCity();
                $formData['country'] = $financialAddress->getCountry()->getId();
            }

            $formData['organisation']      = $this->organisationService
                ->parseOrganisationWithBranch($branch, $affiliation->getFinancial()->getOrganisation());
            $formData['registeredCountry'] = $affiliation->getFinancial()->getOrganisation()->getCountry()->getId();
        }


        if (null === $affiliation->getFinancial()) {
            $formData['organisation']      = $this->organisationService
                ->parseOrganisationWithBranch($branch, $affiliation->getOrganisation());
            $formData['registeredCountry'] = $affiliation->getOrganisation()->getCountry()->getId();
        }

        if (null !== $organisationFinancial) {
            $formData['preferredDelivery'] = $organisationFinancial->getEmail();
            $formData['vat']               = $organisationFinancial->getVat();
            $formData['omitContact']       = $organisationFinancial->getOmitContact();
        }


        $form = new FinancialForm($affiliation, $this->entityManager);
        $data = array_merge($formData, $this->getRequest()->getPost()->toArray());
        $form->setData($data);

        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'community/affiliation/affiliation',
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

                //We need to find the organisation, first by tring the VAT, then via the name and country and then just create it
                $organisation = null;

                //Check if an organisation with the given VAT is already found
                $organisationFinancial = $this->organisationService
                    ->findFinancialOrganisationWithVAT($formData['vat']);


                //If the organisation is found, it has by default an organisation
                if (null !== $organisationFinancial) {
                    $organisation = $organisationFinancial->getOrganisation();
                }

                /** @var Country $country */
                $country = $this->countryService->find(Country::class, (int)$formData['country']);

                //try to find the organisation based on te country and name
                if (null === $organisation) {
                    $organisation = $this->organisationService
                        ->findOrganisationByNameCountry(
                            trim($formData['organisation']),
                            $country
                        );
                }

                /**
                 * If the organisation is still not found, create it
                 */
                if (null === $organisation) {
                    $organisation = new Organisation();
                    $organisation->setOrganisation($formData['organisation']);
                    $organisation->setCountry($country);
                    /**
                     * @var $organisationType Type
                     */
                    $organisationType = $this->organisationService->find(Type::class, Type::TYPE_UNKNOWN);
                    $organisation->setType($organisationType);
                }

                /**
                 *
                 * Update the affiliationFinancial
                 */
                $affiliationFinancial = $affiliation->getFinancial();
                if (null === $affiliationFinancial) {
                    $affiliationFinancial = new Financial();
                    $affiliationFinancial->setAffiliation($affiliation);
                }
                $affiliationFinancial->setContact(
                    $this->contactService->findContactById((int)$formData['contact'])
                );
                $affiliationFinancial->setOrganisation($organisation);

                //Update the branch is complicated so we create a dedicated function for it in the
                //OrganisationService
                $affiliationFinancial->setBranch(
                    OrganisationService::determineBranch($formData['organisation'], $organisation->getOrganisation())
                );


                $this->affiliationService->save($affiliationFinancial);


                if (null !== $affiliation->getFinancial()) {
                    $organisationFinancial = $affiliation->getFinancial()->getOrganisation()->getFinancial();
                } else {
                    $organisationFinancial = $affiliation->getOrganisation()->getFinancial();
                }

                if (null === $organisationFinancial) {
                    $organisationFinancial = new \Organisation\Entity\Financial();
                }

                $organisationFinancial->setOrganisation($organisation);
                /**
                 * The presence of a VAT number triggers the creation of a financial organisation
                 */
                if (!empty($formData['vat'])) {
                    $organisationFinancial->setVat($formData['vat']);

                    //Do an in-situ vat check
                    $vies = new Vies();

                    try {
                        $result = $vies->validateVat(
                            $organisationFinancial->getOrganisation()->getCountry()->getCd(),
                            trim(
                                str_replace(
                                    $organisationFinancial->getOrganisation()->getCountry()->getCd(),
                                    '',
                                    $formData['vat']
                                )
                            )
                        );

                        if ($result->isValid()) {
                            $this->flashMessenger()->addSuccessMessage(
                                sprintf($this->translator->translate('txt-vat-number-is-valid'), $affiliation)
                            );


                            //Update the financial
                            $organisationFinancial->setVatStatus(\Organisation\Entity\Financial::VAT_STATUS_VALID);
                            $organisationFinancial->setDateVat(new DateTime());
                        } else {
                            //Update the financial
                            $organisationFinancial->setVatStatus(\Organisation\Entity\Financial::VAT_STATUS_INVALID);
                            $organisationFinancial->setDateVat(new DateTime());
                            $this->flashMessenger()->setNamespace('error')
                                ->addMessage(
                                    sprintf($this->translator->translate('txt-vat-number-is-invalid'), $affiliation)
                                );
                        }
                    } catch (Throwable $e) {
                        $this->flashMessenger()->setNamespace('danger')
                            ->addMessage(
                                sprintf(
                                    $this->translator->translate('txt-vat-information-could-not-be-verified'),
                                    $affiliation
                                )
                            );
                    }
                } else {
                    $organisationFinancial->setVat(null);
                }

                $organisationFinancial->setEmail($formData['preferredDelivery']);
                $organisationFinancial->setOmitContact($formData['omitContact']);
                $this->organisationService->save($organisationFinancial);


                /*
                 * save the financial address
                 */
                $financialAddress = $this->contactService->getFinancialAddress(
                    $affiliationFinancial->getContact()
                );

                if (null === $financialAddress) {
                    $financialAddress = new Address();
                    $financialAddress->setContact($affiliation->getFinancial()->getContact());
                    /**
                     * @var $addressType AddressType
                     */
                    $addressType = $this->contactService
                        ->find(AddressType::class, AddressType::ADDRESS_TYPE_FINANCIAL);
                    $financialAddress->setType($addressType);
                }
                $financialAddress->setAddress($formData['address']);
                $financialAddress->setZipCode($formData['zipCode']);
                $financialAddress->setCity($formData['city']);
                /**
                 * @var Country $country
                 */
                $country = $this->countryService->find(Country::class, (int)$formData['country']);
                $financialAddress->setCountry($country);
                $this->contactService->save($financialAddress);

                $changelogMessage = sprintf(
                    $this->translator->translate(
                        'txt-affiliation-financial-information-%s-has-successfully-been-updated'
                    ),
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
                    'community/affiliation/affiliation',
                    [
                        'id' => $affiliation->getId(),
                    ],
                    [
                        'fragment' => 'invoicing',
                    ]
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
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'community/affiliation/affiliation',
                    ['id' => $affiliation->getId()],
                    ['fragment' => 'contact']
                );
            }

            if (empty($data['contact']) && empty($data['email'])) {
                $this->flashMessenger()->setNamespace('info')
                    ->addMessage(
                        sprintf(
                            $this->translator->translate('txt-no-contact-has-been-added-affiliation-%s'),
                            $affiliation
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
                'community/affiliation/affiliation',
                ['id' => $affiliation->getId()],
                ['fragment' => 'contact']
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

    public function manageAssociateAction()
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
                    'community/affiliation/affiliation',
                    ['id' => $affiliation->getId()],
                    ['fragment' => 'contact']
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
                'community/affiliation/affiliation',
                ['id' => $affiliation->getId()],
                ['fragment' => 'contact']
            );
        }

        return new ViewModel(
            [
                'affiliation'           => $affiliation,
                'contactsInAffiliation' => $this->contactService->findContactsInAffiliation($affiliation),
            ]
        );
    }

    public function descriptionAction()
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
        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'community/affiliation/affiliation',
                    ['id' => $affiliation->getId()],
                    ['fragment' => 'description']
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
                    Changelog::SOURCE_COMMUNITY,
                    $changelogMessage
                );

                return $this->redirect()->toRoute(
                    'community/affiliation/affiliation',
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

    public function updateEffortSpentAction()
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

        $form = new EffortSpent($totalPlannedEffort);
        $form->setData($data);

        if ($this->getRequest()->isPost()) {
            /**
             * Handle the cancel request
             */
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'community/affiliation/affiliation',
                    [
                        'id' => $affiliation->getId(),
                    ],
                    ['fragment' => 'report']
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
                    'community/affiliation/affiliation',
                    [
                        'id' => $affiliation->getId(),
                    ],
                    ['fragment' => 'report']
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

    public function costAndEffortAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        //Prepare the formData
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

        $data = array_merge(
            $formData,
            $this->getRequest()->getPost()->toArray()
        );

        $form = new CostAndEffort($affiliation, $this->projectService, $this->workpackageService);
        $form->setData($data);

        if ($this->getRequest()->isPost() && $form->isValid()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'community/affiliation/affiliation',
                    ['id' => $affiliation->getId()]
                );
            }

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
            //Update the mode of the project when the saved is pressed
            $project->setMode($this->projectService->getNextMode($project)->getMode());
            //$this->projectService->save($project);

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
                'community/affiliation/affiliation',
                ['id' => $affiliation->getId()]
            );
        }

        return new ViewModel(
            [
                'affiliation'        => $affiliation,
                'project'            => $project,
                'projectService'     => $this->projectService,
                'workpackageService' => $this->workpackageService,
                'contractService'    => $this->contractService,
                'affiliationService' => $this->affiliationService,
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

        $form = new ManageTechnicalContact($this->contactService, $affiliation, $this->identity());
        $form->setData($data);

        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'community/affiliation/affiliation',
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
                    'community/affiliation/affiliation',
                    ['id' => $affiliation->getId()]
                );
            }
        }

        return new ViewModel(['form' => $form, 'affiliation' => $affiliation]);
    }
}
