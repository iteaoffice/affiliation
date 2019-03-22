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
use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Questionnaire\Questionnaire;
use Affiliation\Form\Questionnaire\QuestionnaireForm;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\QuestionnaireService;
use Doctrine\ORM\EntityManager;
use Zend\Http\Request;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\View\Model\ViewModel;

/**
 * Class QuestionnaireController
 * @package Affiliation\Controller\Questionnaire
 */
final class QuestionnaireController extends AffiliationAbstractController
{
    /**
     * @var AffiliationService
     */
    private $affiliationService;
    /**
     * @var QuestionnaireService
     */
    private $questionnaireService;
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        AffiliationService   $affiliationService,
        QuestionnaireService $questionnaireService,
        EntityManager        $entityManager,
        TranslatorInterface  $translator
    ) {
        $this->affiliationService   = $affiliationService;
        $this->questionnaireService = $questionnaireService;
        $this->entityManager        = $entityManager;
        $this->translator           = $translator;
    }

    public function overviewAction()
    {
        $affiliations = $this->affiliationService->findBy(Affiliation::class, ['contact' => $this->identity()]);

        $affiliationList = [];
        foreach ($affiliations as $affiliation) {
            $affiliationList[] = [
                'affiliation'    => $affiliation,
                'questionnaires' => $this->questionnaireService->getAvailableQuestionnaires($affiliation)
            ];
        }

        return new ViewModel([
            'affiliations' => $affiliationList
        ]);
    }

    public function viewAction()
    {
        /** @var Questionnaire $questionnaire */
        $questionnaire = $this->questionnaireService->find(Questionnaire::class, (int)$this->params('id'));
        /** @var Affiliation $affiliation */
        $affiliation   = $this->questionnaireService->find(
            Affiliation::class,
            (int)$this->params('affiliationId')
        );

        if ($questionnaire === null || $affiliation === null) {
            return $this->notFoundAction();
        }

        return new ViewModel([
            'questionnaire' => $questionnaire,
            'affiliation'   => $affiliation,
            'answers'       => $this->questionnaireService->getSortedAnswers($questionnaire, $affiliation)
        ]);
    }

    public function editAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        /** @var Questionnaire $questionnaire */
        $questionnaire = $this->questionnaireService->find(Questionnaire::class, (int)$this->params('id'));
        /** @var Affiliation $affiliation */
        $affiliation   = $this->questionnaireService->find(
            Affiliation::class,
            (int)$this->params('affiliationId')
        );

        if ($questionnaire === null || $affiliation === null) {
            return $this->notFoundAction();
        }

        $form = new QuestionnaireForm(
            $questionnaire,
            $affiliation,
            $this->questionnaireService,
            $this->entityManager
        );

        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            $form->setData($data);

            if (!isset($data['cancel']) && $form->isValid()) {
                // isValid already hydrated the answer objects, so we only need to flush the entity manager
                $this->entityManager->flush();

                $this->flashMessenger()->addSuccessMessage(
                    $this->translator->translate('txt-the-answers-have-been-saved-successfully')
                );
            }

            return $this->redirect()->toRoute(
                'community/affiliation/questionnaire/view',
                ['affiliationId' => $affiliation->getId(), 'id' => $questionnaire->getId()]
            );
        }

        return new ViewModel([
            'form'          => $form,
            'questionnaire' => $questionnaire,
            'answers'       => $this->questionnaireService->getSortedAnswers($questionnaire, $affiliation)
        ]);
    }
}
