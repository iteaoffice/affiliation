<?php

/**
 * ITEA Office copyright message placeholder.
 *
 * PHP Version 5
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   2004-2015 ITEA Office
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */

namespace Affiliation\Controller\Plugin;

use Zend\Http\Request;
use Zend\Mvc\Controller\PluginManager;
use Zend\Mvc\Router\RouteMatch;

/**
 * @category    Application
 */
class GetFilter extends AbstractPlugin
{
    /**
     * @var PluginManager
     */
    protected $serviceManager;
    /**
     * @var array
     */
    protected $filter = [];

    /**
     * Instantiate the filter
     *
     * @return GetFilter
     */
    public function __invoke()
    {
        $encodedFilter = urldecode($this->getRouteMatch()->getParam('encodedFilter'));

        $order     = $this->getRequest()->getQuery('order');
        $direction = $this->getRequest()->getQuery('direction');

        //Take the filter from the URL
        $filter = json_decode(base64_decode($encodedFilter));


        //If the form is submitted, refresh the URL
        if ($this->getRequest()->isGet() && ! is_null($this->getRequest()->getQuery('submit'))) {
            $filter = $this->getRequest()->getQuery()->toArray()['filter'];
        }

        //Create a new filter if not set already
        if ( ! $filter) {
            $filter = [];
        }

        //Add a default order and direction if not known in the filter
        if ( ! isset($filter['order'])) {
            $filter['order']     = 'id';
            $filter['direction'] = 'desc';
        }

        //Overrule the order if set in the query
        if ( ! is_null($order)) {
            $filter['order'] = $order;
        }

        //Overrule the direction if set in the query
        if ( ! is_null($direction)) {
            $filter['direction'] = $direction;
        }

        $this->filter = $filter;

        return $this;
    }

    /**
     * Return the filter
     *
     * @return array
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->filter['order'];
    }

    /**
     * @return mixed
     */
    public function getDirection()
    {
        return $this->filter['direction'];
    }

    /**
     * Give the compressed version of the filter
     *
     * @return string
     */
    public function getHash()
    {
        return base64_encode(json_encode($this->filter));
    }
}
