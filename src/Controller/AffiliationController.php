<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Controller;

use Affiliation\Acl\Assertion\AffiliationAssertion;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\QuestionnaireService;
use Application\Service\AssertionService;
use Calendar\Service\CalendarService;
use Contact\Service\ContactService;
use Invoice\Options\ModuleOptions;
use Invoice\Service\InvoiceService;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Organisation\Service\OrganisationService;
use Organisation\Service\ParentService;
use Project\Acl\Assertion\Project;
use Project\Service\ContractService;
use Project\Service\ProjectService;
use Project\Service\ReportService;
use Project\Service\VersionService;
use Project\Service\WorkpackageService;

use function sprintf;
use function strlen;

/**
 * Class AffiliationController
 * @package Affiliation\Controller
 */
final class AffiliationController extends AffiliationAbstractController
{
    private AffiliationService $affiliationService;
    private QuestionnaireService $questionnaireService;
    private ProjectService $projectService;
    private VersionService $versionService;
    private ContactService $contactService;
    private OrganisationService $organisationService;
    private ReportService $reportService;
    private ContractService $contractService;
    private WorkpackageService $workPackageService;
    private InvoiceService $invoiceService;
    private CalendarService $calendarService;
    private ParentService $parentService;
    private ModuleOptions $invoiceModuleOptions;
    private AssertionService $assertionService;

    public function __construct(
        AffiliationService $affiliationService,
        QuestionnaireService $questionnaireService,
        ProjectService $projectService,
        VersionService $versionService,
        ContactService $contactService,
        OrganisationService $organisationService,
        ReportService $reportService,
        ContractService $contractService,
        WorkpackageService $workPackageService,
        InvoiceService $invoiceService,
        CalendarService $calendarService,
        ParentService $parentService,
        ModuleOptions $invoiceModuleOptions,
        AssertionService $assertionService
    ) {
        $this->affiliationService   = $affiliationService;
        $this->questionnaireService = $questionnaireService;
        $this->projectService       = $projectService;
        $this->versionService       = $versionService;
        $this->contactService       = $contactService;
        $this->organisationService  = $organisationService;
        $this->reportService        = $reportService;
        $this->contractService      = $contractService;
        $this->workPackageService   = $workPackageService;
        $this->invoiceService       = $invoiceService;
        $this->calendarService      = $calendarService;
        $this->parentService        = $parentService;
        $this->invoiceModuleOptions = $invoiceModuleOptions;
        $this->assertionService     = $assertionService;
    }

    public function detailsAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        //Create the affiliation resource
        $this->assertionService->addResource($affiliation, AffiliationAssertion::class);

        return new ViewModel(
            [
                'affiliationService' => $this->affiliationService,
                'projectService'     => $this->projectService,
                'affiliation'        => $affiliation,
                'project'            => $affiliation->getProject(),
                'projectChecklist'   => $this->projectChecklist($affiliation->getProject()),
                'showParentTab'      => $this->invoiceModuleOptions->getInvoiceViaParent(),
                'tab'                => 'details'
            ]
        );
    }

    public function descriptionAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        return new ViewModel(
            [
                'affiliation'      => $affiliation,
                'projectChecklist' => $this->projectChecklist($affiliation->getProject()),
                'showParentTab'    => $this->invoiceModuleOptions->getInvoiceViaParent(),
                'tab'              => 'description'
            ]
        );
    }

    public function marketAccessAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        return new ViewModel(
            [
                'affiliation'      => $affiliation,
                'projectChecklist' => $this->projectChecklist($affiliation->getProject()),
                'showParentTab'    => $this->invoiceModuleOptions->getInvoiceViaParent(),
                'tab'              => 'market-access'
            ]
        );
    }

    public function costsAndEffortAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        //Create the affiliation resource
        $this->assertionService->addResource($affiliation, AffiliationAssertion::class);
        //Create the project resource
        $this->assertionService->addResource($affiliation->getProject(), Project::class);

        return new ViewModel(
            [
                'affiliationService'    => $this->affiliationService,
                'projectService'        => $this->projectService,
                'versionService'        => $this->versionService,
                'contractService'       => $this->contractService,
                'workPackageService'    => $this->workPackageService,
                'affiliation'           => $affiliation,
                'project'               => $affiliation->getProject(),
                'years'                 => $this->projectService->parseYearRange($affiliation->getProject(), true),
                'projectChecklist'      => $this->projectChecklist($affiliation->getProject()),
                'latestVersion'         => $this->projectService->getLatestNotRejectedProjectVersion($affiliation->getProject()),
                'latestContractVersion' => $this->contractService->findLatestContractVersionByAffiliation($affiliation),
                'useContract'           => AffiliationService::useActiveContract($affiliation),
                'showParentTab'         => $this->invoiceModuleOptions->getInvoiceViaParent(),
                'tab'                   => 'costs-and-effort'
            ]
        );
    }

    public function projectVersionsAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        return new ViewModel(
            [
                'affiliationService' => $this->affiliationService,
                'versionService'     => $this->versionService,
                'projectChecklist'   => $this->projectChecklist($affiliation->getProject()),
                'affiliation'        => $affiliation,
                'showParentTab'      => $this->invoiceModuleOptions->getInvoiceViaParent(),
                'tab'                => 'project-versions'
            ]
        );
    }

    public function financialAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        return new ViewModel(
            [
                'affiliation'         => $affiliation,
                'invoiceService'      => $this->invoiceService,
                'contactService'      => $this->contactService,
                'projectChecklist'    => $this->projectChecklist($affiliation->getProject()),
                'organisationService' => $this->organisationService,
                'showParentTab'       => $this->invoiceModuleOptions->getInvoiceViaParent(),
                'tab'                 => 'financial'
            ]
        );
    }

    public function contractAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        return new ViewModel(
            [
                'affiliation'      => $affiliation,
                'showParentTab'    => $this->invoiceModuleOptions->getInvoiceViaParent(),
                'projectChecklist' => $this->projectChecklist($affiliation->getProject()),
                'tab'              => 'financial'
            ]
        );
    }

    public function parentAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        return new ViewModel(
            [
                'affiliation'      => $affiliation,
                'parentService'    => $this->parentService,
                'projectChecklist' => $this->projectChecklist($affiliation->getProject()),
                'showParentTab'    => $this->invoiceModuleOptions->getInvoiceViaParent(),
                'tab'              => 'financial'
            ]
        );
    }

    public function paymentSheetAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));
        $contract    = $this->params('contract');

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        return new ViewModel(
            [
                'year'               => date('Y'),
                'period'             => date('n') <= 6 ? 1 : 2,
                'useContractData'    => null !== $contract,
                'affiliationService' => $this->affiliationService,
                'affiliation'        => $affiliation,
                'projectChecklist'   => $this->projectChecklist($affiliation->getProject()),
                'showParentTab'      => $this->invoiceModuleOptions->getInvoiceViaParent(),
                'tab'                => 'financial'
            ]
        );
    }

    public function contactsAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        return new ViewModel(
            [
                'affiliation'           => $affiliation,
                'projectChecklist'      => $this->projectChecklist($affiliation->getProject()),
                'contactsInAffiliation' => $this->contactService->findContactsInAffiliation($affiliation),
                'showParentTab'         => $this->invoiceModuleOptions->getInvoiceViaParent(),
                'tab'                   => 'management'
            ]
        );
    }

    public function reportingAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        return new ViewModel(
            [
                'affiliation'      => $affiliation,
                'projectChecklist' => $this->projectChecklist($affiliation->getProject()),
                'reportService'    => $this->reportService,
                'versionService'   => $this->versionService,
                'showParentTab'    => $this->invoiceModuleOptions->getInvoiceViaParent(),
                'tab'              => 'management'
            ]
        );
    }

    public function achievementsAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        return new ViewModel(
            [
                'affiliationService'  => $this->affiliationService,
                'projectChecklist'    => $this->projectChecklist($affiliation->getProject()),
                'latestReviewMeeting' => $this->calendarService->findLatestProjectCalendar($affiliation->getProject()),
                'affiliation'         => $affiliation,
                'showParentTab'       => $this->invoiceModuleOptions->getInvoiceViaParent(),
                'tab'                 => 'management'
            ]
        );
    }

    public function questionnairesAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        //Create the affiliation resource
        $this->assertionService->addResource($affiliation, AffiliationAssertion::class);

        $questionnaires = [];
        if ($this->isAllowed($affiliation, 'list-questionnaire')) {
            $questionnaires = $this->questionnaireService->getAvailableQuestionnaires($affiliation);
        }

        return new ViewModel(
            [
                'affiliation'      => $affiliation,
                'projectChecklist' => $this->projectChecklist($affiliation->getProject()),
                'questionnaires'   => $questionnaires,
                'showParentTab'    => $this->invoiceModuleOptions->getInvoiceViaParent(),
                'tab'              => 'management'
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

        $year   = (int)$this->params('year');
        $period = (int)$this->params('period');

        $renderPaymentSheet = $this->renderPaymentSheet(
            $affiliation,
            $year,
            $period,
            null !== $this->params('contract')
        );

        $pdfData = $renderPaymentSheet->getPDFData();
        $response->getHeaders()
            ->addHeaderLine('Pragma: public')
            ->addHeaderLine(
                'Content-Disposition',
                'attachment; filename="' . sprintf(
                    'Payment Sheet %s (%s-%sH).pdf',
                    $affiliation->parseBranchedName(),
                    $year,
                    $period
                ) . '"'
            )
            ->addHeaderLine('Content-Type: application/pdf')
            ->addHeaderLine('Content-Length', strlen($pdfData));
        $response->setContent($pdfData);

        return $response;
    }
}
