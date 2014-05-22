<?php
/**
 * Japaveh Webdesign copyright message placeholder
 *
 * @category    Admin
 * @package     Controller
 * @author      Johan van der Heide <info@japaveh.nl>
 * @copyright   2004-2014 Japaveh Webdesign
 * @license     http://solodb.net/license.txt proprietary
 * @link        http://solodb.net
 */
namespace Affiliation\Controller;

use Admin\Service\FormService;
use Admin\Service\FormServiceAwareInterface;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\AffiliationServiceAwareInterface;
use Contact\Service\ContactService;
use Contact\Service\ContactServiceAwareInterface;
use General\Service\GeneralService;
use General\Service\GeneralServiceAwareInterface;
use Organisation\Service\OrganisationService;
use Organisation\Service\OrganisationServiceAwareInterface;
use Program\Service\ProgramService;
use Program\Service\ProgramServiceAwareInterface;
use Project\Service\ProjectService;
use Project\Service\ProjectServiceAwareInterface;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Japaveh Webdesign copyright message placeholder
 *
 * @category    Admin
 * @package     Controller
 * @author      Johan van der Heide <info@japaveh.nl>
 * @copyright   2004-2014 Japaveh Webdesign
 * @license     http://solodb.net/license.txt proprietary
 * @link        http://solodb.net
 */
class ControllerInitializer implements InitializerInterface
{
    /**
     * @param                                           $instance
     * @param ServiceLocatorInterface|ControllerManager $serviceLocator
     *
     * @return void
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {

        /**
         * @var $sm ServiceLocatorInterface
         */
        $sm = $serviceLocator->getServiceLocator();

        if ($instance instanceof AffiliationServiceAwareInterface) {
            /**
             * @var $affiliationService AffiliationService
             */
            $affiliationService = $sm->get('affiliation_affiliation_service');
            $instance->setAffiliationService($affiliationService);
        }

        if ($instance instanceof FormServiceAwareInterface) {
            /**
             * @var $formService FormService
             */
            $formService = $sm->get('affiliation_form_service');
            $instance->setFormService($formService);
        }

        if ($instance instanceof ProjectServiceAwareInterface) {
            /**
             * @var $projectService ProjectService
             */
            $projectService = $sm->get('project_project_service');
            $instance->setProjectService($projectService);
        }

        if ($instance instanceof ProgramServiceAwareInterface) {
            /**
             * @var $programService ProgramService
             */
            $programService = $sm->get('program_program_service');
            $instance->setProgramService($programService);
        }

        if ($instance instanceof OrganisationServiceAwareInterface) {
            /**
             * @var $organisationService OrganisationService
             */
            $organisationService = $sm->get('organisation_organisation_service');
            $instance->setOrganisationService($organisationService);
        }

        if ($instance instanceof ContactServiceAwareInterface) {
            /**
             * @var $contactService ContactService
             */
            $contactService = $sm->get('contact_contact_service');
            $instance->setContactService($contactService);
        }

        if ($instance instanceof GeneralServiceAwareInterface) {
            /**
             * @var $generalService GeneralService
             */
            $generalService = $sm->get('general_general_service');
            $instance->setGeneralService($generalService);
        }
    }
}
