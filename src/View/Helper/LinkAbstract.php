<?php

/**
 * ITEA Office all rights reserved
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\View\Helper;

use Affiliation\Acl\Assertion\AssertionAbstract;
use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Doa;
use Affiliation\Entity\EntityAbstract;
use Affiliation\Entity\Loi;
use Affiliation\Service\AffiliationService;
use BjyAuthorize\Controller\Plugin\IsAllowed;
use BjyAuthorize\Service\Authorize;
use Contact\Entity\Contact;
use Contact\Service\ContactService;
use Invoice\Service\InvoiceService;
use Organisation\Service\OrganisationService;
use Project\Entity\Report\Report;
use Project\Service\ProjectService;
use Project\Service\VersionService;
use Zend\View\Helper\ServerUrl;
use Zend\View\Helper\Url;

/**
 * Class LinkAbstract.
 */
abstract class LinkAbstract extends AbstractViewHelper
{
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
     * @var Doa
     */
    protected $doa;
    /**
     * @var Loi
     */
    protected $loi;
    /**
     * @var Report
     */
    protected $report;

    /**
     * This function produces the link in the end.
     *
     * @return string
     */
    public function createLink(): string
    {
        /**
         * @var $url Url
         */
        $url = $this->getHelperPluginManager()->get('url');
        /**
         * @var $serverUrl ServerUrl
         */
        $serverUrl = $this->getHelperPluginManager()->get('serverUrl');
        $this->linkContent = [];
        $this->classes = [];
        $this->parseAction();
        $this->parseShow();
        if ('social' === $this->getShow()) {
            return $serverUrl() . $url($this->router, $this->routerParams);
        }
        $uri = '<a href="%s" title="%s" class="%s">%s</a>';

        return sprintf(
            $uri,
            $serverUrl() . $url(
                $this->router,
                $this->routerParams,
                is_null($this->getFragment()) ? [] : ['fragment' => $this->getFragment()]
            ),
            htmlentities((string) $this->text),
            implode(' ', $this->classes),
            in_array($this->getShow(), ['icon', 'button', 'alternativeShow'], true) ? implode('', $this->linkContent)
                : htmlentities(implode('', $this->linkContent))
        );
    }

    /**
     *
     */
    public function parseAction(): void
    {
        $this->action = null;
    }

    /**
     * @throws \Exception
     */
    public function parseShow(): void
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
        foreach ((array)$classes as $class) {
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
     * @param array $showOptions
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
     * @return AssertionAbstract
     */
    public function getAssertion($assertion)
    {
        return $this->getServiceManager()->get($assertion);
    }

    /**
     * @return Authorize
     */
    public function getAuthorizeService()
    {
        return $this->getServiceManager()->get('BjyAuthorize\Service\Authorize');
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
        $isAllowed = $this->getHelperPluginManager()->get('isAllowed');

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
     * @return ProjectService
     */
    public function getProjectService()
    {
        return $this->getServiceManager()->get(ProjectService::class);
    }

    /**
     * @return AffiliationService
     */
    public function getAffiliationService()
    {
        return $this->getServiceManager()->get(AffiliationService::class);
    }

    /**
     * @return VersionService
     */
    public function getVersionService()
    {
        return $this->getServiceManager()->get(VersionService::class);
    }

    /**
     * @return ContactService
     */
    public function getContactService()
    {
        return $this->getServiceManager()->get(ContactService::class);
    }

    /**
     * @return InvoiceService
     */
    public function getInvoiceService()
    {
        return $this->getServiceManager()->get(InvoiceService::class);
    }

    /**
     * @return OrganisationService
     */
    public function getOrganisationService()
    {
        return $this->getServiceManager()->get(OrganisationService::class);
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
     *
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
     *
     * @return LinkAbstract
     */
    public function setPeriod($period)
    {
        $this->period = $period;

        return $this;
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
     *
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
    public function getAffiliation(): Affiliation
    {
        if (is_null($this->affiliation)) {
            $this->affiliation = new Affiliation();
        }

        return $this->affiliation;
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return LinkAbstract
     */
    public function setAffiliation($affiliation)
    {
        $this->affiliation = $affiliation;

        return $this;
    }

    /**
     * @return Doa
     */
    public function getDoa(): Doa
    {
        if (is_null($this->doa)) {
            $this->doa = new Doa();
        }

        return $this->doa;
    }

    /**
     * @param Doa $doa
     */
    public function setDoa($doa)
    {
        $this->doa = $doa;
    }

    /**
     * @return Loi
     */
    public function getLoi()
    {
        if (is_null($this->loi)) {
            $this->loi = new Loi();
        }

        return $this->loi;
    }

    /**
     * @param Loi $loi
     */
    public function setLoi($loi)
    {
        $this->loi = $loi;
    }

    /**
     * @return Report
     */
    public function getReport()
    {
        if (is_null($this->report)) {
            $this->report = new Report();
        }

        return $this->report;
    }

    /**
     * @param Report $report
     */
    public function setReport($report)
    {
        $this->report = $report;
    }
}
