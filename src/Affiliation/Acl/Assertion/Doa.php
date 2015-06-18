<?php
/**
 * Debranova copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 Debranova
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
     * $role, $resource, or $privilege parameters are null, it means that the query applies to all Roles, Resources, or
     * privileges, respectively.
     *
     * @param Acl $acl
     * @param RoleInterface $role
     * @param ResourceInterface $resource
     * @param string $privilege
     *
     * @return bool
     */
    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null)
    {
        $id = $this->getRouteMatch()->getParam('id');
        /*
         * When the privilege is_null (not given by the isAllowed helper), get it from the routeMatch
         */
        if (is_null($privilege)) {
            $privilege = $this->getRouteMatch()->getParam('privilege');
        }
        if (!$resource instanceof DoaEntity && !is_null($id)) {
            /*
             * @var DoaEntity
             */
            $resource = $this->getAffiliationService()->findEntityById('Doa', $id);
        }

        switch ($privilege) {
            case 'upload':
                /*
                 * For the upload we need to see if the user has access on the editing of the affiliation
                 * The affiliation can already be known, but if not grab it from the routeMatch
                 */
                $affiliation = null;
                if ($resource instanceof DoaEntity) {
                    $affiliation = $resource->getAffiliation();
                }
                if (is_null($affiliation)) {
                    /*
                     * The id can originate from two different params
                     */
                    if (!is_null($this->getRouteMatch()->getParam('id'))) {
                        $affiliationId = $this->getRouteMatch()->getParam('id');
                    } else {
                        $affiliationId = $this->getRouteMatch()->getParam('affiliation-id');
                    }
                    $affiliation = $this->getAffiliationService()->setAffiliationId($affiliationId)->getAffiliation();
                }

                return $this->getAffiliationAssert()->assert($acl, $role, $affiliation, 'edit-community');
            case 'replace':
                /*
                 * For the replace we need to see if the user has access on the editing of the affiliation
                 * and the acl should not be approved
                 */

                return is_null($resource->getDateApproved()) &&
                $this->getAffiliationAssert()->assert($acl, $role, $resource->getAffiliation(), 'edit-community');
            case 'render':
                /*
                 * For the upload we need to see if the user has access on the editing of the affiliation
                 */
                if (!is_null($this->getRouteMatch()->getParam('id'))) {
                    $affiliationId = $this->getRouteMatch()->getParam('id');
                } else {
                    $affiliationId = $this->getRouteMatch()->getParam('affiliation-id');
                }
                $affiliation = $this->getAffiliationService()->setAffiliationId($affiliationId)->getAffiliation();

                return $this->getAffiliationAssert()->assert($acl, $role, $affiliation, 'view-community');
            case 'download':
                return $this->getAffiliationAssert()->assert(
                    $acl,
                    $role,
                    $resource->getAffiliation(),
                    'view-community'
                );
            case 'view-admin':
            case 'edit-admin':
            case 'list-admin':
            case 'missing-admin':
            case 'remind-admin':
            case 'reminders-admin':
            case 'approval-admin':
                return $this->rolesHaveAccess([strtolower(Access::ACCESS_OFFICE)]);
        }

        return false;
    }
}
