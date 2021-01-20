<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Navigation\Invokable\Questionnaire;

use General\Navigation\Invokable\AbstractNavigationInvokable;
use Affiliation\Entity\Questionnaire\Category;
use Laminas\Navigation\Page\Mvc;

/**
 * Class CategoryLabel
 *
 * @package Affiliation\Navigation\Invokable\Questionnaire
 */
final class CategoryLabel extends AbstractNavigationInvokable
{
    public function __invoke(Mvc $page): void
    {
        $label = $this->translator->translate('txt-nav-view');

        if ($this->getEntities()->containsKey(Category::class)) {
            /** @var Category $category */
            $category = $this->getEntities()->get(Category::class);
            $page->setParams(\array_merge($page->getParams(), ['id' => $category->getId()]));
            $label = $category->getCategory();
        }
        $page->set('label', $label);
    }
}
