<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Controller
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Affiliation\Controller\Plugin;

/**
 * Class PDF
 * @package Program\Controller\Plugin
 */
class AffiliationPdf extends \FPDI
{
    /**
     * "Remembers" the template id of the imported page
     */
    protected $_tplIdx;
    /**
     * Location of the template
     *
     * @var string
     */
    protected $template;

    /**
     * Draw an imported PDF logo on every page
     */
    public function header()
    {
        if (is_null($this->_tplIdx)) {
            if (!file_exists($this->template)) {
                throw new \InvalidArgumentException(sprintf("Template %s cannot be found", $this->template));
            }
            $this->setSourceFile($this->template);
            $this->_tplIdx = $this->importPage(1);
        }
        $size = $this->useTemplate($this->_tplIdx, 0, 0);
        $this->SetFont('freesans', 'N', 15);
        $this->SetTextColor(0);
        $this->SetXY(PDF_MARGIN_LEFT, 5);
    }

    public function footer()
    {
        // emtpy method body
    }

    /**
     * @param $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }
}
