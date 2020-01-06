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

use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Doa;
use Affiliation\Entity\DoaObject;
use Affiliation\Entity\DoaReminder as DoaReminderEntity;
use Affiliation\Form\Doa\FileApproval;
use Affiliation\Form\Doa\Reminder;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\DoaService;
use Affiliation\Service\FormService;
use Contact\Service\ContactService;
use DateTime;
use Deeplink\Entity\Target;
use Deeplink\Service\DeeplinkService;
use Doctrine\ORM\EntityManager;
use General\Service\EmailService;
use General\Service\GeneralService;
use Organisation\Service\OrganisationService;
use Project\Service\ProjectService;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Validator\File\FilesSize;
use Laminas\Validator\File\MimeType;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use function sprintf;

/**
 * Class DoaManagerController
 *
 * @package Affiliation\Controller
 */
final class DoaManagerController extends AffiliationAbstractController
{
    private DoaService $doaService;
    private AffiliationService $affiliationService;
    private ContactService $contactService;
    private ProjectService $projectService;
    private GeneralService $generalService;
    private EmailService $emailService;
    private DeeplinkService $deeplinkService;
    private FormService $formService;
    private EntityManager $entityManager;
    private TranslatorInterface $translator;

    public function __construct(
        DoaService $doaService,
        AffiliationService $affiliationService,
        ContactService $contactService,
        ProjectService $projectService,
        GeneralService $generalService,
        EmailService $emailService,
        DeeplinkService $deeplinkService,
        FormService $formService,
        EntityManager $entityManager,
        TranslatorInterface $translator
    ) {
        $this->doaService = $doaService;
        $this->affiliationService = $affiliationService;
        $this->contactService = $contactService;
        $this->projectService = $projectService;
        $this->generalService = $generalService;
        $this->emailService = $emailService;
        $this->deeplinkService = $deeplinkService;
        $this->formService = $formService;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    public function approvalAction(): ViewModel
    {
        $notApprovedDigitalDoa = $this->doaService->findNotApprovedDigitalDoa();
        $notApprovedUploadedDoa = $this->doaService->findNotApprovedUploadedDoa();
        $form = new FileApproval($notApprovedUploadedDoa, $this->contactService);

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
        $affiliation = $this->affiliationService->findAffiliationWithMissingDoa();

        return new ViewModel(
            [
                'affiliation' => $affiliation,
            ]
        );
    }

    public function remindAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('affiliationId'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $form = new Reminder($affiliation, $this->contactService, $this->entityManager);

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

        $form->get('subject')->setValue($webInfo->getSubject());
        $form->get('message')->setValue($webInfo->getContent());

        $form->setData($data);

        if ($this->getRequest()->isPost() && $form->isValid()) {
            /*
             * Send the email to the receiving user
             */
            $receiver = $this->contactService->findContactById((int)$form->getData()['receiver']);

            if (null !== $receiver) {
                $this->emailService->setWebInfo('/affiliation/doa:reminder');
                $this->emailService->setSubject($form->getData()['subject']);
                $this->emailService->setEmailContent($form->getData()['message']);
                $this->emailService->addTo($receiver);
                $this->emailService->setTemplateVariable('receiver', $receiver->getDisplayName());
                $this->emailService->setTemplateVariable('organisation', $affiliation->getOrganisation());
                $this->emailService->setTemplateVariable('project', $affiliation->getProject());

                /** @var Target $target */
                $target = $this->deeplinkService->find(Target::class, (int)$data['deeplinkTarget']);
                $this->emailService->setDeeplink($target, $receiver, (string)$affiliation->getId());
                $this->emailService->send();

                //Store the reminder in the database
                $doaReminder = new DoaReminderEntity();
                $doaReminder->setAffiliation($affiliation);
                $doaReminder->setEmail($form->getData()['message']);
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
                        $this->contactService->findContactById((int)$form->getData()['receiver'])->getEmail()
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


        //Get contacts in an organisation
        $contacts = $this->contactService->findContactsInAffiliation($doa->getAffiliation());
        $form->get('affiliation_entity_doa')->get('contact')->setValueOptions(
            $this->contactService->toFormValueOptions($contacts['contacts'])
        )->setDisableInArrayValidator(true);

        /**
         *
         */
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
                        $doa->getAffiliation()->getOrganisation(),
                        $doa->getAffiliation()->getProject()
                    )
                );

                $this->doaService->delete($doa);

                return $this->redirect()->toRoute('zfcadmin/affiliation/doa/approval');
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

    public function approveAction(): JsonModel
    {
        $doa = $this->params()->fromPost('doa');
        $sendEmail = $this->params()->fromPost('sendEmail', 0);

        /**
         * @var $doa Doa
         */
        $doa = $this->affiliationService->find(Doa::class, (int)$doa);

        if ($doa->hasObject()) {
            $contact = $this->params()->fromPost('contact');
            $dateSigned = $this->params()->fromPost('dateSigned');
            if (empty($contact) || empty($dateSigned)) {
                return new JsonModel(
                    [
                        'result' => 'error',
                        'error'  => _('txt-contact-or-date-signed-is-empty'),
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
            $doa->setContact($this->contactService->findContactById((int)$contact));
            $doa->setDateSigned(DateTime::createFromFormat('Y-m-d', $dateSigned));
        }

        $doa->setDateApproved(new DateTime());
        $doa->setApprover($this->identity());
        $this->doaService->save($doa);

        /**
         * Send the email tot he user
         */
        if ($sendEmail === 'true') {
            $this->emailService->setWebInfo('/affiliation/doa/approved');
            $this->emailService->addTo($doa->getContact());

            /** @var Affiliation $affiliation */
            $affiliation = $doa->getAffiliation();

            $this->emailService->setTemplateVariable(
                'organisation',
                OrganisationService::parseBranch(
                    $affiliation->getBranch(),
                    $affiliation->getOrganisation()
                )
            );
            $this->emailService->setTemplateVariable('project', $affiliation->getProject()->parseFullName());
            $this->emailService->setTemplateVariable('signer', $doa->getContact()->parseFullName());
            $this->emailService->setTemplateVariable('date', $doa->getDateSigned()->format('d-m-Y'));
            $this->emailService->setTemplateVariable('uploaded', $doa->hasObject());

            $this->emailService->send();
        }

        return new JsonModel(
            [
                'result' => 'success',
            ]
        );
    }

    public function declineAction(): JsonModel
    {
        $doa = $this->params()->fromPost('doa');
        $doa = $this->affiliationService->find(Doa::class, (int)$doa);
        $this->doaService->delete($doa);

        return new JsonModel(
            [
                'result' => 'success',
            ]
        );
    }
}
