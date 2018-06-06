<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
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
use Contact\Entity\Address;
use Contact\Entity\AddressType;
use Contact\Entity\Contact;
use Contact\Service\ContactService;
use DragonBe\Vies\Vies;
use General\Entity\Country;
use Organisation\Entity\Organisation;
use Organisation\Entity\Type;
use Organisation\Service\OrganisationService;
use Project\Entity\Changelog;
use Project\Entity\Cost\Cost;
use Project\Entity\Effort\Effort;
use Project\Entity\Report\EffortSpent as ReportEffortSpent;
use Project\Service\ProjectService;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

/**
 * @category    Affiliation
 */
class EditController extends AffiliationAbstractController
{
    /**
     * @return \Zend\Http\Response|ViewModel
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function affiliationAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int) $this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $formData = $this->getRequest()->getPost()->toArray();
        $formData['affiliation'] = sprintf(
            "%s|%s",
            $affiliation->getOrganisation()->getId(),
            $affiliation->getBranch()
        );
        $formData['technical'] = $affiliation->getContact()->getId();
        $formData['valueChain'] = $affiliation->getValueChain();
        $formData['marketAccess'] = $affiliation->getMarketAccess();
        $formData['mainContribution'] = $affiliation->getMainContribution();
        $formData['strategicImportance'] = $affiliation->getStrategicImportance();
        $formData['selfFunded'] = $affiliation->getSelfFunded();

        /*
         * Check if the organisation has a financial contact
         */
        if (null !== $affiliation->getFinancial()) {
            $formData['financial'] = $affiliation->getFinancial()->getContact()->getId();
        }
        $form = new AffiliationForm($affiliation, $this->affiliationService);
        $form->setData($formData);

        //Remove the de-activate-button when partner is not active
        if (!$this->affiliationService->isActive($affiliation)) {
            $form->remove('deactivate');
        }

        //Remove the re-activate-button when partner is active
        if ($this->affiliationService->isActive($affiliation)) {
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
                $this->projectService
                    ->updateCountryRationaleByAffiliation($affiliation, ProjectService::AFFILIATION_DEACTIVATE);

                $changelogMessage = sprintf(
                    $this->translate("txt-affiliation-%s-has-successfully-been-deactivated"),
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
                $this->projectService
                    ->updateCountryRationaleByAffiliation($affiliation, ProjectService::AFFILIATION_REACTIVATE);

                $changelogMessage = sprintf(
                    $this->translate("txt-affiliation-%s-has-successfully-been-reactivated"),
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
                list($organisationId, $branch) = explode('|', $formData['affiliation']);
                $organisation = $this->getOrganisationService()->findOrganisationById($organisationId);
                $affiliation->setOrganisation($organisation);
                $affiliation->setContact($this->getContactService()->findContactById($formData['technical']));
                $affiliation->setBranch($branch);
                $this->affiliationService->updateEntity($affiliation);
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
                $financial->setContact($this->getContactService()->findContactById($formData['financial']));
                $this->affiliationService->updateEntity($financial);

                //Update the mode of the project
                $project = $affiliation->getProject();
                $project->setMode($this->projectService->getNextMode($project)->mode);
                $this->projectService->updateEntity($project);

                $changelogMessage = sprintf(
                    $this->translate("txt-affiliation-%s-has-successfully-been-updated"),
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

    /**
     * @return \Zend\Http\Response|ViewModel
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function financialAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int) $this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $formData = [
            'preferredDelivery' => \Organisation\Entity\Financial::EMAIL_DELIVERY,
            'omitContact'       => \Organisation\Entity\Financial::OMIT_CONTACT,
        ];
        $branch = null;
        $financialAddress = null;
        $organisationFinancial = null;

        if (null !== $affiliation->getFinancial()) {
            $organisationFinancial = $affiliation->getFinancial()->getOrganisation()->getFinancial();
            $branch = $affiliation->getFinancial()->getBranch();

            $formData['attention'] = $affiliation->getFinancial()->getContact()->getDisplayName();

            /** @var ContactService $contactService */
            $formData['contact'] = $affiliation->getFinancial()->getContact()->getId();

            $financialAddress = $this->getContactService()->getFinancialAddress(
                $affiliation->getFinancial()->getContact()
            );

            if (null !== $financialAddress) {
                $formData['address'] = $financialAddress->getAddress();
                $formData['zipCode'] = $financialAddress->getZipCode();
                $formData['city'] = $financialAddress->getCity();
                $formData['country'] = $financialAddress->getCountry()->getId();
            }

            $formData['organisation'] = $this->getOrganisationService()
                ->parseOrganisationWithBranch($branch, $affiliation->getFinancial()->getOrganisation());
            $formData['registeredCountry'] = $affiliation->getFinancial()->getOrganisation()->getCountry()->getId();
        }


        if (null === $affiliation->getFinancial()) {
            $formData['organisation'] = $this->getOrganisationService()
                ->parseOrganisationWithBranch($branch, $affiliation->getOrganisation());
            $formData['registeredCountry'] = $affiliation->getOrganisation()->getCountry()->getId();
        }

        if (null !== $organisationFinancial) {
            $formData['preferredDelivery'] = $organisationFinancial->getEmail();
            $formData['vat'] = $organisationFinancial->getVat();
            $formData['omitContact'] = $organisationFinancial->getOmitContact();
        }


        $form = new FinancialForm($affiliation, $this->getEntityManager());

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
                $organisationFinancial = $this->getOrganisationService()
                    ->findFinancialOrganisationWithVAT($formData['vat']);


                //If the organisation is found, it has by default an organisation
                if (null !== $organisationFinancial) {
                    $organisation = $organisationFinancial->getOrganisation();
                }

                /** @var Country $country */
                $country = $this->generalService->find(Country::class, (int)$formData['country']);

                //try to find the organisation based on te country and name
                if (null === $organisation) {
                    $organisation = $this->getOrganisationService()
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
                    $organisationType = $this->getOrganisationService()->getEntityManager()
                        ->getReference(Type::class, Type::TYPE_UNKNOWN);
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
                $affiliationFinancial->setContact($this->getContactService()->findContactById($formData['contact']));
                $affiliationFinancial->setOrganisation($organisation);

                //Update the branch is complicated so we create a dedicated function for it in the
                //OrganisationService
                $affiliationFinancial->setBranch(
                    OrganisationService::determineBranch($formData['organisation'], $organisation->getOrganisation())
                );


                $this->affiliationService->updateEntity($affiliationFinancial);


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
                            $this->flashMessenger()->setNamespace('success')
                                ->addMessage(sprintf($this->translate("txt-vat-number-is-valid"), $affiliation));


                            //Update the financial
                            $organisationFinancial->setVatStatus(\Organisation\Entity\Financial::VAT_STATUS_VALID);
                            $organisationFinancial->setDateVat(new \DateTime());
                        } else {
                            //Update the financial
                            $organisationFinancial->setVatStatus(\Organisation\Entity\Financial::VAT_STATUS_INVALID);
                            $organisationFinancial->setDateVat(new \DateTime());
                            $this->flashMessenger()->setNamespace('error')
                                ->addMessage(sprintf($this->translate("txt-vat-number-is-invalid"), $affiliation));
                        }
                    } catch (\Throwable $e) {
                        $this->flashMessenger()->setNamespace('danger')
                            ->addMessage(
                                sprintf(
                                    $this->translate("txt-vat-information-could-not-be-verified"),
                                    $affiliation
                                )
                            );
                    }
                } else {
                    $organisationFinancial->setVat(null);
                }

                $organisationFinancial->setEmail($formData['preferredDelivery']);
                $organisationFinancial->setOmitContact($formData['omitContact']);
                $this->getOrganisationService()->updateEntity($organisationFinancial);


                /*
                 * save the financial address
                 */
                $financialAddress = $this->getContactService()->getFinancialAddress(
                    $affiliationFinancial->getContact()
                );

                if (null === $financialAddress) {
                    $financialAddress = new Address();
                    $financialAddress->setContact($affiliation->getFinancial()->getContact());
                    /**
                     * @var $addressType AddressType
                     */
                    $addressType = $this->getContactService()
                        ->findEntityById(AddressType::class, AddressType::ADDRESS_TYPE_FINANCIAL);
                    $financialAddress->setType($addressType);
                }
                $financialAddress->setAddress($formData['address']);
                $financialAddress->setZipCode($formData['zipCode']);
                $financialAddress->setCity($formData['city']);
                /**
                 * @var Country $country
                 */
                $country = $this->generalService->find(Country::class, (int)$formData['country']);
                $financialAddress->setCountry($country);
                $this->getContactService()->updateEntity($financialAddress);

                $changelogMessage = sprintf(
                    $this->translate("txt-affiliation-financial-information-%s-has-successfully-been-updated"),
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

    /**
     * @return \Zend\Http\Response|ViewModel
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addAssociateAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int) $this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $data = $this->getRequest()->getPost()->toArray();

        $form = new AddAssociate($affiliation, $this->getContactService());
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
                            $this->translate("txt-no-contact-has-been-added-affiliation-%s"),
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
                $contact = $this->getContactService()->findContactById($data['contact']);

                $this->affiliationService->addAssociate($affiliation, $contact);

                $changelogMessage = sprintf(
                    $this->translate("txt-contact-%s-has-been-added-as-associate-to-affiliation-%s"),
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
                    $this->translate("txt-contact-%s-has-been-added-as-associate-to-affiliation-%s"),
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

    /**
     * @return \Zend\Http\Response|ViewModel
     */
    public function manageAssociateAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int) $this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $data = $this->getRequest()->getPost()->toArray();


        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(
                        sprintf(
                            $this->translate("txt-manage-associates-of-affiliation-%s-cancelled"),
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
            if (\array_key_exists('contact', $data)) {
                //Find the contact
                foreach ($data['contact'] as $contactId) {
                    $contact = $this->contactService->findContactById((int)$contactId);
                    if (null === $contact) {
                        continue;
                    }

                    $affiliation->getAssociate()->removeElement($contact);

                    $removedContacts[] = $contact;
                }

                $this->affiliationService->updateEntity($affiliation);
            }

            $changelogMessage = sprintf(
                $this->translate("txt-%s-associates-were-removed-from-affiliation"),
                \count($removedContacts)
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

    /**
     * @return \Zend\Http\Response|ViewModel
     */
    public function descriptionAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int) $this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $description = new Description();
        if (!$affiliation->getDescription()->isEmpty()) {
            /** @var Description $description */
            $description = $affiliation->getDescription()->first();
        }

        $data = $this->getRequest()->getPost()->toArray();
        $form = $this->getFormService()->prepare($description, $description, $data);
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
                $description->setAffiliation([$affiliation]);
                $description->setContact($this->identity());
                $this->affiliationService->updateEntity($description);

                $changelogMessage = sprintf(
                    $this->translate("txt-description-of-affiliation-%s-has-successfully-been-updated"),
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

    /**
     * @return \Zend\Http\Response|ViewModel
     *
     */
    public function updateEffortSpentAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int) $this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $report = $this->reportService->findReportById((int) $this->params('report'));
        if (null === $report) {
            return $this->notFoundAction();
        }

        $latestVersion = $this->projectService->getLatestProjectVersion($affiliation->getProject());

        if (null === $latestVersion) {
            return $this->notFoundAction();
        }

        $totalPlannedEffort = $this->versionService
            ->findTotalEffortByAffiliationAndVersionUpToReportingPeriod(
                $affiliation,
                $latestVersion,
                $report
            );

        if (!$effortSpent
            = $this->getReportService()->findEffortSpentByReportAndAffiliation($report, $affiliation)
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
                $this->projectService->updateEntity($effortSpent);

                //Update the marketAccess
                $affiliation->setMarketAccess($data['marketAccess']);
                $affiliation->setMainContribution($data['mainContribution']);
                $this->affiliationService->updateEntity($affiliation);

                $changelogMessage = sprintf(
                    $this->translate("txt-effort-spent-of-affiliation-%s-has-successfully-been-updated"),
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

    /**
     * @return ViewModel|Response
     */
    public function costAndEffortAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int) $this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        //Prepare the formData
        $project = $affiliation->getProject();

        $formData = [];
        foreach ($this->projectService->parseEditYearRange($project) as $year) {
            $costPerYear = $this->projectService->findTotalCostByAffiliationPerYear($affiliation);
            if (!\array_key_exists($year, $costPerYear)) {
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
                if (!\array_key_exists($year, $effortPerWorkpackageAndYear)) {
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

                if (null !== $cost && ($costValue['cost'] === '0' || empty($costValue['cost']))) {
                    $this->projectService->removeEntity($cost);
                }

                if ((float)$costValue['cost'] > 0) {
                    /*
                     * Create a new if not set yet
                     */
                    if (null === $cost) {
                        $cost = new Cost();
                        $cost->setAffiliation($affiliation);
                        $dateStart = new \DateTime();
                        $cost->setDateStart($dateStart->modify('first day of january ' . $year));
                        $dateEnd = new \DateTime();
                        $cost->setDateEnd($dateEnd->modify('last day of december ' . $year));
                    }
                    $cost->setCosts((float)$costValue['cost'] * 1000);
                    $this->projectService->updateEntity($cost);
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
                    $effort = $this->getProjectService()
                        ->findEffortByAffiliationAndWorkpackageAndYear(
                            $affiliation,
                            $workpackage,
                            $year
                        );
                    if (null !== $effort && ($effortValue['effort'] === '0' || empty($effortValue['effort']))) {
                        $this->projectService->removeEntity($effort);
                    }

                    if ((float)$effortValue['effort'] > 0) {
                        /*
                         * Create a new if not set yet
                         */
                        if (null === $effort) {
                            $effort = new Effort();
                            $effort->setAffiliation($affiliation);
                            $effort->setWorkpackage($workpackage);
                            $dateStart = new \DateTime();
                            $effort->setDateStart($dateStart->modify('first day of january ' . $year));
                            $dateEnd = new \DateTime();
                            $effort->setDateEnd($dateEnd->modify('last day of december ' . $year));
                        }
                        $effort->setEffort($effortValue['effort']);
                        $this->projectService->updateEntity($effort);
                    }
                }
            }
            //Update the mode of the project when the saved is pressed
            $project->setMode($this->getProjectService()->getNextMode($project)->mode);
            $this->getProjectService()->updateEntity($project);

            $changelogMessage = sprintf(
                $this->translate(
                    "txt-cost-and-effort-of-partner-%s-in-project-%s-has-successfully-been-updated"
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
}
