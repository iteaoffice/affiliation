<?php

/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Service;

use Zend\Form;

class FormService extends ServiceAbstract
{
    /**
     * @var \Zend\Form\Form
     */
    protected $form;


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
            $entity = $this->getEntity($className);
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
     * @param null   $entity
     * @param array  $data
     *
     * @return array|object
     */
    public function prepare($className, $entity = null, $data = [])
    {
        $form = $this->getForm($className, $entity, true);
        $form->setData($data);

        return $form;
    }
}
