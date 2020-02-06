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
use General\ValueObject\Link\Link;
use General\View\Helper\AbstractLink;
use Project\Entity\Report\Report;

/**
 * Class EffortSpentLink
 * @package Affiliation\View\Helper
 */
final class EffortSpentLink extends AbstractLink
{
    public function __invoke(
        Affiliation $affiliation,
        string $show,
        Report $report
    ): string {
        if (! $this->hasAccess($affiliation, AffiliationAssertion::class, 'update-effort-spent')) {
            return '';
        }

        $routeParams = [];
        $routeParams['id'] = $affiliation->getId();
        $routeParams['report'] = $report->getId();
        $showOptions['text'] = $this->translator->translate('txt-update');

        $linkParams = [
            'icon' => 'far fa-edit',
            'route' => 'community/affiliation/edit/update-effort-spent',
            'text' => $showOptions[$show]
                ?? sprintf($this->translator->translate('txt-report-on-%s'), $report->parseName())
        ];

        $linkParams['action'] = 'update-effort-spent';
        $linkParams['show'] = $show;
        $linkParams['routeParams'] = $routeParams;

        return $this->parse(Link::fromArray($linkParams));
    }
}
