<?php

/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Affiliation\View\Helper;

use Affiliation\Acl\Assertion\Affiliation as AffiliationAssertion;
use Affiliation\Entity\Affiliation;

/**
 * Create a link to an affiliation.
 *
 * @category    Affiliation
 */
class AffiliationLink extends LinkAbstract
{
    /**
     * @var Affiliation
     */
    protected $affiliation;

    /**
     * @param Affiliation $affiliation
     * @param             $action
     * @param             $show
     * @param int         $year
     * @param int         $period
     * @param null        $fragment
     *
     * @return string
     *
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function __invoke(
        Affiliation $affiliation,
        $action = 'view',
        $show = 'organisation-branch',
        $year = null,
        $period = null,
        $fragment = null
    ) {
        $this->setAffiliation($affiliation);
        $this->setAction($action);
        $this->setShow($show);
        $this->setYear($year);
        $this->setPeriod($period);
        $this->setFragment($fragment);
        /*
         * Set the non-standard options needed to give an other link value
         */
        $this->setShowOptions([
            'name'                => $this->getAffiliation(),
            'organisation'        => $this->getAffiliation()->getOrganisation()->getOrganisation(),
            'organisation-branch' => $this->getAffiliation()->parseBranchedName()
        ]);
        if (!$this->hasAccess($this->getAffiliation(), AffiliationAssertion::class, $this->getAction())) {
            return $this->getAction() !== 'view-community' ? ''
                : $this->getAffiliation()->getOrganisation()->getOrganisation();
        }
        
        $this->addRouterParam('year', $this->getYear());
        $this->addRouterParam('period', $this->getPeriod());
        $this->addRouterParam('id', $this->getAffiliation()->getId());

        return $this->createLink();
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
     *
     * @return $this
     */
    public function setAffiliation($affiliation)
    {
        $this->affiliation = $affiliation;

        return $this;
    }

    /**
     * Extract the relevant parameters based on the action.
     *
     * @throws \Exception
     */
    public function parseAction()
    {
        switch ($this->getAction()) {
            case 'view-community':
                $this->setRouter('community/affiliation/affiliation');
                $this->setText(sprintf($this->translate("txt-view-affiliation-%s"), $this->getAffiliation()));
                break;
            case 'edit-community':
                $this->setRouter('community/affiliation/edit/affiliation');
                $this->setText(sprintf($this->translate("txt-edit-affiliation-%s"), $this->getAffiliation()));
                break;
            case 'edit-financial':
                $this->setRouter('community/affiliation/edit/financial');
                $this->setText(sprintf($this->translate("txt-edit-financial-affiliation-%s"), $this->getAffiliation()));
                break;
            case 'add-associate':
                $this->setRouter('community/affiliation/edit/add-associate');
                $this->setText(sprintf($this->translate("txt-add-associate-affiliation-%s"), $this->getAffiliation()));
                break;
            case 'edit-description':
                $this->setRouter('community/affiliation/edit/description');
                $this->setText(sprintf(
                    $this->translate("txt-edit-description-affiliation-%s"),
                    $this->getAffiliation()
                ));
                break;
            case 'view-admin':
                $this->setRouter('zfcadmin/affiliation/view');
                $this->setText(sprintf($this->translate("txt-view-affiliation-in-admin-%s"), $this->getAffiliation()));
                break;
            case 'edit-admin':
                $this->setRouter('zfcadmin/affiliation/edit');
                $this->setText(sprintf($this->translate("txt-edit-affiliation-in-admin-%s"), $this->getAffiliation()));
                break;
            case 'merge-admin':
                $this->setRouter('zfcadmin/affiliation/merge');
                $this->setText(sprintf($this->translate("txt-merge-affiliation-in-admin-%s"), $this->getAffiliation()));
                break;
            case 'payment-sheet':
                $this->setRouter('community/affiliation/payment-sheet');
                $this->setText(sprintf(
                    $this->translate("txt-show-payment-sheet-of-affiliation-%s-for-%s-%s"),
                    $this->getAffiliation(),
                    $this->getYear(),
                    $this->getPeriod()
                ));
                break;
            case 'payment-sheet-pdf':
                $this->setRouter('community/affiliation/payment-sheet-pdf');
                $this->setText(sprintf(
                    $this->translate("txt-download-payment-sheet-of-affiliation-%s-for-%s-%s"),
                    $this->getAffiliation(),
                    $this->getYear(),
                    $this->getPeriod()
                ));
                break;
            default:
                throw new \Exception(sprintf("%s is an incorrect action for %s", $this->getAction(), __CLASS__));
        }
    }
}
