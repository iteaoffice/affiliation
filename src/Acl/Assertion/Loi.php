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
use Affiliation\Entity\Loi as LoiEntity;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

/**
 * Class Affiliation.
 */
class Loi extends AssertionAbstract
{
    /**
     * Returns true if and only if the assertion conditions are met.
     *
     * This method is passed the ACL, Role, Resource, and privilege to which the authorization query applies. If the
     * $role, $loi, or $privilege parameters are null, it means that the query applies to all Roles, Resources, or
     * privileges, respectively.
     *
     * @param Acl $acl
     * @param RoleInterface $role
     * @param ResourceInterface $loi
     * @param string $privilege
     *
     * @return bool
     */
    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $loi = null, $privilege = null)
    {
        $this->setPrivilege($privilege);
        $id = $this->getId();

        if (!$loi instanceof LoiEntity && !\is_null($id)) {
            /** @var LoiEntity $loi */
            $loi = $this->getAffiliationService()->findEntityById(LoiEntity::class, $id);
        }

        switch ($this->getPrivilege()) {
            case 'submit':
                if (\is_null($id)) {
                    $id = $this->getRouteMatch()->getParam('affiliationId');
                }
                /*
                 * For the upload we need to see if the user has access on the editing of the affiliation
                 */
                $affiliation = $this->getAffiliationService()->findAffiliationById((int) $id);

                return $this->getAffiliationAssertion()->assert($acl, $role, $affiliation, 'edit-community');
            case 'render':
                /*
                 * For the upload we need to see if the user has access on the editing of the affiliation
                 * The affiliation can already be known, but if not grab it from the getRouteMatch
                 */
                $affiliation = null;
                if ($loi instanceof LoiEntity) {
                    $affiliation = $loi->getAffiliation();
                }
                if (null === $affiliation) {
                    if (\is_null($id)) {
                        $id = $this->getRouteMatch()->getParam('affiliationId');
                    }
                    /*
                     * For the upload we need to see if the user has access on the editing of the affiliation
                     */
                    $affiliation = $this->getAffiliationService()->findAffiliationById((int) $id);
                }

                return $this->getAffiliationAssertion()->assert($acl, $role, $affiliation, 'view-community');
            case 'replace':
                if ($this->rolesHaveAccess([Access::ACCESS_OFFICE])) {
                    return true;
                }

                /*
                 * For the replace we need to see if the user has access on the editing of the affiliation
                 * and the acl should not be approved
                 */

                return \is_null($loi->getDateApproved())
                    && $this->getAffiliationAssertion()
                        ->assert($acl, $role, $loi->getAffiliation(), 'edit-community');
            case 'download':
                return $this->getAffiliationAssertion()->assert($acl, $role, $loi->getAffiliation(), 'view-community');
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
