<?php

/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
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
     * @param int $year
     * @param int $period
     *
     * @return string
     *
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function __invoke(
        Affiliation $affiliation = null,
        $action = 'view',
        $show = 'name',
        $year = null,
        $period = null
    ) {
        $this->setAffiliation($affiliation);
        $this->setAction($action);
        $this->setShow($show);
        $this->setYear($year);
        $this->setPeriod($period);
        /*
         * Set the non-standard options needed to give an other link value
         */
        $this->setShowOptions(
            [
                'name'         => $this->getAffiliation(),
                'organisation' => $this->getAffiliation()->getOrganisation()->getOrganisation(),
            ]
        );
        if (!$this->hasAccess(
            $this->getAffiliation(),
            AffiliationAssertion::class,
            $this->getAction()
        )
        ) {
            return $this->getAction() !== 'view-community' ? '??' : $this->getAffiliation()->getOrganisation()->getOrganisation();
        }
        $this->addRouterParam('entity', 'Affiliation');
        $this->addRouterParam('year', $this->getYear());
        $this->addRouterParam('period', $this->getPeriod());
        $this->addRouterParam('id', $this->getAffiliation()->getId());

        return $this->createLink();
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
                $this->setText(
                    sprintf($this->translate("txt-edit-description-affiliation-%s"), $this->getAffiliation())
                );
                break;
            case 'view-admin':
                $this->setRouter('zfcadmin/affiliation/affiliation');
                $this->setText(sprintf($this->translate("txt-view-affiliation-%s"), $this->getAffiliation()));
                break;
            case 'payment-sheet-admin':
                $this->setRouter('zfcadmin/affiliation-manager/affiliation/payment-sheet');
                $this->setText(
                    sprintf($this->translate("txt-show-payment-sheet-of-affiliation-%s"), $this->getAffiliation())
                );
                break;
            case 'payment-sheet-admin-pdf':
                $this->setRouter('zfcadmin/affiliation-manager/affiliation/payment-sheet-pdf');
                $this->setText(
                    sprintf($this->translate("txt-download-payment-sheet-of-affiliation-%s"), $this->getAffiliation())
                );
                break;
            default:
                throw new \Exception(sprintf("%s is an incorrect action for %s", $this->getAction(), __CLASS__));
        }
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
    }
}
