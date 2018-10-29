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

namespace Affiliation\Acl\Assertion;

use Admin\Entity\Access;
use Affiliation\Entity\Affiliation as AffiliationEntity;
use Affiliation\Service\AffiliationService;
use Interop\Container\ContainerInterface;
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

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->affiliationService = $container->get(AffiliationService::class);
        $this->projectService = $container->get(ProjectService::class);
        $this->reportService = $container->get(ReportService::class);
    }

    public function assert(
        Acl $acl,
        RoleInterface $role = null,
        ResourceInterface $affiliation = null,
        $privilege = null
    ): bool {
        $this->setPrivilege($privilege);
        $id = $this->getId();

        if (!$affiliation instanceof AffiliationEntity && null !== $id) {
            $affiliation = $this->affiliationService->findAffiliationById((int)$id);
        }


        switch ($this->getPrivilege()) {
            case 'view-community':
                if ($this->contactService->contactHasPermit($this->contact, 'view', $affiliation)) {
                    return true;
                }

                //whe the person has view rights on the project, the affiliation can also be viewed
                return $this->contactService->contactHasPermit($this->contact, 'view', $affiliation->getProject());
            case 'add-associate':
            case 'manage-associate':
            case 'edit-cost-and-effort':
            case 'edit-affiliation':
            case 'edit-description':
            case 'edit-community':
                if ($this->projectService->isStopped($affiliation->getProject())) {
                    return false;
                }
                if ($this->contactService->contactHasPermit($this->contact, 'edit', $affiliation)) {
                    return true;
                }
                if ($this->contactService->contactHasPermit($this->contact, ['edit', 'edit_proxy'], $affiliation)) {
                    return true;
                }
                if ($this->contactService->contactHasPermit($this->contact, 'financial', $affiliation)) {
                    return true;
                }
                return false;
                break;
            case 'update-effort-spent':
                return true;
                //Block access to an already closed report
                $reportId = $this->getRouteMatch()->getParam('report');
            if (null !== $reportId) {
                //Find the corresponding report
                $report = $this->reportService->findReportById($reportId);
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
                if ($this->contactService->contactHasPermit($this->contact, 'financial', $affiliation)) {
                    return true;
                }

                break;

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
