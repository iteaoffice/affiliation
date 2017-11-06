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
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

/**
 * Class Affiliation.
 */
class Affiliation extends AssertionAbstract
{
    /**
     * Returns true if and only if the assertion conditions are met.
     *
     * This method is passed the ACL, Role, Resource, and privilege to which the authorization query applies. If the
     * $role, $affiliation, or $privilege parameters are null, it means that the query applies to all Roles, Resources, or
     * privileges, respectively.
     *
     * @param Acl $acl
     * @param RoleInterface $role
     * @param ResourceInterface|AffiliationEntity $affiliation
     * @param string $privilege
     *
     * @return bool
     */
    public function assert(
        Acl $acl,
        RoleInterface $role = null,
        ResourceInterface $affiliation = null,
        $privilege = null
    ): bool {
        $this->setPrivilege($privilege);
        $id = $this->getId();

        if (!$affiliation instanceof AffiliationEntity && !is_null($id)) {
            $affiliation = $this->getAffiliationService()->findAffiliationById($id);
        }

        switch ($this->getPrivilege()) {
            case 'view-community':
                if ($this->getContactService()->contactHasPermit($this->getContact(), 'view', $affiliation)) {
                    return true;
                }

                //whe the person has view rights on the project, the affiliation can also be viewed
                return $this->getProjectAssertion()->assert($acl, $role, $affiliation->getProject(), 'view-community');
            case 'add-associate':
            case 'edit-affiliation':
            case 'edit-description':
            case 'edit-community':
                if ($this->getProjectService()->isStopped($affiliation->getProject())) {
                    return false;
                }
                if ($this->getContactService()->contactHasPermit($this->getContact(), 'edit', $affiliation)) {
                    return true;
                }
                if ($this->getContactService()->contactHasPermit($this->getContact(), 'financial', $affiliation)) {
                    return true;
                }
                break;
            case 'update-effort-spent':
                return true;
                //Block access to an already closed report
                $reportId = $this->getRouteMatch()->getParam('report');
            if (!is_null($reportId)) {
                //Find the corresponding report
                $report = $this->getReportService()->findReportById($reportId);
                if (is_null($report) || $this->getReportService()->isFinal($report)) {
                    return false;
                }
            }

            if ($this->getProjectService()->isStopped($affiliation->getProject())) {
                return false;
            }
            if ($this->getContactService()->contactHasPermit($this->getContact(), 'edit', $affiliation)) {
                return true;
            }

                break;
            case 'edit-financial':
            case 'payment-sheet':
            case 'payment-sheet-contract':
            case 'payment-sheet-pdf':
                if ($this->getContactService()->contactHasPermit($this->getContact(), 'financial', $affiliation)) {
                    return true;
                }

                break;

            case 'view-admin':
            case 'edit-admin':
            case 'merge-admin':
            case 'payment-sheet-admin':
            case 'missing-affiliation-parent':
                return $this->rolesHaveAccess(Access::ACCESS_OFFICE);
        }

        return false;
    }
}
