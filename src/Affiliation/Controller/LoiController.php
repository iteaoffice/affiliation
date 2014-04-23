<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Controller
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Affiliation\Controller;

use Zend\View\Model\ViewModel;
use Zend\Validator\File\FilesSize;

use Affiliation\Form\UploadLoi;
use Affiliation\Entity;
use Affiliation\Entity\Loi;

/**
 * @category    Affiliation
 * @package     Controller
 */
class LoiController extends AffiliationAbstractController
{
    /**
     * Upload the LOI for a project (based on the affiliation)
     *
     * @return ViewModel
     */
    public function uploadAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId(
            $this->getEvent()->getRouteMatch()->getParam('affiliation-id')
        );

        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
        );

        $form = new UploadLoi();
        $form->setData($data);

        if ($this->getRequest()->isPost() && $form->isValid()) {

            if (!isset($data['cancel'])) {
                $fileData = $this->params()->fromFiles();

                //Create a article object element
                $loiObject = new Entity\LoiObject();
                $loiObject->setObject(file_get_contents($fileData['file']['tmp_name']));

                $fileSizeValidator = new FilesSize(PHP_INT_MAX);
                $fileSizeValidator->isValid($fileData['file']);

                $loi = new Entity\Loi();
                $loi->setSize($fileSizeValidator->size);
                $loi->setContentType(
                    $this->getGeneralService()->findContentTypeByContentTypeName($fileData['file']['type'])
                );
                $loi->setContact($this->zfcUserAuthentication()->getIdentity());
                $loi->setAffiliation($affiliationService->getAffiliation());

                $loiObject->setLoi($loi);

                $this->getAffiliationService()->newEntity($loiObject);

                $this->flashMessenger()->setNamespace('success')->addMessage(
                    sprintf(
                        _("txt-loi-for-organisation-%s-project-%s-has-been-uploaded"),
                        $affiliationService->getAffiliation()->getOrganisation(),
                        $affiliationService->getAffiliation()->getProject()
                    )
                );
            }

            $this->redirect()->toRoute(
                'community/affiliation/affiliation',
                array('id' => $affiliationService->getAffiliation()->getId()),
                array('fragment' => 'details')
            );
        }

        return new ViewModel(
            array(
                'affiliationService' => $affiliationService,
                'form'               => $form
            )
        );
    }

    /**
     * Action to replace an mis-uploaded LoI
     *
     * @return ViewModel
     * @throws \Zend\Form\Exception\InvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \Zend\Mvc\Exception\DomainException
     * @throws \Zend\Form\Exception\DomainException
     */
    public function replaceAction()
    {

        $loi = $this->getAffiliationService()->findEntityById(
            'Loi',
            $this->getEvent()->getRouteMatch()->getParam('id')
        );
        $this->getAffiliationService()->setAffiliation($loi->getAffiliation());

        if (is_null($loi) || sizeof($loi->getObject()) === 0) {
            return $this->notFoundAction();
        }

        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
        );

        $form = new UploadLoi();
        $form->setData($data);

        if ($this->getRequest()->isPost() && $form->isValid()) {

            if (!isset($data['cancel'])) {
                $fileData = $this->params()->fromFiles();

                /**
                 * Remove the current entity
                 */
                foreach ($loi->getObject() as $object) {
                    $this->getAffiliationService()->removeEntity($object);
                }

                //Create a article object element
                $affiliationLoiObject = new Entity\LoiObject();
                $affiliationLoiObject->setObject(file_get_contents($fileData['file']['tmp_name']));

                $fileSizeValidator = new FilesSize(PHP_INT_MAX);
                $fileSizeValidator->isValid($fileData['file']);

                $loi->setSize($fileSizeValidator->size);
                $loi->setContact($this->zfcUserAuthentication()->getIdentity());
                $loi->setContentType(
                    $this->getGeneralService()->findContentTypeByContentTypeName($fileData['file']['type'])
                );

                $affiliationLoiObject->setLoi($loi);

                $this->getAffiliationService()->newEntity($affiliationLoiObject);

                $this->flashMessenger()->setNamespace('success')->addMessage(
                    sprintf(
                        _("txt-project-loi-for-organisation-%s-in-project-%s-has-been-replaced"),
                        $loi->getAffiliation()->getOrganisation(),
                        $loi->getAffiliation()->getProject()
                    )
                );
            }

            $this->redirect()->toRoute(
                'community/affiliation/affiliation',
                array('id' => $loi->getAffiliation()->getId()),
                array('fragment' => 'details')
            );
        }

        return new ViewModel(
            array(
                'affiliationService' => $this->getAffiliationService(),
                'form'               => $form
            )
        );
    }

    /**
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function renderAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId(
            $this->getEvent()->getRouteMatch()->getParam('affiliation-id')
        );

        //Create an empty Loi object
        $programLoi = new Loi();
        $programLoi->setContact($this->zfcUserAuthentication()->getIdentity());
        $programLoi->setAffiliation($affiliationService->getAffiliation());

        $renderProjectLoi = $this->renderLoi()->renderProjectLoi($programLoi);

        $response = $this->getResponse();
        $response->getHeaders()
            ->addHeaderLine('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 36000))
            ->addHeaderLine("Cache-Control: max-age=36000, must-revalidate")
            ->addHeaderLine("Pragma: public")
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $programLoi->parseFileName() . '.pdf"')
            ->addHeaderLine('Content-Type: application/pdf')
            ->addHeaderLine('Content-Length', strlen($renderProjectLoi->getPDFData()));

        $response->setContent($renderProjectLoi->getPDFData());

        return $response;
    }

    /**
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function downloadAction()
    {
        set_time_limit(0);

        $loi = $this->getAffiliationService()->findEntityById(
            'Loi',
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        if (is_null($loi) || sizeof($loi->getObject()) === 0) {
            return $this->notFoundAction();
        }

        /**
         * Due to the BLOB issue, we treat this as an array and we need to capture the first element
         */
        $object = $loi->getObject()->first()->getObject();
        $response = $this->getResponse();
        $response->setContent(stream_get_contents($object));

        $response->getHeaders()
            ->addHeaderLine('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 36000))
            ->addHeaderLine("Cache-Control: max-age=36000, must-revalidate")
            ->addHeaderLine(
                'Content-Disposition',
                'attachment; filename="' . $loi->parseFileName() . '.' .
                $loi->getContentType()->getExtension() . '"'
            )
            ->addHeaderLine("Pragma: public")
            ->addHeaderLine('Content-Type: ' . $loi->getContentType()->getContentType())
            ->addHeaderLine('Content-Length: ' . $loi->getSize());

        return $this->response;
    }
}
