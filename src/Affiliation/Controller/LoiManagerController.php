<?php
/**
 * ITEA Office copyright message placeholder
 *
 * PHP Version 5
 *
 * @category    Affiliation
 * @package     Controller
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   2004-2014 ITEA Office
 * @license     http://debranova.org/license.txt proprietary
 * @link        http://debranova.org
 */
namespace Affiliation\Controller;

use Affiliation\Entity\Loi;
use Affiliation\Entity\LoiObject;
use Affiliation\Form\LoiApproval;
use Affiliation\Service\LoiServiceAwareInterface;
use Contact\Service\ContactServiceAwareInterface;
use General\Service\GeneralServiceAwareInterface;
use Project\Service\ProjectServiceAwareInterface;
use Zend\Validator\File\FilesSize;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Affiliation controller
 *
 * @category   Affiliation
 * @package    Controller
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  2004-2014 ITEA Office
 * @license    http://debranova.org/license.txt proprietary
 * @link       http://debranova.org
 */
class LoiManagerController extends AffiliationAbstractController implements
    LoiServiceAwareInterface,
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
                'projectService' => $this->getProjectService()
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
                'projectService' => $this->getProjectService()
            ]
        );
    }

    /**
     * @return ViewModel
     */
    public function missingAction()
    {
        $affiliation = $this->getAffiliationService()->findAffiliationWithMissingLoi();

        return new ViewModel(
            [
                'affiliation' => $affiliation,
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

        if ($this->getRequest()->isPost() && $form->isValid()) {
            /**
             * @var $loi Loi
             */
            $loi = $form->getData();
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation-manager/loi/view',
                    ['id' => $loi->getId()]
                );
            }

            if (isset($data['delete'])) {
                $this->flashMessenger()->setNamespace('success')->addMessage(
                    sprintf(
                        _("txt-project-loi-for-organisation-%s-in-project-%s-has-been-removed"),
                        $loi->getAffiliation()->getOrganisation(),
                        $loi->getAffiliation()->getProject()
                    )
                );

                $this->getLoiService()->removeEntity($loi);

                return $this->redirect()->toRoute('zfcadmin/affiliation-manager/loi/list');
            }

            $fileData = $this->params()->fromFiles();

            if ($fileData['loi']['file']['error'] === 0) {
                /**
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
                'zfcadmin/affiliation-manager/loi/view',
                ['id' => $loi->getId()]
            );

        }

        return new ViewModel(
            [
                'loi'  => $loiService->getLoi(),
                'form' => $form
            ]
        );
    }


    /**
     * Dedicated action to approve LOIs via an AJAX call
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
                    'error'  => _("txt-contact-or-date-signed-is-empty")
                ]
            );
        }

        if (!\DateTime::createFromFormat('Y-h-d', $dateSigned)) {
            return new JsonModel(
                [
                    'result' => 'error',
                    'error'  => _("txt-incorrect-date-format-should-be-yyyy-mm-dd")
                ]
            );
        }

        /**
         * @var $loi Loi
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
