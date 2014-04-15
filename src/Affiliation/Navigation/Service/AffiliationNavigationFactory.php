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
        $translate                = $serviceLocator->get('viewhelpermanager')->get('translate');

        if (strpos($this->routeMatch->getMatchedRouteName(), 'community/affiliation') !== false) {

            /**
             * The affiliationId can be delivered to this service in 2 ways. Either via 'id' or 'affiliation-id'
             * Except for the case when we replace a DoA or LOI
             */
            switch ($this->routeMatch->getMatchedRouteName()) {
                case 'community/affiliation/doa/replace';
                    $doa = $this->affiliationService->findEntityById('Doa', $this->routeMatch->getParam('id'));
                    $this->affiliationService->setAffiliation($doa->getAffiliation());
                    break;
                case 'community/affiliation/loi/replace';
                    $loi = $this->affiliationService->findEntityById('Loi', $this->routeMatch->getParam('id'));
                    $this->affiliationService->setAffiliation($loi->getAffiliation());
                    break;
                default:
                    $affiliationId = !is_null($this->routeMatch->getParam('affiliation-id')) ?
                        $this->routeMatch->getParam('affiliation-id') : $this->routeMatch->getParam('id');

                    $this->affiliationService->setAffiliationId($affiliationId);
                    break;
            }

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
                'label'      => sprintf($translate("txt-affiliation-%s-in-%s"),
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

            if ($this->routeMatch->getMatchedRouteName() === 'community/affiliation/edit') {
                $pages['project']['pages']['projects']['pages']['project']['pages']['affiliation']['pages']['edit'] =
                    array(
                        'label'      => sprintf($translate("txt-edit-affiliation-%s-in-%s"),
                            $this->affiliationService->getAffiliation()->getOrganisation()->getOrganisation(),
                            $this->affiliationService->getAffiliation()->getProject()->getProject()
                        ),
                        'route'      => 'community/affiliation/edit',
                        'routeMatch' => $this->routeMatch,
                        'active'     => true,
                        'router'     => $router,
                        'params'     => array(
                            'id' => $affiliationId
                        )
                    );
            }

            if ($this->routeMatch->getMatchedRouteName() === 'community/affiliation/doa/upload') {
                $pages['project']['pages']['projects']['pages']['project']['pages']['affiliation']['pages']['upload-doa'] = array(
                    'label'      => sprintf($translate("txt-doa-for-organisation-%s-for-project-%s"),
                        $this->affiliationService->getAffiliation()->getOrganisation(),
                        $this->affiliationService->getAffiliation()->getProject()
                    ),
                    'route'      => 'community/affiliation/doa/upload',
                    'routeMatch' => $this->routeMatch,
                    'active'     => true,
                    'router'     => $router,
                    'params'     => array(
                        'affiliation-id' => $affiliationId
                    )
                );
            }

            if ($this->routeMatch->getMatchedRouteName() === 'community/affiliation/doa/replace') {
                $pages['project']['pages']['projects']['pages']['project']['pages']['affiliation']['pages']['replace-doa'] = array(
                    'label'      => sprintf($translate("txt-replace-doa-for-organisation-%s-for-project-%s"),
                        $this->affiliationService->getAffiliation()->getOrganisation(),
                        $this->affiliationService->getAffiliation()->getProject()
                    ),
                    'route'      => 'community/affiliation/doa/replace',
                    'routeMatch' => $this->routeMatch,
                    'active'     => true,
                    'router'     => $router,
                    'params'     => array(
                        'id' => $doa->getId()
                    )
                );
            }

            if ($this->routeMatch->getMatchedRouteName() === 'community/affiliation/loi/replace') {
                $pages['project']['pages']['projects']['pages']['project']['pages']['affiliation']['pages']['replace-loi'] = array(
                    'label'      => sprintf($translate("txt-replace-loi-for-organisation-%s-for-project-%s"),
                        $this->affiliationService->getAffiliation()->getOrganisation(),
                        $this->affiliationService->getAffiliation()->getProject()
                    ),
                    'route'      => 'community/affiliation/loi/replace',
                    'routeMatch' => $this->routeMatch,
                    'active'     => true,
                    'router'     => $router,
                    'params'     => array(
                        'id' => $loi->getId()
                    )
                );
            }
        }

        return $pages;
    }
}
