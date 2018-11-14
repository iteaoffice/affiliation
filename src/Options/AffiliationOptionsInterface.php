<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Options;

/**
 * Interface AffiliationOptionsInterface.
 */
interface AffiliationOptionsInterface
{
    public function setDoaTemplate(string $doaTemplate);

    public function getDoaTemplate();

    public function setLoiTemplate(string $loiTemplate);

    public function getLoiTemplate();

    public function setPaymentSheetTemplate(string $paymentSheetTemplate);

    public function getPaymentSheetTemplate();
}
