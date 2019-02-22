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
use Affiliation\Entity\Question\Question;
use Affiliation\View\Helper\LinkAbstract;

/**
 * Class CategoryLink
 * @package Affiliation\View\Helper\Question
 */
class QuestionLink extends LinkAbstract
{
    /**
     * @var Question
     */
    private $question;

    /**
     * @var string
     */
    private $label = '';

    public function __invoke(
        Question $question = null,
        string   $action = 'view',
        string   $show = 'name'
    ): string
    {
        $this->question = $question ?? new Question();
        $this->label    = (\strlen((string) $this->question->getQuestion()) > 18)
            ? \substr((string) $this->question->getQuestion(),0,15) . '...'
            : (string) $this->question->getQuestion();
        $this->setAction($action);
        $this->setShow($show);

        $this->addRouterParam('id', $this->question->getId());

        $this->setShowOptions([
            'name' => $this->label
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
                $this->setRouter('zfcadmin/affiliation/question/view');
                $this->setText(\sprintf($this->translator->translate("txt-view-question-%s"), $this->label));
                break;
            case 'new':
                $this->setRouter('zfcadmin/affiliation/question/new');
                $this->setText($this->translator->translate("txt-new-question"));
                break;
            case 'edit':
                $this->setRouter('zfcadmin/affiliation/question/edit');
                $this->setText(\sprintf($this->translator->translate("txt-edit-question-%s"), $this->label));
                break;
            default:
                throw new \Exception(\sprintf("%s is an incorrect action for %s", $this->getAction(), __CLASS__));
        }
    }
}
