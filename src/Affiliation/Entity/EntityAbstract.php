<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */

namespace Affiliation\Entity;

use Zend\InputFilter\InputFilter;

/**
 * Annotations class.
 *
 * @author  Johan van der Heide <johan.van.der.heide@itea3.org>
 */
abstract class EntityAbstract implements EntityInterface
{
    /**
     * @var InputFilter
     */
    protected $inputFilter;

    /**
     * @param string $switch
     *
     * @return mixed|string
     */
    public function get($switch)
    {
        switch ($switch) {
            case 'entity_name':
                return implode('', array_slice(explode('\\', get_class($this)), -1));
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
                break;
            case 'underscore_full_entity_name':
                $underscore = function ($m) {
                    return '_' . strtolower($m[1]);
                };

                return preg_replace_callback(
                    '/([A-Z])/',
                    $underscore,
                    lcfirst(str_replace('\\', '', __NAMESPACE__) . $this->get('entity_name'))
                );
        }
    }
}
