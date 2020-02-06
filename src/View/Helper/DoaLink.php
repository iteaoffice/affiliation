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

use Affiliation\Acl\Assertion\Doa as DoaAssertion;
use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Doa;
use General\ValueObject\Link\Link;
use General\View\Helper\AbstractLink;

/**
 * Class DoaLink
 *
 * @package Affiliation\View\Helper
 */
final class DoaLink extends AbstractLink
{
    public function __invoke(
        Doa $doa = null,
        $action = 'view',
        $show = 'name',
        Affiliation $affiliation = null
    ): string {
        $doa ??= (new Doa())->setAffiliation($affiliation);

        if (! $this->hasAccess($doa, DoaAssertion::class, $action)) {
            return '';
        }

        $routeParams = [];
        $showOptions = [];

        if (! $doa->isEmpty()) {
            $routeParams['id'] = $doa->getId();

            $showOptions['name'] = $doa->parseFileName();

            if (! $doa->getObject()->isEmpty()) {
                $routeParams['ext'] = $doa->getContentType()->getExtension();
            }
        }

        if (null !== $affiliation) {
            $routeParams['affiliationId'] = $affiliation->getId();
        }

        switch ($action) {
            case 'submit':
                $linkParams = [
                    'icon'  => 'far fa-share-square',
                    'route' => 'community/affiliation/doa/submit',
                    'text'  => $showOptions[$show]
                        ?? sprintf(
                            $this->translator->translate('txt-submit-doa-for-organisation-%s-in-project-%s-link-title'),
                            $affiliation->parseBranchedName(),
                            $affiliation->getProject()
                        )
                ];
                break;
            case 'render':
                $linkParams = [
                    'icon'  => 'far fa-file-pdf',
                    'route' => 'community/affiliation/doa/render',
                    'text'  => $showOptions[$show]
                        ?? sprintf(
                            $this->translator->translate('txt-render-doa-for-organisation-%s-in-project-%s-link-title'),
                            $affiliation->parseBranchedName(),
                            $affiliation->getProject()
                        )
                ];

                break;
            case 'replace':
                $linkParams = [
                    'icon'  => 'fa-refresh',
                    'route' => 'community/affiliation/doa/replace',
                    'text'  => $showOptions[$show]
                        ?? sprintf(
                            $this->translator->translate('txt-replace-doa-for-organisation-%s-in-project-%s-link-title'),
                            $doa->getAffiliation()->parseBranchedName(),
                            $doa->getAffiliation()->getProject()
                        )
                ];

                break;
            case 'download':
                $linkParams = [
                    'icon'  => 'far fa-file-pdf',
                    'route' => 'community/affiliation/doa/download',
                    'text'  => $showOptions[$show]
                        ?? sprintf(
                            $this->translator->translate('txt-download-doa-for-organisation-%s-in-project-%s-link-title'),
                            $doa->getAffiliation()->parseBranchedName(),
                            $doa->getAffiliation()->getProject()
                        )
                ];

                break;
            case 'reminders-admin':
                $linkParams = [
                    'icon'  => 'far fa-bell',
                    'route' => 'zfcadmin/affiliation/doa/reminders',
                    'text'  => $showOptions[$show]
                        ?? $this->translator->translate('txt-see-reminders')
                ];
                break;
            case 'remind-admin':
                $linkParams = [
                    'icon'  => 'far fa-bell',
                    'route' => 'zfcadmin/affiliation/doa/remind',
                    'text'  => $showOptions[$show]
                        ?? $this->translator->translate('txt-send-reminder')
                ];
                break;
            case 'approval-admin':
                $linkParams = [
                    'icon'  => 'far fa-thumbs-up',
                    'route' => 'zfcadmin/affiliation/doa/approval',
                    'text'  => $showOptions[$show]
                        ?? $this->translator->translate('txt-approval-doa')
                ];

                break;
            case 'missing-admin':
                $linkParams = [
                    'icon'  => 'far fa-star-half-alt',
                    'route' => 'zfcadmin/affiliation/doa/missing',
                    'text'  => $showOptions[$show]
                        ?? $this->translator->translate('txt-missing-doa')
                ];

                break;
            case 'view-admin':
                $linkParams = [
                    'icon'  => 'far fa-file',
                    'route' => 'zfcadmin/affiliation/doa/view',
                    'text'  => $showOptions[$show]
                        ?? $this->translator->translate('txt-view-doa')
                ];

                break;
            case 'edit-admin':
                $linkParams = [
                    'icon'  => 'far fa-edit',
                    'route' => 'zfcadmin/affiliation/doa/edit',
                    'text'  => $showOptions[$show]
                        ?? $this->translator->translate('txt-edit-doa')
                ];
                break;
        }

        $linkParams['action']      = $action;
        $linkParams['show']        = $show;
        $linkParams['routeParams'] = $routeParams;

        return $this->parse(Link::fromArray($linkParams));
    }
}
