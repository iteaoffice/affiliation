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
use Contact\Service\ContactServiceAwareInterface;
use DragonBe\Vies\Vies;
use General\Service\GeneralServiceAwareInterface;
use Organisation\Entity\Organisation;
use Organisation\Entity\Type;
use Organisation\Service\OrganisationServiceAwareInterface;
use Project\Entity\Report\EffortSpent as ReportEffortSpent;
use Project\Service\ProjectService;
use Project\Service\ProjectServiceAwareInterface;
use Project\Service\ReportServiceAwareInterface;
use Project\Service\VersionServiceAwareInterface;
use Zend\View\Model\ViewModel;

/**
 * @category    Affiliation
 */
class EditController extends AffiliationAbstractController
    implements ProjectServiceAwareInterface, GeneralServiceAwareInterface, OrganisationServiceAwareInterface,
               ReportServiceAwareInterface, VersionServiceAwareInterface, ContactServiceAwareInterface
{
    /**
     * Edit a affiliation.
     *
     * @return ViewModel
     */
    public function affiliationAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId($this->getEvent()->getRouteMatch()
            ->getParam('id'));
        if ($affiliationService->isEmpty()) {
            return $this->notFoundAction();
        }
        $projectService = $this->getProjectService()->setProject($affiliationService->getAffiliation()->getProject());
        if ($projectService->isEmpty()) {
            return $this->notFoundAction();
        }
        $formData = [];
        $formData['affiliation'] = sprintf("%s|%s", $affiliationService->getAffiliation()->getOrganisation()->getId(),
            $affiliationService->getAffiliation()->getBranch());
        $formData['technical'] = $affiliationService->getAffiliation()->getContact()->getId();
        $formData['valueChain'] = $affiliationService->getAffiliation()->getValueChain();
        $formData['marketAccess'] = $affiliationService->getAffiliation()->getMarketAccess();
        $formData['mainContribution'] = $affiliationService->getAffiliation()->getMainContribution();

        /*
         * Check if the organisation has a financial contact
         */
        if (!is_null($affiliationService->getAffiliation()->getFinancial())) {
            $formData['financial'] = $affiliationService->getAffiliation()->getFinancial()->getContact()->getId();
        }
        $form = new AffiliationForm($affiliationService);
        $form->setData($formData);
        if ($this->getRequest()->isPost() && $form->setData($_POST)
            && $form->isValid()
        ) {
            $formData = $form->getData();
            $affiliation = $affiliationService->getAffiliation();
            /*
             * When the deactivate button is pressed, handle this in the service layer
             */
            if (!is_null($formData['deactivate'])) {
                $this->getAffiliationService()->deactivateAffiliation($affiliation);

                //Update the rationale for public funding
                $this->getProjectService()
                    ->updateCountryRationaleByAffiliation($affiliation, ProjectService::AFFILIATION_DEACTIVATE);

                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(sprintf(_("txt-affiliation-%s-has-successfully-been-deactivated"),
                        $affiliationService->getAffiliation()));

                return $this->redirect()->toRoute('community/project/project/partners', [
                    'docRef' => $projectService->getProject()->getDocRef()
                ]);
            }
            /*
             * When the deactivate button is pressed, handle this in the service layer
             */
            if (!is_null($formData['reactivate'])) {
                $this->getAffiliationService()->reactivateAffiliation($affiliation);

                //Update the rationale for public funding
                $this->getProjectService()
                    ->updateCountryRationaleByAffiliation($affiliation, ProjectService::AFFILIATION_REACTIVATE);

                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(sprintf(_("txt-affiliation-%s-has-successfully-been-reactivated"),
                        $affiliationService->getAffiliation()));

                return $this->redirect()->toRoute('community/affiliation/affiliation', [
                    'id' => $affiliationService->getAffiliation()->getId(),
                ]);
            }
            /*
             * Parse the organisation and branch
             */
            list($organisationId, $branch) = explode('|', $formData['affiliation']);
            $organisation = $this->getOrganisationService()->setOrganisationId($organisationId)->getOrganisation();
            $affiliation->setOrganisation($organisation);
            $affiliation->setContact($this->getContactService()->setContactId($formData['technical'])->getContact());
            $affiliation->setBranch($branch);
            $this->getAffiliationService()->updateEntity($affiliation);
            $affiliation->setValueChain($formData['valueChain']);
            $affiliation->setMainContribution($formData['mainContribution']);
            $affiliation->setMarketAccess($formData['marketAccess']);
            /*
             * Handle the financial organisation
             */
            if (is_null($financial = $affiliation->getFinancial())) {
                $financial = new Financial();
            }
            $financial->setOrganisation($organisation);
            $financial->setAffiliation($affiliation);
            $financial->setBranch($branch);
            $financial->setContact($this->getContactService()->setContactId($formData['financial'])->getContact());
            $this->getAffiliationService()->updateEntity($financial);

            $this->flashMessenger()->setNamespace('success')
                ->addMessage(sprintf($this->translate("txt-affiliation-%s-has-successfully-been-updated"),
                    $affiliationService->getAffiliation()));

            return $this->redirect()->toRoute('community/affiliation/affiliation', [
                'id' => $affiliationService->getAffiliation()->getId(),
            ]);
        }

        return new ViewModel([
            'affiliationService' => $affiliationService,
            'projectService'     => $projectService,
            'form'               => $form,
        ]);
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function financialAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId($this->getEvent()->getRouteMatch()
            ->getParam('id'));
        if ($affiliationService->isEmpty()) {
            return $this->notFoundAction();
        }
        $projectService = $this->getProjectService()->setProject($affiliationService->getAffiliation()->getProject());
        if ($projectService->isEmpty()) {
            return $this->notFoundAction();
        }
        $organisationService = $this->getOrganisationService()->setOrganisation($affiliationService->getAffiliation()
            ->getOrganisation());
        if ($organisationService->isEmpty()) {
            return $this->notFoundAction();
        }
        $formData = [
            'preferredDelivery' => \Organisation\Entity\Financial::EMAIL_DELIVERY,
            'omitContact'       => \Organisation\Entity\Financial::OMIT_CONTACT
        ];
        $branch = null;
        $financialAddress = null;
        $organisationFinancial = null;

        if (!is_null($affiliationService->getAffiliation()->getFinancial())) {

            //We have a financial organisation, so populate the form with this data
            $organisationService = $this->getOrganisationService()
                ->setOrganisation($affiliationService->getAffiliation()->getFinancial()->getOrganisation());
            $organisationFinancial = $affiliationService->getAffiliation()->getFinancial()->getOrganisation()
                ->getFinancial();

            $branch = $affiliationService->getAffiliation()->getFinancial()->getBranch();
            $formData['attention'] = $affiliationService->getAffiliation()->getFinancial()->getContact()
                ->getDisplayName();

            $contactService = clone $this->getContactService()->setContact($affiliationService->getAffiliation()
                ->getFinancial()->getContact());
            $formData['contact'] = $affiliationService->getAffiliation()->getFinancial()->getContact()->getId();

            if (!is_null($financialAddress = $contactService->getFinancialAddress())) {
                $financialAddress = $contactService->getFinancialAddress()->getAddress();
                $formData['address'] = $financialAddress->getAddress();
                $formData['zipCode'] = $financialAddress->getZipCode();
                $formData['city'] = $financialAddress->getCity();
                $formData['country'] = $financialAddress->getCountry()->getId();
            }
        }

        $formData['organisation'] = $organisationService->parseOrganisationWithBranch($branch);
        $formData['registeredCountry'] = $organisationService->getOrganisation()->getCountry()->getId();

        if (!is_null($organisationFinancial)) {
            $organisationFinancial = $affiliationService->getAffiliation()->getOrganisation()->getFinancial();
            $formData['preferredDelivery'] = $organisationFinancial->getEmail();
            $formData['vat'] = $organisationFinancial->getVat();
            $formData['omitContact'] = $organisationFinancial->getOmitContact();
        }


        $form = new FinancialForm($affiliationService, $this->getGeneralService());
        $form->setData($formData);
        if ($this->getRequest()->isPost() && $form->setData($_POST)
            && $form->isValid()
        ) {
            $formData = $form->getData();

            //We need to find the organisation, first by tring the VAT, then via the name and country and then just create it
            $organisation = null;

            //Check if an organisation with the given VAT is already found
            $organisationFinancial = $this->getOrganisationService()
                ->findFinancialOrganisationWithVAT($formData['vat']);


            //If the organisation is found, it has by default an organiation
            if (!is_null($organisationFinancial)) {
                $organisation = $organisationFinancial->getOrganisation();
            }

            //try to find the organisation based on te country and name
            if (is_null($organisation)) {
                $organisation = $this->getOrganisationService()
                    ->findOrganisationByNameCountry(trim($formData['organisation']),
                        $this->getGeneralService()->findEntityById('Country', $formData['country']));
            }

            /**
             * If the organisation is still not found, create it
             */
            if (is_null($organisation)) {
                $organisation = new Organisation();
                $organisation->setOrganisation($formData['organisation']);
                $organisation->setCountry($this->getGeneralService()->findEntityById('Country', $formData['country']));
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
            $affiliationFinancial = $this->getAffiliationService()->getAffiliation()->getFinancial();
            if (is_null($affiliationFinancial)) {
                $affiliationFinancial = new Financial();
                $affiliationFinancial->setAffiliation($this->getAffiliationService()->getAffiliation());
            }
            $affiliationFinancial->setContact($this->getContactService()->setContactId($formData['contact'])
                ->getContact());
            $affiliationFinancial->setOrganisation($organisation);
            $affiliationFinancial->setBranch(trim(substr($formData['organisation'],
                strlen($organisation->getOrganisation()))));
            $this->getAffiliationService()->updateEntity($affiliationFinancial);


            if (!is_null($affiliationService->getAffiliation()->getFinancial())) {
                $organisationFinancial = $affiliationService->getAffiliation()->getFinancial()->getOrganisation()
                    ->getFinancial();
            } else {
                $organisationFinancial = $affiliationService->getAffiliation()->getOrganisation()->getFinancial();
            }

            if (is_null($organisationFinancial)) {
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
                    $result = $vies->validateVat($organisationFinancial->getOrganisation()->getCountry()->getCd(),
                        trim(str_replace($organisationFinancial->getOrganisation()->getCountry()->getCd(), '',
                            $formData['vat'])));

                    if ($result->isValid()) {

                        $this->flashMessenger()->setNamespace('success')
                            ->addMessage(sprintf($this->translate("txt-vat-number-is-valid"),
                                $affiliationService->getAffiliation()));


                        //Update the financial
                        $organisationFinancial->setVatStatus(\Organisation\Entity\Financial::VAT_STATUS_VALID);
                        $organisationFinancial->setDateVat(new \DateTime());
                    } else {
                        //Update the financial
                        $organisationFinancial->setVatStatus(\Organisation\Entity\Financial::VAT_STATUS_INVALID);
                        $organisationFinancial->setDateVat(new \DateTime());
                        $this->flashMessenger()->setNamespace('error')
                            ->addMessage(sprintf($this->translate("txt-vat-number-is-invalid"),
                                $affiliationService->getAffiliation()));

                    }
                } catch (\Exception $e) {
                    $this->flashMessenger()->setNamespace('danger')
                        ->addMessage(sprintf($this->translate("txt-vat-information-could-not-be-verified"),
                            $affiliationService->getAffiliation()));

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
            $contactService = clone $this->getContactService()->setContact($affiliationFinancial->getContact());
            if (!is_null($contactService->getFinancialAddress())) {
                $financialAddress = $contactService->getFinancialAddress()->getAddress();
            } else {
                $financialAddress = new Address();
                $financialAddress->setContact($affiliationService->getAffiliation()->getFinancial()->getContact());
                /*
                 * @var AddressType
                 */
                $addressType = $this->getContactService()->getEntityManager()
                    ->getReference('Contact\Entity\AddressType', AddressType::ADDRESS_TYPE_FINANCIAL);
                $financialAddress->setType($addressType);
            }
            $financialAddress->setAddress($formData['address']);
            $financialAddress->setZipCode($formData['zipCode']);
            $financialAddress->setCity($formData['city']);
            /*
             * @var Country
             */
            $country = $this->getContactService()->getEntityManager()
                ->getReference('General\Entity\Country', $formData['country']);
            $financialAddress->setCountry($country);
            $this->getContactService()->updateEntity($financialAddress);
            $this->flashMessenger()->setNamespace('success')
                ->addMessage(sprintf($this->translate("txt-affiliation-%s-has-successfully-been-updated"),
                    $affiliationService->getAffiliation()));

            return $this->redirect()->toRoute('community/affiliation/affiliation', [
                'id' => $affiliationService->getAffiliation()->getId(),
            ], [
                    'fragment' => 'invoicing'
                ]);
        }

        return new ViewModel([
            'affiliationService' => $affiliationService,
            'projectService'     => $projectService,
            'form'               => $form,
        ]);
    }

    /**
     * @return array|ViewModel
     */
    public function addAssociateAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId($this->getEvent()->getRouteMatch()
            ->getParam('id'));
        if ($affiliationService->isEmpty()) {
            return $this->notFoundAction();
        }
        $projectService = $this->getProjectService()->setProject($affiliationService->getAffiliation()->getProject());
        if ($projectService->isEmpty()) {
            return $this->notFoundAction();
        }

        $data = array_merge_recursive($this->getRequest()->getPost()->toArray());

        $form = new AddAssociate($affiliationService, $this->getContactService());
        $form->setData($data);

        if ($this->getRequest()->isPost() && $form->isValid()) {
            if (empty($form->getData()['cancel'])) {
                $affiliation = $affiliationService->getAffiliation();
                $affiliation->addAssociate($this->getContactService()->setContactId($form->getData()['contact'])
                    ->getContact());
                $this->getAffiliationService()->updateEntity($affiliation);
            }

            $this->flashMessenger()->setNamespace('success')
                ->addMessage(sprintf($this->translate("txt-affiliation-%s-has-successfully-been-updated"),
                    $affiliationService->getAffiliation()));

            return $this->redirect()
                ->toRoute('community/affiliation/affiliation', ['id' => $affiliationService->getAffiliation()->getId()],
                    ['fragment' => 'contact']);
        }

        return new ViewModel([
            'affiliationService' => $affiliationService,
            'projectService'     => $projectService,
            'form'               => $form,
        ]);
    }

    /**
     * @return ViewModel
     */
    public function descriptionAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId($this->getEvent()->getRouteMatch()
            ->getParam('id'));
        if ($affiliationService->isEmpty()) {
            return $this->notFoundAction();
        }
        $projectService = $this->getProjectService()->setProject($affiliationService->getAffiliation()->getProject());
        if ($projectService->isEmpty()) {
            return $this->notFoundAction();
        }
        if (!$affiliationService->getAffiliation()->getDescription()->isEmpty()) {
            /*
             * @var Description
             */
            $description = $affiliationService->getAffiliation()->getDescription()->first();
        } else {
            $description = new Description();
        }
        $data = $this->getRequest()->getPost()->toArray();
        $form = $this->getFormService()->prepare('description', $description, $data);
        if ($this->getRequest()->isPost() && $form->isValid()) {
            if (array_key_exists('submit', $data)) {
                $description = $form->getData();
                $description->setAffiliation([
                    $affiliationService->getAffiliation(),
                ]);
                $description->setContact($this->zfcUserAuthentication()->getIdentity());
                $this->getAffiliationService()->updateEntity($description);

                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(sprintf($this->translate("txt-affiliation-%s-has-successfully-been-updated"),
                        $affiliationService->getAffiliation()));
            }

            return $this->redirect()
                ->toRoute('community/affiliation/affiliation', ['id' => $affiliationService->getAffiliation()->getId()],
                    ['fragment' => 'description']);
        }

        return new ViewModel([
            'affiliationService' => $affiliationService,
            'projectService'     => $projectService,
            'form'               => $form,
        ]);
    }

    /**
     * @return ViewModel
     */
    public function updateEffortSpentAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId($this->getEvent()->getRouteMatch()
            ->getParam('id'));
        if ($affiliationService->isEmpty()) {
            return $this->notFoundAction();
        }
        $projectService = $this->getProjectService()->setProject($affiliationService->getAffiliation()->getProject());
        $reportService = $this->getReportService()->setReportId($this->getEvent()->getRouteMatch()->getParam('report'));
        if ($reportService->isEmpty()) {
            return $this->notFoundAction();
        }

        //Find the latestVersion

        $report = $reportService->getReport();

        $latestVersion = $this->getProjectService()->getLatestProjectVersion();
        $totalPlannedEffort = $this->getVersionService()
            ->findTotalEffortByAffiliationAndVersionUpToReportingPeriod($affiliationService->getAffiliation(),
                $latestVersion, $report);

        if (!$effortSpent
            = $reportService->findEffortSpentByReportAndAffiliation($report, $affiliationService->getAffiliation())
        ) {
            $effortSpent = new ReportEffortSpent();
            $effortSpent->setAffiliation($affiliationService->getAffiliation());
            $effortSpent->setReport($report);
        }

        /**
         * Inject the known data form the object into the data array for form population
         */
        $data = array_merge([
            'effort'           => $effortSpent->getEffort(),
            'comment'          => $effortSpent->getComment(),
            'summary'          => $effortSpent->getSummary(),
            'marketAccess'     => $affiliationService->getAffiliation()->getMarketAccess(),
            'mainContribution' => $affiliationService->getAffiliation()->getMainContribution()
        ], $this->getRequest()->getPost()->toArray());

        $form = new EffortSpent($totalPlannedEffort);
        $form->setData($data);

        if ($this->getRequest()->isPost()) {
            /**
             * Handle the cancel request
             */
            if (!is_null($this->getRequest()->getPost()->get('cancel'))) {
                return $this->redirect()->toRoute('community/affiliation/affiliation', [
                    'id' => $affiliationService->getAffiliation()->getId()
                ], ['fragment' => 'report']);
            }

            if ($form->isValid()) {
                $effortSpent->setEffort($data['effort']);
                $effortSpent->setComment($data['comment']);
                $effortSpent->setSummary($data['summary']);
                $effortSpent->setContact($this->zfcUserAuthentication()->getIdentity());
                $this->getProjectService()->updateEntity($effortSpent);

                //Update the marketAccess
                $affiliation = $affiliationService->getAffiliation();
                $affiliation->setMarketAccess($data['marketAccess']);
                $affiliation->setMainContribution($data['mainContribution']);
                $this->getAffiliationService()->updateEntity($affiliation);

                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(sprintf($this->translate("txt-affiliation-%s-has-successfully-been-updated"),
                        $affiliationService->getAffiliation()));

                return $this->redirect()->toRoute('community/affiliation/affiliation', [
                    'id' => $affiliationService->getAffiliation()->getId()
                ], ['fragment' => 'report']);
            }
        }

        return new ViewModel([
            'affiliationService' => $affiliationService,
            'projectService'     => $projectService,
            'reportService'      => $reportService,
            'report'             => $report,
            'form'               => $form,
            'totalPlannedEffort' => $totalPlannedEffort
        ]);
    }
}
