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

use Affiliation\Entity\EntityAbstract;

interface ServiceInterface
{
    public function getFullEntityName($entity);

    public function updateEntity(EntityAbstract $entity);

    public function newEntity(EntityAbstract $entity);

    public function getEntityManager();

    public function findAll($entity);
}
