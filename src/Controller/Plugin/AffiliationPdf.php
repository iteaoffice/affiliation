<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Controller\Plugin;

use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * Class PDF.
 */
class AffiliationPdf extends Fpdi
{
    protected ?string $_tplIdx = null;
    protected ?string $template = null;

    /**
     * Draw an imported PDF logo on every page.
     */
    public function header()
    {
        if ($this->_tplIdx === null) {
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

    public function coloredTable(
        array $header,
        array $data,
        array $width = null,
        bool $lastRow = false,
        int $height = 6
    ): void {
        // Colors, line width and bold font
        $this->SetDrawColor(205, 205, 205);
        $this->SetFillColor(255, 255, 255);
        $this->SetLineWidth(0.1);
        $this->SetFont('', 'B');
        // Header
        if (null === $width) {
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
        $fill       = true;
        $rowCounter = 1;
        $rowHeight  = 6;
        foreach ($data as $row) {
            $counter = 0;

            //Calculate the row height
            foreach ($row as $column) {
                $rowHeight = max($height, substr_count((string)$column, PHP_EOL) * 6);
            }

            foreach ($row as $column) {
                if ($lastRow && $rowCounter === \count($data)) {
                    $this->SetFont('', 'B');
                }

                $this->MultiCell(
                    $w[$counter],
                    $rowHeight,
                    $column,
                    'LR',
                    'L',
                    $fill,
                    0,
                    '',
                    '',
                    true,
                    0,
                    false,
                    true,
                    $rowHeight,
                    "M"
                );
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
        // empty method body
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }
}
