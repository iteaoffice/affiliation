<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */

namespace Affiliation\Controller;

use Contact\Service\ContactServiceAwareInterface;
use Invoice\Service\InvoiceServiceAwareInterface;
use Organisation\Service\OrganisationServiceAwareInterface;
use Project\Service\ProjectServiceAwareInterface;
use Project\Service\VersionServiceAwareInterface;
use Zend\View\Model\ViewModel;

/**
 *
 */
class AffiliationManagerController extends AffiliationAbstractController implements
    ContactServiceAwareInterface,
    ProjectServiceAwareInterface,
    VersionServiceAwareInterface,
    OrganisationServiceAwareInterface,
    InvoiceServiceAwareInterface
{
    /**
     * @return ViewModel
     */
    public function viewAction()
    {
        return new ViewModel();
    }

    /**
     * @return ViewModel
     */
    public function paymentSheetAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId($this->params('id'));

        $year = (int) $this->params('year');
        $period = (int) $this->params('period');

        $projectService = $this->getProjectService()->setProject($affiliationService->getAffiliation()->getProject());

        $latestVersion = $projectService->getLatestProjectVersion();
        $versionService = $this->getVersionService()->setVersion($latestVersion);

        $contactService = clone $this->getContactService()->setContact($affiliationService->getAffiliation()->getContact());
        $financialContactService = clone $this->getContactService()->setContact($affiliationService->getAffiliation()->getFinancial()->getContact());

        return new ViewModel([
            'year'                           => $year,
            'period'                         => $period,
            'affiliationService'             => $affiliationService,
            'projectService'                 => $projectService,
            'contactService'                 => $contactService,
            'financialContactService'        => $financialContactService,
            'organisationService'            => $this->getOrganisationService(),
            'invoiceMethod'                  => $this->getInvoiceService()->findInvoiceMethod($projectService->getProject()->getCall()->getProgram()),
            'invoiceService'                 => $this->getInvoiceService(),
            'versionService'                 => $versionService,
            'versionContributionInformation' => $versionService->getProjectVersionContributionInformation(
                $affiliationService->getAffiliation(),
                $latestVersion,
                $year
            )
        ]);
    }

    public function paymentSheetPdfAction()
    {
        $affiliation = $this->getAffiliationService()->setAffiliationId($this->params('id'))->getAffiliation();
        $year = (int) $this->params('year');
        $period = (int) $this->params('period');

        $renderPaymentSheet = $this->renderPaymentSheet()->render($affiliation, $year, $period);
        $response = $this->getResponse();
        $response->getHeaders()
            ->addHeaderLine('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 36000))
            ->addHeaderLine("Cache-Control: max-age=36000, must-revalidate")
            ->addHeaderLine("Pragma: public")
            ->addHeaderLine(
                'Content-Disposition',
                'attachment; filename="' . $affiliation->getOrganisation()->getId() . '.pdf"'
            )
            ->addHeaderLine('Content-Type: application/pdf')
            ->addHeaderLine('Content-Length', strlen($renderPaymentSheet->getPDFData()));
        $response->setContent($renderPaymentSheet->getPDFData());

        return $response;
    }
}
