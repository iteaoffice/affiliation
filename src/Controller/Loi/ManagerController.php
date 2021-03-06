<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Controller\Loi;

use Affiliation\Controller\AffiliationAbstractController;
use Affiliation\Entity\Loi;
use Affiliation\Entity\LoiObject;
use Affiliation\Form\Loi\ApprovalForm;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\FormService;
use Affiliation\Service\LoiService;
use Contact\Service\ContactService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
use General\Service\EmailService;
use General\Service\GeneralService;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Paginator\Paginator;
use Laminas\Validator\File\FilesSize;
use Laminas\Validator\File\MimeType;
use Laminas\View\Model\ViewModel;
use Project\Service\ProjectService;

use function file_get_contents;
use function sprintf;

/**
 * Class ManagerController
 * @package Affiliation\Controller\Loi
 */
final class ManagerController extends AffiliationAbstractController
{
    private LoiService $loiService;
    private ContactService $contactService;
    private AffiliationService $affiliationService;
    private ProjectService $projectService;
    private GeneralService $generalService;
    private FormService $formService;
    private TranslatorInterface $translator;

    public function __construct(
        LoiService $loiService,
        ContactService $contactService,
        AffiliationService $affiliationService,
        ProjectService $projectService,
        GeneralService $generalService,
        FormService $formService,
        TranslatorInterface $translator
    ) {
        $this->loiService         = $loiService;
        $this->contactService     = $contactService;
        $this->affiliationService = $affiliationService;
        $this->projectService     = $projectService;
        $this->generalService     = $generalService;
        $this->formService        = $formService;
        $this->translator         = $translator;
    }

    public function approvalAction(): ViewModel
    {
        $loi = $this->loiService->findNotApprovedLoi();

        $form = new ApprovalForm($loi, $this->contactService);

        return new ViewModel(
            [
                'loi'            => $loi,
                'form'           => $form,
                'projectService' => $this->projectService,
            ]
        );
    }

    public function missingAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationWithMissingLoi();
        $page        = $this->params('page');

        $paginator = new Paginator(new PaginatorAdapter(new ORMPaginator($affiliation, false)));
        $paginator::setDefaultItemCountPerPage(($page === 'all') ? PHP_INT_MAX : 25);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(ceil($paginator->getTotalItemCount() / $paginator::getDefaultItemCountPerPage()));

        return new ViewModel(
            [
                'paginator' => $paginator,
            ]
        );
    }

    public function viewAction(): ViewModel
    {
        $loi = $this->loiService->findLoiById((int)$this->params('id'));
        if (null === $loi) {
            return $this->notFoundAction();
        }

        return new ViewModel(['loi' => $loi]);
    }

    public function editAction()
    {
        $loi = $this->loiService->findLoiById((int)$this->params('id'));

        if (null === $loi) {
            return $this->notFoundAction();
        }

        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
        );

        $form = $this->formService->prepare($loi, $data);


        $form->get('affiliation_entity_loi')->get('contact')->injectContact($loi->getContact());
        if (null !== $loi->getApprover()) {
            $form->get('affiliation_entity_loi')->get('approver')->injectContact($loi->getApprover());
        }


        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/affiliation/loi/view', ['id' => $loi->getId()]);
            }

            if (isset($data['delete'])) {
                $this->flashMessenger()->addSuccessMessage(
                    sprintf(
                        $this->translator->translate(
                            'txt-project-loi-for-organisation-%s-in-project-%s-has-been-removed'
                        ),
                        $loi->getAffiliation()->getOrganisation(),
                        $loi->getAffiliation()->getProject()
                    )
                );

                $this->loiService->delete($loi);

                return $this->redirect()->toRoute('zfcadmin/affiliation/details', ['id' => $loi->getAffiliation()->getId()]);
            }


            if ($form->isValid()) {
                /**
                 * @var Loi $loi
                 */
                $loi = $form->getData();

                $fileData = $this->params()->fromFiles();

                if ($fileData['affiliation_entity_loi']['file']['error'] === 0) {
                    /*
                     * Replace the content of the object
                     */
                    if (! $loi->hasObject()) {
                        $loi->getObject()->setObject(
                            file_get_contents($fileData['affiliation_entity_loi']['file']['tmp_name'])
                        );
                    } else {
                        $loiObject = new LoiObject();
                        $loiObject->setObject(
                            file_get_contents($fileData['affiliation_entity_loi']['file']['tmp_name'])
                        );
                        $loiObject->setLoi($loi);
                        $this->loiService->save($loiObject);
                    }

                    $fileSizeValidator = new FilesSize(PHP_INT_MAX);
                    $fileSizeValidator->isValid($fileData['affiliation_entity_loi']['file']);
                    $loi->setSize($fileSizeValidator->size);

                    $fileTypeValidator = new MimeType();
                    $fileTypeValidator->isValid($fileData['affiliation_entity_loi']['file']);
                    $loi->setContentType(
                        $this->generalService->findContentTypeByContentTypeName($fileTypeValidator->type)
                    );
                }

                //Remove the approver when we have no approved date
                if (null === $loi->getDateApproved()) {
                    $loi->setApprover(null);
                }

                $this->loiService->save($loi);

                $this->flashMessenger()->addSuccessMessage(
                    sprintf(
                        $this->translator->translate(
                            'txt-project-loi-for-organisation-%s-in-project-%s-has-been-updated'
                        ),
                        $loi->getAffiliation()->getOrganisation(),
                        $loi->getAffiliation()->getProject()
                    )
                );

                return $this->redirect()->toRoute('zfcadmin/affiliation/loi/view', ['id' => $loi->getId()]);
            }
        }

        return new ViewModel(
            [
                'loi'  => $loi,
                'form' => $form,
            ]
        );
    }
}
