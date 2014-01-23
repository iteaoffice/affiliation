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

        switch ($action) {
            case 'view':
                $router = 'community/affiliation/affiliation';
                $text   = sprintf($translate("txt-view-affiliation-%s"), $affiliation);
                break;
            default:
                throw new \Exception(sprintf("%s is an incorrect action for %s", $action, __CLASS__));
        }

        $params = array(
            'id'     => $affiliation->getId(),
            'entity' => 'affiliation'
        );

        $classes     = array();
        $linkContent = array();

        switch ($show) {
            case 'icon':
                if ($action === 'edit') {
                    $linkContent[] = '<i class="icon-pencil"></i>';
                } elseif ($action === 'delete') {
                    $linkContent[] = '<i class="icon-remove"></i>';
                } else {
                    $linkContent[] = '<i class="icon-info-sign"></i>';
                }
                break;
            case 'button':
                $linkContent[] = '<i class="icon-pencil icon-white"></i> ' . $text;
                $classes[]     = "btn btn-primary";
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
