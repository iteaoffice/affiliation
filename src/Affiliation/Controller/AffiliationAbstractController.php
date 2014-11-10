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

use Affiliation\Service\AffiliationService;
use Affiliation\Service\AffiliationServiceAwareInterface;
use Affiliation\Service\ConfigAwareInterface;
use Affiliation\Service\DoaService;
use Affiliation\Service\FormService;
use Affiliation\Service\FormServiceAwareInterface;
use Affiliation\Service\LoiService;
use Contact\Service\ContactService;
use General\Service\EmailService;
use General\Service\GeneralService;
use Mailing\Service\MailingService;
use Organisation\Service\OrganisationService;
use Program\Service\ProgramService;
use Project\Service\ProjectService;
use Project\Service\WorkpackageService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use ZfcUser\Controller\Plugin\ZfcUserAuthentication;

/**
 * @category    Affiliation
 * @package     Controller
 * @method      ZfcUserAuthentication zfcUserAuthentication()
 * @method      FlashMessenger flashMessenger()
 * @method      bool isAllowed($resource, $action)
 */
abstract class AffiliationAbstractController extends AbstractActionController implements
    AffiliationServiceAwareInterface,
    FormServiceAwareInterface,
    ConfigAwareInterface
{
    /**
     * @var FormService
     */
    protected $formService;
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
     * @var WorkpackageService
     */
    protected $workpackageService;
    /**
     * @var ContactService
     */
    protected $contactService;
    /**
     * @var ProgramService
     */
    protected $programService;
    /**
     * @var GeneralService
     */
    protected $generalService;
    /**
     * @var MailingService
     */
    protected $mailingService;
    /**
     * @var EmailService
     */
    protected $emailService;
    /**
     * @var DoaService
     */
    protected $doaService;
    /**
     * @var LoiService
     */
    protected $loiService;
    /**
     * @var array
     */
    protected $config = [];

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
     * @return AffiliationService
     */
    public function getAffiliationService()
    {
        return $this->affiliationService;
    }

    /**
     * @param AffiliationService $affiliationService
     *
     * @return AffiliationAbstractController
     */
    public function setAffiliationService(AffiliationService $affiliationService)
    {
        $this->affiliationService = $affiliationService;

        return $this;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     *
     * @return AffiliationAbstractController
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
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
     * @return AffiliationAbstractController
     */
    public function setContactService(ContactService $contactService)
    {
        $this->contactService = $contactService;

        return $this;
    }

    /**
     * @return GeneralService
     */
    public function getGeneralService()
    {
        return $this->generalService;
    }

    /**
     * @param GeneralService $generalService
     *
     * @return AffiliationAbstractController
     */
    public function setGeneralService(GeneralService $generalService)
    {
        $this->generalService = $generalService;

        return $this;
    }

    /**
     * @return OrganisationService
     */
    public function getOrganisationService()
    {
        return $this->organisationService;
    }

    /**
     * @param OrganisationService $organisationService
     *
     * @return AffiliationAbstractController
     */
    public function setOrganisationService(OrganisationService $organisationService)
    {
        $this->organisationService = $organisationService;

        return $this;
    }

    /**
     * @return ProgramService
     */
    public function getProgramService()
    {
        return $this->programService;
    }

    /**
     * @param ProgramService $programService
     *
     * @return AffiliationAbstractController
     */
    public function setProgramService(ProgramService $programService)
    {
        $this->programService = $programService;

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
     * @return AffiliationAbstractController
     */
    public function setProjectService(ProjectService $projectService)
    {
        $this->projectService = $projectService;

        return $this;
    }

    /**
     * @return WorkpackageService
     */
    public function getWorkpackageService()
    {
        return $this->workpackageService;
    }

    /**
     * @param WorkpackageService $workpackageService
     *
     * @return AffiliationAbstractController
     */
    public function setWorkpackageService(WorkpackageService $workpackageService)
    {
        $this->workpackageService = $workpackageService;

        return $this;
    }

    /**
     * @return LoiService
     */
    public function getLoiService()
    {
        return $this->loiService;
    }

    /**
     * @param LoiService $loiService
     *
     * @return AffiliationAbstractController
     */
    public function setLoiService(LoiService $loiService)
    {
        $this->loiService = $loiService;

        return $this;
    }

    /**
     * @return DoaService
     */
    public function getDoaService()
    {
        return $this->doaService;
    }

    /**
     * @param DoaService $doaService
     *
     * @return AffiliationAbstractController
     */
    public function setDoaService(DoaService $doaService)
    {
        $this->doaService = $doaService;

        return $this;
    }

    /**
     * @return MailingService
     */
    public function getMailingService()
    {
        return $this->mailingService;
    }

    /**
     * @param MailingService $mailingService
     *
     * @return AffiliationAbstractController
     */
    public function setMailingService(MailingService $mailingService)
    {
        $this->mailingService = $mailingService;

        return $this;
    }

    /**
     * @return EmailService
     */
    public function getEmailService()
    {
        return $this->emailService;
    }

    /**
     * @param EmailService $emailService
     *
     * @return AffiliationAbstractController
     */
    public function setEmailService(EmailService $emailService)
    {
        $this->emailService = $emailService;

        return $this;
    }


}
