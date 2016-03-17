<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * PHP Version 5
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   2004-2015 ITEA Office
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        https://itea3.org
 */

namespace Affiliation\Controller;

use Affiliation\Entity\Doa;
use Affiliation\Entity\DoaObject;
use Affiliation\Entity\DoaReminder as DoaReminderEntity;
use Affiliation\Form\DoaApproval;
use Affiliation\Form\DoaReminder;
use Deeplink\Entity\Target;
use Deeplink\View\Helper\DeeplinkLink;
use Zend\Validator\File\FilesSize;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Affiliation controller.
 *
 * @category   Affiliation
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  2004-2015 ITEA Office
 * @license    https://itea3.org/license.txt proprietary
 *
 * @link       https://itea3.org
 */
class DoaManagerController extends AffiliationAbstractController
{
    /**
     * @return ViewModel
     */
    public function listAction()
    {
        $doa = $this->getDoaService()->findNotApprovedDoa();

        $form = new DoaApproval($doa, $this->getContactService());

        return new ViewModel([
            'doa'            => $doa,
            'form'           => $form,
            'projectService' => $this->getProjectService(),
        ]);
    }

    /**
     * @return ViewModel
     */
    public function approvalAction()
    {
        $doa = $this->getDoaService()->findNotApprovedDoa();
        $form = new DoaApproval($doa, $this->getContactService());

        return new ViewModel([
            'doa'            => $doa,
            'form'           => $form,
            'projectService' => $this->getProjectService(),
        ]);
    }

    /**
     * @return ViewModel
     */
    public function missingAction()
    {
        $affiliation = $this->getAffiliationService()->findAffiliationWithMissingDoa();

        return new ViewModel([
            'affiliation' => $affiliation,
        ]);
    }

    /**
     * @return ViewModel
     */
    public function remindAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId($this->getEvent()->getRouteMatch()
            ->getParam('affiliation-id'));

        $form = new DoaReminder($affiliationService->getAffiliation(), $this->getContactService());

        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
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
            $receiver = $this->getContactService()->findEntityById('contact', $form->getData()['receiver']);

            $email = $this->getEmailService()->create();
            $email->setFromContact($this->zfcUserAuthentication()->getIdentity());
            $email->addTo($receiver);
            $email->setSubject(str_replace(
                ['[project]'],
                [$affiliationService->getAffiliation()->getProject()],
                $form->getData()['subject']
            ));

            $email->setHtmlLayoutName('signature_twig');
            $email->setReceiver($receiver->getDisplayName());
            $email->setOrganisation($affiliationService->getAffiliation()->getOrganisation());
            $email->setProject($affiliationService->getAffiliation()->getProject());
            $email->setMessage($form->getData()['message']);

            /**
             * Create the deeplink in the email
             *
             * @var $target Target
             */
            $target = $this->getDeeplinkService()->findEntityById('target', $data['deeplinkTarget']);
            //Create a deeplink for the user which redirects to the profile-page
            $deeplink = $this->getDeeplinkService()
                ->createDeeplink($target, $receiver, null, $affiliationService->getAffiliation()->getId());

            /**
             * @var $deeplinkLink DeeplinkLink
             */
            $deeplinkLink = $this->getPluginManager()->getServiceLocator()->get('ViewHelperManager')->get('deeplinkLink');
            $email->setDeeplink($deeplinkLink($deeplink, 'view', 'link'));

            $this->getEmailService()->send();

            //Store the reminder in the database
            $doaReminder = new DoaReminderEntity();
            $doaReminder->setAffiliation($affiliationService->getAffiliation());
            $doaReminder->setEmail($form->getData()['message']);
            $doaReminder->setReceiver($this->getContactService()
                ->findEntityById('contact', $form->getData()['receiver']));
            $doaReminder->setSender($this->zfcUserAuthentication()->getIdentity());
            $this->getDoaService()->newEntity($doaReminder);

            $this->flashMessenger()->setNamespace('success')
                ->addMessage(sprintf(
                    $this->translate("txt-reminder-for-doa-for-organisation-%s-in-project-%s-has-been-sent-to-%s"),
                    $affiliationService->getAffiliation()->getOrganisation(),
                    $affiliationService->getAffiliation()->getProject(),
                    $this->getContactService()->findEntityById('contact', $form->getData()['receiver'])->getEmail()
                ));

            return $this->redirect()->toRoute('zfcadmin/affiliation/doa/missing');
        }

        return new ViewModel([
            'affiliationService' => $affiliationService,
            'form'               => $form,
        ]);
    }

    /**
     * @return ViewModel
     */
    public function remindersAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId($this->getEvent()->getRouteMatch()
            ->getParam('affiliation-id'));

        return new ViewModel([
            'affiliationService' => $affiliationService,
        ]);
    }

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function viewAction()
    {
        $doa = $this->getDoaService()->setDoaId($this->params('id'));
        if (is_null($doa)) {
            return $this->notFoundAction();
        }

        return new ViewModel(['doa' => $doa->getDoa()]);
    }

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $doaService = $this->getDoaService()->setDoaId($this->params('id'));

        if (is_null($doaService)) {
            return $this->notFoundAction();
        }

        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
        );

        $form = $this->getFormService()->prepare('doa', $doaService->getDoa(), $data);

        //Get contacts in an organisation
        $contactService = $this->getContactService();
        $contactService->findContactsInAffiliation($doaService->getDoa()->getAffiliation());
        $form->get('doa')->get('contact')->setValueOptions($contactService->toFormValueOptions())
            ->setDisableInArrayValidator(true);

        /**
         *
         */
        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/affiliation/doa/view', ['id' => $this->params('id')]);
            }

            if (isset($data['delete'])) {
                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(sprintf(
                        $this->translate("txt-project-doa-for-organisation-%s-in-project-%s-has-been-removed"),
                        $doaService->getDoa()->getAffiliation()->getOrganisation(),
                        $doaService->getDoa()->getAffiliation()->getProject()
                    ));

                $this->getDoaService()->removeEntity($doaService->getDoa());

                return $this->redirect()->toRoute('zfcadmin/affiliation/doa/list');
            }

            if ($form->isValid()) {
                /** @var $doa Doa */
                $doa = $form->getData();
                $fileData = $this->params()->fromFiles();

                if ($fileData['doa']['file']['error'] === 0) {
                    /*
                     * Replace the content of the object
                     */
                    if (!$doa->getObject()->isEmpty()) {
                        $doa->getObject()->first()->setObject(file_get_contents($fileData['doa']['file']['tmp_name']));
                    } else {
                        $doaObject = new DoaObject();
                        $doaObject->setObject(file_get_contents($fileData['doa']['file']['tmp_name']));
                        $doaObject->setDoa($doa);
                        $this->getDoaService()->newEntity($doaObject);
                    }

                    //Create a article object element
                    $fileSizeValidator = new FilesSize(PHP_INT_MAX);
                    $fileSizeValidator->isValid($fileData['doa']['file']);
                    $doa->setSize($fileSizeValidator->size);
                    $doa->setContentType($this->getGeneralService()
                        ->findContentTypeByContentTypeName($fileData['doa']['file']['type']));
                }

                $this->getDoaService()->updateEntity($doa);

                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(sprintf(
                        $this->translate("txt-project-doa-for-organisation-%s-in-project-%s-has-been-updated"),
                        $doa->getAffiliation()->getOrganisation(),
                        $doa->getAffiliation()->getProject()
                    ));

                return $this->redirect()->toRoute('zfcadmin/affiliation/doa/view', ['id' => $doa->getId()]);
            }
        }

        return new ViewModel([
            'doa'  => $doaService->getDoa(),
            'form' => $form,
        ]);
    }

    /**
     * Dedicated action to approve DOAs via an AJAX call.
     *
     * @return JsonModel
     */
    public function approveAction()
    {
        $doa = $this->getEvent()->getRequest()->getPost()->get('doa');
        $contact = $this->getEvent()->getRequest()->getPost()->get('contact');
        $dateSigned = $this->getEvent()->getRequest()->getPost()->get('dateSigned');

        if (empty($contact) || empty($dateSigned)) {
            return new JsonModel([
                'result' => 'error',
                'error'  => _("txt-contact-or-date-signed-is-empty"),
            ]);
        }

        if (!\DateTime::createFromFormat('Y-h-d', $dateSigned)) {
            return new JsonModel([
                'result' => 'error',
                'error'  => $this->translate("txt-incorrect-date-format-should-be-yyyy-mm-dd"),
            ]);
        }

        /*
         * @var Doa
         */
        $doa = $this->getAffiliationService()->findEntityById('Doa', $doa);
        $doa->setContact($this->getContactService()->setContactId($contact)->getContact());
        $doa->setDateSigned(\DateTime::createFromFormat('Y-h-d', $dateSigned));
        $doa->setDateApproved(new \DateTime());
        $this->getDoaService()->updateEntity($doa);

        return new JsonModel([
            'result' => 'success',
        ]);
    }
}
