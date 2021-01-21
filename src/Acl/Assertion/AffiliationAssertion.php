<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Acl\Assertion;

use Admin\Entity\Access;
use Affiliation\Entity\Affiliation as AffiliationEntity;
use Affiliation\Service\AffiliationService;
use Interop\Container\ContainerInterface;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;
use Project\Acl\Assertion\Project;
use Project\Service\ReportService;

/**
 * Class AffiliationAssertion
 * @package Affiliation\Acl\Assertion
 */
final class AffiliationAssertion extends AbstractAssertion
{
    private AffiliationService $affiliationService;
    private ReportService $reportService;
    private Project $projectAssertion;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->affiliationService = $container->get(AffiliationService::class);
        $this->reportService      = $container->get(ReportService::class);
        $this->projectAssertion   = $container->get(Project::class);
    }

    public function assert(
        Acl $acl,
        RoleInterface $role = null,
        ResourceInterface $affiliation = null,
        $privilege = null
    ): bool
    {
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
            case 'manage-associates':
            case 'edit-cost-and-effort':
            case 'edit-market-access':
            case 'edit-affiliation':
            case 'edit-description':
            case 'edit-community':
                return $this->contactService->contactHasPermit(
                    $this->contact,
                    ['edit', 'edit_proxy', 'financial'],
                    $affiliation
                );
            case 'technical-contact':
            case 'list-questionnaire':
                return $this->contactService->contactHasPermit(
                    $this->contact,
                    ['edit'],
                    $affiliation
                );
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
                return $this->contactService->contactHasPermit(
                    $this->contact,
                    ['edit', 'edit_proxy', 'financial'],
                    $affiliation
                );

                break;
            case 'edit-financial':
            case 'payment-sheet':
            case 'payment-sheet-contract':
            case 'payment-sheet-pdf':
            case 'payment-sheet-pdf-contract':
                return $this->contactService->contactHasPermit($this->contact, 'financial', $affiliation);
            default:
            case 'view-admin':
            case 'edit-admin':
            case 'add-associate-admin':
            case 'edit-description-admin':
            case 'edit-market-access-admin':
            case 'merge-admin':
            case 'payment-sheet-admin':
            case 'missing-parent':
                return $this->rolesHaveAccess(Access::ACCESS_OFFICE);
        }
    }
}
