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

use Affiliation\Service\AffiliationService;
use Application\Service\AssertionService;
use Contact\Service\ContactService;
use Invoice\Options\ModuleOptions;
use Invoice\Service\InvoiceService;
use Organisation\Service\OrganisationService;
use Organisation\Service\ParentService;
use Program\Service\CallService;
use Project\Acl\Assertion\Project;
use Project\Service\ContractService;
use Project\Service\ProjectService;
use Project\Service\ReportService;
use Project\Service\VersionService;
use Project\Service\WorkpackageService;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

/**
 * Class CommunityController
 *
 * @package Affiliation\Controller
 */
final class CommunityController extends AffiliationAbstractController
{
    /**
     * @var AffiliationService
     */
    private $affiliationService;
    /**
     * @var ProjectService
     */
    private $projectService;
    /**
     * @var VersionService
     */
    private $versionService;
    /**
     * @var ContactService
     */
    private $contactService;
    /**
     * @var OrganisationService
     */
    private $organisationService;
    /**
     * @var ReportService
     */
    private $reportService;
    /**
     * @var ContractService
     */
    private $contractService;
    /**
     * @var WorkpackageService
     */
    private $workpackageService;
    /**
     * @var InvoiceService
     */
    private $invoiceService;
    /**
     * @var ParentService
     */
    private $parentService;
    /**
     * @var CallService
     */
    private $callService;
    /**
     * @var ModuleOptions
     */
    private $invoiceModuleOptions;
    /**
     * @var AssertionService
     */
    private $assertionService;

    public function __construct(
        AffiliationService $affiliationService,
        ProjectService $projectService,
        VersionService $versionService,
        ContactService $contactService,
        OrganisationService $organisationService,
        ReportService $reportService,
        ContractService $contractService,
        WorkpackageService $workpackageService,
        InvoiceService $invoiceService,
        ParentService $parentService,
        CallService $callService,
        ModuleOptions $invoiceModuleOptions,
        AssertionService $assertionService
    ) {
        $this->affiliationService = $affiliationService;
        $this->projectService = $projectService;
        $this->versionService = $versionService;
        $this->contactService = $contactService;
        $this->organisationService = $organisationService;
        $this->reportService = $reportService;
        $this->contractService = $contractService;
        $this->workpackageService = $workpackageService;
        $this->invoiceService = $invoiceService;
        $this->parentService = $parentService;
        $this->callService = $callService;
        $this->invoiceModuleOptions = $invoiceModuleOptions;
        $this->assertionService = $assertionService;
    }


    public function affiliationAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $this->assertionService->addResource($affiliation->getProject(), Project::class);
        $hasProjectEditRights = $this->isAllowed($affiliation->getProject(), 'edit-community');

        return new ViewModel(
            [
                'affiliationService'    => $this->affiliationService,
                'affiliation'           => $affiliation,
                'contactsInAffiliation' => $this->contactService->findContactsInAffiliation($affiliation),
                'projectService'        => $this->projectService,
                'contractService'       => $this->contractService,
                'workpackageService'    => $this->workpackageService,
                'latestVersion'         => $this->projectService->getLatestProjectVersion(
                    $affiliation->getProject()
                ),
                'contractVersion'       => $this->contractService->findLatestContractVersionByAffiliation(
                    $affiliation
                ),
                'versionType'           => $this->projectService->getNextMode(
                    $affiliation->getProject()
                )->getVersionType(),
                'hasProjectEditRights'  => $hasProjectEditRights,
                'reportService'         => $this->reportService,
                'versionService'        => $this->versionService,
                'invoiceService'        => $this->invoiceService,
                'contactService'        => $this->contactService,
                'organisationService'   => $this->organisationService,
                'parentService'         => $this->parentService,
                'callService'           => $this->callService,
                'invoiceViaParent'      => $this->invoiceModuleOptions->getInvoiceViaParent()
            ]
        );
    }

    public function paymentSheetAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));
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
                'affiliationService' => $this->affiliationService,
                'affiliation'        => $affiliation,

            ]
        );
    }

    public function paymentSheetPdfAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $response->setStatusCode(Response::STATUS_CODE_404);
        }

        $year = (int)$this->params('year');
        $period = (int)$this->params('period');

        $renderPaymentSheet = $this->renderPaymentSheet(
            $affiliation,
            $year,
            $period,
            null !== $this->params('contract')
        );

        $response->getHeaders()
            ->addHeaderLine('Pragma: public')
            ->addHeaderLine(
                'Content-Disposition',
                'attachment; filename="' . \sprintf(
                    'payment_sheet_%s_%s_%sH.pdf',
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
