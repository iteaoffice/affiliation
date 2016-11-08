<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Controller\Plugin;

/**
 * Class PDF.
 */
class AffiliationPdf extends \FPDI
{
    /**
     * "Remembers" the template id of the imported page.
     */
    protected $_tplIdx;
    /**
     * Location of the template.
     *
     * @var string
     */
    protected $template;

    /**
     * Draw an imported PDF logo on every page.
     */
    public function header()
    {
        if (is_null($this->_tplIdx)) {
            if (! file_exists($this->template)) {
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

    /**
     * @param $header
     * @param $data
     */
    public function coloredTable($header, $data, array $width = null, $lastRow = false)
    {
        // Colors, line width and bold font
        $this->SetDrawColor(205, 205, 205);
        $this->SetFillColor(255, 255, 255);
        $this->SetLineWidth(0.1);
        $this->SetFont('', 'B');
        // Header
        if (is_null($width)) {
            $w = [40, 35, 40, 45, 40];
        } else {
            $w = $width;
        }

        $num_headers = count($header);

        for ($i = 0; $i < $num_headers; ++$i) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'l', 1);
        }


        if ($num_headers === 0) {
            $this->Cell(array_sum($w), 0, '', 'B');
        }

        $this->Ln();


        // Color and font restoration
        $this->SetFillColor(249, 249, 249);
        $this->SetTextColor(0);
        $this->SetFont('');
        // Data
        $fill       = 0;
        $rowCounter = 1;
        foreach ($data as $row) {
            $counter = 0;

            foreach ($row as $column) {
                if ($lastRow && $rowCounter === (sizeof($data))) {
                    $this->SetFont('', 'B');
                }


                $this->Cell($w[$counter], 6, $column, 'LR', 0, 'L', $fill);
                $counter++;
            }
            $rowCounter++;
            $this->Ln();
            $fill = ! $fill;
        }
        $this->Cell(array_sum($w), 0, '', 'T');
        $this->Ln();
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
