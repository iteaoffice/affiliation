<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\View\Helper\Questionnaire;

use Affiliation\Acl\Assertion\QuestionnaireAssertion;
use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Questionnaire\Questionnaire;
use General\ValueObject\Link\Link;
use General\View\Helper\AbstractLink;

/**
 * Class QuestionnaireLink
 * @package Affiliation\View\Helper\Questionnaire
 */
final class QuestionnaireLink extends AbstractLink
{
    public function __invoke(
        Questionnaire $questionnaire = null,
        string $action = 'view',
        string $show = 'name',
        Affiliation $affiliation = null
    ): string {
        $questionnaire ??= new Questionnaire();

        if (! $this->hasAccess($questionnaire, QuestionnaireAssertion::class, $action)) {
            return '';
        }

        $routeParams = [];
        $showOptions = [];
        if (! $questionnaire->isEmpty()) {
            $routeParams['id']   = $questionnaire->getId();
            $showOptions['name'] = $questionnaire->getQuestionnaire();
        }

        if (null !== $affiliation) {
            $routeParams['affiliationId'] = $affiliation->getId();
        }

        switch ($action) {
            case 'overview':
                $linkParams = [
                    'icon'  => 'fas fa-plus',
                    'route' => 'community/questionnaire/overview',
                    'text'  => $showOptions[$show] ?? $this->translator->translate('txt-view-questionnaire')
                ];
                break;
            case 'view-community':
                $linkParams = [
                    'icon'  => 'far fa-question-circle',
                    'route' => 'community/questionnaire/view',
                    'text'  => $showOptions[$show]
                        ?? $this->translator->translate('txt-view-answers')
                ];
                break;
            case 'edit-community':
                $linkParams = [
                    'icon'  => 'far fa-edit',
                    'route' => 'community/questionnaire/edit',
                    'text'  => $showOptions[$show]
                        ?? $this->translator->translate('txt-update-answers')
                ];
                break;
            case 'view-admin':
                $linkParams = [
                    'icon'  => 'far fa-question-circle',
                    'route' => 'zfcadmin/questionnaire/view',
                    'text'  => $showOptions[$show] ?? $questionnaire->getQuestionnaire()
                ];
                break;
            case 'copy-admin':
                $linkParams = [
                    'icon'  => 'far fa-clone',
                    'route' => 'zfcadmin/questionnaire/copy',
                    'text'  => $showOptions[$show] ?? $this->translator->translate('txt-copy-questionnaire')
                ];
                break;
            case 'edit-admin':
                $linkParams = [
                    'icon'  => 'far fa-edit',
                    'route' => 'zfcadmin/questionnaire/edit',
                    'text'  => $showOptions[$show]
                        ?? $this->translator->translate('txt-edit-questionnaire')
                ];
                break;
            case 'new-admin':
                $linkParams = [
                    'icon'  => 'fas fa-plus',
                    'route' => 'zfcadmin/questionnaire/new',
                    'text'  => $showOptions[$show]
                        ?? $this->translator->translate('txt-new-questionnaire')
                ];
                break;
        }

        $linkParams['action']      = $action;
        $linkParams['show']        = $show;
        $linkParams['routeParams'] = $routeParams;

        return $this->parse(Link::fromArray($linkParams));
    }
}
