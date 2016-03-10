<?php

/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Affiliation\View\Helper;

use Affiliation\Entity\Affiliation;
use Affiliation\Entity\EntityAbstract;
use Affiliation\Service\AffiliationService;
use BjyAuthorize\Controller\Plugin\IsAllowed;
use BjyAuthorize\Service\Authorize;
use Contact\Entity\Contact;
use Contact\Service\ContactService;
use Invoice\Service\InvoiceService;
use Organisation\Service\OrganisationService;
use Project\Service\ProjectService;
use Project\Service\VersionService;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\ServerUrl;
use Zend\View\Helper\Url;
use Zend\View\HelperPluginManager;

/**
 * Class LinkAbstract.
 */
abstract class LinkAbstract extends AbstractHelper implements ServiceLocatorAwareInterface
{
    /**
     * @var HelperPluginManager
     */
    protected $serviceLocator;
    /**
     * @var RouteMatch
     */
    protected $routeMatch = null;
    /**
     * @var string Text to be placed as title or as part of the linkContent
     */
    protected $text;
    /**
     * @var string
     */
    protected $router;
    /**
     * @var string
     */
    protected $action;
    /**
     * @var string
     */
    protected $show;
    /**
     * @var int
     */
    protected $year;
    /**
     * @var int
     */
    protected $period;
    /**
     * @var string
     */
    protected $alternativeShow;
    /**
     * @var array List of parameters needed to construct the URL from the router
     */
    protected $fragment = null;
    /**
     * @var array List of parameters needed to construct the URL from the router
     */
    protected $routerParams = [];
    /**
     * @var array content of the link (will be imploded during creation of the link)
     */
    protected $linkContent = [];
    /**
     * @var array Classes to be given to the link
     */
    protected $classes = [];
    /**
     * @var array
     */
    protected $showOptions = [];
    /**
     * @var Contact
     */
    protected $contact;
    /**
     * @var Affiliation
     */
    protected $affiliation;

    /**
     * This function produces the link in the end.
     *
     * @return string
     */
    public function createLink()
    {
        /**
         * @var $url Url
         */
        $url = $this->serviceLocator->get('url');
        /**
         * @var $serverUrl ServerUrl
         */
        $serverUrl = $this->serviceLocator->get('serverUrl');
        $this->linkContent = [];
        $this->classes = [];
        $this->parseAction();
        $this->parseShow();
        if ('social' === $this->getShow()) {
            return $serverUrl->__invoke() . $url($this->router, $this->routerParams);
        }
        $uri = '<a href="%s" title="%s" class="%s">%s</a>';

        return sprintf(
            $uri,
            $serverUrl() . $url(
                $this->router,
                $this->routerParams,
                is_null($this->getFragment()) ?: ['fragment' => $this->getFragment()]
            ),
            htmlentities($this->text),
            implode(' ', $this->classes),
            in_array($this->getShow(), ['icon', 'button', 'alternativeShow']) ? implode(
                '',
                $this->linkContent
            ) : htmlentities(implode('', $this->linkContent))
        );
    }

    /**
     *
     */
    public function parseAction()
    {
        $this->action = null;
    }

    /**
     * @throws \Exception
     */
    public function parseShow()
    {
        switch ($this->getShow()) {
            case 'icon':
            case 'button':
                switch ($this->getAction()) {
                    case 'edit':
                    case 'edit-description':
                    case 'edit-community':
                    case 'edit-financial':
                    case 'update-effort-spent':
                    case 'edit-admin':
                        $this->addLinkContent('<i class="fa fa-pencil-square-o"></i>');
                        break;
                    case 'download':
                        $this->addLinkContent('<i class="fa fa-download"></i>');
                        break;
                    case 'payment-sheet-pdf':
                        $this->addLinkContent('<i class="fa fa-file-pdf-o"></i>');
                        break;

                    case 'replace':
                        $this->addLinkContent('<span class="glyphicon glyphicon-repeat"></span>');
                        break;
                    default:
                        $this->addLinkContent('<i class="fa fa-link"></i>');
                        break;
                }
                if ($this->getShow() === 'button') {
                    $this->addLinkContent(' ' . $this->getText());
                    $this->addClasses("btn btn-primary");
                }
                break;

            case 'text':
                $this->addLinkContent($this->getText());
                break;
            case 'paginator':
                if (is_null($this->getAlternativeShow())) {
                    throw new \InvalidArgumentException(
                        sprintf("this->alternativeShow cannot be null for a paginator link")
                    );
                }
                $this->addLinkContent($this->getAlternativeShow());
                break;
            case 'social':
                /*
                 * Social is treated in the createLink function, no content needs to be created
                 */

                return;
                break;
            default:
                if (!array_key_exists($this->getShow(), $this->showOptions)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            "The option \"%s\" should be available in the showOptions array, only \"%s\" are available",
                            $this->getShow(),
                            implode(', ', array_keys($this->showOptions))
                        )
                    );
                }
                $this->addLinkContent($this->showOptions[$this->getShow()]);
                break;
        }
    }

    /**
     * @return string
     */
    public function getShow()
    {
        return $this->show;
    }

    /**
     * @param string $show
     */
    public function setShow($show)
    {
        $this->show = $show;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @param $linkContent
     *
     * @return $this
     */
    public function addLinkContent($linkContent)
    {
        if (!is_array($linkContent)) {
            $linkContent = [$linkContent];
        }
        foreach ($linkContent as $content) {
            $this->linkContent[] = $content;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @param string $classes
     *
     * @return $this
     */
    public function addClasses($classes)
    {
        if (!is_array($classes)) {
            $classes = [$classes];
        }
        foreach ($classes as $class) {
            $this->classes[] = $class;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getAlternativeShow()
    {
        return $this->alternativeShow;
    }

    /**
     * @param string $alternativeShow
     */
    public function setAlternativeShow($alternativeShow)
    {
        $this->alternativeShow = $alternativeShow;
    }

    /**
     * @param string $showOptions
     */
    public function setShowOptions($showOptions)
    {
        $this->showOptions = $showOptions;
    }

    /**
     * @param EntityAbstract $entity
     * @param string $assertion
     * @param string $action
     *
     * @return bool
     */
    public function hasAccess(EntityAbstract $entity, $assertion, $action)
    {
        $assertion = $this->getAssertion($assertion);
        if (!is_null($entity) && !$this->getAuthorizeService()->getAcl()->hasResource($entity)) {
            $this->getAuthorizeService()->getAcl()->addResource($entity);
            $this->getAuthorizeService()->getAcl()->allow([], $entity, [], $assertion);
        }
        if (!$this->isAllowed($entity, $action)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $assertion
     *
     * @return mixed
     */
    public function getAssertion($assertion)
    {
        return $this->getServiceLocator()->get($assertion);
    }

    /**
     * Get the service locator.
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator->getServiceLocator();
    }

    /**
     * Set the service locator.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return AbstractHelper
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * @return Authorize
     */
    public function getAuthorizeService()
    {
        return $this->getServiceLocator()->get('BjyAuthorize\Service\Authorize');
    }

    /**
     * @param null|EntityAbstract $resource
     * @param string $privilege
     *
     * @return bool
     */
    public function isAllowed($resource, $privilege = null)
    {
        /**
         * @var $isAllowed IsAllowed
         */
        $isAllowed = $this->serviceLocator->get('isAllowed');

        return $isAllowed($resource, $privilege);
    }

    /**
     * Add a parameter to the list of parameters for the router.
     *
     * @param string $key
     * @param        $value
     * @param bool $allowNull
     */
    public function addRouterParam($key, $value, $allowNull = true)
    {
        if (!$allowNull && is_null($value)) {
            throw new \InvalidArgumentException(sprintf("null is not allowed for %s", $key));
        }
        if (!is_null($value)) {
            $this->routerParams[$key] = $value;
        }
    }

    /**
     * @return string
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param string $router
     */
    public function setRouter($router)
    {
        $this->router = $router;
    }

    /**
     * @return array
     */
    public function getRouterParams()
    {
        return $this->routerParams;
    }

    /**
     * RouteInterface match returned by the router.
     * Use a test on is_null to have the possibility to overrule the serviceLocator lookup for unit tets reasons.
     *
     * @return RouteMatch.
     */
    public function getRouteMatch()
    {
        if (is_null($this->routeMatch)) {
            $this->routeMatch = $this->getServiceLocator()->get('application')->getMvcEvent()->getRouteMatch();
        }

        return $this->routeMatch;
    }

    /**
     * @return ProjectService
     */
    public function getProjectService()
    {
        return $this->getServiceLocator()->get(ProjectService::class);
    }

    /**
     * @return AffiliationService
     */
    public function getAffiliationService()
    {
        return $this->getServiceLocator()->get(AffiliationService::class);
    }

    /**
     * @return VersionService
     */
    public function getVersionService()
    {
        return $this->getServiceLocator()->get(VersionService::class);
    }

    /**
     * @return ContactService
     */
    public function getContactService()
    {
        return clone $this->getServiceLocator()->get(ContactService::class);
    }

    /**
     * @return InvoiceService
     */
    public function getInvoiceService()
    {
        return $this->getServiceLocator()->get(InvoiceService::class);
    }

    /**
     * @return OrganisationService
     */
    public function getOrganisationService()
    {
        return $this->getServiceLocator()->get(OrganisationService::class);
    }

    /**
     * @param RouteMatch $routeMatch
     */
    public function setRouteMatch(RouteMatch $routeMatch)
    {
        $this->routeMatch = $routeMatch;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function translate($string)
    {
        return $this->serviceLocator->get('translate')->__invoke($string);
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param  int $year
     * @return LinkAbstract
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * @return int
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * @param  int $period
     * @return LinkAbstract
     */
    public function setPeriod($period)
    {
        $this->period = $period;

        return $this;
    }

    /**
     * @return array
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * @param string $fragment
     */
    public function setFragment($fragment)
    {
        $this->fragment = $fragment;
    }

    /**
     * @return Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param Contact $contact
     * @return LinkAbstract
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return Affiliation
     */
    public function getAffiliation()
    {
        return $this->affiliation;
    }

    /**
     * @param Affiliation $affiliation
     * @return LinkAbstract
     */
    public function setAffiliation($affiliation)
    {
        $this->affiliation = $affiliation;

        return $this;
    }
}
