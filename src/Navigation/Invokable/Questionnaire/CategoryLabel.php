<?php
/**
 * ITEA Office all rights reserved
 *
 * PHP Version 7
 *
 * @category    Doa
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/affiliation for the canonical source repository
 */

declare(strict_types=1);

namespace Affiliation\Navigation\Invokable\Questionnaire;

use Admin\Navigation\Invokable\AbstractNavigationInvokable;
use Affiliation\Entity\Questionnaire\Category;
use Zend\Navigation\Page\Mvc;

/**
 * Class CategoryLabel
 * @package Affiliation\Navigation\Invokable\Question
 */
class CategoryLabel extends AbstractNavigationInvokable
{
    /**
     * @param Mvc $page
     *
     * @return void
     */
    public function __invoke(Mvc $page): void
    {
        if ($this->getEntities()->containsKey(Category::class)) {
            /** @var Category $category */
            $category = $this->getEntities()->get(Category::class);
            $page->setParams(\array_merge($page->getParams(), ['id' => $category->getId()]));
            $label = $category->getCategory();
        } else {
            $label = $this->translator->translate('txt-nav-view');
        }
        $page->set('label', $label);
    }
}
