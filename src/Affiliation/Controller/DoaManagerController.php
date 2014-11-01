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
use Affiliation\Service\DoaServiceAwareInterface;
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
class DoaManagerController extends AffiliationAbstractController implements DoaServiceAwareInterface
{
    /**
     * @return ViewModel
     */
    public function listAction()
    {
        $doa = $this->getDoaService()->findNotApprovedDoa();

        return new ViewModel(['doa' => $doa]);
    }

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function viewAction()
    {
        $doa = $this->getDoaService()->setDoaId(
            $this->getEvent()->getRouteMatch()->getParam('id')
        );
        if (is_null($doa)) {
            return $this->notFoundAction();
        }

        return new ViewModel(['doa' => $doa]);
    }

    /**
     * Create a new entity
     * @return \Zend\View\Model\ViewModel
     */
    public function newAction()
    {
        $entity = $this->getEvent()->getRouteMatch()->getParam('entity');
        $form = $this->getFormService()->prepare($entity, null, $_POST);
        $form->setAttribute('class', 'form-horizontal');
        if ($this->getRequest()->isPost() && $form->isValid()) {
            $entity = $this->getNewsService()->newEntity($form->getData());

            return $this->redirect()->toRoute(
                'zfcadmin/news-manager/view',
                [
                    'entity' => strtolower($entity->get('entity_name')),
                    'id'     => $entity->getId()
                ]
            );
        }

        return new ViewModel(['form' => $form, 'entity' => $entity]);
    }

}
