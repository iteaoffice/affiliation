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

use Affiliation\Form\Affiliation;
use Affiliation\Form\CreateProgramDoa;
use Affiliation\Entity;

use Program\Entity\ProgramDoa;
use Program\Entity\ProgramDoaObject;

/**
 * @category    Affiliation
 * @package     Controller
 */
class CommunityController extends AffiliationAbstractController
{
    /**
     * Show the details of 1 affiliation
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function affiliationAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId(
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        $projectService = $this->getProjectService()->setProject($affiliationService->getAffiliation()->getProject());

        return new ViewModel(array(
                'affiliationService' => $affiliationService,
                'projectService'     => $projectService,
                'latestVersion'      => $projectService->getLatestProjectVersion(),
                'versionType'        => $projectService->getNextMode()->versionType)
        );
    }

    /**
     * Edit a affiliation
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId(
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        $projectService = $this->getProjectService()->setProject($affiliationService->getAffiliation()->getProject());

        $formData                = array();
        $formData['affiliation'] = sprintf("%s|%s",
            $affiliationService->getAffiliation()->getOrganisation()->getId(),
            $affiliationService->getAffiliation()->getBranch()
        );
        $formData['technical']   = $affiliationService->getAffiliation()->getContact()->getId();
        if (!is_null($affiliationService->getAffiliation()->getFinancial())) {
            $formData['financial'] = $affiliationService->getAffiliation()->getFinancial()->getContact()->getId();
        }

        $form = new Affiliation($affiliationService);
        $form->setData($formData);

        if ($this->getRequest()->isPost() && $form->setData($_POST) && $form->isValid()) {
            $formData = $form->getData();

            $affiliation = $affiliationService->getAffiliation();

            /**
             * When the deactivate button is pressed, handle this in the service layer
             */
            if (!is_null($formData['deactivate'])) {
                $this->getAffiliationService()->deactivateAffiliation($affiliation);
                $this->flashMessenger()->setNamespace('success')->addMessage(
                    sprintf(_("txt-affiliation-%s-has-successfully-been-deactivated"), $affiliationService->getAffiliation())
                );

                return $this->redirect()->toRoute('community/project/project', array(
                        'docRef' => $projectService->getProject()->getDocRef()
                    )
                );
            }

            /**
             * When the deactivate button is pressed, handle this in the service layer
             */
            if (!is_null($formData['reactivate'])) {
                $this->getAffiliationService()->reactivateAffiliation($affiliation);
                $this->flashMessenger()->setNamespace('success')->addMessage(
                    sprintf(_("txt-affiliation-%s-has-successfully-been-reactivated"), $affiliationService->getAffiliation())
                );

                return $this->redirect()->toRoute('community/affiliation/affiliation', array(
                        'id' => $affiliationService->getAffiliation()->getId()
                    )
                );
            }


            /**
             * Parse the organisation and branch
             */
            list($organisationId, $branch) = explode('|', $formData['affiliation']);
            $organisation = $this->getOrganisationService()->setOrganisationId($organisationId)->getOrganisation();
            $affiliation->setOrganisation($organisation);
            $affiliation->setContact($this->getContactService()->setContactId($formData['technical'])->getContact());
            $affiliation->setBranch($branch);
            $this->getAffiliationService()->updateEntity($affiliation);

            /**
             * Handle the financial organisation
             */
            if (is_null($financial = $affiliation->getFinancial())) {
                $financial = new Entity\Financial();
            }
            $financial->setOrganisation($organisation);
            $financial->setAffiliation($affiliation);
            $financial->setBranch($branch);
            $financial->setContact($this->getContactService()->setContactId($formData['financial'])->getContact());
            $this->getAffiliationService()->updateEntity($financial);

            $this->flashMessenger()->setNamespace('success')->addMessage(
                sprintf(_("txt-affiliation-%s-has-successfully-been-updated"), $affiliationService->getAffiliation())
            );

            return $this->redirect()->toRoute('community/affiliation/affiliation', array(
                    'id' => $affiliationService->getAffiliation()->getId()
                )
            );
        }

        return new ViewModel(array(
                'affiliationService' => $affiliationService,
                'projectService'     => $projectService,
                'form'               => $form
            )
        );
    }

    /**
     * @return ViewModel
     */
    public function uploadProgramDoaAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId(
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
        );

        $form = new CreateProgramDoa();
        $form->setData($data);

        if ($this->getRequest()->isPost() && $form->isValid()) {

            if (!isset($data['cancel'])) {
                $fileData = $this->params()->fromFiles();

                //Create a article object element
                $programDoaObject = new ProgramDoaObject();
                $programDoaObject->setObject(file_get_contents($fileData['file']['tmp_name']));

                $fileSizeValidator = new FilesSize(PHP_INT_MAX);
                $fileSizeValidator->isValid($fileData['file']);

                $programDoa = new ProgramDoa();
                $programDoa->setSize($fileSizeValidator->size);
                $programDoa->setContentType(
                    $this->getGeneralService()->findContentTypeByContentTypeName($fileData['file']['type'])
                );

                $programDoa->setContact($this->zfcUserAuthentication()->getIdentity());
                $programDoa->setBranch($affiliationService->getAffiliation()->getBranch());
                $programDoa->setOrganisation($affiliationService->getAffiliation()->getOrganisation());
                $programDoa->setProgram($affiliationService->getAffiliation()->getProject()->getCall()->getProgram());

                $programDoaObject->setProgramDoa($programDoa);

                $this->getProgramService()->newEntity($programDoaObject);

                $this->flashMessenger()->setNamespace('success')->addMessage(
                    sprintf(_("txt-program-doa-for-organisation-%s-and-program-%s-has-been-uploaded"),
                        $affiliationService->getAffiliation()->getOrganisation(),
                        $affiliationService->getAffiliation()->getProject()->getCall()->getProgram()
                    )
                );
            }

            $this->redirect()->toRoute('community/affiliation/affiliation',
                array('id' => $affiliationService->getAffiliation()->getId())
            );
        }


        return new ViewModel(array(
            'affiliationService' => $affiliationService,
            'form'               => $form
        ));
    }
}
