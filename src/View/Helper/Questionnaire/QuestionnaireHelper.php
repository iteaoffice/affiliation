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

    /*
     * Checks whether a questionnaire is editable purely based on the current state of the project/affiliation.
     * Whether the user has rights (Technical Contact or Office) should be checked via the Affiliation assertion class.
     */
    public function isEditable(Questionnaire $questionnaire, Affiliation $affiliation): bool
    {
        $now       = new \DateTime();
        $startDate = $this->getStartDate($questionnaire, $affiliation);
        $endDate   = $this->getEndDate($questionnaire, $affiliation);
        return (
            (($startDate !== null) && ($now >= $startDate))
            && (($endDate === null) || ($now <= $endDate))
        );
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
