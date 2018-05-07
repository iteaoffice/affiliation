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

use Project\Acl\Assertion\Project as ProjectAssertion;
use Zend\View\Model\ViewModel;

/**
 * Class CommunityController
 *
 * @package Affiliation\Controller
 */
class CommunityController extends AffiliationAbstractController
{
    /**
     * @return ViewModel
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function affiliationAction(): ViewModel
    {
        $affiliation = $this->getAffiliationService()->findAffiliationById($this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $this->getProjectService()->addResource($affiliation->getProject(), ProjectAssertion::class);
        $hasProjectEditRights = $this->isAllowed($affiliation->getProject(), 'edit-community');

        return new ViewModel(
            [
                'affiliationService'    => $this->getAffiliationService(),
                'affiliation'           => $affiliation,
                'contactsInAffiliation' => $this->getContactService()->findContactsInAffiliation($affiliation),
                'projectService'        => $this->getProjectService(),
                'contractService'       => $this->getContractService(),
                'workpackageService'    => $this->getWorkpackageService(),
                'latestVersion'         => $this->getProjectService()->getLatestProjectVersion(
                    $affiliation->getProject()
                ),
                'contractVersion'       => $this->getContractService()->findLatestContractVersionByAffiliation(
                    $affiliation
                ),
                'versionType'           => $this->getProjectService()->getNextMode(
                    $affiliation->getProject()
                )->versionType,
                'hasProjectEditRights'  => $hasProjectEditRights,
                'reportService'         => $this->getReportService(),
                'versionService'        => $this->getVersionService(),
                'invoiceService'        => $this->getInvoiceService(),
                'contactService'        => $this->getContactService(),
                'organisationService'   => $this->getOrganisationService(),
                'parentService'         => $this->getParentService(),
                'callService'           => $this->getCallService(),
                'invoiceViaParent'      => $this->getInvoiceModuleOptions()->getInvoiceViaParent()
            ]
        );
    }

    /**
     * @return ViewModel
     */
    public function paymentSheetAction(): ViewModel
    {
        $affiliation = $this->getAffiliationService()->findAffiliationById($this->params('id'));
        $contract = $this->params('contract');

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $year = (int)$this->params('year');
        $period = (int)$this->params('period');

        return new ViewModel(
            [
                'year'               => $year,
                'period'             => $period,
                'useContractData'    => null !== $contract,
                'affiliationService' => $this->getAffiliationService(),
                'affiliation'        => $affiliation,

            ]
        );
    }

    /**
     * @return \Zend\Stdlib\ResponseInterface|ViewModel
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function paymentSheetPdfAction()
    {
        $affiliation = $this->getAffiliationService()->findAffiliationById($this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $year = (int)$this->params('year');
        $period = (int)$this->params('period');

        $renderPaymentSheet = $this->renderPaymentSheet(
            $affiliation,
            $year,
            $period,
            null !== $this->params('contract')
        );
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 36000))
            ->addHeaderLine('Cache-Control: max-age=36000, must-revalidate')->addHeaderLine('Pragma: public')
            ->addHeaderLine(
                'Content-Disposition',
                'attachment; filename="' . sprintf(
                    "payment_sheet_%s_%s_%sH.pdf",
                    $affiliation->getOrganisation()->getDocRef(),
                    $year,
                    $period
                ) . '"'
            )
            ->addHeaderLine('Content-Type: application/pdf')
            ->addHeaderLine('Content-Length', \strlen($renderPaymentSheet->getPDFData()));
        $response->setContent($renderPaymentSheet->getPDFData());

        return $response;
    }
}
