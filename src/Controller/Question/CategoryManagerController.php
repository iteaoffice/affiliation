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
use Affiliation\Entity\Question\Category;
use Affiliation\Service\AffiliationQuestionService;
use Affiliation\Service\FormService;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
use Zend\Http\Request;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;

/**
 * Class CategoryManagerController
 * @package Affiliation\Controller\Question
 */
final class CategoryManagerController extends AffiliationAbstractController
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
        $query        = $this->affiliationQuestionService->findFiltered(Category::class, $filterPlugin->getFilter());

        $paginator = new Paginator(new PaginatorAdapter(new ORMPaginator($query, false)));
        $paginator::setDefaultItemCountPerPage(($page === 'all') ? PHP_INT_MAX : 20);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(\ceil($paginator->getTotalItemCount() / $paginator::getDefaultItemCountPerPage()));

        return new ViewModel([
            'paginator'     => $paginator,
            'encodedFilter' => \urlencode($filterPlugin->getHash()),
            'order'         => $filterPlugin->getOrder(),
            'direction'     => $filterPlugin->getDirection(),
        ]);
    }

    public function viewAction()
    {
        $category = $this->affiliationQuestionService->find(Category::class, (int)$this->params('id'));

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
                $this->redirect()->toRoute('zfcadmin/evaluation/report/criterion/type/list');
            }

            if ($form->isValid()) {
                /** @var Category $category */
                $category = $form->getData();

                $this->affiliationQuestionService->save($category);
                $this->redirect()->toRoute(
                    'zfcadmin/evaluation/report/criterion/type/view',
                    ['id' => $category->getId(),]
                );
            }
        }

        return new ViewModel([
            'form' => $form
        ]);
    }

    public function editAction()
    {
        $category = $this->affiliationQuestionService->find(Category::class, (int)$this->params('id'));

        if ($category === null) {
            return $this->notFoundAction();
        }

        /** @var Request $request */
        $request = $this->getRequest();
        $data    = $request->getPost()->toArray();
        $form    = $this->formService->prepare($category, $data);

        if ($request->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/evaluation/report/criterion/type/list');
            }

            if (isset($data['delete'])) {
                $this->affiliationQuestionService->delete($category);
                return $this->redirect()->toRoute('zfcadmin/evaluation/report/criterion/type/list');
            }

            if ($form->isValid()) {
                /** @var Category $category */
                $category = $form->getData();
                $this->affiliationQuestionService->save($category);
                $this->redirect()->toRoute(
                    'zfcadmin/evaluation/report/criterion/type/view',
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
