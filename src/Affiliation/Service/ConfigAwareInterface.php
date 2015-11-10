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

/**
 * Interface ConfigAwareInterface.
 */
interface ConfigAwareInterface
{
    /**
     * Get config.
     *
     * @return FormService.
     */
    public function getConfig();

    /**
     * Set config.
     *
     * @param $config the value to set.
     *
     * @return \Affiliation\Controller\AffiliationAbstractController
     */
    public function setConfig($config);
}
