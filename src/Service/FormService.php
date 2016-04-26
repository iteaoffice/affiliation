<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * PHP Version 5
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   2004-2015 ITEA Office
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/affiliation for the canonical source repository
 */

namespace Affiliation\Service;

use Affiliation\Entity\EntityAbstract;
use Affiliation\Form\CreateObject;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

class FormService extends ServiceAbstract
{
    /**
     * @param null           $className
     * @param EntityAbstract $entity
     * @param bool           $bind
     *
     * @return Form
     */
    public function getForm($className = null, EntityAbstract $entity = null, $bind = true)
    {
        if (!is_null($className) && is_null($entity)) {
            $entity = new $className();
        }

        if (!is_object($entity)) {
            throw new \InvalidArgumentException("No entity created given");
        }

        $formName = 'Affiliation\\Form\\' . $entity->get('entity_name') . 'Form';
        $filterName = 'Affiliation\\InputFilter\\' . $entity->get('entity_name') . 'Filter';

        /*
         * The filter and the form can dynamically be created by pulling the form from the serviceManager
         * if the form or filter is not give in the serviceManager we will create it by default
         */
        if (!$this->getServiceLocator()->has($formName)) {
            $form = new CreateObject($this->getEntityManager(), new $entity());
        } else {
            $form = $this->getServiceLocator()->get($formName);
        }

        if ($this->getServiceLocator()->has($filterName)) {
            /** @var InputFilter $filter */
            $filter = $this->getServiceLocator()->get($filterName);
            $form->setInputFilter($filter);
        }

        if ($bind) {
            $form->bind($entity);
        }

        return $form;
    }

    /**
     * @param string         $className
     * @param EntityAbstract $entity
     * @param array          $data
     *
     * @return Form
     */
    public function prepare($className, EntityAbstract $entity = null, $data = [])
    {
        $form = $this->getForm($className, $entity, true);
        $form->setData($data);

        return $form;
    }
}
