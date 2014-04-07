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
class LoiLink extends AbstractHelper
{

    /**
     * @param Entity\Loi         $loi
     * @param string             $action
     * @param string             $show
     * @param Entity\Affiliation $affiliation
     *
     * @return string
     * @throws \Exception
     */
    public function __invoke(Entity\Loi $loi = null, $action = 'view', $show = 'name', Entity\Affiliation $affiliation = null)
    {
        $translate = $this->view->plugin('translate');
        $url       = $this->view->plugin('url');
        $serverUrl = $this->view->plugin('serverUrl');
        $isAllowed = $this->view->plugin('isAllowed');

        switch ($action) {
            case 'upload':
                $router = 'community/affiliation/loi/upload';
                $text   = sprintf($translate("txt-upload-loi-for-organisation-%s-in-project-%s-link-title"),
                    $affiliation->getOrganisation(),
                    $affiliation->getProject()
                );
                break;
            case 'render':
                $router = 'community/affiliation/loi/render';
                $text   = sprintf($translate("txt-render-loi-for-organisation-%s-in-project-%s-link-title"),
                    $affiliation->getOrganisation(),
                    $affiliation->getProject()
                );
                break;
            case 'download':
                $router = 'community/affiliation/loi/download';
                $text   = sprintf($translate("txt-download-loi-for-organisation-%s-in-project-%s-link-title"),
                    $loi->getAffiliation()->getOrganisation(),
                    $loi->getAffiliation()->getProject()
                );
                break;
            default:
                throw new \Exception(sprintf("%s is an incorrect action for %s", $action, __CLASS__));
        }

        $params = array(
            'id'             => (!is_null($loi) ? $loi->getId() : null),
            'affiliation-id' => (!is_null($affiliation) ? $affiliation->getId() : null),
            'entity'         => 'doa'
        );

        $classes     = array();
        $linkContent = array();

        switch ($show) {
            case 'icon':
                if ($action === 'edit') {
                    $linkContent[] = '<span class="glyphicon glyphicon-edit"></span>';
                } elseif ($action === 'download') {
                    $linkContent[] = '<span class="glyphicon glyphicon-download"></span>';
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
