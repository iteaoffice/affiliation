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

interface HydrateInterface
{
    /**
     * Needed for the hydration of form elements.
     *
     * @return array
     */
    public function getArrayCopy();

    public function populate();
}
