<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Project
 * @package     Navigation
 * @subpackage  Service
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Affiliation\Navigation\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Navigation\Service\DefaultNavigationFactory;
use Zend\Mvc\Router\Http\RouteMatch;

use Affiliation\Service\AffiliationService;
use Project\Service\ProjectService;

/**
 * Factory for the Project admin navigation
 *
 * @package    Affiliation
 * @subpackage Navigation\Service
 */
class AffiliationNavigationFactory extends DefaultNavigationFactory
{
    /**
     * @var RouteMatch
     */
    protected $routeMatch;
    /**
     * @var AffiliationService;
     */
    protected $affiliationService;
    /**
     * @var ProjectService;
     */
    protected $projectService;


    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param array                   $pages
     *
     * @return array
     */
    public function getExtraPages(ServiceLocatorInterface $serviceLocator, array $pages)
    {
        $application              = $serviceLocator->get('Application');
        $this->routeMatch         = $application->getMvcEvent()->getRouteMatch();
        $router                   = $application->getMvcEvent()->getRouter();
        $this->affiliationService = $serviceLocator->get('affiliation_affiliation_service');
        $this->projectService     = $serviceLocator->get('project_project_service');

        if (strpos($this->routeMatch->getMatchedRouteName(), 'community/affiliation') !== false) {

            $this->affiliationService->setAffiliationId($this->routeMatch->getParam('id'));

            if (is_null($this->affiliationService->getAffiliation()->getId())) {
                return false;
            }

            $this->projectService->setProject($this->affiliationService->getAffiliation()->getProject());

            $pages['project']['pages']['projects']['pages']['project'] = array(
                'label'      => $this->projectService->parseFullName(),
                'route'      => 'community/project/project',
                'routeMatch' => $this->routeMatch,
                'router'     => $router,
                'params'     => array(
                    'docRef' => $this->affiliationService->getAffiliation()->getProject()->getDocRef()
                )
            );

            $pages['project']['pages']['projects']['pages']['project']['pages']['affiliation'] = array(
                'label'      => sprintf(_("txt-affiliation-%s-in-%s"),
                    $this->affiliationService->getAffiliation()->getOrganisation()->getOrganisation(),
                    $this->affiliationService->getAffiliation()->getProject()->getProject()
                ),
                'route'      => 'community/affiliation/affiliation',
                'routeMatch' => $this->routeMatch,
                'active'     => true,
                'router'     => $router,
                'params'     => array(
                    'id' => $this->routeMatch->getParam('id')
                )
            );

            if ($this->routeMatch->getMatchedRouteName() === 'community/affiliation/upload-program-doa') {
                $pages['project']['pages']['projects']['pages']['project']['pages']['affiliation']['pages']['upload-program-doa'] = array(
                    'label'      => sprintf(_("txt-upload-program-doa-for-program-%s"),
                        $this->affiliationService->getAffiliation()->getProject()->getCall()->getProgram()
                    ),
                    'route'      => 'community/affiliation/upload-program-doa',
                    'routeMatch' => $this->routeMatch,
                    'active'     => true,
                    'router'     => $router,
                    'params'     => array(
                        'id' => $this->routeMatch->getParam('id')
                    )
                );
            }
        }

        return $pages;
    }
}