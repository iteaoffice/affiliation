<?php

/**
 * Japaveh Webdesign copyright message placeholder
 *
 * @category    Affiliation
 * @package     Service
 * @author      Johan van der Heide <info@japaveh.nl>
 * @copyright   Copyright (c) 2004-2013 Japaveh Webdesign (http://japaveh.nl)
 */
namespace Affiliation\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form;

use Affiliation\Service\AffiliationService;

class FormService implements ServiceLocatorAwareInterface
{

    /**
     * @var \Zend\Form\Form
     */
    protected $form;
    /**
     * @var \Affiliation\Service\AffiliationService
     */
    protected $affiliationService;
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @param null $className
     * @param null $entity
     * @param bool $bind
     *
     * @return array|object
     */
    public function getForm($className = null, $entity = null, $bind = true)
    {
        if (!$entity) {
            $entity = $this->getAffiliationService()->getEntity($className);
        }

        $formName = 'affiliation_' . $entity->get('underscore_entity_name') . '_form';
        $form     = $this->getServiceLocator()->get($formName);

        $filterName = 'affiliation_' . $entity->get('underscore_entity_name') . '_form_filter';
        $filter     = $this->getServiceLocator()->get($filterName);

        $form->setInputFilter($filter);

        if ($bind) {
            $form->bind($entity);
        }

        return $form;
    }

    /**
     * @param       $className
     * @param null  $entity
     * @param array $data
     *
     * @return array|object
     */
    public function prepare($className, $entity = null, $data = array())
    {
        $form = $this->getForm($className, $entity, true);
        $form->setData($data);

        return $form;
    }

    /**
     * @param AffiliationService $affiliationService
     */
    public function setAffiliationService($affiliationService)
    {
        $this->affiliationService = $affiliationService;
    }

    /**
     * Get affiliationService.
     *
     * @return AffiliationService.
     */
    public function getAffiliationService()
    {
        if (null === $this->affiliationService) {
            $this->affiliationService = $this->getServiceLocator()->get('affiliation_generic_service');
        }

        return $this->affiliationService;
    }

    /**
     * Set the service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get the service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
