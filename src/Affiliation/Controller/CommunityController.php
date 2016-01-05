<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Controller;

use Affiliation\Entity;
use Affiliation\Form\Affiliation;
use Affiliation\Form\Financial;
use Contact\Entity\Address;
use Contact\Entity\AddressType;
use Contact\Service\ContactServiceAwareInterface;
use Invoice\Service\InvoiceServiceAwareInterface;
use Member\Service\MemberServiceAwareInterface;
use Organisation\Entity\Organisation;
use Organisation\Service\OrganisationServiceAwareInterface;
use Program\Service\ProgramServiceAwareInterface;
use Project\Acl\Assertion\Project as ProjectAssertion;
use Project\Service\ProjectServiceAwareInterface;
use Project\Service\ReportServiceAwareInterface;
use Project\Service\VersionServiceAwareInterface;
use Project\Service\WorkpackageServiceAwareInterface;
use Zend\View\Model\ViewModel;

/**
 * @category    Affiliation
 */
class CommunityController extends AffiliationAbstractController implements
    ProjectServiceAwareInterface,
    WorkpackageServiceAwareInterface,
    OrganisationServiceAwareInterface,
    ReportServiceAwareInterface,
    ContactServiceAwareInterface,
    ProgramServiceAwareInterface,
    VersionServiceAwareInterface,
    InvoiceServiceAwareInterface,
    MemberServiceAwareInterface
{
    /**
     * Show the details of 1 affiliation.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function affiliationAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId($this->params('id'));
        $this->getProjectService()->setProject($affiliationService->getAffiliation()->getProject());

        $this->getProjectService()->addResource(
            $affiliationService->getAffiliation()->getProject(),
            ProjectAssertion::class
        );
        $hasProjectEditRights = $this->isAllowed($affiliationService->getAffiliation()->getProject(), 'edit-community');

        return new ViewModel([
            'affiliationService'    => $affiliationService,
            'contactsInAffiliation' => $this->getContactService()
                ->findContactsInAffiliation($affiliationService->getAffiliation()),
            'projectService'        => $this->getProjectService(),
            'workpackageService'    => $this->getWorkpackageService(),
            'latestVersion'         => $this->getProjectService()->getLatestProjectVersion(),
            'versionType'           => $this->getProjectService()->getNextMode()->versionType,
            'hasProjectEditRights'  => $hasProjectEditRights,
            'requireMembership'     => $this->getProgramService()->getOptions()->getRequireMembership(),
            'reportService'         => $this->getReportService(),
            'versionService'        => $this->getVersionService(),
            'invoiceService'        => $this->getInvoiceService()

        ]);
    }

    /**
     * @return ViewModel
     */
    public function paymentSheetAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId($this->params('id'));

        $year = (int)$this->params('year');
        $period = (int)$this->params('period');

//        $projectService = $this->getProjectService()->setProject($affiliationService->getAffiliation()->getProject());
//
//        $latestVersion = $projectService->getLatestProjectVersion();
//        $versionService = $this->getVersionService()->setVersion($latestVersion);
//
//        $contactService = $this->getContactService()->setContact($affiliationService->getAffiliation()->getContact());
//
//        /**
//         * The financial contact can be the billing organsisation (organisationFinancial) or the technical contact if not provided
//         */
//        if (!is_null($affiliationService->getAffiliation()->getFinancial())) {
//            $financialContactService = $this->getContactService()->setContact($affiliationService->getAffiliation()->getFinancial()->getContact());
//        } else {
//            $financialContactService = $this->getContactService()->setContact($affiliationService->getAffiliation()->getContact());
//        }


        return new ViewModel([
            'year'               => $year,
            'period'             => $period,
            'affiliationService' => $affiliationService,

        ]);
    }


    public function paymentSheetPdfAction()
    {
        $affiliation = $this->getAffiliationService()->setAffiliationId($this->params('id'))->getAffiliation();
        $year = (int)$this->params('year');
        $period = (int)$this->params('period');


        $renderPaymentSheet = $this->renderPaymentSheet()->render($affiliation, $year, $period);
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 36000))
            ->addHeaderLine("Cache-Control: max-age=36000, must-revalidate")->addHeaderLine("Pragma: public")
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . sprintf(
                "payment_sheet_%s_%s_%sH.pdf",
                $affiliation->getOrganisation()->getDocRef(),
                $year,
                $period
            ) . '"')
            ->addHeaderLine('Content-Type: application/pdf')
            ->addHeaderLine('Content-Length', strlen($renderPaymentSheet->getPDFData()));
        $response->setContent($renderPaymentSheet->getPDFData());

        return $response;
    }


    /**
     * Edit a affiliation.
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId($this->params('id'));
        $projectService = $this->getProjectService()->setProject($affiliationService->getAffiliation()->getProject());
        $formData = [];
        $formData['affiliation'] = sprintf(
            "%s|%s",
            $affiliationService->getAffiliation()->getOrganisation()->getId(),
            $affiliationService->getAffiliation()->getBranch()
        );
        $formData['technical'] = $affiliationService->getAffiliation()->getContact()->getId();
        $formData['valueChain'] = $affiliationService->getAffiliation()->getValueChain();
        /*
         * Check if the organisation has a financial contact
         */
        if (!is_null($affiliationService->getAffiliation()->getOrganisation()->getFinancial())) {
            $formData['preferredDelivery'] = $affiliationService->getAffiliation()->getOrganisation()->getFinancial()
                ->getEmail();
        }
        /*
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
            /*
             * When the deactivate button is pressed, handle this in the service layer
             */
            if (!is_null($formData['deactivate'])) {
                $this->getAffiliationService()->deactivateAffiliation($affiliation);
                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(sprintf(
                        _("txt-affiliation-%s-has-successfully-been-deactivated"),
                        $affiliationService->getAffiliation()
                    ));

                return $this->redirect()->toRoute(
                    'community/project/project/partners',
                    ['docRef' => $projectService->getProject()->getDocRef()]
                );
            }
            /*
             * When the deactivate button is pressed, handle this in the service layer
             */
            if (!is_null($formData['reactivate'])) {
                $this->getAffiliationService()->reactivateAffiliation($affiliation);
                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(sprintf(
                        _("txt-affiliation-%s-has-successfully-been-reactivated"),
                        $affiliationService->getAffiliation()
                    ));

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
            /*
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
            /*
             * Handle the preferred delivery for the organisation (OrganisationFinancial)
             */
            if (is_null($organisationFinancial = $affiliation->getOrganisation()->getFinancial())) {
                $organisationFinancial = new \Organisation\Entity\Financial();
                $organisationFinancial->setOrganisation($affiliation->getOrganisation());
            }
            $organisationFinancial->setEmail((bool)$formData['preferredDelivery']);
            $this->getOrganisationService()->updateEntity($organisationFinancial);
            $this->flashMessenger()->setNamespace('success')
                ->addMessage(sprintf(
                    _("txt-affiliation-%s-has-successfully-been-updated"),
                    $affiliationService->getAffiliation()
                ));

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
    public function editFinancialAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId($this->params('id'));
        $projectService = $this->getProjectService()->setProject($affiliationService->getAffiliation()->getProject());
        $organisationService = $this->getOrganisationService()->setOrganisation($affiliationService->getAffiliation()
            ->getOrganisation());
        $formData = [];
        $branch = $affiliationService->getAffiliation()->getFinancial()->getBranch();
        $formData['attention'] = $affiliationService->getAffiliation()->getFinancial()->getContact()->getDisplayName();
        $contactService = $this->getContactService()->setContact($affiliationService->getAffiliation()->getFinancial()
            ->getContact());
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
            /*
             * This form is a aggregation of multiple form elements, so we treat it step by step
             */
            /*
             * If the organisation or country has changed, find the new
             */
            if ($formData['organisation'] !== $organisationService->parseOrganisationWithBranch($branch)
                || intval($formData['country']) !== $financialAddress->getCountry()->getId()
            ) {
                /*
                 * The organisation, or country has changed, so try to find this country in the database
                 */
                $organisation = $this->getOrganisationService()
                    ->findOrganisationByNameCountry(
                        trim($formData['organisation']),
                        $this->getGeneralService()->findEntityById('Country', $formData['country'])
                    );
                /*
                 * If the organisation is not found, create it
                 */
                if (is_null($organisation)) {
                    $organisation = new Organisation();
                    $organisation->setOrganisation($formData['organisation']);
                    $organisation->setCountry($this->getGeneralService()
                        ->findEntityById('Country', $formData['country']));
                    /*
                     * @var OrganisationType
                     */
                    $organisationType = $this->getOrganisationService()->getEntityManager()
                        ->getReference('Organisation\Entity\Type', 0);
                    $organisation->setType($organisationType);
                }
                $affiliationFinancial = $this->getAffiliationService()->getAffiliation()->getFinancial();
                $affiliationFinancial->setOrganisation($organisation);
                $affiliationFinancial->setBranch(trim(substr(
                    $formData['organisation'],
                    strlen($organisation->getOrganisation())
                )));
                $this->getAffiliationService()->updateEntity($affiliationFinancial);
            }
            /*
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
            /*
             * save the financial address
             */
            $contactService = $this->getContactService()->setContact($affiliationService->getAffiliation()
                ->getFinancial()->getContact());
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
                ->addMessage(sprintf(
                    _("txt-affiliation-%s-has-successfully-been-updated"),
                    $affiliationService->getAffiliation()
                ));

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
     * @return ViewModel
     */
    public function editDescriptionAction()
    {
        return new ViewModel([

        ]);
    }
}
