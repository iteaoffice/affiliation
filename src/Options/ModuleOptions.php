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
 * Class ModuleOptions.
 */
class ModuleOptions extends AbstractOptions implements AffiliationOptionsInterface
{
    /**
     * Turn off strict options mode.
     */
    protected $__strictMode__ = false;
    /**
     * Location of the PDF having the DOA template.
     *
     * @var string
     */
    protected $doaTemplate = '';
    /**
     * Location of the PDF having the LOI template.
     *
     * @var string
     */
    protected $loiTemplate = '';
    /**
     * Location of the PDF having the payment sheet
     *
     * @var string
     */
    protected $paymentSheetTemplate = '';

    /**
     * @return string
     */
    public function getDoaTemplate()
    {
        return $this->doaTemplate;
    }

    /**
     * @param $doaTemplate
     *
     * @return ModuleOptions
     */
    public function setDoaTemplate($doaTemplate)
    {
        $this->doaTemplate = $doaTemplate;

        return $this;
    }

    /**
     * @return string
     */
    public function getLoiTemplate()
    {
        return $this->loiTemplate;
    }

    /**
     * @param $loiTemplate
     *
     * @return ModuleOptions
     */
    public function setLoiTemplate($loiTemplate)
    {
        $this->loiTemplate = $loiTemplate;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentSheetTemplate()
    {
        return $this->paymentSheetTemplate;
    }

    /**
     * @param  string $paymentSheetTemplate
     *
     * @return ModuleOptions
     */
    public function setPaymentSheetTemplate($paymentSheetTemplate)
    {
        $this->paymentSheetTemplate = $paymentSheetTemplate;

        return $this;
    }
}
