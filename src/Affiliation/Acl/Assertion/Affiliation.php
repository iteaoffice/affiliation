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
     * $role, $resource, or $privilege parameters are null, it means that the query applies to all Roles, Resources, or
     * privileges, respectively.
     *
     * @param Acl                                 $acl
     * @param RoleInterface                       $role
     * @param ResourceInterface|AffiliationEntity $resource
     * @param string                              $privilege
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
        if (!$resource instanceof AffiliationEntity && !is_null($id)) {
            /*
             * @var AffiliationEntity
             */
            $resource = $this->getAffiliationService()->setAffiliationId($id)->getAffiliation();
        }

        switch ($privilege) {
            case 'view-community':
                if ($this->getContactService()->hasPermit('view', $resource)) {
                    return true;
                }

                //whe the person has view rights on the project, the affiliation can also be viewed
                return $this->getProjectAssert()->assert($acl, $role, $resource->getProject(), 'view-community');
            case 'add-associate':
            case 'edit-affiliation':
            case 'edit-description':
            case 'edit-community':
                $this->getProjectService()->setProject($resource->getProject());
                if ($this->getProjectService()->isStopped()) {
                    return false;
                }
                if ($this->getContactService()->hasPermit('edit', $resource)) {
                    return true;
                }
                if ($this->getContactService()->hasPermit('financial', $resource)) {
                    return true;
                }
                break;
            case 'edit-financial':
                if ($this->getContactService()->hasPermit('financial', $resource)) {
                    return true;
                }
                break;
        }

        return false;
    }
}
