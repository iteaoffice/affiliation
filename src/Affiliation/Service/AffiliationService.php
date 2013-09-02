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

/**
 * AffiliationService
 *
 * this is a generic wrapper service for all the other services
 *
 * First parameter of all methods (lowercase, underscore_separated)
 * will be used to fetch the correct model service, one exception is the 'linkModel'
 * method.
 *
 */
class AffiliationService extends ServiceAbstract
{
    /**
     * @var AffiliationService
     */
    protected $affiliationService;

    /**
     * Find 1 entity based on the name
     *
     * @param   $entity
     * @param   $name
     *
     * @return object
     */
    public function findEntityByName($entity, $name)
    {
        return $this->getEntityManager()->getRepository($this->getFullEntityName($entity))->findOneBy(
            array('name' => $name)
        );
    }
}
