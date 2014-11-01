<?php
/**
 * ITEA Office copyright message placeholder
 *
 * PHP Version 5
 *
 * @category    Affiliation
 * @package     Service
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   2004-2014 ITEA Office
 * @license     http://debranova.org/license.txt proprietary
 * @link        http://debranova.org
 */
namespace Affiliation\Service;

/**
 * Create a link to an Loi
 *
 * @category   Affiliation
 * @package    Service
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @license    http://debranova.org/licence.txt proprietary
 * @link       http://debranova.org
 */
interface LoiServiceAwareInterface
{
    /**
     * The Loi service
     *
     * @param  LoiService $LoiService
     * @return \Affiliation\Controller\AffiliationAbstractController|null
     */
    public function setLoiService(LoiService $LoiService);

    /**
     * Get Loi service
     *
     * @return LoiService
     */
    public function getLoiService();
}
