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

use Zend\Stdlib\AbstractOptions;

/**
 * Class ModuleOptions
 *
 * @package Affiliation\Options
 */
final class ModuleOptions extends AbstractOptions implements AffiliationOptionsInterface
{
    protected $doaTemplate = '';
    protected $loiTemplate = '';
    protected $paymentSheetTemplate = '';

    public function getDoaTemplate(): string
    {
        return $this->doaTemplate;
    }

    public function setDoaTemplate(string $doaTemplate): ModuleOptions
    {
        $this->doaTemplate = $doaTemplate;

        return $this;
    }

    public function getLoiTemplate(): string
    {
        return $this->loiTemplate;
    }

    public function setLoiTemplate($loiTemplate): ModuleOptions
    {
        $this->loiTemplate = $loiTemplate;

        return $this;
    }

    public function getPaymentSheetTemplate(): string
    {
        return $this->paymentSheetTemplate;
    }

    public function setPaymentSheetTemplate(string $paymentSheetTemplate): ModuleOptions
    {
        $this->paymentSheetTemplate = $paymentSheetTemplate;

        return $this;
    }
}
