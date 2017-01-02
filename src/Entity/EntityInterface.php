<?php
/**
 * ITEA Office all rights reserved
 *
 * PHP Version 7
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */

namespace Affiliation\Entity;

/**
 * Interface EntityInterface
 *
 * @package Affiliation\Entity
 */
interface EntityInterface
{
    /**
     * @param $property
     *
     * @return mixed
     */
    public function __get($property);

    /**
     * @param $property
     * @param $value
     *
     * @return mixed
     */
    public function __set($property, $value);

    /**
     * @return mixed
     */
    public function __toString();

    /**
     * @return mixed
     */
    public function getId();
}
