<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */

namespace Affiliation\Controller\Plugin;

use Affiliation\Entity\Affiliation;
use Affiliation\Options\ModuleOptions;
use Affiliation\Service\AffiliationService;
use Contact\Service\ContactService;
use Invoice\Service\InvoiceService;
use Organisation\Service\OrganisationService;
use Project\Service\ProjectService;
use Project\Service\VersionService;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class RenderLoi.
 */
class RenderPaymentSheet extends AbstractPlugin
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @param  Affiliation    $affiliation
     * @param $year
     * @param $period
     * @return AffiliationPdf
     * @throws \Exception
     */
    public function render(Affiliation $affiliation, $year, $period)
    {
        $projectService = $this->getProjectService()->setProject($affiliation->getProject());

        $latestVersion = $projectService->getLatestProjectVersion();
        $versionService = $this->getVersionService()->setVersion($latestVersion);

        $contactService = clone $this->getContactService()->setContact($affiliation->getContact());
        $financialContactService = clone $this->getContactService()->setContact($affiliation->getFinancial()->getContact());

        $pdf = new AffiliationPdf();
        $pdf->setTemplate($this->getModuleOptions()->getPaymentSheetTemplate());
        $pdf->addPage();
        $pdf->SetFontSize(9);
        $twig = $this->getServiceLocator()->get('ZfcTwigRenderer');
        /*
         * Use the NDA object to render the filename
         */
//        $pdf->Write(0, $doa->parseFileName());
        $paymentSheetContent = $twig->render(
            'affiliation/pdf/payment-sheet',
            [
                'year'                           => $year,
                'period'                         => $period,
                'affiliationService'             => $this->getAffiliationService(),
                'projectService'                 => $projectService,
                'contactService'                 => $contactService,
                'financialContactService'        => $financialContactService,
                'organisationService'            => $this->getOrganisationService(),
                'invoiceMethod'                  => $this->getInvoiceService()->findInvoiceMethod($projectService->getProject()->getCall()->getProgram()),
                'invoiceService'                 => $this->getInvoiceService(),
                'versionService'                 => $versionService,
                'versionContributionInformation' => $versionService->getProjectVersionContributionInformation(
                    $affiliation,
                    $latestVersion,
                    $year
                )
            ]
        );
        $pdf->writeHTMLCell(0, 0, 14, 50, $paymentSheetContent);

        return $pdf;
    }

    /**
     * Gateway to the Project Service.
     *
     * @return ProjectService
     */
    public function getProjectService()
    {
        return $this->getServiceLocator()->get(ProjectService::class);
    }

    /**
     * Gateway to the Version Service.
     *
     * @return VersionService
     */
    public function getVersionService()
    {
        return $this->getServiceLocator()->get(VersionService::class);
    }

    /**
     * Gateway to the Affiliation Service.
     *
     * @return AffiliationService
     */
    public function getAffiliationService()
    {
        return $this->getServiceLocator()->get(AffiliationService::class);
    }

    /**
     * Gateway to the Organisation Service.
     *
     * @return OrganisationService
     */
    public function getOrganisationService()
    {
        return $this->getServiceLocator()->get(OrganisationService::class);
    }

    /**
     * Gateway to the Invoice Service.
     *
     * @return InvoiceService
     */
    public function getInvoiceService()
    {
        return $this->getServiceLocator()->get(InvoiceService::class);
    }

    /**
     * Gateway to the Contact Service.
     *
     * @return ContactService
     */
    public function getContactService()
    {
        return $this->getServiceLocator()->get('contact_contact_service');
    }

    /**
     * @return ModuleOptions
     */
    public function getModuleOptions()
    {
        return $this->getServiceLocator()->get('affiliation_module_options');
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return $this
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }
}
