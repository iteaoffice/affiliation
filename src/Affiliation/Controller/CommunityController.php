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

use Affiliation\Form\Affiliation;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

use Affiliation\Service\AffiliationService;
use Affiliation\Service\FormServiceAwareInterface;
use Affiliation\Service\FormService;
use Affiliation\Entity;

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
}
