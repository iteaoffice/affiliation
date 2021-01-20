<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Controller;

use Affiliation\Entity;
use Affiliation\Entity\Loi;
use Affiliation\Form\Loi\SubmitForm;
use Affiliation\Form\Loi\UploadForm;
use Affiliation\Form\SubmitLoi;
use Affiliation\Form\UploadLoi;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\LoiService;
use General\Service\GeneralService;
use Laminas\Http\Response;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Validator\File\FilesSize;
use Laminas\Validator\File\MimeType;
use Laminas\View\Model\ViewModel;
use Project\Entity\Changelog;
use Project\Service\ProjectService;

/**
 * Class LoiController
 *
 * @package Affiliation\Controller
 */
final class LoiController extends AffiliationAbstractController
{
    private LoiService $loiService;
    private AffiliationService $affiliationService;
    private ProjectService $projectService;
    private GeneralService $generalService;
    private TranslatorInterface $translator;

    public function __construct(
        LoiService $loiService,
        AffiliationService $affiliationService,
        ProjectService $projectService,
        GeneralService $generalService,
        TranslatorInterface $translator
    ) {
        $this->loiService         = $loiService;
        $this->affiliationService = $affiliationService;
        $this->projectService     = $projectService;
        $this->generalService     = $generalService;
        $this->translator         = $translator;
    }


    public function submitAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('affiliationId'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $contact = $this->identity();

        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
        );

        $form = new SubmitForm();
        $form->setData($data);

        if ($this->getRequest()->isPost() && isset($data['cancel'])) {
            return $this->redirect()->toRoute('community/affiliation/details', ['id' => $affiliation->getId()]);
        }

        if ($this->getRequest()->isPost() && ! isset($data['approve']) && $form->isValid()) {
            if (isset($data['submit'])) {
                $fileData = $form->getData('file');
                $this->affiliationService->uploadLoi($fileData['file'], $contact, $affiliation);

                $this->flashMessenger()->addSuccessMessage(
                    sprintf($this->translator->translate('txt-loi-has-been-uploaded-successfully'))
                );
            }


            return $this->redirect()->toRoute('community/affiliation/details', ['id' => $affiliation->getId()]);
        }

        if ($this->getRequest()->isPost() && isset($data['approve'])) {
            if ($data['selfApprove'] === '0') {
                $form->getInputFilter()->get('selfApprove')->setErrorMessage('Error');
                $form->get('selfApprove')->setMessages(['Error']);
            }

            if ($data['selfApprove'] === '1') {
                $this->affiliationService->submitLoi($contact, $affiliation);

                $changelogMessage = sprintf(
                    $this->translator->translate(
                        'txt-loi-for-%s-has-been-submitted-and-approved-successfully'
                    ),
                    $affiliation
                );

                $this->flashMessenger()->addSuccessMessage($changelogMessage);
                $this->projectService->addMessageToChangelog(
                    $affiliation->getProject(),
                    $this->identity(),
                    Changelog::TYPE_PARTNER,
                    Changelog::SOURCE_COMMUNITY,
                    $changelogMessage
                );

                return $this->redirect()->toRoute(
                    'community/affiliation/details',
                    ['id' => $affiliation->getId()]
                );
            }
        }

        return new ViewModel(
            [
                'affiliation' => $affiliation,
                'form'        => $form,
            ]
        );
    }

    public function replaceAction()
    {
        $loi = $this->loiService->findLoiById((int)$this->params('id'));

        if (null === $loi || \count($loi->getObject()) === 0) {
            return $this->notFoundAction();
        }
        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
        );
        $form = new UploadForm();
        $form->get('submit')->setValue(_('txt-replace-loi'));
        $form->setData($data);
        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()
                    ->toRoute(
                        'community/affiliation/details',
                        ['id' => $loi->getAffiliation()->getId()]
                    );
            }

            if ($form->isValid()) {
                $fileData = $this->params()->fromFiles();
                /*
                 * Remove the current entity
                 */
                foreach ($loi->getObject() as $object) {
                    $this->affiliationService->delete($object);
                }
                //Create a article object element
                $affiliationLoiObject = new Entity\LoiObject();
                $affiliationLoiObject->setObject(file_get_contents($fileData['file']['tmp_name']));

                $fileSizeValidator = new FilesSize(PHP_INT_MAX);
                $fileSizeValidator->isValid($fileData['file']);
                $loi->setSize($fileSizeValidator->size);

                $loi->setContact($this->identity());
                $loi->setDateSigned(new \DateTime());

                $fileTypeValidator = new MimeType();
                $fileTypeValidator->isValid($fileData['file']);
                $loi->setContentType(
                    $this->generalService->findContentTypeByContentTypeName($fileTypeValidator->type)
                );

                $affiliationLoiObject->setLoi($loi);
                $this->affiliationService->save($affiliationLoiObject);

                $changelogMessage = sprintf(
                    $this->translator->translate('txt-project-loi-for-organisation-%s-in-project-%s-has-been-replaced'),
                    $loi->getAffiliation()->getOrganisation(),
                    $loi->getAffiliation()->getProject()
                );

                $this->flashMessenger()->addSuccessMessage($changelogMessage);
                $this->projectService->addMessageToChangelog(
                    $loi->getAffiliation()->getProject(),
                    $this->identity(),
                    Changelog::TYPE_PARTNER,
                    Changelog::SOURCE_COMMUNITY,
                    $changelogMessage
                );

                return $this->redirect()
                    ->toRoute(
                        'community/affiliation/details',
                        ['id' => $loi->getAffiliation()->getId()]
                    );
            }
        }

        return new ViewModel(
            [
                'affiliationService' => $this->affiliationService,
                'loi'                => $loi,
                'form'               => $form,
            ]
        );
    }

    public function renderAction(): Response
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('affiliationId'));

        /** @var Response $response */
        $response = $this->getResponse();

        if (null === $affiliation) {
            return $response->setStatusCode(Response::STATUS_CODE_404);
        }

        //Create an empty Loi object
        $programLoi = new Loi();
        $programLoi->setContact($this->identity());
        $programLoi->setAffiliation($affiliation);
        $renderProjectLoi = $this->renderLoi()->renderProjectLoi($programLoi);

        $response->getHeaders()
            ->addHeaderLine('Pragma: public')
            ->addHeaderLine(
                'Content-Disposition',
                'attachment; filename="' . $programLoi->parseFileName() . '.pdf"'
            )
            ->addHeaderLine('Content-Type: application/pdf')
            ->addHeaderLine('Content-Length', \strlen($renderProjectLoi->getPDFData()));
        $response->setContent($renderProjectLoi->getPDFData());

        return $response;
    }

    public function downloadAction(): Response
    {
        $loi = $this->loiService->findLoiById((int)$this->params('id'));

        /** @var Response $response */
        $response = $this->getResponse();

        if (null === $loi || \count($loi->getObject()) === 0) {
            return $response->setStatusCode(Response::STATUS_CODE_404);
        }
        /*
         * Due to the BLOB issue, we treat this as an array and we need to capture the first element
         */
        $object = $loi->getObject()->first()->getObject();

        $response->setContent(\stream_get_contents($object));
        $response->getHeaders()
            ->addHeaderLine(
                'Content-Disposition',
                'attachment; filename="' . $loi->parseFileName() . '.' . $loi->getContentType()->getExtension() . '"'
            )
            ->addHeaderLine('Pragma: public')
            ->addHeaderLine(
                'Content-Type: ' . $loi->getContentType()
                    ->getContentType()
            )->addHeaderLine('Content-Length: ' . $loi->getSize());

        return $response;
    }
}
