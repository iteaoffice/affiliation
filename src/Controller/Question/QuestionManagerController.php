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

namespace Affiliation\Controller\Question;

use Affiliation\Controller\AffiliationAbstractController;
use Affiliation\Entity\Question\Question;
use Affiliation\Form\Question\QuestionFilter;
use Affiliation\Service\AffiliationQuestionService;
use Affiliation\Service\FormService;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
use Zend\Http\Request;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;

/**
 * Class QuestionManagerController
 * @package Affiliation\Controller\Question
 */
final class QuestionManagerController extends AffiliationAbstractController
{
    /**
     * @var AffiliationQuestionService
     */
    private $affiliationQuestionService;

    /**
     * @var FormService
     */
    private $formService;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        AffiliationQuestionService $affiliationQuestionService,
        FormService                $formService,
        TranslatorInterface        $translator
    ) {
        $this->affiliationQuestionService = $affiliationQuestionService;
        $this->formService                = $formService;
        $this->translator                 = $translator;
    }

    public function listAction()
    {
        $page         = $this->params()->fromRoute('page', 1);
        $filterPlugin = $this->getAffiliationFilter();
        $query        = $this->affiliationQuestionService->findFiltered(Question::class, $filterPlugin->getFilter());

        $paginator = new Paginator(new PaginatorAdapter(new ORMPaginator($query, false)));
        $paginator::setDefaultItemCountPerPage(($page === 'all') ? PHP_INT_MAX : 20);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(\ceil($paginator->getTotalItemCount() / $paginator::getDefaultItemCountPerPage()));

        $form = new QuestionFilter();
        $form->setData(['filter' => $filterPlugin->getFilter()]);

        return new ViewModel([
            'form'          => $form,
            'paginator'     => $paginator,
            'encodedFilter' => \urlencode($filterPlugin->getHash()),
            'order'         => $filterPlugin->getOrder(),
            'direction'     => $filterPlugin->getDirection(),
        ]);
    }

    public function viewAction()
    {
        $question = $this->affiliationQuestionService->find(Question::class, (int)$this->params('id'));

        if ($question === null) {
            return $this->notFoundAction();
        }

        return new ViewModel([
            'question' => $question
        ]);
    }

    public function newAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $data    = $request->getPost()->toArray();
        $form    = $this->formService->prepare(new Question(), $data);
        $form->remove('delete');

        if ($request->isPost()) {
            if (!isset($data['cancel']) && $form->isValid()) {
                /** @var Question $question */
                $question = $form->getData();

                $this->affiliationQuestionService->save($question);
            }
            return $this->redirect()->toRoute('zfcadmin/affiliation/question/list');
        }

        return new ViewModel([
            'form' => $form
        ]);
    }

    public function editAction()
    {
        /** @var Question $question */
        $question = $this->affiliationQuestionService->find(Question::class, (int)$this->params('id'));

        if ($question === null) {
            return $this->notFoundAction();
        }

        /** @var Request $request */
        $request = $this->getRequest();
        $data    = $request->getPost()->toArray();
        $form    = $this->formService->prepare($question, $data);
        if ($this->affiliationQuestionService->hasAnswers($question)) {
            $form->remove('delete');
        }

        if ($request->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/affiliation/question/list');
            }

            if (isset($data['delete'])) {
                $this->affiliationQuestionService->delete($question);
                return $this->redirect()->toRoute('zfcadmin/affiliation/question/list');
            }

            if ($form->isValid()) {
                /** @var Question $question */
                $question = $form->getData();
                $this->affiliationQuestionService->save($question);
                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/question/view',
                    ['id' => $question->getId()]
                );
            }
        }

        return new ViewModel([
            'form'     => $form,
            'question' => $question
        ]);
    }
}
