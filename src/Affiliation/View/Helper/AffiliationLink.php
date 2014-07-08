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

use Affiliation\Acl\Assertion\Affiliation as AffiliationAssertion;
use Affiliation\Entity\Affiliation;

/**
 * Create a link to an affiliation
 *
 * @category    Affiliation
 * @package     View
 * @subpackage  Helper
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
     *
     * @return string
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function __invoke(Affiliation $affiliation = null, $action = 'view', $show = 'name')
    {
        $this->setAffiliation($affiliation);
        $this->setAction($action);
        $this->setShow($show);
        /**
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
            return $this->getAffiliation()->getOrganisation()->getOrganisation();
        }
        $this->addRouterParam('entity', 'Affiliation');
        $this->addRouterParam('id', $this->getAffiliation()->getId());

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
            case 'edit-description':
                $this->setRouter('community/affiliation/edit/description');
                $this->setText(
                    sprintf($this->translate("txt-edit-description-affiliation-%s"), $this->getAffiliation())
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
