<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Controller;

use Affiliation\Entity;
use Affiliation\Entity\Doa;
use Affiliation\Form\UploadDoa;
use Zend\Validator\File\FilesSize;
use Zend\View\Model\ViewModel;

/**
 * @category    Affiliation
 */
class DoaController extends AffiliationAbstractController
{
    /**
     * Upload a DOA for a project (based on the affiliation).
     *
     * @return ViewModel
     */
    public function uploadAction()
    {
        $affiliation = $this->getAffiliationService()->findAffiliationById($this->params('affiliationId'));

        if (is_null($affiliation)) {
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
                $affiliationDoa->setContentType(
                    $this->getGeneralService()
                        ->findContentTypeByContentTypeName($fileData['file']['type'])
                );
                $affiliationDoa->setContact($this->zfcUserAuthentication()->getIdentity());
                $affiliationDoa->setAffiliation($affiliation);
                $affiliationDoaObject->setDoa($affiliationDoa);
                $this->getAffiliationService()->newEntity($affiliationDoaObject);
                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(
                        sprintf(
                            _("txt-project-doa-for-organisation-%s-in-project-%s-has-been-uploaded"),
                            $affiliation->getOrganisation(),
                            $affiliation->getProject()
                        )
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
                'affiliationService' => $this->getAffiliationService(),
                'affiliation'        => $affiliation,
                'form'               => $form,
            ]
        );
    }

    /**
     * Action to replace an mis-uploaded DoA.
     *
     * @return ViewModel
     *
     * @throws \Zend\Form\Exception\InvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \Zend\Mvc\Exception\DomainException
     * @throws \Zend\Form\Exception\DomainException
     */
    public function replaceAction()
    {
        /**
         * @var Doa $doa
         */
        $doa = $this->getAffiliationService()->findEntityById(Doa::class, $this->params('id'));

        if (is_null($doa) || count($doa->getObject()) === 0) {
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
                    $this->getAffiliationService()->removeEntity($object);
                }
                //Create a article object element
                $affiliationDoaObject = new Entity\DoaObject();
                $affiliationDoaObject->setObject(file_get_contents($fileData['file']['tmp_name']));
                $fileSizeValidator = new FilesSize(PHP_INT_MAX);
                $fileSizeValidator->isValid($fileData['file']);
                $doa->setSize($fileSizeValidator->size);
                $doa->setContact($this->zfcUserAuthentication()->getIdentity());
                $doa->setContentType(
                    $this->getGeneralService()
                        ->findContentTypeByContentTypeName($fileData['file']['type'])
                );
                $affiliationDoaObject->setDoa($doa);
                $this->getAffiliationService()->newEntity($affiliationDoaObject);
                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(
                        sprintf(
                            _("txt-project-doa-for-organisation-%s-in-project-%s-has-been-replaced"),
                            $doa->getAffiliation()->getOrganisation(),
                            $doa->getAffiliation()->getProject()
                        )
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
                'affiliationService' => $this->getAffiliationService(),
                'doa'                => $doa,
                'form'               => $form,
            ]
        );
    }

    /**
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function renderAction()
    {
        $affiliation = $this->getAffiliationService()->findAffiliationById($this->params('iaffiliationIdd'));

        if (is_null($affiliation)) {
            return $this->notFoundAction();
        }
        //Create an empty Doa object
        $programDoa = new Doa();
        $programDoa->setContact($this->zfcUserAuthentication()->getIdentity());
        $programDoa->setAffiliation($affiliation);
        $renderProjectDoa = $this->renderDoa()->renderProjectDoa($programDoa);
        $response         = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 36000))
            ->addHeaderLine("Cache-Control: max-age=36000, must-revalidate")->addHeaderLine("Pragma: public")
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $programDoa->parseFileName() . '.pdf"')
            ->addHeaderLine('Content-Type: application/pdf')
            ->addHeaderLine('Content-Length', strlen($renderProjectDoa->getPDFData()));
        $response->setContent($renderProjectDoa->getPDFData());

        return $response;
    }

    /**
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function downloadAction()
    {
        set_time_limit(0);
        /*
         * @var Doa $doa
         */
        $doa = $this->getAffiliationService()->findEntityById(Doa::class, $this->params('id'));
        if (is_null($doa) || count($doa->getObject()) === 0) {
            return $this->notFoundAction();
        }
        /*
         * Due to the BLOB issue, we treat this as an array and we need to capture the first element
         */
        $object   = $doa->getObject()->first()->getObject();
        $response = $this->getResponse();
        $response->setContent(stream_get_contents($object));
        $response->getHeaders()->addHeaderLine('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 36000))
            ->addHeaderLine("Cache-Control: max-age=36000, must-revalidate")->addHeaderLine(
                'Content-Disposition',
                'attachment; filename="' . $doa->parseFileName() . '.' . $doa->getContentType()->getExtension() . '"'
            )
            ->addHeaderLine("Pragma: public")->addHeaderLine(
                'Content-Type: ' . $doa->getContentType()
                    ->getContentType()
            )->addHeaderLine('Content-Length: ' . $doa->getSize());

        return $this->response;
    }
}
