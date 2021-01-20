<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\View\Helper\Questionnaire;

use Affiliation\Entity\Questionnaire\Category;
use General\ValueObject\Link\Link;
use General\View\Helper\AbstractLink;

/**
 * Class CategoryLink
 * @package Affiliation\View\Helper\Questionnaire
 */
final class CategoryLink extends AbstractLink
{
    public function __invoke(
        Category $category = null,
        string $action = 'view',
        string $show = 'name',
        int $length = null
    ): string {
        $category ??= new Category();

        $routeParams = [];
        $showOptions = [];
        if (! $category->isEmpty()) {
            $routeParams['id']   = $category->getId();
            $showOptions['name'] = $category->getCategory();
        }

        switch ($action) {
            case 'new':
                $linkParams = [
                    'icon'  => 'fas fa-plus',
                    'route' => 'zfcadmin/affiliation/questionnaire/category/new',
                    'text'  => $showOptions[$show]
                        ?? $this->translator->translate('txt-new-category')
                ];
                break;
            case 'edit':
                $linkParams = [
                    'icon'  => 'far fa-edit',
                    'route' => 'zfcadmin/affiliation/questionnaire/category/edit',
                    'text'  => $showOptions[$show]
                        ?? $this->translator->translate('txt-edit-category')
                ];
                break;
            case 'view':
                $linkParams = [
                    'icon'  => 'far fa-circle',
                    'route' => 'zfcadmin/affiliation/questionnaire/category/view',
                    'text'  => $showOptions[$show] ?? $category->getCategory()
                ];
                break;
        }

        $linkParams['action']      = $action;
        $linkParams['show']        = $show;
        $linkParams['routeParams'] = $routeParams;

        return $this->parse(Link::fromArray($linkParams));
    }
}
