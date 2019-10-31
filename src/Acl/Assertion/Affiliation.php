<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Acl\Assertion;

use Admin\Entity\Access;
use Affiliation\Entity\Affiliation as AffiliationEntity;
use Affiliation\Service\AffiliationService;
use Interop\Container\ContainerInterface;
use Project\Acl\Assertion\Project;
use Project\Service\ProjectService;
use Project\Service\ReportService;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

/**
 * Class Affiliation.
 */
final class Affiliation extends AbstractAssertion
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
     * @var ReportService
     */
    private $reportService;
    /**
     * @var Project
     */
    private $projectAssertion;
    /**
     * @var QuestionnaireAssertion
     */
    private $questionnaireAssertion;


    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->affiliationService = $container->get(AffiliationService::class);
        $this->projectService = $container->get(ProjectService::class);
        $this->reportService = $container->get(ReportService::class);
        $this->projectAssertion = $container->get(Project::class);
        $this->questionnaireAssertion = $container->get(QuestionnaireAssertion::class);
    }

    public function assert(
        Acl $acl,
        RoleInterface $role = null,
        ResourceInterface $affiliation = null,
        $privilege = null
    ): bool {
        $this->setPrivilege($privilege);
        $id = $this->getId();

        if (!($affiliation instanceof AffiliationEntity) && (null !== $id)) {
            $affiliation = $this->affiliationService->findAffiliationById((int)$id);
        }

        if (null === $affiliation) {
            return true;
        }

        switch ($this->getPrivilege()) {
            case 'view-community':
                if ($this->contactService->contactHasPermit($this->contact, 'view', $affiliation)) {
                    return true;
                }

                // When the person has view rights on the project, the affiliation can also be viewed
                return $this->projectAssertion->assert($acl, $role, $affiliation->getProject(), 'view-community');
            case 'add-associate':
            case 'manage-associate':
            case 'edit-cost-and-effort':
            case 'edit-affiliation':
            case 'edit-description':
            case 'edit-community':
                if ($this->projectService->isStopped($affiliation->getProject())) {
                    return false;
                }
                return $this->contactService->contactHasPermit(
                    $this->contact,
                    ['edit', 'edit_proxy', 'financial'],
                    $affiliation
                );
            case 'list-questionnaire':
                return $this->questionnaireAssertion->assert($acl, $role, $affiliation, 'list-community');
            case 'update-effort-spent':
                // Block access to an already closed report
                $reportId = $this->getRouteMatch()->getParam('report');
                if (null !== $reportId) {
                    //Find the corresponding report
                    $report = $this->reportService->findReportById((int)$reportId);
                    if (null === $report || $this->reportService->isFinal($report)) {
                        return false;
                    }
                }

                if ($this->projectService->isStopped($affiliation->getProject())) {
                    return false;
                }
                if ($this->contactService->contactHasPermit($this->contact, 'edit', $affiliation)) {
                    return true;
                }

                break;
            case 'edit-financial':
            case 'payment-sheet':
            case 'payment-sheet-contract':
            case 'payment-sheet-pdf':
            case 'payment-sheet-pdf-contract':
                return $this->contactService->contactHasPermit($this->contact, 'financial', $affiliation);
            case 'view-admin':
            case 'edit-admin':
            case 'add-associate-admin':
            case 'merge-admin':
            case 'payment-sheet-admin':
            case 'missing-affiliation-parent':
                return $this->rolesHaveAccess(Access::ACCESS_OFFICE);
        }

        return false;
    }
}
