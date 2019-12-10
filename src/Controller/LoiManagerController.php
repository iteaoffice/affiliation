<?php
/**
 * ITEA Office all rights reserved
 *
 * PHP Version 7
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        https://itea3.org
 */

declare(strict_types=1);

namespace Affiliation\Controller;

use Affiliation\Entity\Loi;
use Affiliation\Entity\LoiObject;
use Affiliation\Form\LoiApproval;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\FormService;
use Affiliation\Service\LoiService;
use Contact\Service\ContactService;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
use General\Service\EmailService;
use General\Service\GeneralService;
use Project\Service\ProjectService;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Paginator\Paginator;
use Zend\Validator\File\FilesSize;
use Zend\Validator\File\MimeType;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use function file_get_contents;
use function sprintf;

/**
 * Class LoiManagerController
 *
 * @package Affiliation\Controller
 */
final class LoiManagerController extends AffiliationAbstractController
{
    private LoiService $loiService;
    private ContactService $contactService;
    private AffiliationService $affiliationService;
    private ProjectService $projectService;
    private GeneralService $generalService;
    private EmailService $emailService;
    private EntityManager $entityManager;
    private FormService $formService;
    private TranslatorInterface $translator;

    public function __construct(
        LoiService $loiService,
        ContactService $contactService,
        AffiliationService $affiliationService,
        ProjectService $projectService,
        GeneralService $generalService,
        EmailService $emailService,
        EntityManager $entityManager,
        FormService $formService,
        TranslatorInterface $translator
    ) {
        $this->loiService = $loiService;
        $this->contactService = $contactService;
        $this->affiliationService = $affiliationService;
        $this->projectService = $projectService;
        $this->generalService = $generalService;
        $this->emailService = $emailService;
        $this->entityManager = $entityManager;
        $this->formService = $formService;
        $this->translator = $translator;
    }

    public function approvalAction(): ViewModel
    {
        $loi = $this->loiService->findNotApprovedLoi();

        $form = new LoiApproval($loi, $this->contactService);

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
        $page = $this->params('page');

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

                return $this->redirect()->toRoute('zfcadmin/affiliation/loi/list');
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
                    if (!$loi->getObject()->isEmpty()) {
                        $loi->getObject()->first()->setObject(
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

                    //Create a article object element
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

    public function approveAction(): ViewModel
    {
        $loi = $this->getEvent()->getRequest()->getPost()->get('loi');
        $contact = $this->getEvent()->getRequest()->getPost()->get('contact');
        $dateSigned = $this->getEvent()->getRequest()->getPost()->get('dateSigned');

        if (empty($contact) || empty($dateSigned)) {
            return new JsonModel(
                [
                    'result' => 'error',
                    'error'  => $this->translator->translate('txt-contact-or-date-signed-is-empty'),
                ]
            );
        }

        if (!DateTime::createFromFormat('Y-m-d', $dateSigned)) {
            return new JsonModel(
                [
                    'result' => 'error',
                    'error'  => $this->translator->translate('txt-incorrect-date-format-should-be-yyyy-mm-dd'),
                ]
            );
        }

        /**
         * @var $loi Loi
         */
        $loi = $this->affiliationService->find(Loi::class, (int)$loi);
        $loi->setContact($this->contactService->findContactById((int)$contact));
        $loi->setApprover($this->identity());
        $loi->setDateSigned(DateTime::createFromFormat('Y-m-d', $dateSigned));
        $loi->setDateApproved(new DateTime());
        $this->loiService->save($loi);

        return new JsonModel(
            [
                'result' => 'success',
            ]
        );
    }
}
