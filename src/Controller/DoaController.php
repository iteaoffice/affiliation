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
use Affiliation\Form;
use Affiliation\Service\AffiliationService;
use General\Service\GeneralService;
use Laminas\Http\Response;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Validator\File\FilesSize;
use Laminas\Validator\File\MimeType;
use Laminas\View\Model\ViewModel;
use Organisation\Service\OrganisationService;
use Project\Entity\Changelog;
use Project\Service\ProjectService;
use ZfcTwig\View\TwigRenderer;

/**
 * Class DoaController
 *
 * @package Affiliation\Controller
 */
final class DoaController extends AffiliationAbstractController
{
    private AffiliationService $affiliationService;
    private ProjectService $projectService;
    private GeneralService $generalService;
    private TranslatorInterface $translator;
    private TwigRenderer $renderer;

    public function __construct(
        AffiliationService $affiliationService,
        ProjectService $projectService,
        GeneralService $generalService,
        TranslatorInterface $translator,
        TwigRenderer $twigRenderer
    ) {
        $this->affiliationService = $affiliationService;
        $this->projectService     = $projectService;
        $this->generalService     = $generalService;
        $this->translator         = $translator;
        $this->renderer           = $twigRenderer;
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

        $form = new Form\Doa\SubmitForm();
        $form->setData($data);

        if ($this->getRequest()->isPost() && isset($data['cancel'])) {
            return $this->redirect()->toRoute('community/affiliation/details', ['id' => $affiliation->getId()]);
        }

        if ($this->getRequest()->isPost() && isset($data['upload'])) {
            //Remove the digital elements
            $form->getInputFilter()->get('group_name')->setRequired(false);
            $form->getInputFilter()->get('chamber_of_commerce_number')->setRequired(false);
            $form->getInputFilter()->get('chamber_of_commerce_location')->setRequired(false);
            $form->getInputFilter()->remove('selfApprove');

            if ($form->isValid()) {
                $fileData = $form->getData('file');
                $this->affiliationService->uploadDoa($fileData['file'], $contact, $affiliation);

                $this->flashMessenger()->addSuccessMessage(
                    sprintf($this->translator->translate('txt-doa-has-been-uploaded-successfully'))
                );

                $changelogMessage = sprintf(
                    $this->translator->translate(
                        'txt-paper-version-of-project-doa-for-organisation-%s-in-project-%s-has-been-uploaded'
                    ),
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
                    'community/affiliation/details',
                    ['id' => $affiliation->getId()]
                );
            }
        }

        if ($this->getRequest()->isPost() && isset($data['sign'])) {
            $form->getInputFilter()->get('file')->setRequired(false);

            if ($form->isValid()) {
                $this->affiliationService->submitDoa($contact, $affiliation, $data);

                $changelogMessage = sprintf(
                    $this->translator->translate(
                        'txt-project-doa-for-organisation-%s-in-project-%s-has-been-uploaded'
                    ),
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
                    'community/affiliation/details',
                    ['id' => $affiliation->getId()]
                );
            }
        }

        $doaContent = $this->renderer->render(
            'affiliation/pdf/doa',
            [
                'contact'      => $contact,
                'organisation' => OrganisationService::parseBranch($affiliation->getBranch(), $affiliation->getOrganisation()),
                'project_name' => $affiliation->getProject()->parseFullName(),
                'form'         => $form,
            ]
        );

        return new ViewModel(
            [
                'affiliation' => $affiliation,
                'doaContent'  => $doaContent,
                'form'        => $form,
            ]
        );
    }

    public function replaceAction()
    {
        /**
         * @var Entity\Doa $doa
         */
        $doa = $this->affiliationService->find(Entity\Doa::class, (int)$this->params('id'));

        if (null === $doa || ! $doa->hasObject()) {
            return $this->notFoundAction();
        }
        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
        );
        $form = new Form\Doa\SubmitForm();
        $form->setData($data);
        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()
                    ->toRoute(
                        'community/affiliation/details',
                        ['id' => $doa->getAffiliation()->getId()]
                    );
            }

            if ($form->isValid()) {
                $fileData = $this->params()->fromFiles();

                $doaObject = $doa->getObject();
                if (null === $doaObject) {
                    $doaObject = new Entity\DoaObject();
                    $doaObject->setDoa($doa);
                }

                $doaObject->setObject(file_get_contents($fileData['file']['tmp_name']));
                $fileSizeValidator = new FilesSize(PHP_INT_MAX);
                $fileSizeValidator->isValid($fileData['file']);
                $doa->setSize($fileSizeValidator->size);
                $doa->setContact($this->identity());

                $fileTypeValidator = new MimeType();
                $fileTypeValidator->isValid($fileData['file']);
                $doa->setContentType(
                    $this->generalService->findContentTypeByContentTypeName($fileTypeValidator->type)
                );

                $this->affiliationService->save($doaObject);

                $changelogMessage = sprintf(
                    $this->translator->translate('txt-project-doa-for-organisation-%s-in-project-%s-has-been-replaced'),
                    $doa->getAffiliation()->parseBranchedName(),
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
                        'community/affiliation/details',
                        ['id' => $doa->getAffiliation()->getId()]
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


    public function downloadAction(): Response
    {
        /** @var Entity\Doa $doa */
        $doa = $this->affiliationService->find(Entity\Doa::class, (int)$this->params('id'));

        /** @var Response $response */
        $response = $this->getResponse();

        if (null === $doa || ! $doa->hasObject()) {
            return $response->setStatusCode(Response::STATUS_CODE_404);
        }

        $object = $doa->getObject()->getObject();

        $response->setContent(stream_get_contents($object));
        $response->getHeaders()->addHeaderLine(
            'Content-Disposition',
            'attachment; filename="' . $doa->parseFileName() . '.' . $doa->getContentType()->getExtension() . '"'
        )->addHeaderLine('Pragma: public')->addHeaderLine(
            'Content-Type: ' . $doa->getContentType()->getContentType()
        )->addHeaderLine('Content-Length: ' . $doa->getSize());

        return $response;
    }
}
