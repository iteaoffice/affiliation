<?php

/**
 * ITEA Office all rights reserved
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\View\Helper;

use Affiliation\Acl\Assertion\Affiliation as AffiliationAssertion;
use Affiliation\Entity\Affiliation;
use Project\Entity\Report\Report;

/**
 * Class EffortSpentLink
 * @package Affiliation\View\Helper
 */
class EffortSpentLink extends LinkAbstract
{
    /**
     * @param Report $report
     * @param string $action
     * @param string $show
     * @param Affiliation $affiliation
     *
     * @return string
     */
    public function __invoke(Affiliation $affiliation, $action, $show, Report $report): string
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
                'update' => $this->translator->translate('txt-update'),
            ]
        );
        if (!$this->hasAccess($this->getAffiliation(), AffiliationAssertion::class, $this->getAction())) {
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
    public function parseAction(): void
    {
        switch ($this->getAction()) {
            case 'update-effort-spent':
                $this->setRouter('community/affiliation/edit/update-effort-spent');
                $this->setText(sprintf($this->translator->translate("txt-report-on-%s"), $this->getReport()->parseName()));
                break;
            default:
                throw new \Exception(sprintf("%s is an incorrect action for %s", $this->getAction(), __CLASS__));
        }
    }
}
