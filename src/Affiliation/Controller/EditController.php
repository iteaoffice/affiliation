<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Controller
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Affiliation\Controller;

use Affiliation\Entity\Description;
use Affiliation\Entity\Financial;
use Affiliation\Form\AddAssociate;
use Affiliation\Form\Affiliation as AffiliationForm;
use Affiliation\Form\Financial as FinancialForm;
use Contact\Entity\Address;
use Contact\Entity\AddressType;
use Contact\Service\ContactServiceAwareInterface;
use General\Entity\Country;
use General\Service\GeneralServiceAwareInterface;
use Organisation\Entity\Organisation;
use Organisation\Entity\Type as OrganisationType;
use Organisation\Service\OrganisationServiceAwareInterface;
use Project\Service\ProjectService;
use Project\Service\ProjectServiceAwareInterface;
use Zend\View\Model\ViewModel;

/**
 * @category    Affiliation
 * @package     Controller
 */
class EditController extends AffiliationAbstractController implements
    ProjectServiceAwareInterface,
    GeneralServiceAwareInterface,
    OrganisationServiceAwareInterface,
    ContactServiceAwareInterface
{
    /**
     * Edit a affiliation
     *
     * @return ViewModel
     */
    public function affiliationAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId(
            $this->getEvent()->getRouteMatch()->getParam('id')
        );
        if ($affiliationService->isEmpty()) {
            return $this->notFoundAction();
        }
        $projectService = $this->getProjectService()->setProject(
            $affiliationService->getAffiliation()->getProject()
        );
        if ($projectService->isEmpty()) {
            return $this->notFoundAction();
        }
        $formData = [];
        $formData['affiliation'] = sprintf(
            "%s|%s",
            $affiliationService->getAffiliation()->getOrganisation()->getId(),
            $affiliationService->getAffiliation()->getBranch()
        );
        $formData['technical'] = $affiliationService->getAffiliation()->getContact()->getId();
        $formData['valueChain'] = $affiliationService->getAffiliation()->getValueChain();
        $formData['marketAccess'] = $affiliationService->getAffiliation()->getMarketAccess();
        $formData['mainContribution'] = $affiliationService->getAffiliation()->getMainContribution();

        /**
         * Check if the organisation has a financial contact
         */
        if (!is_null($affiliationService->getAffiliation()->getFinancial())) {
            $formData['financial'] = $affiliationService->getAffiliation()->getFinancial()->getContact()->getId();
        }
        $form = new AffiliationForm($affiliationService);
        $form->setData($formData);
        if ($this->getRequest()->isPost() && $form->setData($_POST) && $form->isValid()) {
            $formData = $form->getData();
            $affiliation = $affiliationService->getAffiliation();
            /**
             * When the deactivate button is pressed, handle this in the service layer
             */
            if (!is_null($formData['deactivate'])) {
                $this->getAffiliationService()->deactivateAffiliation($affiliation);

                //Update the rationale for public funding
                $this->getProjectService()->updateCountryRationaleByAffiliation(
                    $affiliation,
                    ProjectService::AFFILIATION_DEACTIVATE
                );

                $this->flashMessenger()->setNamespace('success')->addMessage(
                    sprintf(
                        _("txt-affiliation-%s-has-successfully-been-deactivated"),
                        $affiliationService->getAffiliation()
                    )
                );

                return $this->redirect()->toRoute(
                    'community/project/project',
                    ['docRef' => $projectService->getProject()->getDocRef()],
                    ['fragment' => 'partners']
                );
            }
            /**
             * When the deactivate button is pressed, handle this in the service layer
             */
            if (!is_null($formData['reactivate'])) {
                $this->getAffiliationService()->reactivateAffiliation($affiliation);

                //Update the rationale for public funding
                $this->getProjectService()->updateCountryRationaleByAffiliation(
                    $affiliation,
                    ProjectService::AFFILIATION_REACTIVATE
                );

                $this->flashMessenger()->setNamespace('success')->addMessage(
                    sprintf(
                        _("txt-affiliation-%s-has-successfully-been-reactivated"),
                        $affiliationService->getAffiliation()
                    )
                );

                return $this->redirect()->toRoute(
                    'community/affiliation/affiliation',
                    [
                        'id' => $affiliationService->getAffiliation()->getId()
                    ]
                );
            }
            /**
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
            /**
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

            $this->flashMessenger()->setNamespace('success')->addMessage(
                sprintf(_("txt-affiliation-%s-has-successfully-been-updated"), $affiliationService->getAffiliation())
            );

            return $this->redirect()->toRoute(
                'community/affiliation/affiliation',
                [
                    'id' => $affiliationService->getAffiliation()->getId()
                ]
            );
        }

        return new ViewModel(
            [
                'affiliationService' => $affiliationService,
                'projectService'     => $projectService,
                'form'               => $form
            ]
        );
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     * @throws \Doctrine\ORM\ORMException
     */
    public function financialAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId(
            $this->getEvent()->getRouteMatch()->getParam('id')
        );
        if ($affiliationService->isEmpty()) {
            return $this->notFoundAction();
        }
        $projectService = $this->getProjectService()->setProject(
            $affiliationService->getAffiliation()->getProject()
        );
        if ($projectService->isEmpty()) {
            return $this->notFoundAction();
        }
        $organisationService = $this->getOrganisationService()->setOrganisation(
            $affiliationService->getAffiliation()->getOrganisation()
        );
        if ($organisationService->isEmpty()) {
            return $this->notFoundAction();
        }
        $formData = [];
        $branch = null;
        $financialAddress = null;

        if (!is_null($affiliationService->getAffiliation()->getFinancial())) {
            $branch = $affiliationService->getAffiliation()->getFinancial()->getBranch();
            $formData['attention'] = $affiliationService->getAffiliation()->getFinancial()->getContact(
            )->getDisplayName();

            $contactService = clone $this->getContactService()->setContact(
                $affiliationService->getAffiliation()->getFinancial()->getContact()
            );
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
        if (!is_null(
            $organisationFinancial = $affiliationService->getAffiliation()->getOrganisation()->getFinancial()
        )
        ) {
            $formData['preferredDelivery'] = $organisationFinancial->getEmail();
            $formData['vat'] = $organisationFinancial->getVat();
            $formData['omitContact'] = $organisationFinancial->getOmitContact();
        }

        $form = new FinancialForm($affiliationService, $this->getGeneralService());
        $form->setData($formData);
        if ($this->getRequest()->isPost() && $form->setData($_POST) && $form->isValid()) {
            $formData = $form->getData();
            /**
             * This form is a aggregation of multiple form elements, so we treat it step by step
             */
            /**
             * If the organisation or country has changed or is not set, find the new
             */
            if ($formData['organisation'] !== $organisationService->parseOrganisationWithBranch($branch) ||
                is_null($financialAddress) ||
                intval($formData['country']) !== $financialAddress->getCountry()->getId()
            ) {
                /**
                 * The organisation, or country has changed, so try to find this country in the database
                 */
                $organisation = $this->getOrganisationService()->findOrganisationByNameCountry(
                    trim($formData['organisation']),
                    $this->getGeneralService()->findEntityById('Country', $formData['country'])
                );
                /**
                 * If the organisation is not found, create it
                 */
                if (is_null($organisation)) {
                    $organisation = new Organisation();
                    $organisation->setOrganisation($formData['organisation']);
                    $organisation->setCountry(
                        $this->getGeneralService()->findEntityById('Country', $formData['country'])
                    );
                    /**
                     * @var $organisationType OrganisationType
                     */
                    $organisationType = $this->getOrganisationService()->getEntityManager()->getReference(
                        'Organisation\Entity\Type',
                        0
                    );
                    $organisation->setType($organisationType);
                }
                $affiliationFinancial = $this->getAffiliationService()->getAffiliation()->getFinancial();
                if (is_null($affiliationFinancial)) {
                    $affiliationFinancial = new Financial();
                    $affiliationFinancial->setAffiliation($this->getAffiliationService()->getAffiliation());
                }
                $affiliationFinancial->setContact(
                    $this->getContactService()->setContactId($formData['contact'])->getContact()
                );
                $affiliationFinancial->setOrganisation($organisation);
                $affiliationFinancial->setBranch(
                    trim(substr($formData['organisation'], strlen($organisation->getOrganisation())))
                );
                $this->getAffiliationService()->updateEntity($affiliationFinancial);
            }
            /**
             * The presence of a VAT number triggers the creation of a financial organisation
             */
            if (!empty($formData['vat'])) {
                if (is_null($affiliationService->getAffiliation()->getOrganisation()->getFinancial())) {
                    $organisationFinancial = new \Organisation\Entity\Financial();
                } else {
                    $organisationFinancial = $affiliationService->getAffiliation()->getOrganisation()->getFinancial();
                }
                $organisationFinancial->setOrganisation($affiliationService->getAffiliation()->getOrganisation());
                $organisationFinancial->setVat($formData['vat']);
                $organisationFinancial->setOmitContact($formData['omitContact']);
                $this->getOrganisationService()->updateEntity($organisationFinancial);
            }
            /**
             * save the financial address
             */
            $contactService = $this->getContactService()->setContact(
                $affiliationService->getAffiliation()->getFinancial()->getContact()
            );
            if (!is_null($contactService->getFinancialAddress())) {
                $financialAddress = $contactService->getFinancialAddress()->getAddress();
            } else {
                $financialAddress = new Address();
                $financialAddress->setContact($affiliationService->getAffiliation()->getFinancial()->getContact());
                /**
                 * @var $addressType AddressType
                 */
                $addressType = $this->getContactService()->getEntityManager()->getReference(
                    'Contact\Entity\AddressType',
                    AddressType::ADDRESS_TYPE_FINANCIAL
                );
                $financialAddress->setType($addressType);
            }
            $financialAddress->setAddress($formData['address']);
            $financialAddress->setZipCode($formData['zipCode']);
            $financialAddress->setCity($formData['city']);
            /**
             * @var $country Country
             */
            $country = $this->getContactService()->getEntityManager()->getReference(
                'General\Entity\Country',
                $formData['country']
            );
            $financialAddress->setCountry($country);
            $this->getContactService()->updateEntity($financialAddress);
            $this->flashMessenger()->setNamespace('success')->addMessage(
                sprintf(_("txt-affiliation-%s-has-successfully-been-updated"), $affiliationService->getAffiliation())
            );

            return $this->redirect()->toRoute(
                'community/affiliation/affiliation',
                [
                    'id' => $affiliationService->getAffiliation()->getId()
                ]
            );
        }

        return new ViewModel(
            [
                'affiliationService' => $affiliationService,
                'projectService'     => $projectService,
                'form'               => $form
            ]
        );
    }

    /**
     * @return array|ViewModel
     */
    public function addAssociateAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId(
            $this->getEvent()->getRouteMatch()->getParam('id')
        );
        if ($affiliationService->isEmpty()) {
            return $this->notFoundAction();
        }
        $projectService = $this->getProjectService()->setProject(
            $affiliationService->getAffiliation()->getProject()
        );
        if ($projectService->isEmpty()) {
            return $this->notFoundAction();
        }

        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray()
        );

        $form = new AddAssociate($affiliationService, $this->getContactService());
        $form->setData($data);

        if ($this->getRequest()->isPost() && $form->isValid()) {
            if (empty($form->getData()['cancel'])) {
                $affiliation = $affiliationService->getAffiliation();
                $affiliation->addAssociate(
                    $this->getContactService()->setContactId($form->getData()['contact'])->getContact()
                );
                $this->getAffiliationService()->updateEntity($affiliation);
            }

            return $this->redirect()->toRoute(
                'community/affiliation/affiliation',
                ['id' => $affiliationService->getAffiliation()->getId()],
                ['fragment' => 'contact']
            );
        }

        return new ViewModel(
            [
                'affiliationService' => $affiliationService,
                'projectService'     => $projectService,
                'form'               => $form
            ]
        );
    }

    /**
     * @return ViewModel
     */
    public function descriptionAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId(
            $this->getEvent()->getRouteMatch()->getParam('id')
        );
        if ($affiliationService->isEmpty()) {
            return $this->notFoundAction();
        }
        $projectService = $this->getProjectService()->setProject(
            $affiliationService->getAffiliation()->getProject()
        );
        if ($projectService->isEmpty()) {
            return $this->notFoundAction();
        }
        if (!$affiliationService->getAffiliation()->getDescription()->isEmpty()) {
            /**
             * @var $description Description
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
                $description->setAffiliation(
                    [
                        $affiliationService->getAffiliation()
                    ]
                );
                $description->setContact($this->zfcUserAuthentication()->getIdentity());
                $this->getAffiliationService()->updateEntity($description);
            }

            return $this->redirect()->toRoute(
                'community/affiliation/affiliation',
                ['id' => $affiliationService->getAffiliation()->getId()],
                ['fragment' => 'description']
            );
        }

        return new ViewModel(
            [
                'affiliationService' => $affiliationService,
                'projectService'     => $projectService,
                'form'               => $form
            ]
        );
    }
}
