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

use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Invoice as AffiliationInvoice;
use Affiliation\Options\ModuleOptions;
use Affiliation\Service\AffiliationService;
use Contact\Service\ContactService;
use Invoice\Entity\Method;
use Invoice\Service\InvoiceService;
use Organisation\Entity\Financial;
use Organisation\Service\OrganisationService;
use Project\Service\ProjectService;
use Project\Service\VersionService;
use Zend\I18n\View\Helper\Translate;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class RenderLoi.
 */
class RenderPaymentSheet extends AbstractPlugin
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @param Affiliation $affiliation
     * @param             $year
     * @param             $period
     *
     * @return AffiliationPdf
     * @throws \Exception
     */
    public function render(Affiliation $affiliation, $year, $period)
    {
        $projectService = $this->getProjectService()->setProject($affiliation->getProject());

        $latestVersion = $projectService->getLatestProjectVersion();
        $versionService = $this->getVersionService()->setVersion($latestVersion);

        $contactService = clone $this->getContactService()->setContact($affiliation->getContact());

        if (!is_null($this->getAffiliationService()->getFinancialContact($affiliation))) {
            $financialContactService = clone $this->getContactService()->setContact($this->getAffiliationService()
                ->getFinancialContact($affiliation));
        } else {
            $financialContactService = null;
        }


        $affiliationService = $this->getAffiliationService()->setAffiliation($affiliation);

        $versionContributionInformation = $versionService->getProjectVersionContributionInformation($affiliation,
            $latestVersion, $year);

        $invoiceMethod = $this->getInvoiceService()->findInvoiceMethod($projectService->getProject()->getCall()
            ->getProgram());


        $pdf = new AffiliationPdf();
        $pdf->setTemplate($this->getModuleOptions()->getPaymentSheetTemplate());
        $pdf->addPage();
        $pdf->SetFontSize(9);
        $pdf->SetTopMargin(55);

        $pdf->writeHTMLCell(0, 0, '', '',
            '<h1 style="color: #00a651">' . sprintf($this->translate("txt-payment-sheet-year-%s-period-%s"), $year,
                $period) . '</h1>', 0, 1, 0, true, '', true);
        $pdf->Ln();
        $pdf->Line(10, 65, 190, 65, ['color' => [0, 166, 81]]);


        //Project information
        $pdf->writeHTMLCell(0, 0, '', '', sprintf("<h3>%s</h3>", $this->translate("txt-project-details")), 0, 1, 0,
            true, '', true);

        $projectDetails = [
            [
                $this->translate("txt-project-number"),
                $projectService->getProject()->getNumber()
            ],
            [
                $this->translate("txt-project-name"),
                $projectService->getProject()->getProject()
            ],
            [
                $this->translate("txt-start-date"),
                $projectService->parseOfficialDateStart()->format('d-m-Y')
            ],
            [
                $this->translate("txt-start-end"),
                $projectService->parseOfficialDateEnd()->format('d-m-Y')
            ],
            [
                $this->translate("txt-version-name"),
                $versionService->getVersion()->getVersionType()
            ],
            [
                $this->translate("txt-version-status"),
                $versionService->parseStatus()
            ],
            [
                $this->translate("txt-version-date"),
                (!is_null($versionService->getVersion()->getDateReviewed()) ? $versionService->getVersion()
                    ->getDateReviewed()->format("d-m-Y") : '')
            ],
        ];

        $pdf->coloredTable([], $projectDetails, [55, 130]);


        //Partner information
        $pdf->writeHTMLCell(0, 0, '', '', sprintf("<h3>%s</h3>", $this->translate("txt-project-partner")), 0, 1, 0,
            true, '', true);

        $partnersDetails = [
            [
                $this->translate("txt-organisation"),
                $affiliationService->getAffiliation()->getOrganisation()
            ],
            [
                $this->translate("txt-organisation-type"),
                $affiliationService->getAffiliation()->getOrganisation()->getType()
            ],
            [
                $this->translate("txt-country"),
                $affiliationService->getAffiliation()->getOrganisation()->getCountry()
            ],
            [
                $this->translate("txt-total-person-years"),
                $this->parseEffort($versionContributionInformation->totalEffort)
            ],
            [
                $this->translate("txt-total-costs"),
                $this->parseKiloCost($versionContributionInformation->totalCost)
            ],
            [
                $this->translate("txt-average-cost"),
                ($versionContributionInformation->totalEffort > 0
                    ? $this->parseKiloCost($versionContributionInformation->totalCost
                        / $versionContributionInformation->totalEffort) : '-') . '/PY'
            ]
        ];

        $pdf->coloredTable([], $partnersDetails, [55, 130]);

        //Technical contact
        $pdf->writeHTMLCell(0, 0, '', '', sprintf("<h3>%s</h3>", $this->translate("txt-technical-contact")), 0, 1, 0,
            true, '', true);
        $partnersDetails = [
            [
                $this->translate("txt-name"),
                trim($contactService->parseAttention() . ' ' . $contactService->parseFullName())
            ],
            [
                $this->translate("txt-email"),
                $contactService->getContact()->getEmail()
            ],
        ];

        $pdf->coloredTable([], $partnersDetails, [55, 130]);


        if (!is_null($financialContactService)) {
            //Financial contact
            $pdf->writeHTMLCell(0, 0, '', '', sprintf("<h3>%s</h3>", $this->translate("txt-financial-contact")), 0, 1,
                0, true, '', true);


            $financialAddress = $financialContactService->getFinancialAddress();
            $financialDetails = [
                [
                    $this->translate("txt-name"),
                    trim($financialContactService->parseAttention() . ' ' . $financialContactService->parseFullName())
                ],
                [
                    $this->translate("txt-email"),
                    $financialContactService->getContact()->getEmail()
                ],
                [
                    $this->translate("txt-vat-number"),
                    $affiliationService->parseVatNumber($affiliationService->getAffiliation())
                ],
                [
                    $this->translate("txt-billing-address"),
                    (!is_null($financialAddress) ? sprintf("%s \n %s\n%s\n%s %s\n%s", $this->getOrganisationService()
                        ->parseOrganisationWithBranch($affiliationService->getAffiliation()->getFinancial()
                            ->getBranch(), $affiliationService->getAffiliation()->getFinancial()->getOrganisation()),
                        trim($financialContactService->parseAttention() . ' '
                            . $financialContactService->parseFullName()), $financialAddress->getAddress()->getAddress(),
                        $financialAddress->getAddress()->getZipCode(), $financialAddress->getAddress()->getCity(),
                        strtoupper($financialAddress->getAddress()->getCountry()))
                        : "No billing address could be found")
                ],
                [
                    $this->translate("txt-preferred-delivery"),
                    ($affiliationService->getAffiliation()->getFinancial()->getOrganisation()->getFinancial()
                            ->getEmail() === Financial::EMAIL_DELIVERY)
                        ? sprintf($this->translate("txt-by-email-to-%s"),
                        $financialContactService->getContact()->getEmail()) : $this->translate("txt-by-postal-mail")

                ],
            ];

            $pdf->coloredTable([], $financialDetails, [55, 130]);
        }

        $pdf->addPage();

        $pdf->writeHTMLCell(0, 0, '', '', '<h3>' . $this->translate("txt-contribution-overview") . '</h3>', 0, 1, 0,
            true, '', true);
        $pdf->Ln();


        //Funding information
        $header = [
            $this->translate("txt-period"),
            $this->translate("txt-funding-status"),
            $this->translate("txt-costs"),
            $this->translate("txt-fee-percentage"),
            $this->translate("txt-contribution"),
            $this->translate("txt-due"),
            $this->translate("txt-amount-due"),

        ];

        $fundingDetails = [];

        $totalDueBasedOnProjectData = 0;
        foreach ($projectService->parseYearRange(false, $affiliation) as $projectYear) {
            $dueFactor = $affiliationService->parseContributionFactorDue($projectYear, $year, $period);

            $yearData = [];

            $yearData[] = $projectYear;

            if ($affiliationService->isSelfFunded()) {
                $yearData[] = $this->translate("txt-self-funded");
            } elseif (!is_null($affiliationService->getFundingInYear($projectYear))) {
                $yearData[] = $affiliationService->getFundingInYear($projectYear)->getStatus()->getStatus();
            } else {
                $yearData[] = "-";
            }

            if ($invoiceMethod->getId() === Method::METHOD_PERCENTAGE) {
                if (array_key_exists($projectYear, $versionContributionInformation->cost)) {
                    $dueInYear = $versionContributionInformation->cost[$projectYear] / 100
                        * $projectService->findProjectFeeByYear($projectYear)->getPercentage();
                    $yearData[] = $this->parseCost($versionContributionInformation->cost[$projectYear]);
                } else {
                    $dueInYear = 0;
                    $yearData[] = $this->parseCost(0);
                }

                if ($affiliationService->isFundedInYear($projectYear)) {
                    $yearData[] = $this->parsePercent($projectService->findProjectFeeByYear($projectYear)
                        ->getPercentage());
                } else {
                    $yearData[] = $this->parsePercent(0);
                }
            } else {
                if (array_key_exists($projectYear, $versionContributionInformation->cost)) {
                    $dueInYear = $versionContributionInformation->effort[$projectYear]
                        * $projectService->findProjectFeeByYear($projectYear)->getContribution();
                    $yearData[] = $this->parseEffort($versionContributionInformation->effort[$projectYear]);
                } else {
                    $dueInYear = 0;
                    $yearData[] = 0;
                }

                if ($affiliationService->isFundedInYear($projectYear)) {
                    $yearData[] = $this->parseCost($projectService->findProjectFeeByYear($projectYear)
                        ->getContribution());
                } else {
                    $yearData[] = $this->parseCost(0);
                }
            }

            if ($affiliationService->isFundedInYear($projectYear)) {
                $yearData[] = $this->parseCost($dueInYear);
            } else {
                $yearData[] = $this->parseCost(0);
            }

            $yearData[] = $this->parsePercent($dueFactor * 100);
            $yearData[] = $this->parseCost($dueInYear * $dueFactor);

            $totalDueBasedOnProjectData += $dueInYear * $dueFactor;

            $fundingDetails[] = $yearData;
        }

        //Add the total column
        $totalColumn = [
            '',
            '',
            '',
            '',
            '',
            $this->translate("txt-total"),
            $this->parseCost($totalDueBasedOnProjectData)
        ];

        $fundingDetails[] = $totalColumn;


        $pdf->coloredTable($header, $fundingDetails, [15, 25, 35, 25, 25, 25, 35], true);

        $contributionDue = $affiliationService->parseContributionDue($versionService->getVersion(), $year, $period);
        $contributionPaid = $affiliationService->parseContributionPaid($year, $period);

        $balance = $affiliationService->parseBalance($versionService->getVersion(), $year, $period);
        $total = $affiliationService->parseTotal($versionService->getVersion(), $year, $period);
        $contribution = $affiliationService->parseContribution($versionService->getVersion(), $year, $period);


        $pdf->writeHTMLCell(0, 0, '', '',
            '<h3>' . sprintf($this->translate("txt-already-sent-invoices-upto-year-%s-period-%s"), $year, $period)
            . '</h3>', 0, 1, 0, true, '', true);
        $pdf->Ln();


        //Funding information
        $header = [
            $this->translate("txt-invoice"),
            $this->translate("txt-period"),
            $this->translate("txt-date"),
            $this->translate("txt-contribution"),
            $this->translate("txt-paid"),
            $this->translate("txt-invoiced"),
        ];


        //Old Invoices
        $previousInvoices = [];
        foreach ($affiliation->getInvoice() as $affiliationInvoice) {
            if (!is_null($affiliationInvoice->getInvoice()->getDayBookNumber())
                && ($affiliationInvoice->getYear() < $year)
                    || ($affiliationInvoice->getYear() === $year)
                        && ($affiliationInvoice->getPeriod() < $period))
            {
                $previousInvoices[] = $affiliationInvoice;
            }
        }

        $currentInvoiceDetails = [];

        if (sizeof($previousInvoices) === 0) {
            $currentInvoiceDetails[] = [
                $this->translate("txt-no-invoices-found")
            ];
        } else {
            /**
             * @var $affiliationInvoice AffiliationInvoice
             */
            foreach ($previousInvoices as $affiliationInvoice) {
                $this->getInvoiceService()->setInvoice($affiliationInvoice->getInvoice());
                $currentInvoiceDetails[] = [
                    $affiliationInvoice->getInvoice()->getInvoiceNr(),
                    sprintf("%s-%s", $affiliationInvoice->getYear(), $affiliationInvoice->getPeriod()),
                    $affiliationInvoice->getInvoice()->getDateSent()->format('d-m-Y'),
                    $this->parseCost($this->getInvoiceService()->parseSumAmount()),
                    (!is_null($affiliationInvoice->getInvoice()->getBookingDate()) ? $affiliationInvoice->getInvoice()
                        ->getBookingDate()->format('d-m-Y') : ''),
                    $this->parseCost($this->getInvoiceService()->parseTotal())
                ];
            }
        }

        //Add the total column
        $currentInvoiceDetails[] = [
            '',
            '',
            $this->translate("txt-total"),
            $this->parseCost($contributionPaid),
            '',
            '',
        ];

        $pdf->coloredTable($header, $currentInvoiceDetails, [40, 35, 25, 25, 25, 35], true);


        $pdf->writeHTMLCell(0, 0, '', '', '<h3>' . $this->translate("txt-correction-calculation") . '</h3>', 0, 1, 0,
            true, '', true);


        $correctionDetails = [
            [
                sprintf($this->translate("txt-total-contribution-invoiced-upto-year-%s-period-%s"), $year, $period),
                $this->parseCost($contributionPaid)
            ],
            [
                sprintf($this->translate("txt-total-contribution-amount-due-upto-year-%s-period-%s"), $year, $period),
                $this->parseCost($contributionDue)
            ],
            [
                $this->translate("txt-correction"),
                $this->parseCost($balance)
            ],
        ];

        $pdf->coloredTable([], $correctionDetails, [95, 85], true);


        $pdf->writeHTMLCell(0, 0, '', '',
            '<h3>' . sprintf($this->translate("txt-invoice-for-year-%s-period-%s"), $year, $period) . '</h3>', 0, 1, 0,
            true, '', true);
        $pdf->Ln();

        //Partner information
        $header = [
            $this->translate("txt-period"),
            $this->translate("txt-contribution"),
            $this->translate("txt-amount"),

        ];

        $upcomingDetails = [
            [
                sprintf("%s-%s", $year, $period),
                sprintf($this->translate("txt-%s-contribution-for-%s"),
                    $this->parsePercent($affiliationService->parseContributionFactor($year, $period) * 100), $year),
                $this->parseCost($contribution)
            ],
            [
                '',
                $this->translate("txt-correction"),
                $this->parseCost($balance)
            ],
            [
                '',
                $this->translate("txt-total"),
                $this->parseCost($total)
            ],
        ];

        $pdf->coloredTable($header, $upcomingDetails, [25, 70, 85], true);


        //Funding information
        $header = [
            $this->translate("txt-invoice-number"),
            $this->translate("txt-period"),
            $this->translate("txt-date"),
            $this->translate("txt-paid"),
            $this->translate("txt-total-excl-vat"),
            $this->translate("txt-total"),
        ];


        //Old Invoices
        $upcomingInvoices = [];
        foreach ($affiliation->getInvoice() as $affiliationInvoice) {
            if ($affiliationInvoice->getYear() > $year || $affiliationInvoice->getYear() === $year
                && $affiliationInvoice->getPeriod() > $period
            ) {
                if (!is_null($affiliationInvoice->getInvoice()->getDateSent())) {
                    $upcomingInvoices[] = $affiliationInvoice;
                }
            }
        }

        $upcomingInvoiceDetails = [];

        if (sizeof($upcomingInvoices) > 0) {
            $pdf->writeHTMLCell(0, 0, '', '',
                '<h3>' . sprintf($this->translate("txt-already-sent-invoices-after-year-%s-period-%s") . '</h3>', $year,
                    $period), 0, 1, 0, true, '', true);
            $pdf->Ln();

            /**
             * @var $affiliationInvoice AffiliationInvoice
             */
            foreach ($upcomingInvoices as $affiliationInvoice) {
                $this->getInvoiceService()->setInvoice($affiliationInvoice->getInvoice());
                $upcomingInvoiceDetails[] = [
                    $affiliationInvoice->getInvoice()->getInvoiceNr(),
                    sprintf("%s-%s", $affiliationInvoice->getYear(), $affiliationInvoice->getPeriod()),
                    $affiliationInvoice->getInvoice()->getDateSent()->format('d-m-Y'),
                    (!is_null($affiliationInvoice->getInvoice()->getBookingDate()) ? $affiliationInvoice->getInvoice()
                        ->getBookingDate()->format('d-m-Y') : ''),
                    $this->parseCost($this->getInvoiceService()->parseSumAmount()),
                    $this->parseCost($this->getInvoiceService()->parseTotal())
                ];
            }

            $pdf->coloredTable($header, $upcomingInvoiceDetails, [45, 25, 25, 25, 25, 35], true);
        }


        return $pdf;
    }

    /**
     * @param $effort
     *
     * @return string
     */
    public function parseEffort($effort)
    {
        return sprintf("%s %s", number_format($effort, 2, '.', ','), 'PY');
    }

    /**
     * @param $percent
     *
     * @return string
     */
    public function parsePercent($percent)
    {
        return sprintf("%s %s", number_format($percent, 2, '.', ','), "%");
    }

    /**
     * @param $cost
     *
     * @return string
     */
    public function parseKiloCost($cost)
    {
        return sprintf("%s kEUR", number_format($cost / 1000, 0, '.', ','));
    }

    /**
     * @param $cost
     *
     * @return string
     */
    public function parseCost($cost)
    {
        return sprintf("%s EUR", number_format($cost, 2, '.', ','));
    }


    /**
     * Gateway to the Project Service.
     *
     * @return ProjectService
     */
    public function getProjectService()
    {
        return $this->getServiceLocator()->get(ProjectService::class);
    }

    /**
     * Gateway to the Version Service.
     *
     * @return VersionService
     */
    public function getVersionService()
    {
        return $this->getServiceLocator()->get(VersionService::class);
    }

    /**
     * Gateway to the Affiliation Service.
     *
     * @return AffiliationService
     */
    public function getAffiliationService()
    {
        return $this->getServiceLocator()->get(AffiliationService::class);
    }

    /**
     * Gateway to the Organisation Service.
     *
     * @return OrganisationService
     */
    public function getOrganisationService()
    {
        return $this->getServiceLocator()->get(OrganisationService::class);
    }

    /**
     * Gateway to the Invoice Service.
     *
     * @return InvoiceService
     */
    public function getInvoiceService()
    {
        return $this->getServiceLocator()->get(InvoiceService::class);
    }


    /**
     * Gateway to the Contact Service.
     *
     * @return ContactService
     */
    public function getContactService()
    {
        return $this->getServiceLocator()->get('contact_contact_service');
    }

    /**
     * @return ModuleOptions
     */
    public function getModuleOptions()
    {
        return $this->getServiceLocator()->get('affiliation_module_options');
    }


    /**
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return $this
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }


    /**
     * Proxy for the flash messenger helper to have the string translated earlier.
     *
     * @param $string
     *
     * @return string
     */
    protected function translate($string)
    {
        /**
         * @var $translate Translate
         */
        $translate = $this->getServiceLocator()->get('ViewHelperManager')->get('translate');

        return $translate($string);
    }
}
