<?php
/**
 * ITEA Office all rights reserved
 *
 * PHP Version 7
 *
 * @category    Doa
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/affiliation for the canonical source repository
 */

declare(strict_types=1);

namespace Affiliation\Navigation\Invokable\Questionnaire;

use Admin\Navigation\Invokable\AbstractNavigationInvokable;
use Affiliation\Entity\Questionnaire\Question;
use Zend\Navigation\Page\Mvc;
use function array_merge;
use function strlen;
use function substr;

/**
 * Class QuestionLabel
 *
 * @package Affiliation\Navigation\Invokable\Question
 */
final class QuestionLabel extends AbstractNavigationInvokable
{
    public function __invoke(Mvc $page): void
    {
        $label = $this->translator->translate('txt-nav-view');

        if ($this->getEntities()->containsKey(Question::class)) {
            /** @var Question $question */
            $question = $this->getEntities()->get(Question::class);
            $page->setParams(array_merge($page->getParams(), ['id' => $question->getId()]));
            $label = (strlen($question->getQuestion()) > 33)
                ? substr($question->getQuestion(), 0, 30) . '...'
                : $question->getQuestion();
        }
        $page->set('label', $label);
    }
}
