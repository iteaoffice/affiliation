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

namespace Affiliation\Controller\Questionnaire;

use Affiliation\Controller\AffiliationAbstractController;
use Affiliation\Entity\Questionnaire\Questionnaire;
use Affiliation\Service\FormService;
use Affiliation\Service\QuestionnaireService;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\View\Model\ViewModel;

/**
 * Class QuestionnaireController
 * @package Affiliation\Controller\Questionnaire
 */
final class QuestionnaireController extends AffiliationAbstractController
{
    /**
     * @var QuestionnaireService
     */
    private $questionnaireService;

    /**
     * @var FormService
     */
    private $formService;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        QuestionnaireService $questionnaireService,
        FormService          $formService,
        TranslatorInterface  $translator
    ) {
        $this->questionnaireService = $questionnaireService;
        $this->formService          = $formService;
        $this->translator           = $translator;
    }

    public function viewAction()
    {
        $questionnaire = $this->questionnaireService->find(Questionnaire::class, (int)$this->params('id'));

        if ($questionnaire === null) {
            return $this->notFoundAction();
        }

        return new ViewModel([
            'questionnaire' => $questionnaire
        ]);
    }

    public function newAction()
    {

    }

    public function editAction()
    {

    }
}
