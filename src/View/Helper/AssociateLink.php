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

use Affiliation\Entity\Affiliation;
use Contact\Entity\Contact;
use General\ValueObject\Link\Link;
use General\View\Helper\AbstractLink;

/**
 * Class AssociateLink
 * @package Affiliation\View\Helper
 */
final class AssociateLink extends AbstractLink
{
    public function __invoke(
        Affiliation $affiliation,
        string $action,
        string $show,
        Contact $contact
    ): string {
        $routeParams = [];
        $showOptions = [];

        $routeParams['id']      = $affiliation->getId();
        $routeParams['contact'] = $contact->getId();
        $showOptions['contact'] = $contact->parseFullName();

        $linkParams = [
            'icon'  => 'far fa-edit',
            'route' => 'zfcadmin/affiliation/edit/associate',
            'text'  => $showOptions[$show]
                ?? $this->translator->translate('txt-edit-associate')
        ];

        $linkParams['action']      = $action;
        $linkParams['show']        = $show;
        $linkParams['routeParams'] = $routeParams;

        return $this->parse(Link::fromArray($linkParams));
    }
}
