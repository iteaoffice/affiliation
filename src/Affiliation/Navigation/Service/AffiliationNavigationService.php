<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */

namespace Affiliation\Navigation\Service;

/**
 * Factory for the Affiliation admin navigation.
 */
class AffiliationNavigationService extends NavigationServiceAbstract
{
    /**
     * Add the dedicated pages to the navigation.
     */
    public function update()
    {
        if (!is_null($this->getRouteMatch()) &&
            strtolower($this->getRouteMatch()->getParam('namespace')) === 'affiliation'
        ) {
            if (strpos($this->getRouteMatch()->getMatchedRouteName(), 'community') !== false) {
                $this->updateCommunityNavigation();
            }
            //            if (!is_null($this->getRouteMatch()->getParam('id'))) {
            //                $this->getAffiliationService()->setAffiliationId($this->getRouteMatch()->getParam('id'));
            //            }
        }
    }

    /**
     * Update the navigation for a affiliation.
     */
    public function updateCommunityNavigation()
    {
        $communityNavigation = $this->getNavigation()->findOneBy('route', 'community/project/list');
        /*
         * Go over the routes to see if we need to extend the $this->pages array
         */
        switch ($this->getRouteMatch()->getMatchedRouteName()) {
            case 'community/affiliation/affiliation':
                $this->getAffiliationService()->setAffiliationId($this->getRouteMatch()->getParam('id'));
                $this->getProjectService()->setProject($this->getAffiliationService()->getAffiliation()->getProject());
                $communityNavigation->addPage(
                    [
                        'label'  => sprintf(
                            $this->translate("%s"),
                            $this->getProjectService()->parseFullName()
                        ),
                        'route'  => 'community/project/project/basics',
                        'router' => $this->getRouter(),
                        'params' => [
                            'docRef' => $this->getProjectService()->getProject()->getDocRef(),
                        ],
                        'pages'  => [
                            'affiliation' => [
                                'label'  => sprintf(
                                    $this->getAffiliationService()->getAffiliation()->getOrganisation()
                                ),
                                'active' => true,
                                'route'  => $this->getRouteMatch()->getMatchedRouteName(),
                                'router' => $this->getRouter(),
                                'params' => [
                                    'id' => $this->getRouteMatch()->getParam('id'),
                                ],
                            ],
                        ],
                    ]
                );
                break;
            case 'community/affiliation/edit/affiliation':
                $this->getAffiliationService()->setAffiliationId($this->getRouteMatch()->getParam('id'));
                $this->getProjectService()->setProject($this->getAffiliationService()->getAffiliation()->getProject());
                $communityNavigation->addPage(
                    [
                        'label'  => sprintf(
                            $this->translate("%s"),
                            $this->getProjectService()->parseFullName()
                        ),
                        'route'  => 'community/project/project/basics',
                        'router' => $this->getRouter(),
                        'params' => [
                            'docRef' => $this->getProjectService()->getProject()->getDocRef(),
                        ],
                        'pages'  => [
                            'affiliation' => [
                                'label'  => sprintf(
                                    $this->getAffiliationService()->getAffiliation()->getOrganisation()
                                ),
                                'active' => true,
                                'route'  => 'community/affiliation/affiliation',
                                'router' => $this->getRouter(),
                                'params' => [
                                    'id' => $this->getAffiliationService()->getAffiliation()->getId(),
                                ],
                                'pages'  => [
                                    'edit' => [
                                        'label'  => sprintf(
                                            $this->translate("txt-edit-affiliation-%s-in-%s"),
                                            $this->getAffiliationService()->getAffiliation()->getOrganisation(),
                                            $this->getProjectService()->parseFullName()
                                        ),
                                        'active' => true,
                                        'route'  => $this->getRouteMatch()->getMatchedRouteName(),
                                        'router' => $this->getRouter(),
                                        'params' => [
                                            'id' => $this->getAffiliationService()->getAffiliation()->getId(),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                );
                break;
            case 'community/affiliation/edit/description':
                $this->getAffiliationService()->setAffiliationId($this->getRouteMatch()->getParam('id'));
                $this->getProjectService()->setProject($this->getAffiliationService()->getAffiliation()->getProject());
                $communityNavigation->addPage(
                    [
                        'label'  => sprintf(
                            $this->translate("%s"),
                            $this->getProjectService()->parseFullName()
                        ),
                        'route'  => 'community/project/project/basics',
                        'router' => $this->getRouter(),
                        'params' => [
                            'docRef' => $this->getProjectService()->getProject()->getDocRef(),
                        ],
                        'pages'  => [
                            'affiliation' => [
                                'label'  => sprintf(
                                    $this->getAffiliationService()->getAffiliation()->getOrganisation()
                                ),
                                'active' => true,
                                'route'  => 'community/affiliation/affiliation',
                                'router' => $this->getRouter(),
                                'params' => [
                                    'id' => $this->getAffiliationService()->getAffiliation()->getId(),
                                ],
                                'pages'  => [
                                    'edit' => [
                                        'label'  => sprintf(
                                            $this->translate("txt-edit-description-of-affiliation-%s-in-%s"),
                                            $this->getAffiliationService()->getAffiliation()->getOrganisation(),
                                            $this->getProjectService()->parseFullName()
                                        ),
                                        'active' => true,
                                        'route'  => $this->getRouteMatch()->getMatchedRouteName(),
                                        'router' => $this->getRouter(),
                                        'params' => [
                                            'id' => $this->getAffiliationService()->getAffiliation()->getId(),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                );
                break;
            case 'community/affiliation/edit/add-associate':
                $this->getAffiliationService()->setAffiliationId($this->getRouteMatch()->getParam('id'));
                $this->getProjectService()->setProject($this->getAffiliationService()->getAffiliation()->getProject());
                $communityNavigation->addPage(
                    [
                        'label'  => sprintf(
                            $this->translate("%s"),
                            $this->getProjectService()->parseFullName()
                        ),
                        'route'  => 'community/project/project/basics',
                        'router' => $this->getRouter(),
                        'params' => [
                            'docRef' => $this->getProjectService()->getProject()->getDocRef(),
                        ],
                        'pages'  => [
                            'affiliation' => [
                                'label'  => sprintf(
                                    $this->getAffiliationService()->getAffiliation()->getOrganisation()
                                ),
                                'active' => true,
                                'route'  => 'community/affiliation/affiliation',
                                'router' => $this->getRouter(),
                                'params' => [
                                    'id' => $this->getAffiliationService()->getAffiliation()->getId(),
                                ],
                                'pages'  => [
                                    'edit' => [
                                        'label'  => sprintf(
                                            $this->translate("txt-add-associates-to-affiliation-%s-in-%s"),
                                            $this->getAffiliationService()->getAffiliation()->getOrganisation(),
                                            $this->getProjectService()->parseFullName()
                                        ),
                                        'active' => true,
                                        'route'  => $this->getRouteMatch()->getMatchedRouteName(),
                                        'router' => $this->getRouter(),
                                        'params' => [
                                            'id' => $this->getAffiliationService()->getAffiliation()->getId(),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                );
                break;
            case 'community/affiliation/doa/upload':
                $this->getAffiliationService()->setAffiliationId($this->getRouteMatch()->getParam('affiliation-id'));
                $this->getProjectService()->setProject($this->getAffiliationService()->getAffiliation()->getProject());
                $communityNavigation->addPage(
                    [
                        'label'  => sprintf(
                            $this->translate("txt-affiliation-%s-in-%s"),
                            $this->getAffiliationService()->getAffiliation()->getOrganisation(),
                            $this->getProjectService()->parseFullName()
                        ),
                        'active' => false,
                        'route'  => 'community/affiliation/affiliation',
                        'router' => $this->getRouter(),
                        'params' => [
                            'id' => $this->getAffiliationService()->getAffiliation()->getId(),
                        ],
                        'pages'  => [
                            'upload' => [
                                'label'  => sprintf(
                                    $this->translate("txt-upload-doa-for-organisation-%s-for-project-%s"),
                                    $this->getAffiliationService()->getAffiliation()->getOrganisation(),
                                    $this->getAffiliationService()->getAffiliation()->getProject()
                                ),
                                'route'  => $this->getRouteMatch()->getMatchedRouteName(),
                                'active' => true,
                                'router' => $this->getRouter(),
                                'params' => [
                                    'affiliation-id' => $this->getAffiliationService()->getAffiliation()->getId(),
                                ],
                            ],
                        ],
                    ]
                );
                break;
            case 'community/affiliation/doa/replace':
                $doa = $this->getAffiliationService()->findEntityById('Doa', $this->getRouteMatch()->getParam('id'));
                $this->getAffiliationService()->setAffiliation($doa->getAffiliation());
                $this->getProjectService()->setProject($doa->getAffiliation()->getProject());
                $communityNavigation->addPage(
                    [
                        'label'  => sprintf(
                            $this->translate("txt-affiliation-%s-in-%s"),
                            $this->getAffiliationService()->getAffiliation()->getOrganisation(),
                            $this->getProjectService()->parseFullName()
                        ),
                        'active' => false,
                        'route'  => 'community/affiliation/affiliation',
                        'router' => $this->getRouter(),
                        'params' => [
                            'id' => $this->getAffiliationService()->getAffiliation()->getId(),
                        ],
                        'pages'  => [
                            'replace' => [
                                'label'  => sprintf(
                                    $this->translate("txt-replace-doa-for-organisation-%s-for-project-%s"),
                                    $this->getAffiliationService()->getAffiliation()->getOrganisation(),
                                    $this->getAffiliationService()->getAffiliation()->getProject()
                                ),
                                'route'  => $this->getRouteMatch()->getMatchedRouteName(),
                                'active' => true,
                                'router' => $this->getRouter(),
                                'params' => $this->getRouteMatch()->getParams(),
                            ],
                        ],
                    ]
                );
                break;
            case 'community/affiliation/loi/upload':
                $this->getAffiliationService()->setAffiliationId($this->getRouteMatch()->getParam('affiliation-id'));
                $this->getProjectService()->setProject($this->getAffiliationService()->getAffiliation()->getProject());
                $communityNavigation->addPage(
                    [
                        'label'  => sprintf(
                            $this->translate("txt-affiliation-%s-in-%s"),
                            $this->getAffiliationService()->getAffiliation()->getOrganisation(),
                            $this->getProjectService()->parseFullName()
                        ),
                        'active' => false,
                        'route'  => 'community/affiliation/affiliation',
                        'router' => $this->getRouter(),
                        'params' => [
                            'id' => $this->getAffiliationService()->getAffiliation()->getId(),
                        ],
                        'pages'  => [
                            'upload' => [
                                'label'  => sprintf(
                                    $this->translate("txt-upload-loi-for-organisation-%s-for-project-%s"),
                                    $this->getAffiliationService()->getAffiliation()->getOrganisation(),
                                    $this->getAffiliationService()->getAffiliation()->getProject()
                                ),
                                'route'  => $this->getRouteMatch()->getMatchedRouteName(),
                                'active' => true,
                                'router' => $this->getRouter(),
                                'params' => [
                                    'affiliation-id' => $this->getAffiliationService()->getAffiliation()->getId(),
                                ],
                            ],
                        ],
                    ]
                );
                break;
            case 'community/affiliation/loi/replace':
                $loi = $this->getAffiliationService()->findEntityById('Loi', $this->getRouteMatch()->getParam('id'));
                $this->getAffiliationService()->setAffiliation($loi->getAffiliation());
                $this->getProjectService()->setProject($this->getAffiliationService()->getAffiliation()->getProject());
                $communityNavigation->addPage(
                    [
                        'label'  => sprintf(
                            $this->translate("txt-affiliation-%s-in-%s"),
                            $this->getAffiliationService()->getAffiliation()->getOrganisation(),
                            $this->getProjectService()->parseFullName()
                        ),
                        'active' => false,
                        'route'  => 'community/affiliation/affiliation',
                        'router' => $this->getRouter(),
                        'params' => [
                            'id' => $this->getAffiliationService()->getAffiliation()->getId(),
                        ],
                        'pages'  => [
                            'replace' => [
                                'label'  => sprintf(
                                    $this->translate("txt-replace-loi-for-organisation-%s-for-project-%s"),
                                    $this->getAffiliationService()->getAffiliation()->getOrganisation(),
                                    $this->getAffiliationService()->getAffiliation()->getProject()
                                ),
                                'route'  => $this->getRouteMatch()->getMatchedRouteName(),
                                'active' => true,
                                'router' => $this->getRouter(),
                                'params' => $this->getRouteMatch()->getParams(),
                            ],
                        ],
                    ]
                );
                break;
        }
    }
}
