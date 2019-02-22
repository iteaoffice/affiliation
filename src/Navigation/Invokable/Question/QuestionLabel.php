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

namespace Affiliation\Navigation\Invokable\Question;

use Admin\Navigation\Invokable\AbstractNavigationInvokable;
use Affiliation\Entity\Doa;
use Affiliation\Entity\Question\Category;
use Affiliation\Entity\Question\Question;
use Zend\Navigation\Page\Mvc;

/**
 * Class QuestionLabel
 * @package Affiliation\Navigation\Invokable\Question
 */
class QuestionLabel extends AbstractNavigationInvokable
{
    /**
     * @param Mvc $page
     *
     * @return void
     */
    public function __invoke(Mvc $page): void
    {
        if ($this->getEntities()->containsKey(Question::class)) {
            /** @var Question $question */
            $question = $this->getEntities()->get(Question::class);
            $page->setParams(\array_merge($page->getParams(), ['id' => $question->getId()]));
            $label = (\strlen($question->getQuestion()) > 13)
                ? \substr($question->getQuestion(),0,10) . '...'
                : $question->getQuestion();
        } else {
            $label = $this->translator->translate('txt-nav-view');
        }
        $page->set('label', $label);
    }
}
