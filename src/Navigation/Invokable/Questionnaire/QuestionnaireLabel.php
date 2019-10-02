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
use Affiliation\Entity\Questionnaire\Questionnaire;
use Zend\Navigation\Page\Mvc;

/**
 * Class QuestionnaireLabel
 * @package Affiliation\Navigation\Invokable\Questionnaire
 */
class QuestionnaireLabel extends AbstractNavigationInvokable
{
    /**
     * @param Mvc $page
     *
     * @return void
     */
    public function __invoke(Mvc $page): void
    {
        if ($this->getEntities()->containsKey(Questionnaire::class)) {
            /** @var Questionnaire $questionnaire */
            $questionnaire = $this->getEntities()->get(Questionnaire::class);
            $page->setParams(\array_merge($page->getParams(), ['id' => $questionnaire->getId()]));
            $label = (\strlen($questionnaire->getQuestionnaire()) > 33)
                ? \substr($questionnaire->getQuestionnaire(), 0, 30) . '...'
                : $questionnaire->getQuestionnaire();
        } else {
            $label = $this->translator->translate('txt-nav-view');
        }
        $page->set('label', $label);
    }
}
