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
class DoaLink extends AbstractHelper
{
    /**
     * @param Entity\Doa         $doa
     * @param string             $action
     * @param string             $show
     * @param Entity\Affiliation $affiliation
     *
     * @return string
     * @throws \Exception
     */
    public function __invoke(
        Entity\Doa $doa = null,
        $action = 'view',
        $show = 'name',
        Entity\Affiliation $affiliation = null
    ) {
        $translate = $this->view->plugin('translate');
        $url       = $this->view->plugin('url');
        $serverUrl = $this->view->plugin('serverUrl');
        $isAllowed = $this->view->plugin('isAllowed');

        /**
         * Add the resource on the fly
         */
        if (is_null($doa)) {
            $doa = new Entity\Doa();
        }

        $auth      = $this->view->getHelperPluginManager()->getServiceLocator()->get('BjyAuthorize\Service\Authorize');
        $assertion = $this->view->getHelperPluginManager()->getServiceLocator()->get('affiliation_acl_assertion_doa');

        if (!is_null($doa) && !$auth->getAcl()->hasResource($doa)) {
            $auth->getAcl()->addResource($doa);
            $auth->getAcl()->allow([], $doa, [], $assertion);
        }

        if (!is_null($doa) && !$isAllowed($doa, $action)) {
            return $action . ' is not possible for ' . $doa->getId();
        }

        switch ($action) {
            case 'upload':
                $router = 'community/affiliation/doa/upload';
                $text   = sprintf(
                    $translate("txt-upload-doa-for-organisation-%s-in-project-%s-link-title"),
                    $affiliation->getOrganisation(),
                    $affiliation->getProject()
                );
                break;
            case 'render':
                $router = 'community/affiliation/doa/render';
                $text   = sprintf(
                    $translate("txt-render-doa-for-organisation-%s-in-project-%s-link-title"),
                    $affiliation->getOrganisation(),
                    $affiliation->getProject()
                );
                break;
            case 'replace':
                $router = 'community/affiliation/doa/replace';
                $text   = sprintf(
                    $translate("txt-replace-doa-for-organisation-%s-in-project-%s-link-title"),
                    $doa->getAffiliation()->getOrganisation(),
                    $doa->getAffiliation()->getProject()
                );
                break;
            case 'download':
                $router = 'community/affiliation/doa/download';
                $text   = sprintf(
                    $translate("txt-download-doa-for-organisation-%s-in-project-%s-link-title"),
                    $doa->getAffiliation()->getOrganisation(),
                    $doa->getAffiliation()->getProject()
                );
                break;
            default:
                throw new \Exception(sprintf("%s is an incorrect action for %s", $action, __CLASS__));
        }

        $params = array(
            'id'             => (!is_null($doa) ? $doa->getId() : null),
            'affiliation-id' => (!is_null($affiliation) ? $affiliation->getId() : null),
            'entity'         => 'doa'
        );

        $classes     = [];
        $linkContent = [];

        switch ($show) {
            case 'icon':
                if ($action === 'edit') {
                    $linkContent[] = '<span class="glyphicon glyphicon-edit"></span>';
                } elseif ($action === 'download') {
                    $linkContent[] = '<span class="glyphicon glyphicon-download"></span>';
                } elseif ($action === 'replace') {
                    $linkContent[] = '<span class="glyphicon glyphicon-repeat"></span>';
                } else {
                    $linkContent[] = '<span class="glyphicon glyphicon-info-sign"></span>';
                }
                break;
            case 'button':
                $linkContent[] = '<span class="glyphicon glyphicon-info"></span> ' . $text;
                $classes[]     = "btn btn-primary";
                break;
            case 'text':
                $linkContent[] = $text;
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
