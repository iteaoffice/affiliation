<?php
/**
 * Debranova copyright message placeholder
 *
 * @category    Affiliation
 * @package     Acl
 * @subpackage  Assertion
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 Debranova
 */
namespace Affiliation\Acl\Assertion;

use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\ServiceManager\ServiceManager;

use Project\Service\ProjectService;

use Affiliation\Service\AffiliationService;
use Affiliation\Entity\Affiliation as AffiliationEntity;

use Contact\Service\ContactService;

/**
 * Class Affiliation
 * @package Affiliation\Acl\Assertion
 */
class Affiliation implements AssertionInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    /**
     * @var AffiliationService
     */
    protected $affiliationService;
    /**
     * @var ProjectService
     */
    protected $projectService;
    /**
     * @var ContactService
     */
    protected $contactService;
    /**
     * @var array
     */
    protected $accessRoles = array();


    /**
     * @param ServiceManager $serviceManager
     */
    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager     = $serviceManager;
        $this->affiliationService = $this->serviceManager->get("affiliation_affiliation_service");
        $this->projectService     = $this->serviceManager->get("project_project_service");
        $this->contactService     = $this->serviceManager->get("contact_contact_service");

        /**
         * Store locally in the object the contact information
         */
        if ($this->serviceManager->get('zfcuser_auth_service')->hasIdentity()) {
            $this->contactService->setContact($this->serviceManager->get('zfcuser_auth_service')->getIdentity());
            $this->accessRoles = $this->contactService->getContact()->getRoles();
        }
    }

    /**
     * Returns true if and only if the assertion conditions are met
     *
     * This method is passed the ACL, Role, Resource, and privilege to which the authorization query applies. If the
     * $role, $resource, or $privilege parameters are null, it means that the query applies to all Roles, Resources, or
     * privileges, respectively.
     *
     * @param Acl               $acl
     * @param RoleInterface     $role
     * @param ResourceInterface $resource
     * @param string            $privilege
     *
     * @return bool
     */
    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null)
    {
        $routeMatch = $this->serviceManager->get("Application")->getMvcEvent()->getRouteMatch();

        $id = $routeMatch->getParam('id');

        /**
         * When the privilege is_null (not given by the isAllowed helper), get it from the routeMatch
         */
        if (is_null($privilege)) {
            $privilege = $routeMatch->getParam('privilege');
        }

        if (!$resource instanceof AffiliationEntity && !is_null($id)) {
            $resource = $this->affiliationService->findEntityById('Affiliation', $id);
        }

        switch ($privilege) {
            case 'view-community':

                if ($this->contactService->hasPermit('view', $resource)) {
                    return true;
                }

                $projectAssert = $this->serviceManager->get("project_acl_assertion_project");

                return $projectAssert->assert($acl, $role, $resource->getProject(), 'view-community');
                break;
            case 'edit-community':

                $this->projectService->setProject($resource->getProject());


                if ($this->projectService->isStopped()) {
                    return false;
                }

                if ($this->contactService->hasPermit('edit', $resource)) {
                    return true;
                }


                $projectAssert = $this->serviceManager->get("project_acl_assertion_project");

                return $projectAssert->assert($acl, $role, $resource->getProject(), 'edit-community');

                break;

            case 'edit-financial':

                if ($this->contactService->hasPermit('financial', $resource)) {
                    return true;
                }

                return $this->rolesHaveAccess('office');

                break;
        }

        return false;
    }

    /**
     * Returns true when a role or roles have access
     *
     * @param $roles
     *
     * @return bool
     */
    private function rolesHaveAccess($roles)
    {
        if (!is_array($roles)) {
            $roles = array($roles);
        }

        foreach ($this->accessRoles as $access) {
            if (in_array(strtolower($access), $roles)) {
                return true;
            }
        }
    }
}
