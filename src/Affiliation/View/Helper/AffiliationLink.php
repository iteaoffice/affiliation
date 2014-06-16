<?php

/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     View
 * @subpackage  Helper
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Affiliation\View\Helper;

use Zend\View\Helper\AbstractHelper;

use Affiliation\Entity;

/**
 * Create a link to an affiliation
 *
 * @category    Affiliation
 * @package     View
 * @subpackage  Helper
 */
class AffiliationLink extends AbstractHelper
{
    /**
     * @param \Affiliation\Entity\Affiliation $affiliation
     * @param                                 $action
     * @param                                 $show
     *
     * @return string
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function __invoke(Entity\Affiliation $affiliation = null, $action = 'view', $show = 'name')
    {
        $translate = $this->view->plugin('translate');
        $url       = $this->view->plugin('url');
        $serverUrl = $this->view->plugin('serverUrl');
        $isAllowed = $this->view->plugin('isAllowed');

        /**
         * Add the resource on the fly
         */
        if (is_null($affiliation)) {
            $affiliation = new Entity\Affiliation();
        }

        $auth      = $this->view->getHelperPluginManager()->getServiceLocator()->get('BjyAuthorize\Service\Authorize');
        $assertion = $this->view->getHelperPluginManager()->getServiceLocator()->get(
            'affiliation_acl_assertion_affiliation'
        );

        if (!is_null($affiliation) && !$auth->getAcl()->hasResource($affiliation)) {
            $auth->getAcl()->addResource($affiliation);
            $auth->getAcl()->allow([], $affiliation, [], $assertion);
        }

        if (!is_null($affiliation) && !$isAllowed($affiliation, $action)) {
            return $action . ' is not possible for ' . $affiliation;
        }

        switch ($action) {
            case 'view-community':
                $router = 'community/affiliation/affiliation';
                $text   = sprintf($translate("txt-view-affiliation-%s"), $affiliation);
                break;
            case 'edit-community':
                $router = 'community/affiliation/edit';
                $text   = sprintf($translate("txt-edit-affiliation-%s"), $affiliation);
                break;
            case 'edit-financial':
                $router = 'community/affiliation/edit-financial';
                $text   = sprintf($translate("txt-edit-financial-affiliation-%s"), $affiliation);
                break;
            default:
                throw new \Exception(sprintf("%s is an incorrect action for %s", $action, __CLASS__));
        }

        $params = array(
            'id'     => $affiliation->getId(),
            'entity' => 'affiliation'
        );

        $classes     = [];
        $linkContent = [];

        switch ($show) {
            case 'icon':
                if ($action === 'edit') {
                    $linkContent[] = '<span class="glyphicon glyphicon-edit"></span>';
                } else {
                    $linkContent[] = '<span class="glyphicon glyphicon-info-sign"></span>';
                }
                break;
            case 'button':
                $linkContent[] = '<span class="glyphicon glyphicon-info-sign"></span> ' . $text;
                $classes[]     = "btn btn-primary";
                break;
            case 'text':
                $linkContent[] = $text;
                break;
            case 'organisation':
                $linkContent[] = $affiliation->getOrganisation()->getOrganisation();
                break;
            default:
                $linkContent[] = $affiliation;
                break;
        }

        $uri = '<a href="%s" title="%s" class="%s">%s</a>';

        return sprintf(
            $uri,
            $serverUrl->__invoke() . $url($router, $params),
            $text,
            implode($classes),
            implode($linkContent)
        );
    }
}
