<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */

namespace Affiliation\Options;

/**
 * Interface AffiliationOptionsInterface.
 */
interface AffiliationOptionsInterface
{
    /**
     * @param $doaTemplate
     *
     * @return AffiliationOptionsInterface
     */
    public function setDoaTemplate($doaTemplate);

    /**
     * @return string
     */
    public function getDoaTemplate();

    /**
     * @param $loiTemplate
     *
     * @return AffiliationOptionsInterface
     */
    public function setLoiTemplate($loiTemplate);

    /**
     * @return string
     */
    public function getLoiTemplate();

    /**
     * @return string
     */
    public function getPaymentSheetTemplate();

    /**
     * @param string $paymentSheetTemplate
     * @return ModuleOptions
     */
    public function setPaymentSheetTemplate($paymentSheetTemplate);
}
