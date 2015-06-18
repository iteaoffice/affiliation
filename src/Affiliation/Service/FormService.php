<?php

/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */

namespace Affiliation\Service;

use Zend\Form;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
     * @return \Zend\Form\Form
     */
    private function getForm($className = null, $entity = null, $bind = true)
    {
        if (!$entity) {
            $entity = $this->getAffiliationService()->getEntity($className);
        }
        $formName = 'affiliation_' . $entity->get('underscore_entity_name') . '_form';
        $form = $this->getServiceLocator()->get($formName);
        $filterName = 'affiliation_' . $entity->get('underscore_entity_name') . '_form_filter';
        $filter = $this->getServiceLocator()->get($filterName);
        $form->setInputFilter($filter);
        if ($bind) {
            $form->bind($entity);
        }

        return $form;
    }

    /**
     * @param string $className
     * @param null $entity
     * @param array $data
     *
     * @return array|object
     */
    public function prepare($className, $entity = null, $data = [])
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
            $this->affiliationService = $this->getServiceLocator()->get(AffiliationService::class);
        }

        return $this->affiliationService;
    }

    /**
     * Set the service locator.
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get the service locator.
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
