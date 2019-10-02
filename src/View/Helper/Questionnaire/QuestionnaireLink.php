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

use Affiliation\Acl\Assertion\QuestionnaireAssertion;
use Affiliation\Entity\Affiliation;
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
        string        $action = 'view-community',
        string        $show = 'name',
        Affiliation   $affiliation = null
    ): string {
        $this->questionnaire = $questionnaire ?? new Questionnaire();
        $this->affiliation   = $affiliation ?? new Affiliation();
        $this->setAction($action);
        $this->setShow($show);
        $this->addRouterParam('id', $this->questionnaire->getId());
        $this->addRouterParam('affiliationId', $this->affiliation->getId());

        $this->setShowOptions([
            'name' => $this->questionnaire->getQuestionnaire()
        ]);

        if (!$this->hasAccess($this->affiliation, QuestionnaireAssertion::class, $this->getAction())) {
            return '';
        }

        return $this->createLink();
    }

    public function parseAction(): void
    {
        switch ($this->getAction()) {
            case 'overview':
                $this->setRouter('community/affiliation/questionnaire/overview');
                $this->setShowOptions([
                    'notification' => $this->translator->translate('txt-questionnaires-pending')
                ]);
                break;
            case 'view-community':
                $this->setRouter('community/affiliation/questionnaire/view');
                $this->setText($this->translator->translate("txt-view-answers"));
                break;
            case 'edit-community':
                $this->setRouter('community/affiliation/questionnaire/edit');
                $this->setText($this->translator->translate("txt-update-answers"));
                break;
            case 'view-admin':
                $this->setRouter('zfcadmin/affiliation/questionnaire/view');
                $this->setText(\sprintf(
                    $this->translator->translate("txt-view-questionnaire-%s"),
                    $this->questionnaire->getQuestionnaire()
                ));
                break;
            case 'new-admin':
                $this->setRouter('zfcadmin/affiliation/questionnaire/new');
                $this->setText($this->translator->translate("txt-new-questionnaire"));
                break;
            case 'edit-admin':
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
