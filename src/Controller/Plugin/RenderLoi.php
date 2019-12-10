<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Controller\Plugin;

use Affiliation\Entity\Loi;
use Affiliation\Options\ModuleOptions;
use Contact\Service\ContactService;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use ZfcTwig\View\TwigRenderer;

/**
 * Class RenderLoi
 *
 * @package Affiliation\Controller\Plugin
 */
final class RenderLoi extends AbstractPlugin
{
    private ModuleOptions $moduleOptions;
    private ContactService $contactService;
    private TwigRenderer $renderer;

    public function __construct(ModuleOptions $moduleOptions, ContactService $contactService, TwigRenderer $renderer)
    {
        $this->moduleOptions = $moduleOptions;
        $this->contactService = $contactService;
        $this->renderer = $renderer;
    }

    public function renderProjectLoi(Loi $loi): AffiliationPdf
    {
        $pdf = new AffiliationPdf();
        $pdf->setTemplate($this->moduleOptions->getDoaTemplate());
        $pdf->AddPage();
        $pdf->SetFontSize(9);

        // Write the contact details
        $pdf->SetXY(14, 55);
        $pdf->Write(0, $loi->getContact()->parseFullName());
        $pdf->SetXY(14, 60);
        $pdf->Write(0, $this->contactService->parseOrganisation($loi->getContact()));

        // Write the current date
        $pdf->SetXY(77, 55);
        $pdf->Write(0, date('d-m-Y'));

        // Write the Reference
        $pdf->SetXY(118, 55);

        // Use the NDA object to render the filename
        $pdf->Write(0, $loi->parseFileName());
        $ndaContent = $this->renderer->render(
            'affiliation/pdf/loi-project',
            [
                'contact'      => $loi->getContact(),
                'project'      => $loi->getAffiliation()->getProject(),
                'organisation' => $loi->getAffiliation()->getOrganisation(),
            ]
        );
        $pdf->writeHTMLCell(0, 0, 14, 70, $ndaContent);

        // Signage block
        $pdf->SetXY(14, 250);
        $pdf->Write(0, 'Undersigned');
        $pdf->SetXY(14, 260);
        $pdf->Write(0, 'Name:');
        $pdf->SetXY(100, 260);
        $pdf->Write(0, 'Date of Signature:');
        $pdf->SetXY(14, 270);
        $pdf->Write(0, 'Function:');
        $pdf->SetXY(100, 270);
        $pdf->Write(0, 'Signature:');
        $pdf->Line(130, 275, 190, 275);
        $pdf->Line(30, 265, 90, 265);
        $pdf->Line(130, 265, 190, 265);
        $pdf->Line(30, 275, 90, 275);

        return $pdf;
    }
}
