<?php
/**
 * Japaveh Webdesign copyright message placeholder
 *
 * @category    Affiliation
 * @package     Entity
 * @author      Johan van der Heide <info@japaveh.nl>
 * @copyright   Copyright (c) 2004-2013 Japaveh Webdesign (http://japaveh.nl)
 */
namespace Affiliation\Entity;

use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilter;

/**
 * Annotations class
 *
 * @author  Johan van der Heide <info@japaveh.nl>
 */
abstract class EntityAbstract implements EntityInterface, InputFilterAwareInterface
{
    /**
     * @var InputFilter
     */
    protected $inputFilter;

    /**
     * @param $switch
     *
     * @return mixed|string
     */
    public function get($switch)
    {
        switch ($switch) {
            case 'entity_name':
                return join('', array_slice(explode('\\', get_class($this)), -1));
            case 'dashed_entity_name':
                $dash = function ($m) {
                    return '-' . strtolower($m[1]);
                };

                return preg_replace_callback('/([A-Z])/', $dash, lcfirst($this->get('entity_name')));
            case 'underscore_entity_name':
                $underscore = function ($m) {
                    return '_' . strtolower($m[1]);
                };

                return preg_replace_callback('/([A-Z])/', $underscore, lcfirst($this->get('entity_name')));
        }
    }
}
