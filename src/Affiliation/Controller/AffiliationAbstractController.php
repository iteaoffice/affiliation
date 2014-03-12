<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Controller
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Affiliation\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

use Project\Service\ProjectService;
use Organisation\Service\OrganisationService;
use Contact\Service\ContactService;
use General\Service\GeneralService;
use Program\Service\ProgramService;

use Affiliation\Service\AffiliationService;
use Affiliation\Service\FormServiceAwareInterface;
use Affiliation\Service\FormService;
use Affiliation\Service\ConfigAwareInterface;

/**
 * @category    Affiliation
 * @package     Controller
 */
abstract class AffiliationAbstractController extends AbstractActionController implements
    FormServiceAwareInterface,
    ServiceLocatorAwareInterface,
    ConfigAwareInterface
{
    /**
     * @var AffiliationService
     */
    protected $affiliationService;
    /**
     * @var OrganisationService
     */
    protected $organisationService;
    /**
     * @var ProjectService
     */
    protected $projectService;
    /**
     * @var FormService
     */
    protected $formService;
    /**
     * @var array
     */
    protected $config = array();


    /**
     * @return FormService
     */
    public function getFormService()
    {
        return $this->formService;
    }

    /**
     * @param $formService
     *
     * @return AffiliationAbstractController
     */
    public function setFormService($formService)
    {
        $this->formService = $formService;

        return $this;
    }

    /**
     * Gateway to the Affiliation Service
     *
     * @return AffiliationService
     */
    public function getAffiliationService()
    {
        if (null === $this->affiliationService) {
            $this->setAffiliationService($this->getServiceLocator()->get('affiliation_affiliation_service'));
        }

        return $this->affiliationService;
    }

    /**
     * Gateway to the Project Service
     *
     * @return ProjectService
     */
    public function getProjectService()
    {
        return $this->getServiceLocator()->get('project_project_service');
    }

    /**
     * Gateway to the Organisation Service
     *
     * @return OrganisationService
     */
    public function getOrganisationService()
    {
        return $this->getServiceLocator()->get('organisation_organisation_service');
    }

    /**
     * Gateway to the Contact Service
     *
     * @return ContactService
     */
    public function getContactService()
    {
        return $this->getServiceLocator()->get('contact_contact_service');
    }

    /**
     * Gateway to the Program Service
     *
     * @return ProgramService
     */
    public function getProgramService()
    {
        return $this->getServiceLocator()->get('program_program_service');
    }

    /**
     * Gateway to the General Service
     *
     * @return GeneralService
     */
    public function getGeneralService()
    {
        return $this->getServiceLocator()->get('general_general_service');
    }

    /**
     * @param $affiliationService
     *
     * @return $this
     */
    public function setAffiliationService($affiliationService)
    {
        $this->affiliationService = $affiliationService;

        return $this;
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        if (null === $this->config) {
            $this->setConfig($this->config);
        }

        return $this->config;
    }
}
