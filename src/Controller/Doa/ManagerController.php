<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Controller\Doa;

use Affiliation\Controller\AffiliationAbstractController;
use Affiliation\Entity;
use Affiliation\Form;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\DoaService;
use Affiliation\Service\FormService;
use Contact\Service\ContactService;
use General\Service\EmailService;
use General\Service\GeneralService;
use Invoice\Options\ModuleOptions;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Validator\File\FilesSize;
use Laminas\Validator\File\MimeType;
use Laminas\View\Model\ViewModel;
use Project\Service\ProjectService;

use function sprintf;

/**
 * Class ManagerController
 * @package Affiliation\Controller\Doa
 */
final class ManagerController extends AffiliationAbstractController
{
    private DoaService $doaService;
    private AffiliationService $affiliationService;
    private ContactService $contactService;
    private ProjectService $projectService;
    private GeneralService $generalService;
    private EmailService $emailService;
    private ModuleOptions $invoiceModuleOptions;
    private FormService $formService;
    private TranslatorInterface $translator;

    public function __construct(
        DoaService $doaService,
        AffiliationService $affiliationService,
        ContactService $contactService,
        ProjectService $projectService,
        GeneralService $generalService,
        EmailService $emailService,
        ModuleOptions $invoiceModuleOptions,
        FormService $formService,
        TranslatorInterface $translator
    ) {
        $this->doaService           = $doaService;
        $this->affiliationService   = $affiliationService;
        $this->contactService       = $contactService;
        $this->projectService       = $projectService;
        $this->generalService       = $generalService;
        $this->emailService         = $emailService;
        $this->invoiceModuleOptions = $invoiceModuleOptions;
        $this->formService          = $formService;
        $this->translator           = $translator;
    }

    public function approvalAction(): ViewModel
    {
        $notApprovedDigitalDoa  = $this->doaService->findNotApprovedDigitalDoa();
        $notApprovedUploadedDoa = $this->doaService->findNotApprovedUploadedDoa();
        $form                   = new Form\Doa\ApprovalForm($notApprovedUploadedDoa, $this->contactService);

        return new ViewModel(
            [
                'notApprovedUploadedDoa' => $notApprovedUploadedDoa,
                'notApprovedDigitalDoa'  => $notApprovedDigitalDoa,
                'form'                   => $form,
                'projectService'         => $this->projectService,
            ]
        );
    }

    public function missingAction(): ViewModel
    {
        $invoiceViaParent = $this->invoiceModuleOptions->getInvoiceViaParent();

        $affiliation = $this->affiliationService->findAffiliationWithMissingDoa($invoiceViaParent);

        return new ViewModel(
            [
                'affiliation'      => $affiliation,
                'projectService'   => $this->projectService,
                'invoiceViaParent' => $invoiceViaParent
            ]
        );
    }

    public function remindAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('affiliationId'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $form = new Form\Doa\ReminderForm($affiliation, $this->contactService);

        $data = array_merge(
            [
                'receiver' => $affiliation->getContact()->getId(),
            ],
            $this->getRequest()->getPost()->toArray()
        );

        //Get the corresponding template
        $webInfo = $this->generalService->findWebInfoByInfo('/affiliation/doa:reminder');

        if (null === $webInfo) {
            return $this->notFoundAction();
        }


        $form->setData($data);

        if ($this->getRequest()->isPost() && $form->isValid()) {
            /*
             * Send the email to the receiving user
             */
            $receiver = $this->contactService->findContactById((int)$form->getData()['receiver']);

            if (null !== $receiver) {
                $email = $this->emailService->createNewWebInfoEmailBuilder('/affiliation/doa:reminder');
                $email->addContactTo($receiver);
                $templateVariables = [
                    'receiver'     => $receiver->parseFullName(),
                    'organisation' => $affiliation->parseBranchedName(),
                    'country'      => $affiliation->getOrganisation()->getCountry(),
                    'project'      => $affiliation->getProject()->parseFullName()
                ];
                $email->setTemplateVariables($templateVariables);

                $email->addDeeplink('community/affiliation/details', 'deeplink', $receiver);
                $this->emailService->sendBuilder($email);

                //Store the reminder in the database
                $doaReminder = new Entity\Doa\Reminder();
                $doaReminder->setAffiliation($affiliation);
                $doaReminder->setEmail($email->getHtmlPart());
                $doaReminder->setReceiver($receiver);
                $doaReminder->setSender($this->identity());
                $this->doaService->save($doaReminder);

                $this->flashMessenger()->addSuccessMessage(
                    sprintf(
                        $this->translator->translate(
                            'txt-reminder-for-doa-for-organisation-%s-in-project-%s-has-been-sent-to-%s'
                        ),
                        $affiliation->getOrganisation(),
                        $affiliation->getProject(),
                        $receiver->parseFullName()
                    )
                );

                return $this->redirect()->toRoute('zfcadmin/affiliation/doa/missing');
            }
        }

        return new ViewModel(
            [
                'affiliation' => $affiliation,
                'form'        => $form,
                'webInfo'     => $webInfo
            ]
        );
    }

    public function remindersAction(): ViewModel
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('affiliationId'));

        return new ViewModel(
            [
                'affiliation' => $affiliation,
            ]
        );
    }

    public function viewAction(): ViewModel
    {
        $doa = $this->doaService->findDoaById((int)$this->params('id'));

        if (null === $doa) {
            return $this->notFoundAction();
        }

        return new ViewModel(['doa' => $doa]);
    }

    public function editAction()
    {
        $doa = $this->doaService->findDoaById((int)$this->params('id'));

        if (null === $doa) {
            return $this->notFoundAction();
        }

        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
        );

        $form = $this->formService->prepare($doa, $data);

        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/affiliation/doa/view', ['id' => $this->params('id')]);
            }

            if (isset($data['delete'])) {
                $this->flashMessenger()->addSuccessMessage(
                    sprintf(
                        $this->translator->translate(
                            'txt-project-doa-for-organisation-%s-in-project-%s-has-been-removed'
                        ),
                        $doa->getAffiliation()->parseBranchedName(),
                        $doa->getAffiliation()->getProject()
                    )
                );

                $this->doaService->delete($doa);

                return $this->redirect()->toRoute('zfcadmin/affiliation/doa/approval');
            }

            if ($form->isValid()) {

                /** @var $doa Entity\Doa */
                $doa      = $form->getData();
                $fileData = $this->params()->fromFiles();

                //If we have no date_approved, we remove the approver
                if (empty($data['affiliation_entity_doa']['date_approved'])) {
                    $doa->setApprover(null);
                }

                if ($fileData['affiliation_entity_doa']['file']['error'] === 0) {
                    /*
                     * Replace the content of the object
                     */
                    if ($doa->hasObject()) {
                        $doa->getObject()->setObject(
                            file_get_contents($fileData['affiliation_entity_doa']['file']['tmp_name'])
                        );
                    } else {
                        $doaObject = new Entity\DoaObject();
                        $doaObject->setObject(
                            file_get_contents($fileData['affiliation_entity_doa']['file']['tmp_name'])
                        );
                        $doaObject->setDoa($doa);
                        $this->doaService->save($doaObject);
                    }

                    //Create a article object element
                    $fileSizeValidator = new FilesSize(PHP_INT_MAX);
                    $fileSizeValidator->isValid($fileData['affiliation_entity_doa']['file']);
                    $doa->setSize($fileSizeValidator->size);

                    $fileTypeValidator = new MimeType();
                    $fileTypeValidator->isValid($fileData['affiliation_entity_doa']['file']);
                    $doa->setContentType(
                        $this->generalService->findContentTypeByContentTypeName($fileTypeValidator->type)
                    );
                }

                $this->doaService->save($doa);

                $this->flashMessenger()->addSuccessMessage(
                    sprintf(
                        $this->translator->translate(
                            'txt-project-doa-for-organisation-%s-in-project-%s-has-been-updated'
                        ),
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
}
