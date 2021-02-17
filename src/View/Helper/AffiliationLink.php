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

/**
 * Class AffiliationLink
 *
 * @package Affiliation\View\Helper
 */
final class AffiliationLink extends AbstractLink
{
    public function __invoke(
        Affiliation $affiliation,
        string $action = 'view',
        string $show = 'organisation-branch',
        int $year = null,
        int $period = null
    ): string {
        if (! $this->hasAccess($affiliation, AffiliationAssertion::class, $action)) {
            return $action !== 'view-community' ? '' : $affiliation->parseBranchedName();
        }

        $routeParams = [];
        $showOptions = [];

        $routeParams['id']     = $affiliation->getId();
        $routeParams['year']   = $year;
        $routeParams['period'] = $period;

        $showOptions['name']                = $affiliation->parseBranchedName();
        $showOptions['organisation']        = $affiliation->getOrganisation()->getOrganisation();
        $showOptions['parent-organisation'] = null === $affiliation->getParentOrganisation()
            ? 'Parent not known'
            : $affiliation->getParentOrganisation()->getOrganisation()->getOrganisation();
        $showOptions['organisation-branch'] = $affiliation->parseBranchedName();


        switch ($action) {
            case 'view-community':
                $linkParams = [
                    'icon'  => 'far fa-circle',
                    'route' => 'community/affiliation/details',
                    'text'  => $showOptions[$show] ?? $affiliation->parseBranchedName()
                ];
                break;
            case 'description':
                $linkParams = [
                    'icon'  => 'far fa-circle',
                    'route' => 'community/affiliation/description',
                    'text'  => $showOptions[$show] ?? $this->translator->translate('txt-description')
                ];
                break;
            case 'reporting':
                $linkParams = [
                    'icon'  => 'far fa-file',
                    'route' => 'community/affiliation/reporting',
                    'text'  => $showOptions[$show] ?? $this->translator->translate('txt-reporting')
                ];
                break;
            case 'market-access':
                $linkParams = [
                    'icon'  => 'far fa-circle',
                    'route' => 'community/affiliation/market-access',
                    'text'  => $showOptions[$show] ?? $this->translator->translate('txt-market-accesse')
                ];
                break;
            case 'technical-contact': //deprecated
            case 'edit-technical-contact':
                $linkParams = [
                    'icon'  => 'far fa-user',
                    'route' => 'community/affiliation/edit/technical-contact',
                    'text'  => $showOptions[$show]
                        ?? $this->translator->translate('txt-manage-technical-contact-link-text')];
                break;
            case 'edit-community':
                $linkParams = [
                    'icon'  => 'far fa-edit',
                    'route' => 'community/affiliation/edit/affiliation',
                    'text'  => $showOptions[$show]
                        ?? sprintf($this->translator->translate('txt-edit-affiliation-%s'), $affiliation->parseBranchedName())
                ];

                break;
            case 'edit-market-access':
                $linkParams = [
                    'icon'  => 'far fa-edit',
                    'route' => 'community/affiliation/edit/market-access',
                    'text'  => $showOptions[$show]
                        ?? $this->translator->translate('txt-edit-market-access')
                ];

                break;
            case 'edit-financial':
                $linkParams = [
                    'icon'  => 'far fa-edit',
                    'route' => 'community/affiliation/edit/financial',
                    'text'  => $showOptions[$show] ?? $this->translator->translate('txt-edit-financial-information')
                ];
                break;
            case 'add-associate':
                $linkParams = [
                    'icon'  => 'fas fa-user-plus',
                    'route' => 'community/affiliation/edit/add-associate',
                    'text'  => $showOptions[$show] ?? $this->translator->translate('txt-add-associate')
                ];

                break;
            case 'manage-associates':
                $linkParams = [
                    'icon'  => 'fas fa-users',
                    'route' => 'community/affiliation/edit/manage-associates',
                    'text'  => $showOptions[$show] ?? $this->translator->translate('txt-manage-associates')
                ];
                break;
            case 'edit-costs-and-effort':
                $linkParams = [
                    'icon'  => 'far fa-edit',
                    'route' => 'community/affiliation/edit/costs-and-effort',
                    'text'  => $showOptions[$show] ?? $this->translator->translate('txt-edit-costs-and-effort')
                ];
                break;
            case 'edit-description':
                $linkParams = [
                    'icon'  => 'far fa-edit',
                    'route' => 'community/affiliation/edit/description',
                    'text'  => $showOptions[$show] ?? $this->translator->translate('txt-edit-description')
                ];
                break;
            case 'view-admin':
                $linkParams = [
                    'icon'  => 'far fa-circle',
                    'route' => 'zfcadmin/affiliation/details',
                    'text'  => $showOptions[$show] ?? $affiliation->parseBranchedName()
                ];

                break;
            case 'reporting-admin':
                $linkParams = [
                    'icon'  => 'far fa-file',
                    'route' => 'zfcadmin/affiliation/reporting',
                    'text'  => $showOptions[$show] ?? $this->translator->translate('txt-reporting')
                ];

                break;
            case 'edit-admin':
                $linkParams = [
                    'icon'  => 'far fa-edit',
                    'route' => 'zfcadmin/affiliation/edit/affiliation',
                    'text'  => $showOptions[$show]
                        ?? sprintf($this->translator->translate('txt-edit-affiliation-in-admin-%s'), $affiliation->parseBranchedName())
                ];
                break;
            case 'edit-description-admin':
                $linkParams = [
                    'icon'  => 'far fa-edit',
                    'route' => 'zfcadmin/affiliation/edit/description',
                    'text'  => $showOptions[$show]
                        ?? $this->translator->translate('txt-edit-description')
                ];
                break;
            case 'edit-market-access-admin':
                $linkParams = [
                    'icon'  => 'far fa-edit',
                    'route' => 'zfcadmin/affiliation/edit/market-access',
                    'text'  => $showOptions[$show]
                        ?? $this->translator->translate('txt-edit-market-access')
                ];
                break;
            case 'edit-financial-admin':
                $linkParams = [
                    'icon'  => 'far fa-edit',
                    'route' => 'zfcadmin/affiliation/edit/financial',
                    'text'  => $showOptions[$show]
                        ?? $this->translator->translate('txt-edit-financial')
                ];
                break;
            case 'manage-associates-admin':
                $linkParams = [
                    'icon'  => 'fas fa-users',
                    'route' => 'zfcadmin/affiliation/edit/manage-associates',
                    'text'  => $showOptions[$show] ?? $this->translator->translate('txt-manage-associates')
                ];
                break;
            case 'add-associate-admin':
                $linkParams = [
                    'icon'  => 'fas fa-user-plus',
                    'route' => 'zfcadmin/affiliation/edit/add-associate',
                    'text'  => $showOptions[$show] ?? $this->translator->translate('txt-add-associate')
                ];

                break;
            case 'merge-admin':
                $linkParams = [
                    'icon'  => 'fas fa-compress-alt',
                    'route' => 'zfcadmin/affiliation/merge',
                    'text'  => $showOptions[$show]
                        ?? sprintf($this->translator->translate('txt-merge-affiliation-in-admin-%s'), $affiliation->parseBranchedName())
                ];
                break;
            case 'payment-sheet':
                $linkParams = [
                    'icon'  => 'fas fa-euro-sign',
                    'route' => 'community/affiliation/payment-sheet',
                    'text'  => $showOptions[$show]
                        ?? sprintf(
                            $this->translator->translate('txt-show-payment-sheet-of-affiliation-%s-for-%s-%s'),
                            $affiliation->parseBranchedName(),
                            $year,
                            $period
                        )
                ];
                break;
            case 'payment-sheet-contract':
                $routeParams['contract'] = 'contract';
                $linkParams              = [
                    'icon'  => 'fas fa-euro-sign',
                    'route' => 'community/affiliation/payment-sheet',
                    'text'  => $showOptions[$show]
                        ?? sprintf(
                            $this->translator->translate(
                                'txt-show-contract-based-payment-sheet-of-affiliation-%s-for-%s-%s'
                            ),
                            $affiliation->parseBranchedName(),
                            $year,
                            $period
                        )
                ];
                break;
            case 'payment-sheet-pdf':
                $linkParams = [
                    'icon'  => 'far fa-file-pdf',
                    'route' => 'community/affiliation/payment-sheet-pdf',
                    'text'  => $showOptions[$show]
                        ?? sprintf(
                            $this->translator->translate('txt-download-payment-sheet-of-affiliation-%s-for-%s-%s'),
                            $affiliation->parseBranchedName(),
                            $year,
                            $period
                        )
                ];
                break;
            case 'payment-sheet-pdf-contract':
                $routeParams['contract'] = 'contract';
                $linkParams              = [
                    'icon'  => 'far fa-file-pdf',
                    'route' => 'community/affiliation/payment-sheet-pdf',
                    'text'  => $showOptions[$show]
                        ?? sprintf(
                            $this->translator->translate('txt-download-contract-payment-sheet-of-affiliation-%s-for-%s-%s'),
                            $affiliation->parseBranchedName(),
                            $year,
                            $period
                        )
                ];
                break;
        }

        $linkParams['action']      = $action;
        $linkParams['show']        = $show;
        $linkParams['routeParams'] = $routeParams;

        return $this->parse(Link::fromArray($linkParams));
    }
}
