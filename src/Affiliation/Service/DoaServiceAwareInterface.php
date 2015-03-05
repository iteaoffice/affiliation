<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * PHP Version 5
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   2004-2014 ITEA Office
 * @license     http://debranova.org/license.txt proprietary
 *
 * @link        http://debranova.org
 */

namespace Affiliation\Service;

/**
 * Create a link to an Doa.
 *
 * @category   Affiliation
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @license    http://debranova.org/licence.txt proprietary
 *
 * @link       http://debranova.org
 */
interface DoaServiceAwareInterface
{
    /**
     * The Doa service.
     *
     * @param DoaService $DoaService
     *
     * @return \Affiliation\Controller\AffiliationAbstractController|null
     */
    public function setDoaService(DoaService $DoaService);

    /**
     * Get Doa service.
     *
     * @return DoaService
     */
    public function getDoaService();
}
