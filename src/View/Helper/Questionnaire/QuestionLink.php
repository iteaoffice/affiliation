<?php

/**
 * ITEA Office all rights reserved
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\View\Helper\Questionnaire;

use Affiliation\Entity\Questionnaire\Question;
use Affiliation\View\Helper\LinkAbstract;

/**
 * Class QuestionLink
 * @package Affiliation\View\Helper\Questionnaire
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
        string   $show = 'name',
        int      $length = null
    ): string {
        $this->question = $question ?? new Question();
        $this->label    = (($length !== null) && (\strlen((string) $this->question->getQuestion()) > ($length+3)))
            ? \substr((string) $this->question->getQuestion(), 0, $length) . '...'
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
                $this->setRouter('zfcadmin/affiliation/questionnaire/question/view');
                $this->setText(\sprintf($this->translator->translate("txt-view-question-%s"), $this->label));
                break;
            case 'new':
                $this->setRouter('zfcadmin/affiliation/questionnaire/question/new');
                $this->setText($this->translator->translate("txt-new-question"));
                break;
            case 'edit':
                $this->setRouter('zfcadmin/affiliation/questionnaire/question/edit');
                $this->setText(\sprintf($this->translator->translate("txt-edit-question-%s"), $this->label));
                break;
            default:
                throw new \Exception(\sprintf("%s is an incorrect action for %s", $this->getAction(), __CLASS__));
        }
    }
}
