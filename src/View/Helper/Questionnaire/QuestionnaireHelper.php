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

use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Questionnaire\Questionnaire;
use Affiliation\Service\QuestionnaireService;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\View\Helper\AbstractHelper;

/**
 * Class QuestionnaireHelper
 * @package Affiliation\View\Helper\Questionnaire
 */
class QuestionnaireHelper extends AbstractHelper
{
    /**
     * @var QuestionnaireService
     */
    private $questionnaireService;

    public function __construct(QuestionnaireService $questionnaireService)
    {
        $this->questionnaireService = $questionnaireService;
    }

    public function __invoke(): QuestionnaireHelper
    {
        return $this;
    }

    public function getStartDate(Questionnaire $questionnaire, Affiliation $affiliation): ?\DateTime
    {
        return $this->questionnaireService->getStartDate($questionnaire, $affiliation);
    }

    public function getEndDate(Questionnaire $questionnaire, Affiliation $affiliation): ?\DateTime
    {
        return $this->questionnaireService->getEndDate($questionnaire, $affiliation);
    }

    public function isOpen(Questionnaire $questionnaire, Affiliation $affiliation): bool
    {
        return $this->questionnaireService->isOpen($questionnaire, $affiliation);
    }

    public function parseCompletedPercentage(Questionnaire $questionnaire, Affiliation $affiliation): string
    {
        $percentage = $this->questionnaireService->parseCompletedPercentage($questionnaire, $affiliation);
        $template   = '<div class="progress" style="margin-bottom: 0; height: 2em;">
            <div class="progress-bar bg-%s" role="progressbar" aria-valuenow="%d" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em; width: %d%%;">%s</div>
        </div>';

        $style = 'danger';
        if ($percentage === 100) {
            $style = 'success';
        } elseif ($percentage > 49) {
            $style = 'warning';
        }

        $label = $percentage . '%';

        return \sprintf($template, $style, $percentage, $percentage, $label);
    }
}
