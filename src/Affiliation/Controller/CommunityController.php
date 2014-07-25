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

use Affiliation\Entity;
use Affiliation\Form\Affiliation;
use Affiliation\Form\Financial;
use Contact\Entity\Address;
use Contact\Entity\AddressType;
use Contact\Service\ContactServiceAwareInterface;
use General\Entity\Country;
use Organisation\Entity\Organisation;
use Organisation\Entity\Type as OrganisationType;
use Organisation\Service\OrganisationServiceAwareInterface;
use Project\Service\ProjectServiceAwareInterface;
use Zend\View\Model\ViewModel;

/**
 * @category    Affiliation
 * @package     Controller
 */
class CommunityController extends AffiliationAbstractController implements
    ProjectServiceAwareInterface,
    OrganisationServiceAwareInterface,
    ContactServiceAwareInterface
{
    /**
     * Show the details of 1 affiliation
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function affiliationAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId(
            $this->getEvent()->getRouteMatch()->getParam('id')
        );
        $this->getProjectService()->setProject($affiliationService->getAffiliation()->getProject());

        return new ViewModel(
            [
                'affiliationService' => $affiliationService,
                'contactsInAffiliation' => $this->getContactService()->findContactsInAffiliation(
                    $affiliationService->getAffiliation()
                ),
                'projectService' => $this->getProjectService(),
                'latestVersion' => $this->getProjectService()->getLatestProjectVersion(null, null, true),
                'versionType' => $this->getProjectService()->getNextMode()->versionType
            ]
        );
    }

    /**
     * Edit a affiliation
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId(
            $this->getEvent()->getRouteMatch()->getParam('id')
        );
        $projectService = $this->getProjectService()->setProject(
            $affiliationService->getAffiliation()->getProject()
        );
        $formData = [];
        $formData['affiliation'] = sprintf(
            "%s|%s",
            $affiliationService->getAffiliation()->getOrganisation()->getId(),
            $affiliationService->getAffiliation()->getBranch()
        );
        $formData['technical'] = $affiliationService->getAffiliation()->getContact()->getId();
        $formData['valueChain'] = $affiliationService->getAffiliation()->getValueChain();
        /**
         * Check if the organisation has a financial contact
         */
        if (!is_null($affiliationService->getAffiliation()->getOrganisation()->getFinancial())) {
            $formData['preferredDelivery'] = $affiliationService->getAffiliation()->getOrganisation()->getFinancial()
                ->getEmail();
        }
        /**
         * Check if the organisation has a financial contact
         */
        if (!is_null($affiliationService->getAffiliation()->getFinancial())) {
            $formData['financial'] = $affiliationService->getAffiliation()->getFinancial()->getContact()->getId();
        }
        $form = new Affiliation($affiliationService);
        $form->setData($formData);
        if ($this->getRequest()->isPost() && $form->setData($_POST) && $form->isValid()) {
            $formData = $form->getData();
            $affiliation = $affiliationService->getAffiliation();
            /**
             * When the deactivate button is pressed, handle this in the service layer
             */
            if (!is_null($formData['deactivate'])) {
                $this->getAffiliationService()->deactivateAffiliation($affiliation);
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
            /**
             * Handle the financial organisation
             */
            if (is_null($financial = $affiliation->getFinancial())) {
                $financial = new Entity\Financial();
            }
            $financial->setOrganisation($organisation);
            $financial->setAffiliation($affiliation);
            $financial->setBranch($branch);
            $financial->setContact($this->getContactService()->setContactId($formData['financial'])->getContact());
            $this->getAffiliationService()->updateEntity($financial);
            /**
             * Handle the preferred delivery for the organisation (OrganisationFinancial)
             */
            if (is_null($organisationFinancial = $affiliation->getOrganisation()->getFinancial())) {
                $organisationFinancial = new \Organisation\Entity\Financial();
                $organisationFinancial->setOrganisation($affiliation->getOrganisation());
            }
            $organisationFinancial->setEmail((bool)$formData['preferredDelivery']);
            $this->getOrganisationService()->updateEntity($organisationFinancial);
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
    public function editFinancialAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId(
            $this->getEvent()->getRouteMatch()->getParam('id')
        );
        $projectService = $this->getProjectService()->setProject(
            $affiliationService->getAffiliation()->getProject()
        );
        $organisationService = $this->getOrganisationService()->setOrganisation(
            $affiliationService->getAffiliation()->getOrganisation()
        );
        $formData = [];
        $branch = null;
        $branch = $affiliationService->getAffiliation()->getFinancial()->getBranch();
        $formData['attention'] = $affiliationService->getAffiliation()->getFinancial()->getContact()->getDisplayName();
        $contactService = $this->getContactService()->setContact(
            $affiliationService->getAffiliation()->getFinancial()->getContact()
        );
        if (!is_null($financialAddress = $contactService->getFinancialAddress())) {
            $financialAddress = $contactService->getFinancialAddress()->getAddress();
            $formData['address'] = $financialAddress->getAddress();
            $formData['zipCode'] = $financialAddress->getZipCode();
            $formData['city'] = $financialAddress->getCity();
            $formData['country'] = $financialAddress->getCountry()->getId();
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
        $form = new Financial($affiliationService, $this->getGeneralService());
        $form->setData($formData);
        if ($this->getRequest()->isPost() && $form->setData($_POST) && $form->isValid()) {
            $formData = $form->getData();
            /**
             * This form is a aggregation of multiple form elements, so we treat it step by step
             */
            /**
             * If the organisation or country has changed, find the new
             */
            if ($formData['organisation'] !== $organisationService->parseOrganisationWithBranch($branch) ||
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
                $affiliationFinancial->setOrganisation($organisation);
                $affiliationFinancial->setBranch(
                    trim(substr($formData['organisation'], strlen($organisation->getOrganisation())))
                );
                $this->getAffiliationService()->updateEntity($affiliationFinancial);
            }
            /**
             * The presence of a VAT number triggers the creation of a financial organiation
             */
            if (empty($formData['vat'])) {
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
     * @return ViewModel
     */
    public function editDescriptionAction()
    {
        return new ViewModel(
            [

            ]
        );
    }
}
