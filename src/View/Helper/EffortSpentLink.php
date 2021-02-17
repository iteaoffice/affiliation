<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\View\Helper;

use Affiliation\Acl\Assertion\AffiliationAssertion;
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
        if (! $this->hasAccess($affiliation, AffiliationAssertion::class, 'edit-effort-spent')) {
            return '';
        }

        $routeParams           = [];
        $routeParams['id']     = $affiliation->getId();
        $routeParams['report'] = $report->getId();
        $showOptions['text']   = $this->translator->translate('txt-update');

        $linkParams = [
            'icon'  => 'far fa-edit',
            'route' => 'community/affiliation/edit/effort-spent',
            'text'  => $showOptions[$show]
                ?? sprintf($this->translator->translate('txt-report-on-%s'), $report->parseName())
        ];

        $linkParams['action']      = 'effort-spent';
        $linkParams['show']        = $show;
        $linkParams['routeParams'] = $routeParams;

        return $this->parse(Link::fromArray($linkParams));
    }
}
