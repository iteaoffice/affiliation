<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Controller\Questionnaire;

use Affiliation\Controller\AffiliationAbstractController;
use Affiliation\Entity\Questionnaire\Category;
use Affiliation\Form\Questionnaire\QuestionnaireFilter;
use Affiliation\Service\FormService;
use Affiliation\Service\QuestionnaireService;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
use Laminas\Http\Request;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Paginator\Paginator;
use Laminas\View\Model\ViewModel;

/**
 * Class CategoryManagerController
 * @package Affiliation\Controller\Question
 */
final class CategoryManagerController extends AffiliationAbstractController
{
    private QuestionnaireService $questionnaireService;
    private FormService $formService;
    private TranslatorInterface $translator;

    public function __construct(
        QuestionnaireService $questionnaireService,
        FormService $formService,
        TranslatorInterface $translator
    ) {
        $this->questionnaireService = $questionnaireService;
        $this->formService          = $formService;
        $this->translator           = $translator;
    }

    public function listAction()
    {
        $page         = $this->params()->fromRoute('page', 1);
        $filterPlugin = $this->getAffiliationFilter();
        $query        = $this->questionnaireService->findFiltered(Category::class, $filterPlugin->getFilter());

        $paginator = new Paginator(new PaginatorAdapter(new ORMPaginator($query, false)));
        $paginator::setDefaultItemCountPerPage(($page === 'all') ? PHP_INT_MAX : 20);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(\ceil($paginator->getTotalItemCount() / $paginator::getDefaultItemCountPerPage()));

        $form = new QuestionnaireFilter();
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
        $category = $this->questionnaireService->find(Category::class, (int)$this->params('id'));

        if ($category === null) {
            return $this->notFoundAction();
        }

        return new ViewModel([
            'category' => $category
        ]);
    }

    public function newAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $data    = $request->getPost()->toArray();
        $form    = $this->formService->prepare(new Category(), $data);
        $form->remove('delete');

        if ($request->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/affiliation/questionnaire/category/list');
            }
            if ($form->isValid()) {
                /** @var Category $category */
                $category = $form->getData();
                $this->questionnaireService->save($category);
                $this->flashMessenger()->addSuccessMessage(
                    $this->translator->translate('txt-question-category-has-successfully-been-created')
                );
                return $this->redirect()->toRoute('zfcadmin/affiliation/questionnaire/category/list');
            }
        }

        return new ViewModel([
            'form' => $form
        ]);
    }

    public function editAction()
    {
        /** @var Category $category */
        $category = $this->questionnaireService->find(Category::class, (int)$this->params('id'));

        if ($category === null) {
            return $this->notFoundAction();
        }

        /** @var Request $request */
        $request = $this->getRequest();
        $data    = $request->getPost()->toArray();
        $form    = $this->formService->prepare($category, $data);
        if ($category->getQuestions()->count() > 0) {
            $form->remove('delete');
        }

        if ($request->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/affiliation/questionnaire/category/list');
            }

            if (isset($data['delete'])) {
                $this->questionnaireService->delete($category);
                $this->flashMessenger()->addSuccessMessage(
                    $this->translator->translate('txt-question-category-has-successfully-been-removed')
                );
                return $this->redirect()->toRoute('zfcadmin/affiliation/questionnaire/category/list');
            }

            if ($form->isValid()) {
                /** @var Category $category */
                $category = $form->getData();
                $this->questionnaireService->save($category);
                $this->flashMessenger()->addSuccessMessage(
                    $this->translator->translate('txt-question-category-has-successfully-been-updated')
                );
                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/questionnaire/category/view',
                    ['id' => $category->getId()]
                );
            }
        }

        return new ViewModel([
            'form'     => $form,
            'category' => $category
        ]);
    }
}
