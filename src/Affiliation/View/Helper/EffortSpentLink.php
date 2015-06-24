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
use Project\Entity\Report\Report;

/**
 * Create a link to an report.
 *
 * @category    Affiliation
 */
class EffortSpentLink extends LinkAbstract
{
    /**
     * @var Report
     */
    protected $report;
    /**
     * @var Affiliation
     */
    protected $affiliation;

    /**
     * @param Report $report
     * @param string $action
     * @param string $show
     * @param Affiliation $affiliation
     *
     * @return string
     */
    public function __invoke(Affiliation $affiliation, $action, $show, Report $report)
    {
        $this->setReport($report);
        $this->setAffiliation($affiliation);
        $this->setAction($action);
        $this->setShow($show);
        /*
         * Set the non-standard options needed to give an other link value
         */
        $this->setShowOptions(
            [
                'update' => $this->translate('txt-update')
            ]
        );
        if (!$this->hasAccess(
            $this->getAffiliation(),
            AffiliationAssertion::class,
            $this->getAction()
        )
        ) {
            return '';
        }

        $this->addRouterParam('id', $this->getAffiliation()->getId());
        $this->addRouterParam('report', $this->getReport()->getId());

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
            case 'update-effort-spent':
                $this->setRouter('community/affiliation/edit/update-effort-spent');
                $this->setText(
                    sprintf(
                        $this->translate("txt-report-on-%s"),
                        $this->getReport()->parseName()
                    )
                );
                break;
            default:
                throw new \Exception(sprintf("%s is an incorrect action for %s", $this->getAction(), __CLASS__));
        }
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
