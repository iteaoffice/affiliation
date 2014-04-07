<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Options
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Affiliation\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions implements
    AffiliationOptionsInterface
{
    /**
     * Turn off strict options mode
     */
    protected $__strictMode__ = false;

    /**
     * Location of the PDF having the DOA template
     *
     * @var string
     */
    protected $doaTemplate = '';
    /**
     * Location of the PDF having the LOI template
     *
     * @var string
     */
    protected $loiTemplate = '';

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
    public function getDoaTemplate()
    {
        return $this->doaTemplate;
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
    public function getLoiTemplate()
    {
        return $this->loiTemplate;
    }
}
