<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Controller;

use Affiliation\Entity;
use Affiliation\Entity\Doa;
use Affiliation\Form\UploadDoa;
use Affiliation\Service\AffiliationService;
use General\Service\GeneralService;
use Project\Entity\Changelog;
use Project\Service\ProjectService;
use Zend\Http\Response;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Validator\File\FilesSize;
use Zend\Validator\File\MimeType;
use Zend\View\Model\ViewModel;

/**
 * Class DoaController
 *
 * @package Affiliation\Controller
 */
final class DoaController extends AffiliationAbstractController
{
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
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        AffiliationService $affiliationService,
        ProjectService $projectService,
        GeneralService $generalService,
        TranslatorInterface $translator
    ) {
        $this->affiliationService = $affiliationService;
        $this->projectService = $projectService;
        $this->generalService = $generalService;
        $this->translator = $translator;
    }


    public function uploadAction()
    {
        $affiliation = $this->affiliationService->findAffiliationById((int)$this->params('affiliationId'));

        if (null === $affiliation) {
            return $this->notFoundAction();
        }

        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
        );
        $form = new UploadDoa();
        $form->setData($data);
        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'community/affiliation/affiliation',
                    ['id' => $affiliation->getId()],
                    ['fragment' => 'details']
                );
            }

            if ($form->isValid()) {
                $fileData = $this->params()->fromFiles();
                //Create a article object element
                $affiliationDoaObject = new Entity\DoaObject();
                $affiliationDoaObject->setObject(file_get_contents($fileData['file']['tmp_name']));
                $fileSizeValidator = new FilesSize(PHP_INT_MAX);
                $fileSizeValidator->isValid($fileData['file']);
                $affiliationDoa = new Entity\Doa();
                $affiliationDoa->setSize($fileSizeValidator->size);

                $fileTypeValidator = new MimeType();
                $fileTypeValidator->isValid($fileData['file']);
                $affiliationDoa->setContentType(
                    $this->generalService->findContentTypeByContentTypeName($fileTypeValidator->type)
                );

                $affiliationDoa->setContact($this->identity());
                $affiliationDoa->setAffiliation($affiliation);
                $affiliationDoaObject->setDoa($affiliationDoa);
                $this->affiliationService->save($affiliationDoaObject);

                $changelogMessage = sprintf(
                    $this->translator->translate('txt-project-doa-for-organisation-%s-in-project-%s-has-been-uploaded'),
                    $affiliation->getOrganisation(),
                    $affiliation->getProject()
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
                    'community/affiliation/affiliation',
                    ['id' => $affiliation->getId()],
                    ['fragment' => 'details']
                );
            }
        }

        return new ViewModel(
            [
                'affiliationService' => $this->affiliationService,
                'affiliation'        => $affiliation,
                'form'               => $form,
            ]
        );
    }

    public function replaceAction()
    {
        /**
         * @var Doa $doa
         */
        $doa = $this->affiliationService->find(Doa::class, (int) $this->params('id'));

        if (null === $doa || count($doa->getObject()) === 0) {
            return $this->notFoundAction();
        }
        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
        );
        $form = new UploadDoa();
        $form->setData($data);
        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()
                    ->toRoute(
                        'community/affiliation/affiliation',
                        ['id' => $doa->getAffiliation()->getId()],
                        ['fragment' => 'details']
                    );
            }

            if ($form->isValid()) {
                $fileData = $this->params()->fromFiles();
                /*
                 * Remove the current entity
                 */
                foreach ($doa->getObject() as $object) {
                    $this->affiliationService->delete($object);
                }
                //Create a article object element
                $affiliationDoaObject = new Entity\DoaObject();
                $affiliationDoaObject->setObject(file_get_contents($fileData['file']['tmp_name']));
                $fileSizeValidator = new FilesSize(PHP_INT_MAX);
                $fileSizeValidator->isValid($fileData['file']);
                $doa->setSize($fileSizeValidator->size);
                $doa->setContact($this->identity());

                $fileTypeValidator = new MimeType();
                $fileTypeValidator->isValid($fileData['file']);
                $doa->setContentType(
                    $this->generalService->findContentTypeByContentTypeName($fileTypeValidator->type)
                );

                $affiliationDoaObject->setDoa($doa);
                $this->affiliationService->save($affiliationDoaObject);

                $changelogMessage = sprintf(
                    $this->translator->translate('txt-project-doa-for-organisation-%s-in-project-%s-has-been-replaced'),
                    $doa->getAffiliation()->getOrganisation(),
                    $doa->getAffiliation()->getProject()
                );

                $this->flashMessenger()->addSuccessMessage($changelogMessage);
                $this->projectService->addMessageToChangelog(
                    $doa->getAffiliation()->getProject(),
                    $this->identity(),
                    Changelog::TYPE_PARTNER,
                    Changelog::SOURCE_COMMUNITY,
                    $changelogMessage
                );

                return $this->redirect()
                    ->toRoute(
                        'community/affiliation/affiliation',
                        ['id' => $doa->getAffiliation()->getId()],
                        ['fragment' => 'details']
                    );
            }
        }

        return new ViewModel(
            [
                'affiliationService' => $this->affiliationService,
                'doa'                => $doa,
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

        //Create an empty Doa object
        $programDoa = new Doa();
        $programDoa->setContact($this->identity());
        $programDoa->setAffiliation($affiliation);
        $renderProjectDoa = $this->renderDoa()->renderProjectDoa($programDoa);

        $response->getHeaders()
            ->addHeaderLine('Pragma: public')
            ->addHeaderLine(
                'Content-Disposition',
                'attachment; filename="' . $programDoa->parseFileName() . '.pdf"'
            )
            ->addHeaderLine('Content-Type: application/pdf')
            ->addHeaderLine('Content-Length', \strlen($renderProjectDoa->getPDFData()));
        $response->setContent($renderProjectDoa->getPDFData());

        return $response;
    }

    public function downloadAction(): Response
    {
        /** @var Doa $doa */
        $doa = $this->affiliationService->find(Doa::class, (int)$this->params('id'));

        /** @var Response $response */
        $response = $this->getResponse();

        if (null === $doa || \count($doa->getObject()) === 0) {
            return $response->setStatusCode(Response::STATUS_CODE_404);
        }

        /*
         * Due to the BLOB issue, we treat this as an array and we need to capture the first element
         */
        $object = $doa->getObject()->first()->getObject();

        $response->setContent(stream_get_contents($object));
        $response->getHeaders()
            ->addHeaderLine(
                'Content-Disposition',
                'attachment; filename="' . $doa->parseFileName() . '.' . $doa->getContentType()->getExtension() . '"'
            )
            ->addHeaderLine('Pragma: public')->addHeaderLine(
                'Content-Type: ' . $doa->getContentType()
                    ->getContentType()
            )->addHeaderLine('Content-Length: ' . $doa->getSize());

        return $response;
    }
}
