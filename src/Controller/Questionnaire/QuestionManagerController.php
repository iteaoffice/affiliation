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
use Affiliation\Entity\Questionnaire\Question;
use Affiliation\Form\Questionnaire\QuestionnaireFilter;
use Affiliation\Service\FormService;
use Affiliation\Service\QuestionnaireService;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
use Zend\Http\Request;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;

/**
 * Class QuestionManagerController
 *
 * @package Affiliation\Controller\Question
 */
final class QuestionManagerController extends AffiliationAbstractController
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
        FormService $formService,
        TranslatorInterface $translator
    ) {
        $this->questionnaireService = $questionnaireService;
        $this->formService = $formService;
        $this->translator = $translator;
    }

    public function listAction(): ViewModel
    {
        $page = $this->params()->fromRoute('page', 1);
        $filterPlugin = $this->getAffiliationFilter();
        $query = $this->questionnaireService->findFiltered(Question::class, $filterPlugin->getFilter());

        $paginator = new Paginator(new PaginatorAdapter(new ORMPaginator($query, false)));
        $paginator::setDefaultItemCountPerPage(($page === 'all') ? PHP_INT_MAX : 20);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(\ceil($paginator->getTotalItemCount() / $paginator::getDefaultItemCountPerPage()));

        $form = new QuestionnaireFilter();
        $form->setData(['filter' => $filterPlugin->getFilter()]);

        return new ViewModel(
            [
                'form'          => $form,
                'paginator'     => $paginator,
                'encodedFilter' => \urlencode($filterPlugin->getHash()),
                'order'         => $filterPlugin->getOrder(),
                'direction'     => $filterPlugin->getDirection(),
            ]
        );
    }

    public function viewAction(): ViewModel
    {
        $question = $this->questionnaireService->find(Question::class, (int)$this->params('id'));

        if ($question === null) {
            return $this->notFoundAction();
        }

        return new ViewModel(
            [
                'question' => $question
            ]
        );
    }

    public function newAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $data = $request->getPost()->toArray();
        $form = $this->formService->prepare(new Question(), $data);
        $form->remove('delete');

        if ($request->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/affiliation/questionnaire/question/list');
            }

            if (isset($data['submit']) && $form->isValid()) {
                /** @var Question $question */
                $question = $form->getData();
                $this->questionnaireService->save($question);
                $this->flashMessenger()->addSuccessMessage(
                    $this->translator->translate('txt-question-has-successfully-been-created')
                );

                return $this->redirect()->toRoute('zfcadmin/affiliation/questionnaire/question/list');
            }
        }

        return new ViewModel(
            [
                'form' => $form
            ]
        );
    }

    public function editAction()
    {
        /** @var Question $question */
        $question = $this->questionnaireService->find(Question::class, (int)$this->params('id'));

        if ($question === null) {
            return $this->notFoundAction();
        }

        /** @var Request $request */
        $request = $this->getRequest();
        $data = $request->getPost()->toArray();
        $form = $this->formService->prepare($question, $data);
        if ($this->questionnaireService->questionHasAnswers($question)) {
            $form->remove('delete');
        }

        if ($request->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/affiliation/questionnaire/question/list');
            }

            if (isset($data['delete'])) {
                $this->questionnaireService->delete($question);
                $this->flashMessenger()->addSuccessMessage(
                    $this->translator->translate('txt-question-has-successfully-been-removed')
                );
                return $this->redirect()->toRoute('zfcadmin/affiliation/questionnaire/question/list');
            }

            if ($form->isValid()) {
                /** @var Question $question */
                $question = $form->getData();
                $this->questionnaireService->save($question);
                $this->flashMessenger()->addSuccessMessage(
                    $this->translator->translate('txt-question-has-successfully-been-updated')
                );
                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/questionnaire/question/view',
                    ['id' => $question->getId()]
                );
            }
        }

        return new ViewModel(
            [
                'form'     => $form,
                'question' => $question
            ]
        );
    }
}
