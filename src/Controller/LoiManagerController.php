<?php
/**
 * ITEA Office all rights reserved
 *
 * PHP Version 7
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        https://itea3.org
 */

declare(strict_types=1);

namespace Affiliation\Controller;

use Affiliation\Entity\Loi;
use Affiliation\Entity\LoiObject;
use Affiliation\Entity\LoiReminder as LoiReminderEntity;
use Affiliation\Form\LoiApproval;
use Affiliation\Form\LoiReminder;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\FormService;
use Affiliation\Service\LoiService;
use Contact\Service\ContactService;
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

/**
 * Class LoiManagerController
 *
 * @package Affiliation\Controller
 */
final class LoiManagerController extends AffiliationAbstractController
{
    /**
     * @var LoiService
     */
    private $loiService;
    /**
     * @var ContactService
     */
    private $contactService;
    /**
     * @var AffiliationService
     */
    private $affiliationService;
    /**
     * @var ProjectService
     */
    private $projectService;
    /**
     * @var GeneralService
     */
    private $generalService;
    /**
     * @var EmailService
     */
    private $emailService;
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var FormService
     */
    private $formService;
    /**
     * @var TranslatorInterface
     */
    private $translator;

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


    public function listAction(): ViewModel
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

    public function remindAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('affiliationId'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $form = new LoiReminder($affiliation, $this->contactService, $this->entityManager);

        $data = $this->getRequest()->getPost()->toArray();

        //Get the corresponding template
        $webInfo = $this->generalService->findWebInfoByInfo('/affiliation/loi:reminder');

        if (null === $webInfo) {
            return $this->notFoundAction();
        }

        $form->get('subject')->setValue($webInfo->getSubject());
        $form->get('message')->setValue($webInfo->getContent());

        $form->setData($data);

        if ($this->getRequest()->isPost() && $form->isValid()) {
            $receiver = $this->contactService->findContactById((int)$form->getData()['receiver']);

            if (null === $receiver) {
                $this->emailService->setWebInfo('/affiliation/loi:reminder');
                $this->emailService->setSubject($form->getData()['subject']);
                $this->emailService->setEmailContent($form->getData()['message']);
                $this->emailService->addTo($receiver);
                $this->emailService->setTemplateVariable('receiver', $receiver->parseFullName());
                $this->emailService->setTemplateVariable('organisation', $affiliation->parseBranchedName());
                $this->emailService->setTemplateVariable('project', $affiliation->getProject()->parseFullName());

                $this->emailService->send();

                //Store the reminder in the database
                $loiReminder = new LoiReminderEntity();
                $loiReminder->setAffiliation($affiliation);
                $loiReminder->setEmail($form->getData()['message']);
                $loiReminder->setReceiver(
                    $this->contactService->findContactById((int)$form->getData()['receiver'])
                );
                $loiReminder->setSender($this->identity());
                $this->loiService->save($loiReminder);

                $this->flashMessenger()->addSuccessMessage(
                    \sprintf(
                        $this->translator->translate(
                            'txt-reminder-for-loi-for-organisation-%s-in-project-%s-has-been-sent-to-%s'
                        ),
                        $affiliation->getOrganisation(),
                        $affiliation->getProject(),
                        $this->contactService->findContactById((int)$form->getData()['receiver'])->getEmail()
                    )
                );

                return $this->redirect()->toRoute('zfcadmin/affiliation/loi/missing');
            }
        }

        return new ViewModel(
            [
                'affiliation' => $affiliation,
                'form'        => $form,
            ]
        );
    }

    public function remindersAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('affiliationId'));

        return new ViewModel(
            [
                '$affiliation' => $affiliation,
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

        //Get contacts in an organisation
        $contacts = $this->contactService->findContactsInAffiliation($loi->getAffiliation());
        $form->get('affiliation_entity_loi')->get('contact')->setValueOptions(
            $this->contactService
                ->toFormValueOptions($contacts['contacts'])
        );

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
                            \file_get_contents($fileData['affiliation_entity_loi']['file']['tmp_name'])
                        );
                    } else {
                        $loiObject = new LoiObject();
                        $loiObject->setObject(
                            \file_get_contents($fileData['affiliation_entity_loi']['file']['tmp_name'])
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

        if (!\DateTime::createFromFormat('Y-m-d', $dateSigned)) {
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
        $loi->setDateSigned(\DateTime::createFromFormat('Y-m-d', $dateSigned));
        $loi->setDateApproved(new \DateTime());
        $this->loiService->save($loi);

        return new JsonModel(
            [
                'result' => 'success',
            ]
        );
    }
}
