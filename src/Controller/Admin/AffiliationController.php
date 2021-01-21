<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

namespace Affiliation\Controller\Admin;

use Affiliation\Acl\Assertion\AffiliationAssertion;
use Affiliation\Controller\AffiliationAbstractController;
use Affiliation\Entity\Affiliation;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\QuestionnaireService;
use Application\Service\AssertionService;
use Calendar\Service\CalendarService;
use Contact\Service\ContactService;
use Invoice\Service\InvoiceService;
use Laminas\Http\Request;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\View\Model\ViewModel;
use Organisation\Service\OrganisationService;
use Organisation\Service\ParentService;
use Project\Acl\Assertion\Project;
use Project\Service\ContractService;
use Project\Service\ProjectService;
use Project\Service\ReportService;
use Project\Service\VersionService;
use Project\Service\WorkpackageService;

/**
 * Class ManagerController
 * @package Affiliation\Controller
 */
final class AffiliationController extends AffiliationAbstractController
{
    private AffiliationService $affiliationService;
    private QuestionnaireService $questionnaireService;
    private ProjectService $projectService;
    private VersionService $versionService;
    private ContractService $contractService;
    private ContactService $contactService;
    private OrganisationService $organisationService;
    private ReportService $reportService;
    private WorkpackageService $workPackageService;
    private InvoiceService $invoiceService;
    private CalendarService $calendarService;
    private ParentService $parentService;
    private AssertionService $assertionService;
    private TranslatorInterface $translator;

    public function __construct(
        AffiliationService $affiliationService,
        QuestionnaireService $questionnaireService,
        ProjectService $projectService,
        VersionService $versionService,
        ContractService $contractService,
        ContactService $contactService,
        OrganisationService $organisationService,
        ReportService $reportService,
        WorkpackageService $workPackageService,
        InvoiceService $invoiceService,
        CalendarService $calendarService,
        ParentService $parentService,
        AssertionService $assertionService,
        TranslatorInterface $translator
    )
    {
        $this->affiliationService   = $affiliationService;
        $this->questionnaireService = $questionnaireService;
        $this->translator           = $translator;
        $this->projectService       = $projectService;
        $this->versionService       = $versionService;
        $this->contractService      = $contractService;
        $this->contactService       = $contactService;
        $this->organisationService  = $organisationService;
        $this->reportService        = $reportService;
        $this->workPackageService   = $workPackageService;
        $this->invoiceService       = $invoiceService;
        $this->calendarService      = $calendarService;
        $this->parentService        = $parentService;
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
                'organisation'       => $affiliation->getOrganisation(),
                'tab'                => 'details'
            ]);
    }

    public function descriptionAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        return new ViewModel(
            [
                'affiliation' => $affiliation,
                'tab'         => 'description'
            ]);
    }

    public function marketAccessAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        return new ViewModel(
            [
                'affiliation' => $affiliation,
                'tab'         => 'market-access'
            ]);
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
                'latestVersion'         => $this->projectService->getLatestNotRejectedProjectVersion($affiliation->getProject()),
                'latestContractVersion' => $this->contractService->findLatestContractVersionByAffiliation($affiliation),
                'useContract'           => AffiliationService::useActiveContract($affiliation),
                'tab'                   => 'costs-and-effort'
            ]);
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
                'projectService'     => $this->projectService,
                'versionService'     => $this->versionService,
                'contractService'    => $this->contractService,
                'workPackageService' => $this->workPackageService,
                'affiliation'        => $affiliation,
                'project'            => $affiliation->getProject(),
                'years'              => $this->projectService->parseYearRange($affiliation->getProject(), true),
                'tab'                => 'project-versions'
            ]);
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
                'organisationService' => $this->organisationService,
                'tab'                 => 'financial'
            ]);
    }

    public function contractAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        return new ViewModel(
            [
                'affiliation' => $affiliation,
                'tab'         => 'contract'
            ]);
    }

    public function parentAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        return new ViewModel(
            [
                'affiliation'   => $affiliation,
                'parentService' => $this->parentService,
                'tab'           => 'parent'
            ]);
    }

    public function paymentSheetAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));
        $contract    = $this->params('contract');

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $year   = (int)$this->params('year');
        $period = (int)$this->params('period');

        return new ViewModel(
            [
                'year'               => $year,
                'period'             => $period,
                'useContractData'    => null !== $contract,
                'affiliationService' => $this->affiliationService,
                'affiliation'        => $affiliation,
                'tab'                => 'payment-sheet'
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
                'contactsInAffiliation' => $this->contactService->findContactsInAffiliation($affiliation),
                'contactService'        => $this->contactService,
                'tab'                   => 'contacts'
            ]);
    }

    public function reportingAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        return new ViewModel(
            [
                'affiliation'    => $affiliation,
                'reportService'  => $this->reportService,
                'versionService' => $this->versionService,
                'tab'            => 'reporting'
            ]);
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
                'latestReviewMeeting' => $this->calendarService->findLatestProjectCalendar($affiliation->getProject()),
                'affiliation'         => $affiliation,
                'tab'                 => 'achievements'
            ]);
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
                'affiliation'    => $affiliation,
                'questionnaires' => $questionnaires,
                'tab'            => 'questionnaires'
            ]);
    }

    public function mergeAction()
    {
        /** @var Request $request */
        $request         = $this->getRequest();
        $mainAffiliation = $this->affiliationService->findAffiliationById((int)$this->params('id'));

        if (null === $mainAffiliation) {
            return $this->notFoundAction();
        }

        $data = $request->getPost()->toArray();

        if (isset($data['merge'], $data['submit']) && $request->isPost()) {
            // Find the second affiliation
            /** @var Affiliation $otherAffiliation */
            $otherAffiliation  = $this->affiliationService->findAffiliationById((int)$data['merge']);
            $otherOrganisation = $otherAffiliation->getOrganisation();

            $result = $this->mergeAffiliation($mainAffiliation, $otherAffiliation);

            if ($result['success'] === true) {
                $this->flashMessenger()->addSuccessMessage(
                    sprintf(
                        $this->translator->translate(
                            'txt-merge-of-affiliation-%s-and-%s-in-project-%s-was-successful'
                        ),
                        $mainAffiliation->getOrganisation(),
                        $otherOrganisation,
                        $mainAffiliation->getProject()
                    )
                );
            } else {
                $this->flashMessenger()->addErrorMessage(
                    sprintf($this->translator->translate('txt-merge-failed:-%s'), $result['errorMessage'])
                );
            }

            return $this->redirect()->toRoute(
                'zfcadmin/affiliation/details',
                ['id' => $mainAffiliation->getId()]
            );
        }

        return new ViewModel(
            [
                'affiliationService'  => $this->affiliationService,
                'affiliation'         => $mainAffiliation,
                'merge'               => $data['merge'] ?? null,
                'projectService'      => $this->projectService,
                'organisationService' => $this->organisationService,
            ]
        );
    }
}
