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

namespace Affiliation\View\Helper\Questionnaire;

use Affiliation\Entity\Questionnaire\Question;
use Affiliation\Entity\Questionnaire\Questionnaire;
use Affiliation\View\Helper\LinkAbstract;

/**
 * Class QuestionnaireLink
 * @package Affiliation\View\Helper\Questionnaire
 */
class QuestionnaireLink extends LinkAbstract
{
    /**
     * @var Questionnaire
     */
    private $questionnaire;

    public function __invoke(
        Questionnaire $questionnaire = null,
        string        $action = 'view',
        string        $show = 'name'
    ): string
    {
        $this->questionnaire = $questionnaire ?? new Questionnaire();
        $this->setAction($action);
        $this->setShow($show);
        $this->addRouterParam('id', $this->questionnaire->getId());

        $this->setShowOptions([
            'name' => $this->questionnaire->getQuestionnaire()
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
                $this->setRouter('zfcadmin/affiliation/questionnaire/view');
                $this->setText(\sprintf(
                    $this->translator->translate("txt-view-questionnaire-%s"),
                    $this->questionnaire->getQuestionnaire()
                ));
                break;
            case 'new':
                $this->setRouter('zfcadmin/affiliation/questionnaire/new');
                $this->setText($this->translator->translate("txt-new-questionnaire"));
                break;
            case 'edit':
                $this->setRouter('zfcadmin/affiliation/questionnaire/edit');
                $this->setText(\sprintf(
                    $this->translator->translate("txt-edit-questionnaire-%s"),
                    $this->questionnaire->getQuestionnaire()
                ));
                break;
            default:
                throw new \Exception(\sprintf("%s is an incorrect action for %s", $this->getAction(), __CLASS__));
        }
    }
}
