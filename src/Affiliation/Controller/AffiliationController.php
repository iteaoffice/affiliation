<?php
/**
 * Japaveh Webdesign copyright message placeholder
 *
 * @category    Affiliation
 * @package     Controller
 * @author      Johan van der Heide <info@japaveh.nl>
 * @copyright   Copyright (c) 2004-2013 Japaveh Webdesign (http://japaveh.nl)
 */
namespace Affiliation\Controller;

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
class AffiliationController extends AbstractActionController implements
    FormServiceAwareInterface, ServiceLocatorAwareInterface
{
    /**
     * @var AffiliationService
     */
    protected $affiliationService;
    /**
     * @var FormService
     */
    protected $formService;

    /**
     * Message container
     * @return array|void
     */
    public function indexAction()
    {
    }

    /**
     * Give a list of affiliations
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function affiliationsAction()
    {
        $affiliations = $this->getAffiliationService()->findAll('affiliation');

        return new ViewModel(array('affiliations' => $affiliations));
    }

    /**
     * Show the details of 1 affiliation
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function affiliationAction()
    {
        $affiliation = $this->getAffiliationService()->findEntityById(
            'affiliation',
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        return new ViewModel(array('affiliation' => $affiliation));
    }

    /**
     * Give a list of facilities
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function facilitiesAction()
    {
        $facilities = $this->getAffiliationService()->findAll('facility');

        return new ViewModel(array('facilities' => $facilities));
    }

    /**
     * Show the details of 1 facility
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function facilityAction()
    {
        $facility = $this->getAffiliationService()->findEntityById(
            'facility',
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        return new ViewModel(array('facility' => $facility));
    }

    /**
     * Give a list of areas
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function areasAction()
    {
        $areas = $this->getAffiliationService()->findAll('area');

        return new ViewModel(array('areas' => $areas));
    }

    /**
     * Show the details of 1 area
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function areaAction()
    {
        $area = $this->getAffiliationService()->findEntityById(
            'area',
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        return new ViewModel(array('area' => $area));
    }

    /**
     * Give a list of area2s
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function area2sAction()
    {
        $area2s = $this->getAffiliationService()->findAll('area2');

        return new ViewModel(array('area2s' => $area2s));
    }

    /**
     * Show the details of 1 area
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function area2Action()
    {
        $area2 = $this->getAffiliationService()->findEntityById(
            'area2',
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        return new ViewModel(array('area2' => $area2));
    }

    /**
     * Give a list of areas
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function subAreasAction()
    {
        $subAreas = $this->getAffiliationService()->findAll('subArea');

        return new ViewModel(array('subAreas' => $subAreas));
    }

    /**
     * Show the details of 1 area
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function subAreaAction()
    {
        $subArea = $this->getAffiliationService()->findEntityById(
            'subArea',
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        return new ViewModel(array('subArea' => $subArea));
    }

    /**
     * Give a list of operAreas
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function operAreasAction()
    {
        $operAreas = $this->getAffiliationService()->findAll('operArea');

        return new ViewModel(array('operAreas' => $operAreas));
    }

    /**
     * Show the details of 1 operArea
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function operAreaAction()
    {
        $operArea = $this->getAffiliationService()->findEntityById(
            'operArea',
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        return new ViewModel(array('operArea' => $operArea));
    }

    /**
     * Give a list of operAreas
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function operSubAreasAction()
    {
        $operSubAreas = $this->getAffiliationService()->findAll('operSubArea');

        return new ViewModel(array('operSubAreas' => $operSubAreas));
    }

    /**
     * Show the details of 1 operArea
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function operSubAreaAction()
    {
        $operSubArea = $this->getAffiliationService()->findEntityById(
            'operSubArea',
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        return new ViewModel(array('operSubArea' => $operSubArea));
    }

    /**
     * Edit an entity
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $this->layout(false);
        $entity = $this->getAffiliationService()->findEntityById(
            $this->getEvent()->getRouteMatch()->getParam('entity'),
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        $form = $this->getFormService()->prepare($entity->get('entity_name'), $entity, $_POST);
        $form->setAttribute('class', 'form-vertical live-form-edit');
        $form->setAttribute('id', 'affiliation-' . strtolower($entity->get('entity_name')) . '-' . $entity->getId());

        if ($this->getRequest()->isPost() && $form->isValid()) {
            $this->getAffiliationService()->updateEntity($form->getData());

            $view = new ViewModel(array($this->getEvent()->getRouteMatch()->getParam('entity') => $form->getData()));
            $view->setTemplate(
                "affiliation/partial/" . $this->getEvent()->getRouteMatch()->getParam('entity') . '.twig'
            );

            return $view;
        }

        return new ViewModel(array('form' => $form, 'entity' => $entity));
    }

    /**
     * Trigger to switch layout
     *
     * @param $layout
     */
    public function layout($layout)
    {
        if (false === $layout) {
            $this->getEvent()->getViewModel()->setTemplate('layout/nolayout');
        } else {
            $this->getEvent()->getViewModel()->setTemplate('layout/' . $layout);
        }
    }

    /**
     * @return FormService
     */
    public function getFormService()
    {
        return $this->formService;
    }

    /**
     * @param $formService
     *
     * @return AffiliationController
     */
    public function setFormService($formService)
    {
        $this->formService = $formService;

        return $this;
    }

    /**
     * Gateway to the Affiliation Service
     *
     * @return AffiliationService
     */
    public function getAffiliationService()
    {
        return $this->getServiceLocator()->get('affiliation_generic_service');
    }

    /**
     * @param $affiliationService
     *
     * @return AffiliationController
     */
    public function setAffiliationService($affiliationService)
    {
        $this->affiliationService = $affiliationService;

        return $this;
    }
}
