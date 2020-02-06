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

use Affiliation\Acl\Assertion\Loi as LoiAssertion;
use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Loi;
use General\ValueObject\Link\Link;
use General\View\Helper\AbstractLink;

/**
 * Class LoiLink
 *
 * @package Affiliation\View\Helper
 */
final class LoiLink extends AbstractLink
{
    public function __invoke(
        Loi $loi = null,
        $action = 'view',
        $show = 'name',
        Affiliation $affiliation = null
    ): string {
        $loi ??= (new Loi())->setAffiliation($affiliation);

        if (! $this->hasAccess($loi, LoiAssertion::class, $action)) {
            return '';
        }

        $routeParams = [];
        $showOptions = [];

        if (! $loi->isEmpty()) {
            $routeParams['id'] = $loi->getId();
            $showOptions['name'] = $loi->parseFileName();
        }

        if (null !== $affiliation) {
            $routeParams['affiliationId'] = $affiliation->getId();
        }

        switch ($action) {
            case 'submit':
                $linkParams = [
                    'icon' => 'far fa-share-square',
                    'route' => 'community/affiliation/loi/submit',
                    'text' => $showOptions[$show]
                        ?? sprintf(
                            $this->translator->translate('txt-submit-loi-for-organisation-%s-in-project-%s-link-title'),
                            $affiliation->parseBranchedName(),
                            $affiliation->getProject()
                        )
                ];
                break;
            case 'render':
                $linkParams = [
                    'icon' => 'far fa-file-pdf',
                    'route' => 'community/affiliation/loi/render',
                    'text' => $showOptions[$show]
                        ?? sprintf(
                            $this->translator->translate('txt-render-loi-for-organisation-%s-in-project-%s-link-title'),
                            $affiliation->parseBranchedName(),
                            $affiliation->getProject()
                        )
                ];

                break;
            case 'replace':
                $linkParams = [
                    'icon' => 'fa-refresh',
                    'route' => 'community/affiliation/loi/replace',
                    'text' => $showOptions[$show]
                        ?? sprintf(
                            $this->translator->translate('txt-replace-loi-for-organisation-%s-in-project-%s-link-title'),
                            $loi->getAffiliation()->parseBranchedName(),
                            $loi->getAffiliation()->getProject()
                        )
                ];

                break;
            case 'download':
                $linkParams = [
                    'icon' => 'far fa-file-pdf',
                    'route' => 'community/affiliation/loi/download',
                    'text' => $showOptions[$show]
                        ?? sprintf(
                            $this->translator->translate('txt-download-loi-for-organisation-%s-in-project-%s-link-title'),
                            $loi->getAffiliation()->parseBranchedName(),
                            $loi->getAffiliation()->getProject()
                        )
                ];

                break;
            case 'remind-admin':
                $linkParams = [
                    'icon' => 'far fa-bell',
                    'route' => 'zfcadmin/affiliation/loi/remind',
                    'text' => $showOptions[$show]
                        ?? $this->translator->translate('txt-send-reminder')
                ];
                break;
            case 'approval-admin':
                $linkParams = [
                    'icon' => 'far fa-thumbs-up',
                    'route' => 'zfcadmin/affiliation/loi/approval',
                    'text' => $showOptions[$show]
                        ?? $this->translator->translate('txt-approval-loi')
                ];

                break;
            case 'missing-admin':
                $linkParams = [
                    'icon' => 'var fa-star-half-alt',
                    'route' => 'zfcadmin/affiliation/loi/missing',
                    'text' => $showOptions[$show]
                        ?? $this->translator->translate('txt-missing-loi')
                ];

                break;
            case 'view-admin':
                $linkParams = [
                    'icon' => 'far fa-file',
                    'route' => 'zfcadmin/affiliation/loi/view',
                    'text' => $showOptions[$show]
                        ?? $this->translator->translate('txt-view-loi')
                ];

                break;
            case 'edit-admin':
                $linkParams = [
                    'icon' => 'far fa-edit',
                    'route' => 'zfcadmin/affiliation/loi/edit',
                    'text' => $showOptions[$show]
                        ?? $this->translator->translate('txt-edit-loi')
                ];
                break;
        }

        $linkParams['action'] = $action;
        $linkParams['show'] = $show;
        $linkParams['routeParams'] = $routeParams;

        return $this->parse(Link::fromArray($linkParams));
    }
}
