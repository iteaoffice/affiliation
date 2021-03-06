<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Navigation\Invokable\Questionnaire;

use General\Navigation\Invokable\AbstractNavigationInvokable;
use Affiliation\Entity\Questionnaire\Questionnaire;
use Laminas\Navigation\Page\Mvc;

use function array_merge;
use function strlen;
use function substr;

/**
 * Class QuestionnaireLabel
 *
 * @package Affiliation\Navigation\Invokable\Questionnaire
 */
final class QuestionnaireLabel extends AbstractNavigationInvokable
{
    public function __invoke(Mvc $page): void
    {
        $label = $this->translator->translate('txt-nav-view');

        if ($this->getEntities()->containsKey(Questionnaire::class)) {
            /** @var Questionnaire $questionnaire */
            $questionnaire = $this->getEntities()->get(Questionnaire::class);
            $page->setParams(array_merge($page->getParams(), ['id' => $questionnaire->getId()]));
            $label = (strlen($questionnaire->getQuestionnaire()) > 33)
                ? substr($questionnaire->getQuestionnaire(), 0, 30) . '...'
                : $questionnaire->getQuestionnaire();
        }
        $page->set('label', $label);
    }
}
