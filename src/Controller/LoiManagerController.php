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
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
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
class LoiManagerController extends AffiliationAbstractController
{
    /**
     * @return ViewModel
     */
    public function listAction()
    {
        $loi = $this->getLoiService()->findNotApprovedLoi();

        $form = new LoiApproval($loi, $this->getContactService());

        return new ViewModel(
            [
                'loi'            => $loi,
                'form'           => $form,
                'projectService' => $this->getProjectService(),
            ]
        );
    }

    /**
     * @return ViewModel
     */
    public function approvalAction()
    {
        $loi = $this->getLoiService()->findNotApprovedLoi();

        $form = new LoiApproval($loi, $this->getContactService());

        return new ViewModel(
            [
                'loi'            => $loi,
                'form'           => $form,
                'projectService' => $this->getProjectService(),
            ]
        );
    }

    /**
     * @return ViewModel
     */
    public function missingAction()
    {
        $affiliation = $this->getAffiliationService()->findAffiliationWithMissingLoi();
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

    /**
     * @return ViewModel|array
     */
    public function remindAction()
    {
        $affiliation = $this->getAffiliationService()->findAffiliationById((int) $this->params('affiliationId'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $form = new LoiReminder($affiliation, $this->getContactService(), $this->getEntityManager());

        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
        );

        //Get the corresponding template
        $webInfo = $this->getGeneralService()->findWebInfoByInfo('/affiliation/loi:reminder');

        $form->get('subject')->setValue($webInfo->getSubject());
        $form->get('message')->setValue($webInfo->getContent());

        $form->setData($data);

        if ($this->getRequest()->isPost() && $form->isValid()) {
            /*
             * Send the email to the reminded user
             */
            $email = $this->getEmailService()->create();
            $email->setFromContact($this->identity());
            $email->addTo($this->identity());
            $email->setSubject(str_replace(['[project]'], [$affiliation->getProject()], $form->getData()['subject']));

            $email->setHtmlLayoutName('signature_twig');
            $email->setReceiver(
                $this->getContactService()->findContactById($form->getData()['receiver'])
                    ->getDisplayName()
            );
            $email->setOrganisation($affiliation->getOrganisation());
            $email->setProject($affiliation->getProject());
            $email->setMessage($form->getData()['message']);

            $this->getEmailService()->send();

            //Store the reminder in the database
            $loiReminder = new LoiReminderEntity();
            $loiReminder->setAffiliation($affiliation);
            $loiReminder->setEmail($form->getData()['message']);
            $loiReminder->setReceiver($this->getContactService()->findContactById($form->getData()['receiver']));
            $loiReminder->setSender($this->identity());
            $this->getLoiService()->newEntity($loiReminder);

            $this->flashMessenger()->setNamespace('success')
                ->addMessage(
                    sprintf(
                        $this->translate("txt-reminder-for-loi-for-organisation-%s-in-project-%s-has-been-sent-to-%s"),
                        $affiliation->getOrganisation(),
                        $affiliation->getProject(),
                        $this->getContactService()->findContactById($form->getData()['receiver'])->getEmail()
                    )
                );

            return $this->redirect()->toRoute('zfcadmin/affiliation/loi/missing');
        }

        return new ViewModel(
            [
                'affiliation' => $affiliation,
                'form'        => $form,
            ]
        );
    }

    /**
     * @return ViewModel
     */
    public function remindersAction()
    {
        $affiliation = $this->getAffiliationService()->findAffiliationById((int) $this->params('affiliationId'));

        return new ViewModel(
            [
                '$affiliation' => $affiliation,
            ]
        );
    }

    /**
     * @return \Zend\View\Model\ViewModel|array
     */
    public function viewAction()
    {
        $loi = $this->getLoiService()->findLoiById((int) $this->params('id'));
        if (\is_null($loi)) {
            return $this->notFoundAction();
        }

        return new ViewModel(['loi' => $loi]);
    }

    /**
     * @return array|\Zend\Http\Response|ViewModel
     */
    public function editAction()
    {
        $loi = $this->getLoiService()->findLoiById((int) $this->params('id'));

        if (\is_null($loi)) {
            return $this->notFoundAction();
        }

        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
        );

        $form = $this->getFormService()->prepare($loi, $loi, $data);

        //Get contacts in an organisation
        $contacts = $this->getContactService()->findContactsInAffiliation($loi->getAffiliation());
        $form->get('affiliation_entity_loi')->get('contact')->setValueOptions(
            $this->getContactService()
                ->toFormValueOptions($contacts['contacts'])
        );

        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/affiliation/loi/view', ['id' => $loi->getId()]);
            }

            if (isset($data['delete'])) {
                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(
                        sprintf(
                            $this->translate("txt-project-loi-for-organisation-%s-in-project-%s-has-been-removed"),
                            $loi->getAffiliation()->getOrganisation(),
                            $loi->getAffiliation()->getProject()
                        )
                    );

                $this->getLoiService()->removeEntity($loi);

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
                        $this->getLoiService()->newEntity($loiObject);
                    }

                    //Create a article object element
                    $fileSizeValidator = new FilesSize(PHP_INT_MAX);
                    $fileSizeValidator->isValid($fileData['affiliation_entity_loi']['file']);
                    $loi->setSize($fileSizeValidator->size);

                    $fileTypeValidator = new MimeType();
                    $fileTypeValidator->isValid($fileData['affiliation_entity_loi']['file']);
                    $loi->setContentType($this->getGeneralService()->findContentTypeByContentTypeName($fileTypeValidator->type));
                }

                $this->getLoiService()->updateEntity($loi);

                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(
                        sprintf(
                            $this->translate("txt-project-loi-for-organisation-%s-in-project-%s-has-been-updated"),
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


    /**
     * @return ViewModel
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function approveAction(): ViewModel
    {
        $loi = $this->getEvent()->getRequest()->getPost()->get('loi');
        $contact = $this->getEvent()->getRequest()->getPost()->get('contact');
        $dateSigned = $this->getEvent()->getRequest()->getPost()->get('dateSigned');

        if (empty($contact) || empty($dateSigned)) {
            return new JsonModel(
                [
                    'result' => 'error',
                    'error'  => $this->translate("txt-contact-or-date-signed-is-empty"),
                ]
            );
        }

        if (!\DateTime::createFromFormat('Y-m-d', $dateSigned)) {
            return new JsonModel(
                [
                    'result' => 'error',
                    'error'  => $this->translate("txt-incorrect-date-format-should-be-yyyy-mm-dd"),
                ]
            );
        }

        /**
         * @var $loi Loi
         */
        $loi = $this->getAffiliationService()->findEntityById(Loi::class, $loi);
        $loi->setContact($this->getContactService()->findContactById($contact));
        $loi->setApprover($this->identity());
        $loi->setDateSigned(\DateTime::createFromFormat('Y-m-d', $dateSigned));
        $loi->setDateApproved(new \DateTime());
        $this->getLoiService()->updateEntity($loi);

        return new JsonModel(
            [
                'result' => 'success',
            ]
        );
    }
}
