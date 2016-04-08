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
use Project\Acl\Assertion\Project as ProjectAssertion;
use Zend\View\Model\ViewModel;

/**
 * @category    Affiliation
 */
class CommunityController extends AffiliationAbstractController
{
    /**
     * Show the details of 1 affiliation.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function affiliationAction()
    {
        $affiliation = $this->getAffiliationService()->findAffiliationById($this->params('id'));

        if (is_null($affiliation)) {
            return $this->notFoundAction();
        }

        $this->getProjectService()->addResource($affiliation->getProject(), ProjectAssertion::class);
        $hasProjectEditRights = $this->isAllowed($affiliation->getProject(), 'edit-community');

        return new ViewModel([
            'affiliationService'    => $this->getAffiliationService(),
            'affiliation'           => $affiliation,
            'contactsInAffiliation' => $this->getContactService()->findContactsInAffiliation($affiliation),
            'projectService'        => $this->getProjectService(),
            'workpackageService'    => $this->getWorkpackageService(),
            'latestVersion'         => $this->getProjectService()->getLatestProjectVersion($affiliation->getProject()),
            'versionType'           => $this->getProjectService()->getNextMode($affiliation->getProject())->versionType,
            'hasProjectEditRights'  => $hasProjectEditRights,
            'requireMembership'     => $this->getProgramModuleOptions()->getRequireMembership(),
            'reportService'         => $this->getReportService(),
            'versionService'        => $this->getVersionService(),
            'invoiceService'        => $this->getInvoiceService(),
        ]);
    }

    /**
     * @return ViewModel
     */
    public function paymentSheetAction()
    {
        $affiliation = $this->getAffiliationService()->findAffiliationById($this->params('id'));

        if (is_null($affiliation)) {
            return $this->notFoundAction();
        }

        $year = (int)$this->params('year');
        $period = (int)$this->params('period');

        return new ViewModel([
            'year'               => $year,
            'period'             => $period,
            'affiliationService' => $this->getAffiliationService(),
            'affiliation'        => $affiliation,

        ]);
    }


    public function paymentSheetPdfAction()
    {
        $affiliation = $this->getAffiliationService()->findAffiliationById($this->params('id'));

        if (is_null($affiliation)) {
            return $this->notFoundAction();
        }

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
}