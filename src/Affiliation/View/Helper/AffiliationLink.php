<?php

/**
 * Japaveh Webdesign copyright message placeholder
 *
 * @category    Affiliation
 * @package     View
 * @subpackage  Helper
 * @author      Johan van der Heide <info@japaveh.nl>
 * @copyright   Copyright (c) 2004-2013 Japaveh Webdesign (http://japaveh.nl)
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
     * @param \Affiliation\Entity\Affiliation $subArea
     * @param                                 $action
     * @param                                 $show
     *
     * @return string
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function __invoke(Entity\Affiliation $subArea = null, $action = 'view', $show = 'name')
    {
        $translate = $this->view->plugin('translate');
        $url       = $this->view->plugin('url');
        $serverUrl = $this->view->plugin('serverUrl');
        $isAllowed = $this->view->plugin('isAllowed');

        if (!$isAllowed('affiliation', $action)) {
            if ($action === 'view' && $show === 'name') {
                return $subArea;
            }

            return '';
        }

        switch ($action) {
            case 'new':
                $router  = 'zfcadmin/affiliation-manager/new';
                $text    = sprintf($translate("txt-new-affiliation"));
                $subArea = new Entity\Affiliation();
                break;
            case 'edit':
                $router = 'zfcadmin/affiliation-manager/edit';
                $text   = sprintf($translate("txt-edit-affiliation-%s"), $subArea);
                break;
            case 'view':
                $router = 'affiliation/affiliation';
                $text   = sprintf($translate("txt-view-affiliation-%s"), $subArea);
                break;
            default:
                throw new \Exception(sprintf("%s is an incorrect action for %s", $action, __CLASS__));
        }

        if (is_null($subArea)) {
            throw new \RuntimeException(
                sprintf(
                    "Area needs to be an instance of %s, %s given in %s",
                    "Affiliation\Entity\Affiliation",
                    get_class($subArea),
                    __CLASS__
                )
            );
        }

        $params = array(
            'id'     => $subArea->getId(),
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
            case 'name':
                $linkContent[] = $subArea->getName();
                break;
            default:
                $linkContent[] = $subArea;
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
