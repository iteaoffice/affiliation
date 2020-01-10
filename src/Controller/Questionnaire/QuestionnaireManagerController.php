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

namespace Affiliation\Controller\Questionnaire;

use Affiliation\Controller\AffiliationAbstractController;
use Affiliation\Entity\Questionnaire\Category;
use Affiliation\Entity\Questionnaire\Question;
use Affiliation\Entity\Questionnaire\Questionnaire;
use Affiliation\Form\Questionnaire\QuestionnaireFilter;
use Affiliation\Form\Questionnaire\QuestionnaireForm;
use Affiliation\Service\QuestionnaireService;
use Affiliation\Service\FormService;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
use Laminas\Http\Request;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Paginator\Paginator;
use Laminas\View\Model\ViewModel;

use function ceil;
use function urlencode;

/**
 * Class QuestionnaireManagerController
 * @package Affiliation\Controller\Questionnaire
 */
final class QuestionnaireManagerController extends AffiliationAbstractController
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
        $query        = $this->questionnaireService->findFiltered(Questionnaire::class, $filterPlugin->getFilter());

        $paginator = new Paginator(new PaginatorAdapter(new ORMPaginator($query, false)));
        $paginator::setDefaultItemCountPerPage(($page === 'all') ? PHP_INT_MAX : 20);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(ceil($paginator->getTotalItemCount() / $paginator::getDefaultItemCountPerPage()));

        $form = new QuestionnaireFilter();
        $form->setData(['filter' => $filterPlugin->getFilter()]);

        return new ViewModel([
            'form'          => $form,
            'paginator'     => $paginator,
            'encodedFilter' => urlencode($filterPlugin->getHash()),
            'order'         => $filterPlugin->getOrder(),
            'direction'     => $filterPlugin->getDirection(),
        ]);
    }

    public function viewAction()
    {
        /** @var Questionnaire $questionnaire */
        $questionnaire = $this->questionnaireService->find(
            Questionnaire::class,
            (int)$this->params('id')
        );

        if ($questionnaire === null) {
            return $this->notFoundAction();
        }

        return new ViewModel([
            'questionnaire' => $questionnaire
        ]);
    }

    public function newAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $data    = $request->getPost()->toArray();
        $form    = $this->formService->prepare(new Questionnaire(), $data);
        $form->remove('delete');

        if ($request->isPost()) {
            if (! isset($data['cancel']) && $form->isValid()) {
                /** @var Question $question */
                $question = $form->getData();
                $this->questionnaireService->save($question);
                $this->flashMessenger()->addSuccessMessage(
                    $this->translator->translate('txt-questionnaire-has-successfully-been-created')
                );
            }
            return $this->redirect()->toRoute('zfcadmin/affiliation/questionnaire/list');
        }

        return new ViewModel([
            'form' => $form
        ]);
    }

    public function editAction()
    {
        /** @var Questionnaire $questionnaire */
        $questionnaire = $this->questionnaireService->find(
            Questionnaire::class,
            (int)$this->params('id')
        );

        if ($questionnaire === null) {
            return $this->notFoundAction();
        }

        /** @var Request $request */
        $request = $this->getRequest();
        $data    = $request->getPost()->toArray();
        $form    = $this->formService->prepare($questionnaire, $data);
        if ($this->questionnaireService->hasAnswers($questionnaire)) {
            $form->remove('delete');
        }

        if ($request->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/affiliation/questionnaire/list');
            }

            if (isset($data['delete'])) {
                $this->questionnaireService->delete($questionnaire);
                $this->flashMessenger()->addSuccessMessage(
                    $this->translator->translate('txt-questionnaire-has-successfully-been-removed')
                );
                return $this->redirect()->toRoute('zfcadmin/affiliation/questionnaire/list');
            }

            if ($form->isValid()) {
                /** @var Questionnaire $questionnaire */
                $questionnaire = $form->getData();
                $this->questionnaireService->save($questionnaire);
                $this->flashMessenger()->addSuccessMessage(
                    $this->translator->translate('txt-questionnaire-has-successfully-been-updated')
                );
                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/questionnaire/view',
                    ['id' => $questionnaire->getId()]
                );
            }
        }

        return new ViewModel([
            'form'          => $form,
            'questionnaire' => $questionnaire,
            'categories'    => $this->questionnaireService->findBy(Category::class, [], ['sequence' => 'ASC'])
        ]);
    }

    public function copyAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        /** @var Questionnaire $questionnaire */
        $questionnaire = $this->questionnaireService->find(Questionnaire::class, (int) $this->params('id'));

        if ($questionnaire === null) {
            return $this->notFoundAction();
        }

        $questionnaireCopy = $this->questionnaireService->copyQuestionnaire($questionnaire);
        $data              = $request->getPost()->toArray();
        $form              = $this->formService->prepare($questionnaireCopy, $data);
        $form->remove('delete');

        if ($request->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/affiliation/questionnaire/list');
            }

            if ($form->isValid()) {
                /** @var Questionnaire $questionnaireCopy */
                $questionnaireCopy = $form->getData();
                $this->questionnaireService->save($questionnaireCopy);
                $this->flashMessenger()->addSuccessMessage(
                    $this->translator->translate('txt-questionnaire-has-successfully-been-copied')
                );
                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/questionnaire/view',
                    ['id' => $questionnaireCopy->getId()]
                );
            }
        }

        return new ViewModel([
            'form'          => $form,
            'questionnaire' => $questionnaire,
            'categories'    => $this->questionnaireService->findBy(Category::class, [], ['sequence' => 'ASC'])
        ]);
    }
}
