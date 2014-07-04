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

use Affiliation\Acl\Assertion\Loi as LoiAssertion;
use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Loi;

/**
 * Create a link to an loi
 *
 * @category    Affiliation
 * @package     View
 * @subpackage  Helper
 */
class LoiLink extends LinkAbstract
{
    /**
     * @var Loi
     */
    protected $loi;
    /**
     * @var Affiliation
     */
    protected $affiliation;

    /**
     * @param Loi         $loi
     * @param string      $action
     * @param string      $show
     * @param Affiliation $affiliation
     *
     * @return string
     */
    public function __invoke(Loi $loi = null, $action = 'view', $show = 'name', Affiliation $affiliation = null)
    {
        $this->setLoi($loi);
        $this->setAffiliation($affiliation);
        $this->setAction($action);
        $this->setShow($show);
        /**
         * Set the non-standard options needed to give an other link value
         */
        $this->setShowOptions(
            array(
                'name' => $this->getLoi(),
            )
        );
        if (!$this->hasAccess(
            $this->getLoi(),
            LoiAssertion::class,
            $this->getAction()
        )
        ) {
            return 'Access denied';
        }
        $this->addRouterParam('entity', 'Loi');
        $this->addRouterParam('id', $this->getLoi()->getId());
        $this->addRouterParam('affiliation-id', $this->getAffiliation()->getId());

        return $this->createLink();
    }

    /**
     * Extract the relevant parameters based on the action
     *
     * @throws \Exception
     */
    public function parseAction()
    {
        switch ($this->getAction()) {
            case 'upload':
                $this->setRouter('community/affiliation/loi/upload');
                $this->setText(
                    sprintf(
                        $this->translate("txt-upload-loi-for-organisation-%s-in-project-%s-link-title"),
                        $this->getAffiliation()->getOrganisation(),
                        $this->getAffiliation()->getProject()
                    )
                );
                break;
            case 'render':
                $this->setRouter('community/affiliation/loi/render');
                $this->setText(
                    sprintf(
                        $this->translate("txt-render-loi-for-organisation-%s-in-project-%s-link-title"),
                        $this->getLoi()->getAffiliation()->getOrganisation(),
                        $this->getLoi()->getAffiliation()->getProject()
                    )
                );
                break;
            case 'replace':
                $this->setRouter('community/affiliation/loi/replace');
                $this->setText(
                    sprintf(
                        $this->translate("txt-replace-loi-for-organisation-%s-in-project-%s-link-title"),
                        $this->getLoi()->getAffiliation()->getOrganisation(),
                        $this->getLoi()->getAffiliation()->getProject()
                    )
                );
                break;
            case 'download':
                $this->setRouter('community/affiliation/loi/download');
                $this->setText(
                    sprintf(
                        $this->translate("txt-download-loi-for-organisation-%s-in-project-%s-link-title"),
                        $this->getLoi()->getAffiliation()->getOrganisation(),
                        $this->getLoi()->getAffiliation()->getProject()
                    )
                );
                break;
            default:
                throw new \Exception(sprintf("%s is an incorrect action for %s", $this->getAction(), __CLASS__));
        }
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
     * @return Affiliation
     */
    public function getAffiliation()
    {
        if (is_null($this->affiliation)) {
            $this->affiliation = new Affiliation();
        }

        return $this->affiliation;
    }

    /**
     * @param Affiliation $affiliation
     */
    public function setAffiliation($affiliation)
    {
        $this->affiliation = $affiliation;
        /**
         * Only overwrite the the Affiliation in the LOI when this is not is_null
         */
        if (!is_null($affiliation)) {
            $this->getLoi()->setAffiliation($affiliation);
        }
    }
}

