<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Controller;

use Affiliation\Entity\Description;
use Affiliation\Entity\Financial;
use Affiliation\Form\AddAssociate;
use Affiliation\Form\Affiliation as AffiliationForm;
use Affiliation\Form\EffortSpent;
use Affiliation\Form\Financial as FinancialForm;
use Contact\Entity\Address;
use Contact\Entity\AddressType;
use Contact\Service\ContactService;
use DragonBe\Vies\Vies;
use General\Entity\Country;
use Organisation\Entity\Organisation;
use Organisation\Entity\Type;
use Project\Entity\Report\EffortSpent as ReportEffortSpent;
use Project\Service\ProjectService;
use Zend\View\Model\ViewModel;

/**
 * @category    Affiliation
 */
class EditController extends AffiliationAbstractController
{
    /**
     * @return array|\Zend\Http\Response|ViewModel
     */
    public function affiliationAction()
    {
        $affiliation = $this->getAffiliationService()->findAffiliationById($this->params('id'));

        if (is_null($affiliation)) {
            return $this->notFoundAction();
        }

        $formData                        = $this->getRequest()->getPost()->toArray();
        $formData['affiliation']         = sprintf(
            "%s|%s",
            $affiliation->getOrganisation()->getId(),
            $affiliation->getBranch()
        );
        $formData['technical']           = $affiliation->getContact()->getId();
        $formData['valueChain']          = $affiliation->getValueChain();
        $formData['marketAccess']        = $affiliation->getMarketAccess();
        $formData['mainContribution']    = $affiliation->getMainContribution();
        $formData['strategicImportance'] = $affiliation->getStrategicImportance();

        /*
         * Check if the organisation has a financial contact
         */
        if (! is_null($affiliation->getFinancial())) {
            $formData['financial'] = $affiliation->getFinancial()->getContact()->getId();
        }
        $form = new AffiliationForm($affiliation, $this->getAffiliationService());
        $form->setData($formData);

        //Remove the de-activate-button when partner is not active
        if (! $this->getAffiliationService()->isActive($affiliation)) {
            $form->remove('deactivate');
        }

        //Remove the re-activate-button when partner is active
        if ($this->getAffiliationService()->isActive($affiliation)) {
            $form->remove('reactivate');
        }

        if ($this->getAffiliationService()->affiliationHasCostOrEffortInDraft($affiliation)) {
            $form->remove('deactivate');
        }

        if ($this->getRequest()->isPost() && $form->setData($this->getRequest()->getPost()->toArray())) {
            /*
             * When the deactivate button is pressed, handle this in the service layer
             */
            if (isset($formData['deactivate'])) {
                $this->getAffiliationService()->deactivateAffiliation($affiliation);

                //Update the rationale for public funding
                $this->getProjectService()
                    ->updateCountryRationaleByAffiliation($affiliation, ProjectService::AFFILIATION_DEACTIVATE);

                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(sprintf(_("txt-affiliation-%s-has-successfully-been-deactivated"), $affiliation));

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
                $this->getAffiliationService()->reactivateAffiliation($affiliation);

                //Update the rationale for public funding
                $this->getProjectService()
                    ->updateCountryRationaleByAffiliation($affiliation, ProjectService::AFFILIATION_REACTIVATE);

                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(sprintf(_("txt-affiliation-%s-has-successfully-been-reactivated"), $affiliation));

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
                    ['id' => $affiliation->getId(), ]
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
                $this->getAffiliationService()->updateEntity($affiliation);
                $affiliation->setValueChain($formData['valueChain']);
                $affiliation->setMainContribution($formData['mainContribution']);
                $affiliation->setMarketAccess($formData['marketAccess']);
                $affiliation->setStrategicImportance($formData['strategicImportance']);
                /*
                 * Handle the financial organisation
                 */
                if (is_null($financial = $affiliation->getFinancial())) {
                    $financial = new Financial();
                }
                $financial->setOrganisation($organisation);
                $financial->setAffiliation($affiliation);
                $financial->setBranch($branch);
                $financial->setContact($this->getContactService()->findContactById($formData['financial']));
                $this->getAffiliationService()->updateEntity($financial);

                //Update the mode of the project
                $project = $affiliation->getProject();
                $project->setMode($this->getProjectService()->getNextMode($project)->mode);
                $this->getProjectService()->updateEntity($project);

                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(
                        sprintf(
                            $this->translate("txt-affiliation-%s-has-successfully-been-updated"),
                            $affiliation
                        )
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
                'affiliationHasCostOrEffortInDraft' => $this->getAffiliationService()
                    ->affiliationHasCostOrEffortInDraft($affiliation),
                'affiliationService'                => $this->getAffiliationService(),
                'projectService'                    => $this->getProjectService(),
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
        $affiliation = $this->getAffiliationService()->findAffiliationById($this->params('id'));

        if (is_null($affiliation)) {
            return $this->notFoundAction();
        }

        $formData              = [
            'preferredDelivery' => \Organisation\Entity\Financial::EMAIL_DELIVERY,
            'omitContact'       => \Organisation\Entity\Financial::OMIT_CONTACT,
        ];
        $branch                = null;
        $financialAddress      = null;
        $organisationFinancial = null;

        if (! is_null($affiliation->getFinancial())) {
            $organisationFinancial = $affiliation->getFinancial()->getOrganisation()->getFinancial();
            $branch                = $affiliation->getFinancial()->getBranch();
            $formData['attention'] = $affiliation->getFinancial()->getContact()->getDisplayName();

            /** @var ContactService $contactService */
            $formData['contact'] = $affiliation->getFinancial()->getContact()->getId();

            if (! is_null(
                $financialAddress = $this->getContactService()->getFinancialAddress(
                    $affiliation->getFinancial()
                        ->getContact()
                )
            )
            ) {
                $formData['address'] = $financialAddress->getAddress();
                $formData['zipCode'] = $financialAddress->getZipCode();
                $formData['city']    = $financialAddress->getCity();
                $formData['country'] = $financialAddress->getCountry()->getId();
            }
        }

        $formData['organisation']      = $this->getOrganisationService()
            ->parseOrganisationWithBranch($branch, $affiliation->getOrganisation());
        $formData['registeredCountry'] = $affiliation->getOrganisation()->getCountry()->getId();

        if (! is_null($organisationFinancial)) {
            $organisationFinancial         = $affiliation->getOrganisation()->getFinancial();
            $formData['preferredDelivery'] = $organisationFinancial->getEmail();
            $formData['vat']               = $organisationFinancial->getVat();
            $formData['omitContact']       = $organisationFinancial->getOmitContact();
        }


        $form = new FinancialForm($affiliation, $this->getGeneralService(), $this->getEntityManager());

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


                //If the organisation is found, it has by default an organiation
                if (! is_null($organisationFinancial)) {
                    $organisation = $organisationFinancial->getOrganisation();
                }

                //try to find the organisation based on te country and name
                if (is_null($organisation)) {
                    $organisation = $this->getOrganisationService()
                        ->findOrganisationByNameCountry(
                            trim($formData['organisation']),
                            $this->getGeneralService()->findEntityById(Country::class, $formData['country'])
                        );
                }

                /**
                 * If the organisation is still not found, create it
                 */
                if (is_null($organisation)) {
                    $organisation = new Organisation();
                    $organisation->setOrganisation($formData['organisation']);
                    $organisation->setCountry(
                        $this->getGeneralService()
                            ->findEntityById(Country::class, $formData['country'])
                    );
                    /**
                     * @var $organisationType Type
                     */
                    $organisationType = $this->getOrganisationService()->getEntityManager()
                        ->getReference('Organisation\Entity\Type', 0);
                    $organisation->setType($organisationType);
                }

                /**
                 *
                 * Update the affiliationFinancial
                 */
                $affiliationFinancial = $affiliation->getFinancial();
                if (is_null($affiliationFinancial)) {
                    $affiliationFinancial = new Financial();
                    $affiliationFinancial->setAffiliation($affiliation);
                }
                $affiliationFinancial->setContact($this->getContactService()->findContactById($formData['contact']));
                $affiliationFinancial->setOrganisation($organisation);
                $affiliationFinancial->setBranch(
                    trim(
                        substr(
                            $formData['organisation'],
                            strlen($organisation->getOrganisation())
                        )
                    )
                );
                $this->getAffiliationService()->updateEntity($affiliationFinancial);


                if (! is_null($affiliation->getFinancial())) {
                    $organisationFinancial = $affiliation->getFinancial()->getOrganisation()->getFinancial();
                } else {
                    $organisationFinancial = $affiliation->getOrganisation()->getFinancial();
                }

                if (is_null($organisationFinancial)) {
                    $organisationFinancial = new \Organisation\Entity\Financial();
                }

                $organisationFinancial->setOrganisation($organisation);
                /**
                 * The presence of a VAT number triggers the creation of a financial organisation
                 */
                if (! empty($formData['vat'])) {
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
                    } catch (\Exception $e) {
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

                if (is_null(
                    $financialAddress = $this->getContactService()
                        ->getFinancialAddress($affiliationFinancial->getContact())
                )) {
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
                $country = $this->getGeneralService()->findEntityById(Country::class, $formData['country']);
                $financialAddress->setCountry($country);
                $this->getContactService()->updateEntity($financialAddress);
                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(
                        sprintf(
                            $this->translate("txt-affiliation-%s-has-successfully-been-updated"),
                            $affiliation
                        )
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
                'affiliationService' => $this->getAffiliationService(),
                'projectService'     => $this->getProjectService(),
                'form'               => $form,
            ]
        );
    }

    /**
     * @return array|ViewModel
     */
    public function addAssociateAction()
    {
        $affiliation = $this->getAffiliationService()->findAffiliationById($this->params('id'));

        if (is_null($affiliation)) {
            return $this->notFoundAction();
        }

        $data = array_merge_recursive($this->getRequest()->getPost()->toArray());

        $form = new AddAssociate($affiliation, $this->getContactService());
        $form->setData($data);

        if ($this->getRequest()->isPost() && $form->isValid()) {
            if (empty($form->getData()['cancel'])) {
                $affiliation->addAssociate($this->getContactService()->findContactById($form->getData()['contact']));
                $this->getAffiliationService()->updateEntity($affiliation);
            }

            $this->flashMessenger()->setNamespace('success')
                ->addMessage(
                    sprintf(
                        $this->translate("txt-affiliation-%s-has-successfully-been-updated"),
                        $affiliation
                    )
                );

            return $this->redirect()->toRoute(
                'community/affiliation/affiliation',
                ['id' => $affiliation->getId()],
                ['fragment' => 'contact']
            );
        }

        return new ViewModel(
            [
                'affiliation'        => $affiliation,
                'affiliationService' => $this->getAffiliationService(),
                'projectService'     => $this->getProjectService(),
                'form'               => $form,
            ]
        );
    }

    /**
     * @return ViewModel
     */
    public function descriptionAction()
    {
        $affiliation = $this->getAffiliationService()->findAffiliationById($this->params('id'));

        if (is_null($affiliation)) {
            return $this->notFoundAction();
        }

        if (! $affiliation->getDescription()->isEmpty()) {
            /** @var Description $description */
            $description = $affiliation->getDescription()->first();
        } else {
            $description = new Description();
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
                $description->setAffiliation(
                    [
                        $affiliation,
                    ]
                );
                $description->setContact($this->zfcUserAuthentication()->getIdentity());
                $this->getAffiliationService()->updateEntity($description);

                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(
                        sprintf(
                            $this->translate("txt-affiliation-%s-has-successfully-been-updated"),
                            $affiliation
                        )
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
                'affiliationService' => $this->getAffiliationService(),
                'projectService'     => $this->getProjectService(),
                'form'               => $form,
            ]
        );
    }

    /**
     * @return ViewModel
     */
    public function updateEffortSpentAction()
    {
        $affiliation = $this->getAffiliationService()->findAffiliationById($this->params('id'));

        if (is_null($affiliation)) {
            return $this->notFoundAction();
        }

        $report = $this->getReportService()->findReportById($this->params('report'));
        if (is_null($report)) {
            return $this->notFoundAction();
        }

        $latestVersion      = $this->getProjectService()->getLatestProjectVersion($affiliation->getProject());
        $totalPlannedEffort = $this->getVersionService()
            ->findTotalEffortByAffiliationAndVersionUpToReportingPeriod($affiliation, $latestVersion, $report);

        if (! $effortSpent
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
            if (! is_null($this->getRequest()->getPost()->get('cancel'))) {
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
                $effortSpent->setContact($this->zfcUserAuthentication()->getIdentity());
                $this->getProjectService()->updateEntity($effortSpent);

                //Update the marketAccess
                $affiliation->setMarketAccess($data['marketAccess']);
                $affiliation->setMainContribution($data['mainContribution']);
                $this->getAffiliationService()->updateEntity($affiliation);

                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(
                        sprintf(
                            $this->translate("txt-affiliation-%s-has-successfully-been-updated"),
                            $affiliation
                        )
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
                'affiliationService' => $this->getAffiliationService(),
                'projectService'     => $this->getProjectService(),
                'reportService'      => $this->getReportService(),
                'report'             => $report,
                'form'               => $form,
                'totalPlannedEffort' => $totalPlannedEffort,
            ]
        );
    }
}
