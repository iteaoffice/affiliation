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

use Affiliation\Entity\Doa;
use Affiliation\Entity\DoaObject;
use Affiliation\Entity\DoaReminder as DoaReminderEntity;
use Affiliation\Form\DoaApproval;
use Affiliation\Form\DoaReminder;
use Deeplink\Entity\Target;
use Deeplink\View\Helper\DeeplinkLink;
use Zend\Validator\File\FilesSize;
use Zend\Validator\File\MimeType;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Class DoaManagerController
 *
 * @package Affiliation\Controller
 */
class DoaManagerController extends AffiliationAbstractController
{
    /**
     * @return ViewModel
     */
    public function listAction(): ViewModel
    {
        $doa = $this->getDoaService()->findNotApprovedDoa();

        $form = new DoaApproval($doa, $this->getContactService());

        return new ViewModel(
            [
                'doa'            => $doa,
                'form'           => $form,
                'projectService' => $this->getProjectService(),
            ]
        );
    }

    /**
     * @return ViewModel
     */
    public function approvalAction(): ViewModel
    {
        $doa = $this->getDoaService()->findNotApprovedDoa();
        $form = new DoaApproval($doa, $this->getContactService());

        return new ViewModel(
            [
                'doa'            => $doa,
                'form'           => $form,
                'projectService' => $this->getProjectService(),
            ]
        );
    }

    /**
     * @return ViewModel
     */
    public function missingAction(): ViewModel
    {
        $affiliation = $this->getAffiliationService()->findAffiliationWithMissingDoa();

        return new ViewModel(
            [
                'affiliation' => $affiliation,
            ]
        );
    }

    /**
     * @return array|\Zend\Http\Response|ViewModel
     */
    public function remindAction()
    {
        $affiliation = $this->getAffiliationService()->findAffiliationById((int) $this->params('affiliationId'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $form = new DoaReminder($affiliation, $this->getContactService());

        $data = array_merge(
            [
                'receiver' => $affiliation->getContact()->getId(),
            ],
            $this->getRequest()->getPost()->toArray()
        );

        //Get the corresponding template
        $webInfo = $this->getGeneralService()->findWebInfoByInfo('/affiliation/doa:reminder');

        $form->get('subject')->setValue($webInfo->getSubject());
        $form->get('message')->setValue($webInfo->getContent());

        $form->setData($data);

        if ($this->getRequest()->isPost() && $form->isValid()) {
            /*
             * Send the email to the receiving user
             */
            $receiver = $this->getContactService()->findContactById($form->getData()['receiver']);

            $email = $this->getEmailService()->create();
            $email->setFromContact($this->identity());
            $email->addTo($receiver);
            $email->setSubject(str_replace(['[project]'], [$affiliation->getProject()], $form->getData()['subject']));

            $email->setHtmlLayoutName('signature_twig');
            $email->setReceiver($receiver->getDisplayName());
            $email->setOrganisation($affiliation->getOrganisation());
            $email->setProject($affiliation->getProject());
            $email->setMessage($form->getData()['message']);

            /**
             * Create the deeplink in the email
             *
             * @var Target $target
             */
            $target = $this->deeplinkService->find(Target::class, (int)$data['deeplinkTarget']);
            //Create a deeplink for the user which redirects to the profile-page
            $deeplink = $this->deeplinkService->createDeeplink($target, $receiver, null, $affiliation->getId());

            /**
             * @var $deeplinkLink DeeplinkLink
             */
            $deeplinkLink = $this->getViewHelperManager()->get('deeplinkLink');
            $email->setDeeplink($deeplinkLink($deeplink, 'view', 'link'));

            $this->getEmailService()->send();

            //Store the reminder in the database
            $doaReminder = new DoaReminderEntity();
            $doaReminder->setAffiliation($affiliation);
            $doaReminder->setEmail($form->getData()['message']);
            $doaReminder->setReceiver($this->getContactService()->findContactById($form->getData()['receiver']));
            $doaReminder->setSender($this->identity());
            $this->getDoaService()->newEntity($doaReminder);

            $this->flashMessenger()->setNamespace('success')
                ->addMessage(
                    sprintf(
                        $this->translate("txt-reminder-for-doa-for-organisation-%s-in-project-%s-has-been-sent-to-%s"),
                        $affiliation->getOrganisation(),
                        $affiliation->getProject(),
                        $this->getContactService()->findContactById($form->getData()['receiver'])->getEmail()
                    )
                );

            return $this->redirect()->toRoute('zfcadmin/affiliation/doa/missing');
        }

        return new ViewModel(
            [
                'affiliation' => $affiliation,
                'form'        => $form,
                'webInfo'     => $webInfo
            ]
        );
    }

    /**
     * @return ViewModel
     */
    public function remindersAction(): ViewModel
    {
        $affiliation = $this->getAffiliationService()->findAffiliationById((int) $this->params('affiliationId'));

        return new ViewModel(
            [
                'affiliation' => $affiliation,
            ]
        );
    }

    /**
     * @return ViewModel
     */
    public function viewAction(): ViewModel
    {
        $doa = $this->getDoaService()->findDoaById((int) $this->params('id'));
        if (null === $doa) {
            return $this->notFoundAction();
        }

        return new ViewModel(['doa' => $doa]);
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editAction()
    {
        $doa = $this->getDoaService()->findDoaById((int) $this->params('id'));
        if (null === $doa) {
            return $this->notFoundAction();
        }

        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
        );

        $form = $this->getFormService()->prepare($doa, $doa, $data);


        //Get contacts in an organisation
        $contacts = $this->getContactService()->findContactsInAffiliation($doa->getAffiliation());
        $form->get('affiliation_entity_doa')->get('contact')->setValueOptions(
            $this->getContactService()->toFormValueOptions($contacts['contacts'])
        )->setDisableInArrayValidator(true);

        /**
         *
         */
        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/affiliation/doa/view', ['id' => $this->params('id')]);
            }

            if (isset($data['delete'])) {
                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(
                        sprintf(
                            $this->translate("txt-project-doa-for-organisation-%s-in-project-%s-has-been-removed"),
                            $doa->getAffiliation()->getOrganisation(),
                            $doa->getAffiliation()->getProject()
                        )
                    );

                $this->getDoaService()->removeEntity($doa);

                return $this->redirect()->toRoute('zfcadmin/affiliation/doa/list');
            }

            if ($form->isValid()) {
                /** @var $doa Doa */
                $doa = $form->getData();
                $fileData = $this->params()->fromFiles();

                if ($fileData['affiliation_entity_doa']['file']['error'] === 0) {
                    /*
                     * Replace the content of the object
                     */
                    if (!$doa->getObject()->isEmpty()) {
                        $doa->getObject()->first()->setObject(
                            file_get_contents($fileData['affiliation_entity_doa']['file']['tmp_name'])
                        );
                    } else {
                        $doaObject = new DoaObject();
                        $doaObject->setObject(
                            file_get_contents($fileData['affiliation_entity_doa']['file']['tmp_name'])
                        );
                        $doaObject->setDoa($doa);
                        $this->getDoaService()->newEntity($doaObject);
                    }

                    //Create a article object element
                    $fileSizeValidator = new FilesSize(PHP_INT_MAX);
                    $fileSizeValidator->isValid($fileData['affiliation_entity_doa']['file']);
                    $doa->setSize($fileSizeValidator->size);

                    $fileTypeValidator = new MimeType();
                    $fileTypeValidator->isValid($fileData['affiliation_entity_doa']['file']);
                    $doa->setContentType(
                        $this->getGeneralService()->findContentTypeByContentTypeName($fileTypeValidator->type)
                    );
                }

                $this->getDoaService()->updateEntity($doa);

                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(
                        sprintf(
                            $this->translate("txt-project-doa-for-organisation-%s-in-project-%s-has-been-updated"),
                            $doa->getAffiliation()->getOrganisation(),
                            $doa->getAffiliation()->getProject()
                        )
                    );

                return $this->redirect()->toRoute('zfcadmin/affiliation/doa/view', ['id' => $doa->getId()]);
            }
        }

        return new ViewModel(
            [
                'doa'  => $doa,
                'form' => $form,
            ]
        );
    }

    /**
     * @return JsonModel
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function approveAction(): JsonModel
    {
        $doa = $this->params()->fromPost('doa');
        $contact = $this->params()->fromPost('contact');
        $dateSigned = $this->params()->fromPost('dateSigned');

        if (empty($contact) || empty($dateSigned)) {
            return new JsonModel(
                [
                    'result' => 'error',
                    'error'  => _("txt-contact-or-date-signed-is-empty"),
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
         * @var $doa Doa
         */
        $doa = $this->getAffiliationService()->findEntityById(Doa::class, $doa);
        $doa->setContact($this->getContactService()->findContactById($contact));
        $doa->setDateSigned(\DateTime::createFromFormat('Y-m-d', $dateSigned));
        $doa->setDateApproved(new \DateTime());
        $this->getDoaService()->updateEntity($doa);

        return new JsonModel(
            [
                'result' => 'success',
            ]
        );
    }
}
