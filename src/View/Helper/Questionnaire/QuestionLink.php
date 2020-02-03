<?php

/**
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/general for the canonical source repository
 */

declare(strict_types=1);

namespace Affiliation\View\Helper\Questionnaire;

use Affiliation\Entity\Questionnaire\Question;
use General\ValueObject\Link\Link;
use General\View\Helper\AbstractLink;

/**
 * Class QuestionLink
 * @package General\View\Helper
 */
final class QuestionLink extends AbstractLink
{
    public function __invoke(
        Question $question = null,
        string $action = 'view',
        string $show = 'name',
        int $length = null
    ): string
    {
        $question ??= new Question();

        $label = (($length !== null) && (strlen((string)$question->getQuestion()) > ($length + 3)))
            ? substr((string)$question->getQuestion(), 0, $length) . '...'
            : (string)$question->getQuestion();

        $routeParams = [];
        $showOptions = [];
        if (!$question->isEmpty()) {
            $routeParams['id']   = $question->getId();
            $showOptions['name'] = $label;
        }

        switch ($action) {
            case 'new':
                $linkParams = [
                    'icon'  => 'fas fa-plus',
                    'route' => 'zfcadmin/affiliation/questionnaire/question/new',
                    'text'  => $showOptions[$show]
                        ?? $this->translator->translate('txt-new-question')
                ];
                break;
            case 'edit':
                $linkParams = [
                    'icon'  => 'far fa-edit',
                    'route' => 'zfcadmin/affiliation/questionnaire/question/edit',
                    'text'  => $showOptions[$show]
                        ?? $this->translator->translate('txt-edit-question')
                ];
                break;
            case 'view':
                $linkParams = [
                    'icon'  => 'far fa-question-circle',
                    'route' => 'zfcadmin/affiliation/questionnaire/question/view',
                    'text'  => $showOptions[$show] ?? $label
                ];
                break;
        }

        $linkParams['action']      = $action;
        $linkParams['show']        = $show;
        $linkParams['routeParams'] = $routeParams;

        return $this->parse(Link::fromArray($linkParams));
    }
}
