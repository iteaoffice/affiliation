<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Acl\Assertion;

use Admin\Entity\Access;
use Affiliation\Entity\Doa as DoaEntity;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

/**
 * Class Affiliation.
 *
 * @var object
 */
class Doa extends AssertionAbstract
{
    /**
     * Returns true if and only if the assertion conditions are met.
     *
     * This method is passed the ACL, Role, Resource, and privilege to which the authorization query applies. If the
     * $role, $doa, or $privilege parameters are null, it means that the query applies to all Roles, Resources, or
     * privileges, respectively.
     *
     * @param Acl               $acl
     * @param RoleInterface     $role
     * @param ResourceInterface $doa
     * @param string            $privilege
     *
     * @return bool
     */
    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $doa = null, $privilege = null)
    {
        $this->setPrivilege($privilege);
        $id = $this->getId();

        if (! $doa instanceof DoaEntity && ! is_null($id)) {
            /** @var DoaEntity $doa */
            $doa = $this->getAffiliationService()->findEntityById(DoaEntity::class, $id);
        }

        switch ($this->getPrivilege()) {
            case 'upload':
                /*
                 * For the upload we need to see if the user has access on the editing of the affiliation
                 * The affiliation can already be known, but if not grab it from the routeMatch
                 */
                $affiliation = null;
                if ($doa instanceof DoaEntity) {
                    $affiliation = $doa->getAffiliation();
                }
                if (is_null($affiliation)) {
                    /*
                     * The id can originate from two different params
                     */
                    if (! is_null($this->getRouteMatch()->getParam('id'))) {
                        $affiliationId = $this->getRouteMatch()->getParam('id');
                    } else {
                        $affiliationId = $this->getRouteMatch()->getParam('affiliationId');
                    }
                    $affiliation = $this->getAffiliationService()->findAffiliationById($affiliationId);
                }

                return $this->getAffiliationAssertion()->assert($acl, $role, $affiliation, 'edit-community');
            case 'replace':
                /*
                 * For the replace we need to see if the user has access on the editing of the affiliation
                 * and the acl should not be approved
                 */

                return is_null($doa->getDateApproved())
                    && $this->getAffiliationAssertion()->assert($acl, $role, $doa->getAffiliation(), 'edit-community');
            case 'render':
                /*
                 * For the upload we need to see if the user has access on the editing of the affiliation
                 */
                if (! is_null($this->getRouteMatch()->getParam('id'))) {
                    $affiliationId = $this->getRouteMatch()->getParam('id');
                } else {
                    $affiliationId = $this->getRouteMatch()->getParam('affiliationId');
                }
                $affiliation = $this->getAffiliationService()->findAffiliationById($affiliationId);

                return $this->getAffiliationAssertion()->assert($acl, $role, $affiliation, 'view-community');
            case 'download':
                return $this->getAffiliationAssertion()->assert($acl, $role, $doa->getAffiliation(), 'view-community');
            case 'view-admin':
            case 'edit-admin':
            case 'list-admin':
            case 'missing-admin':
            case 'remind-admin':
            case 'reminders-admin':
            case 'approval-admin':
                return $this->rolesHaveAccess([Access::ACCESS_OFFICE]);
        }

        return false;
    }
}
