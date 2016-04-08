<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category   Project
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  2004-2015 ITEA Office
 * @license    https://itea3.org/license.txt proprietary
 *
 * @link       https://itea3.org
 */

namespace Affiliation\Acl\Assertion;

use Admin\Service\AdminService;
use Affiliation\Acl\Assertion\Affiliation as AffiliationAssertion;
use Affiliation\Service\AffiliationService;
use Contact\Entity\Contact;
use Contact\Service\ContactService;
use Project\Acl\Assertion\Project as ProjectAssertion;
use Project\Service\ProjectService;
use Project\Service\ReportService;
use Zend\Http\Request;
use Zend\Mvc\Router\RouteMatch;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Create a link to an document.
 *
 * @category   Project
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  2004-2015 ITEA Office
 * @license    https://itea3.org/license.txt proprietary
 *
 * @link       https://itea3.org
 */
abstract class AssertionAbstract implements AssertionInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;
    /**
     * @var ContactService
     */
    protected $contactService;
    /**
     * @var Contact
     */
    protected $contact;
    /**
     * @var AffiliationService
     */
    protected $affiliationService;
    /**
     * @var ProjectService
     */
    protected $projectService;
    /**
     * @var ReportService
     */
    protected $reportService;
    /**
     * @var ProjectAssertion
     */
    protected $projectAssertion;
    /**
     * @var AffiliationAssertion
     */
    protected $affiliationAssertion;
    /**
     * @var AdminService
     */
    protected $adminService;
    /**
     * @var string
     */
    protected $privilege;
    /**
     * @var array
     */
    protected $accessRoles = [];

    /**
     * @return RouteMatch
     */
    public function getRouteMatch()
    {
        return $this->getServiceLocator()->get("Application")->getMvcEvent()->getRouteMatch();
    }

    /**
     * Proxy to the original request object to handle form.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->getServiceLocator()->get('application')->getMvcEvent()->getRequest();
    }

    /**
     * @return bool
     */
    public function hasContact()
    {
        return !$this->getContact()->isEmpty();
    }

    /**
     * @return ContactService
     */
    public function getContactService()
    {
        return $this->contactService;
    }

    /**
     * @param ContactService $contactService
     *
     * @return AssertionAbstract
     */
    public function setContactService($contactService)
    {
        $this->contactService = $contactService;

        return $this;
    }

    /**
     * @return Contact
     */
    public function getContact()
    {
        if (is_null($this->contact)) {
            $this->contact = new Contact();
        }

        return $this->contact;
    }

    /**
     * @param Contact $contact
     *
     * @return AssertionAbstract
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Returns true when a role or roles have access.
     *
     * @param $roles
     *
     * @return boolean
     */
    protected function rolesHaveAccess($roles)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $roles = array_map('strtolower', $roles);

        foreach ($this->getAccessRoles() as $access) {
            if (in_array(strtolower($access), $roles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getAccessRoles()
    {
        if (empty($this->accessRoles) && !$this->getContact()->isEmpty()) {
            $this->accessRoles = $this->getAdminService()->findAccessRolesByContactAsArray($this->getContact());
        }

        return $this->accessRoles;
    }

    /**
     * @return string
     */
    public function getPrivilege()
    {
        return $this->privilege;
    }

    /**
     * @param string $privilege
     *
     * @return AssertionAbstract
     */
    public function setPrivilege($privilege)
    {
        /**
         * When the privilege is_null (not given by the isAllowed helper), get it from the routeMatch
         */
        if (is_null($privilege)) {
            $this->privilege = $this->getRouteMatch()
                ->getParam('privilege', $this->getRouteMatch()->getParam('action'));
        } else {
            $this->privilege = $privilege;
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        if (!is_null($id = $this->getRequest()->getPost('id'))) {
            return $id;
        }
        if (is_null($this->getRouteMatch())) {
            return null;
        }

        return $this->getRouteMatch()->getParam('id');
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return AssertionAbstract
     */
    public function setServiceLocator($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * @return AffiliationService
     */
    public function getAffiliationService()
    {
        return $this->affiliationService;
    }

    /**
     * @param AffiliationService $affiliationService
     *
     * @return AssertionAbstract
     */
    public function setAffiliationService($affiliationService)
    {
        $this->affiliationService = $affiliationService;

        return $this;
    }

    /**
     * @return ProjectService
     */
    public function getProjectService()
    {
        return $this->projectService;
    }

    /**
     * @param ProjectService $projectService
     *
     * @return AssertionAbstract
     */
    public function setProjectService($projectService)
    {
        $this->projectService = $projectService;

        return $this;
    }

    /**
     * @return ReportService
     */
    public function getReportService()
    {
        return $this->reportService;
    }

    /**
     * @param ReportService $reportService
     *
     * @return AssertionAbstract
     */
    public function setReportService($reportService)
    {
        $this->reportService = $reportService;

        return $this;
    }

    /**
     * @return ProjectAssertion
     */
    public function getProjectAssertion()
    {
        return $this->projectAssertion;
    }

    /**
     * @param ProjectAssertion $projectAssertion
     *
     * @return AssertionAbstract
     */
    public function setProjectAssertion($projectAssertion)
    {
        $this->projectAssertion = $projectAssertion;

        return $this;
    }

    /**
     * @return Affiliation
     */
    public function getAffiliationAssertion()
    {
        if (is_null($this->affiliationAssertion)) {
            $this->affiliationAssertion = $this->getServiceLocator()->get(Affiliation::class);
        }

        return $this->affiliationAssertion;
    }

    /**
     * @param Affiliation $affiliationAssertion
     *
     * @return AssertionAbstract
     */
    public function setAffiliationAssertion($affiliationAssertion)
    {
        $this->affiliationAssertion = $affiliationAssertion;

        return $this;
    }

    /**
     * @return AdminService
     */
    public function getAdminService()
    {
        return $this->adminService;
    }

    /**
     * @param AdminService $adminService
     *
     * @return AssertionAbstract
     */
    public function setAdminService($adminService)
    {
        $this->adminService = $adminService;

        return $this;
    }
}
