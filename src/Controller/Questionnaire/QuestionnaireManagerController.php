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
use Affiliation\Service\QuestionnaireService;
use Affiliation\Service\FormService;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
use Zend\Http\Request;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;
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
        FormService          $formService,
        TranslatorInterface  $translator
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
            if (!isset($data['cancel']) && $form->isValid()) {
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
}
