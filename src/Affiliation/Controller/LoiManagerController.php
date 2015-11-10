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

use Affiliation\Entity\LoiObject;
use Affiliation\Entity\LoiReminder as LoiReminderEntity;
use Affiliation\Form\LoiApproval;
use Affiliation\Form\LoiReminder;
use Affiliation\Service\LoiServiceAwareInterface;
use Contact\Service\ContactServiceAwareInterface;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
use General\Service\EmailServiceAwareInterface;
use General\Service\GeneralServiceAwareInterface;
use Project\Service\ProjectServiceAwareInterface;
use Zend\Paginator\Paginator;
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
class LoiManagerController extends AffiliationAbstractController implements
    LoiServiceAwareInterface,
    EmailServiceAwareInterface,
    ProjectServiceAwareInterface,
    GeneralServiceAwareInterface,
    ContactServiceAwareInterface
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
        $paginator->setDefaultItemCountPerPage(($page === 'all') ? PHP_INT_MAX : 15);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(ceil($paginator->getTotalItemCount() / $paginator->getDefaultItemCountPerPage()));

        return new ViewModel(
            [
                'paginator' => $paginator,
            ]
        );
    }

    /**
     * @return ViewModel
     */
    public function remindAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId(
            $this->getEvent()->getRouteMatch()->getParam('affiliation-id')
        );

        $form = new LoiReminder($affiliationService->getAffiliation(), $this->getContactService());

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
            $email->setFromContact($this->zfcUserAuthentication()->getIdentity());
            $email->addTo($this->zfcUserAuthentication()->getIdentity());
            $email->setSubject(
                str_replace(
                    ['[project]'],
                    [$affiliationService->getAffiliation()->getProject()],
                    $form->getData()['subject']
                )
            );

            $email->setHtmlLayoutName('signature_twig');
            $email->setReceiver(
                $this->getContactService()->findEntityById('contact', $form->getData()['receiver'])->getDisplayName()
            );
            $email->setOrganisation($affiliationService->getAffiliation()->getOrganisation());
            $email->setProject($affiliationService->getAffiliation()->getProject());
            $email->setMessage($form->getData()['message']);

            $this->getEmailService()->send();

            //Store the reminder in the database
            $loiReminder = new LoiReminderEntity();
            $loiReminder->setAffiliation($affiliationService->getAffiliation());
            $loiReminder->setEmail($form->getData()['message']);
            $loiReminder->setReceiver(
                $this->getContactService()->findEntityById('contact', $form->getData()['receiver'])
            );
            $loiReminder->setSender($this->zfcUserAuthentication()->getIdentity());
            $this->getLoiService()->newEntity($loiReminder);

            $this->flashMessenger()->setNamespace('success')->addMessage(
                sprintf(
                    _("txt-reminder-for-loi-for-organisation-%s-in-project-%s-has-been-sent-to-%s"),
                    $affiliationService->getAffiliation()->getOrganisation(),
                    $affiliationService->getAffiliation()->getProject(),
                    $this->getContactService()->findEntityById('contact', $form->getData()['receiver'])->getEmail()
                )
            );

            return $this->redirect()->toRoute('zfcadmin/affiliation/loi/missing');
        }

        return new ViewModel(
            [
                'affiliationService' => $affiliationService,
                'form'               => $form,
            ]
        );
    }

    /**
     * @return ViewModel
     */
    public function remindersAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId(
            $this->getEvent()->getRouteMatch()->getParam('affiliation-id')
        );

        return new ViewModel(
            [
                'affiliationService' => $affiliationService,
            ]
        );
    }

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function viewAction()
    {
        $loi = $this->getLoiService()->setLoiId(
            $this->getEvent()->getRouteMatch()->getParam('id')
        );
        if (is_null($loi)) {
            return $this->notFoundAction();
        }

        return new ViewModel(['loi' => $loi->getLoi()]);
    }

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $loiService = $this->getLoiService()->setLoiId(
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        if (is_null($loiService)) {
            return $this->notFoundAction();
        }

        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
        );

        $form = $this->getFormService()->prepare('loi', $loiService->getLoi(), $data);

        //Get contacts in an organisation
        $this->getContactService()->findContactsInAffiliation($loiService->getLoi()->getAffiliation());
        $form->get('loi')->get('contact')->setValueOptions($this->getContactService()->toFormValueOptions());

        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/loi/view',
                    ['id' => $loiService->getLoi()->getId()]
                );
            }

            if (isset($data['delete'])) {
                $this->flashMessenger()->setNamespace('success')->addMessage(
                    sprintf(
                        _("txt-project-loi-for-organisation-%s-in-project-%s-has-been-removed"),
                        $loiService->getLoi()->getAffiliation()->getOrganisation(),
                        $loiService->getLoi()->getAffiliation()->getProject()
                    )
                );

                $this->getLoiService()->removeEntity($loiService->getLoi());

                return $this->redirect()->toRoute('zfcadmin/affiliation/loi/list');
            }


            if ($form->isValid()) {
                /*
                 * @var Loi
                 */
                $loi = $form->getData();

                $fileData = $this->params()->fromFiles();

                if ($fileData['loi']['file']['error'] === 0) {
                    /*
                     * Replace the content of the object
                     */
                    if (!$loi->getObject()->isEmpty()) {
                        $loi->getObject()->first()->setObject(file_get_contents($fileData['loi']['file']['tmp_name']));
                    } else {
                        $loiObject = new LoiObject();
                        $loiObject->setObject(file_get_contents($fileData['loi']['file']['tmp_name']));
                        $loiObject->setLoi($loi);
                        $this->getLoiService()->newEntity($loiObject);
                    }

                    //Create a article object element
                    $fileSizeValidator = new FilesSize(PHP_INT_MAX);
                    $fileSizeValidator->isValid($fileData['loi']['file']);
                    $loi->setSize($fileSizeValidator->size);
                    $loi->setContentType(
                        $this->getGeneralService()->findContentTypeByContentTypeName($fileData['loi']['file']['type'])
                    );
                }

                $this->getLoiService()->updateEntity($loi);

                $this->flashMessenger()->setNamespace('success')->addMessage(
                    sprintf(
                        _("txt-project-loi-for-organisation-%s-in-project-%s-has-been-updated"),
                        $loi->getAffiliation()->getOrganisation(),
                        $loi->getAffiliation()->getProject()
                    )
                );

                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/loi/view',
                    ['id' => $loi->getId()]
                );
            }
        }

        return new ViewModel(
            [
                'loi'  => $loiService->getLoi(),
                'form' => $form,
            ]
        );
    }


    /**
     * Dedicated action to approve LOIs via an AJAX call.
     *
     * @return JsonModel
     */
    public function approveAction()
    {
        $loi = $this->getEvent()->getRequest()->getPost()->get('loi');
        $contact = $this->getEvent()->getRequest()->getPost()->get('contact');
        $dateSigned = $this->getEvent()->getRequest()->getPost()->get('dateSigned');

        if (empty($contact) || empty($dateSigned)) {
            return new JsonModel(
                [
                    'result' => 'error',
                    'error'  => _("txt-contact-or-date-signed-is-empty"),
                ]
            );
        }

        if (!\DateTime::createFromFormat('Y-h-d', $dateSigned)) {
            return new JsonModel(
                [
                    'result' => 'error',
                    'error'  => _("txt-incorrect-date-format-should-be-yyyy-mm-dd"),
                ]
            );
        }

        /*
         * @var Loi
         */
        $loi = $this->getAffiliationService()->findEntityById('Loi', $loi);
        $loi->setContact($this->getContactService()->setContactId($contact)->getContact());
        $loi->setDateSigned(\DateTime::createFromFormat('Y-h-d', $dateSigned));
        $loi->setDateApproved(new \DateTime());
        $this->getLoiService()->updateEntity($loi);

        return new JsonModel(
            [
                'result' => 'success',
            ]
        );
    }
}
