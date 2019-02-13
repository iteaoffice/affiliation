<?php

/**
 * ITEA Office all rights reserved
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\View\Helper\Question;

use Affiliation\Entity\Question\Category;
use Affiliation\View\Helper\LinkAbstract;

/**
 * Class CategoryLink
 * @package Affiliation\View\Helper\Question
 */
class CategoryLink extends LinkAbstract
{
    /**
     * @var Category
     */
    private $category;

    public function __invoke(
        Category $category = null,
        string   $action = 'view',
        string   $show = 'name'
    ): string
    {
        $this->category = $category ?? new Category();
        $this->setAction($action);
        $this->setShow($show);

        $this->addRouterParam('id', $this->category->getId());
        $this->setShowOptions([
            'name' => $this->category->getCategory()
        ]);

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
            case 'view':
                $this->setRouter('zfcadmin/affiliation/question/category/view');
                $this->setText(sprintf($this->translator->translate("txt-view-category-%s"), $this->category));
                break;
            case 'new':
                $this->setRouter('zfcadmin/affiliation/question/category/new');
                $this->setText($this->translator->translate("txt-new-category"));
                break;
            case 'edit':
                $this->setRouter('zfcadmin/affiliation/question/category/edit');
                $this->setText(sprintf($this->translator->translate("txt-edit-category-%s"), $this->category));
                break;
            default:
                throw new \Exception(sprintf("%s is an incorrect action for %s", $this->getAction(), __CLASS__));
        }
    }
}
