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

namespace Affiliation\Controller\Plugin;

use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Invoice as AffiliationInvoice;
use General\Entity\Currency;
use General\Entity\ExchangeRate;
use Invoice\Entity\Method;
use Organisation\Entity\Financial;

/**
 * Class RenderLoi.
 */
class RenderPaymentSheet extends AbstractPlugin
{
    /**
     * @param Affiliation $affiliation
     * @param int $year
     * @param int $period
     * @param bool $useContractData
     * @return AffiliationPdf
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function render(
        Affiliation $affiliation,
        int $year,
        int $period,
        bool $useContractData = true
    ): AffiliationPdf {
        $project = $affiliation->getProject();
        $contact = $affiliation->getContact();
        $latestVersion = $this->getProjectService()->getLatestProjectVersion($project);
        $financialContact = $this->getAffiliationService()->getFinancialContact($affiliation);

        $versionContributionInformation = $this->getVersionService()
            ->getProjectVersionContributionInformation($affiliation, $latestVersion);

        $contractVersion = $this->getContractService()->findLatestContractVersionByAffiliation($affiliation);

        //Create a default currency
        $currency = new Currency();
        $currency->setName('EUR');
        $currency->setSymbol('&euro;');

        $exchangeRate = new ExchangeRate();
        $exchangeRate->setRate(1);

        if (!\is_null($contractVersion) && $useContractData) {
            $currency = $contractVersion->getContract()->getCurrency();
            $exchangeRate = $this->getContractService()->findExchangeRateInInvoicePeriod($currency, $year, $period);
        }


        $contractContributionInformation = null;
        if (null !== $contractVersion) {
            $contractContributionInformation = $this->getContractService()->getContractVersionContributionInformation(
                $affiliation,
                $contractVersion
            );
        }

        $invoiceMethod = $this->getAffiliationService()->parseInvoiceMethod(
            $affiliation,
            $useContractData
        );

        /** @var \TCPDF $pdf */
        $pdf = new AffiliationPdf();
        $pdf->setTemplate($this->getModuleOptions()->getPaymentSheetTemplate());
        $pdf->AddPage();
        $pdf->SetFontSize(9);
        $pdf->SetTopMargin(55);

        $pdf->writeHTMLCell(
            0,
            0,
            '',
            '',
            '<h1 style="color: #00a651">' . sprintf(
                $this->translate("txt-payment-sheet-year-%s-period-%s"),
                $year,
                $period
            ) . '</h1>',
            0,
            1,
            0,
            true,
            '',
            true
        );
        $pdf->Ln();
        $pdf->Line(10, 65, 190, 65, ['color' => [0, 166, 81]]);


        //Project information
        $pdf->writeHTMLCell(
            0,
            0,
            '',
            '',
            sprintf("<h3>%s</h3>", $this->translate("txt-project-details")),
            0,
            1,
            0,
            true,
            '',
            true
        );

        $projectDetails = [
            [
                $this->translate("txt-project-number"),
                $project->getNumber(),
            ],
            [
                $this->translate("txt-project-name"),
                $project->getProject(),
            ],
            [
                $this->translate("txt-start-date"),
                $this->getProjectService()->parseOfficialDateStart($project)->format('d-m-Y'),
            ],
            [
                $this->translate("txt-start-end"),
                $this->getProjectService()->parseOfficialDateEnd($project)->format('d-m-Y'),
            ],
            [
                $this->translate("txt-version-name"),
                $latestVersion->getVersionType(),
            ],
            [
                $this->translate("txt-version-status"),
                $this->getVersionService()->parseStatus($latestVersion),
            ],
            [
                $this->translate("txt-version-date"),
                !\is_null($latestVersion->getDateReviewed()) ? $latestVersion->getDateReviewed()->format("d-m-Y")
                    : '',
            ],
        ];

        $pdf->coloredTable([], $projectDetails, [55, 130]);


        //Partner information
        $pdf->writeHTMLCell(
            0,
            0,
            '',
            '',
            sprintf("<h3>%s</h3>", $this->translate("txt-project-partner")),
            0,
            1,
            0,
            true,
            '',
            true
        );

        $partnersDetails = [
            [
                $this->translate("txt-organisation"),
                $affiliation->getOrganisation(),
            ],
            [
                $this->translate("txt-organisation-type"),
                $affiliation->getOrganisation()->getType(),
            ],
            [
                $this->translate("txt-country"),
                $affiliation->getOrganisation()->getCountry(),
            ],
            [
                $this->translate("txt-total-person-years"),
                $this->parseEffort($versionContributionInformation->totalEffort),
            ],
            [
                $this->translate("txt-total-costs"),
                $this->parseKiloCost($versionContributionInformation->totalCost),
            ],
            [
                $this->translate("txt-average-cost"),
                ($versionContributionInformation->totalEffort > 0
                    ? $this->parseKiloCost(
                        $versionContributionInformation->totalCost
                        / $versionContributionInformation->totalEffort
                    ) : '-') . '/PY',
            ],
        ];

        $pdf->coloredTable([], $partnersDetails, [55, 130]);

        //Technical contact
        $pdf->writeHTMLCell(
            0,
            0,
            '',
            '',
            sprintf("<h3>%s</h3>", $this->translate("txt-technical-contact")),
            0,
            1,
            0,
            true,
            '',
            true
        );
        $partnersDetails = [
            [
                $this->translate("txt-name"),
                trim($this->getContactService()->parseAttention($contact) . ' ' . $contact->parseFullName()),
            ],
            [
                $this->translate("txt-email"),
                $contact->getEmail(),
            ],
        ];

        $pdf->coloredTable([], $partnersDetails, [55, 130]);


        if (!\is_null($financialContact)) {
            //Financial contact
            $pdf->writeHTMLCell(
                0,
                0,
                '',
                '',
                sprintf("<h3>%s</h3>", $this->translate("txt-financial-contact")),
                0,
                1,
                0,
                true,
                '',
                true
            );


            $financialAddress = $this->getContactService()->getFinancialAddress($financialContact);
            $financialDetails = [
                [
                    $this->translate("txt-name"),
                    trim(
                        $this->getContactService()->parseAttention($financialContact) . ' '
                        . $financialContact->parseFullName()
                    ),
                ],
                [
                    $this->translate("txt-email"),
                    $financialContact->getEmail(),
                ],
                [
                    $this->translate("txt-vat-number"),
                    $this->getAffiliationService()->parseVatNumber($affiliation),
                ],
                [
                    $this->translate("txt-billing-address"),
                    !\is_null($financialAddress) ? sprintf(
                        "%s \n %s\n%s\n%s %s\n%s",
                        $this->getOrganisationService()->parseOrganisationWithBranch(
                            $affiliation->getFinancial()
                                ->getBranch(),
                            $affiliation->getFinancial()->getOrganisation()
                        ),
                        trim(
                            $this->getContactService()->parseAttention($financialContact) . ' '
                            . $financialContact->parseFullName()
                        ),
                        $financialAddress->getAddress(),
                        $financialAddress->getZipCode(),
                        $financialAddress->getCity(),
                        strtoupper($financialAddress->getCountry()->getCountry())
                    ) : "No billing address could be found",
                ],
                [
                    $this->translate("txt-preferred-delivery"),
                    \is_null($affiliation->getFinancial())
                    || \is_null($affiliation->getFinancial()->getOrganisation()->getFinancial())
                        ? 'No billing organisation known'
                        : (($affiliation->getFinancial()->getOrganisation()->getFinancial()->getEmail()
                        === Financial::EMAIL_DELIVERY) ? sprintf(
                            $this->translate("txt-by-email-to-%s"),
                            $financialContact->getEmail()
                        ) : $this->translate("txt-by-postal-mail")),

                ],
            ];

            $pdf->coloredTable([], $financialDetails, [55, 130]);
        }

        $pdf->AddPage();

        $pdf->writeHTMLCell(
            0,
            0,
            '',
            '',
            '<h3>' . $this->translate("txt-contribution-overview") . '</h3>',
            0,
            1,
            0,
            true,
            '',
            true
        );
        $pdf->Ln();

        switch ($invoiceMethod) {
            case Method::METHOD_PERCENTAGE_CONTRACT:
                //Funding information
                $header = [
                    $this->translate("txt-period"),
                    $this->translate("txt-funding-status"),
                    $this->translate("txt-costs-local-currency"),
                    $this->translate("txt-fee-percentage"),
                    $this->translate("txt-contribution"),
                    $this->translate("txt-txt-amount-invoiced"),
                ];
                break;
            default:
                $header = [
                    $this->translate("txt-period"),
                    $this->translate("txt-funding-status"),
                    $this->translate("txt-costs"),
                    $this->translate("txt-fee-percentage"),
                    $this->translate("txt-contribution"),
                    $this->translate("txt-due"),
                    $this->translate("txt-amount-due"),
                ];
                break;
        }

        $fundingDetails = [];

        $totalDueBasedOnProjectData = 0;
        foreach ($this->getProjectService()->parseYearRange($project) as $projectYear) {
            $dueFactor = $this->getAffiliationService()
                ->parseContributionFactorDue($affiliation, $projectYear, $year, $period);

            $yearData = [];

            $yearData[] = $projectYear;

            if ($this->getAffiliationService()->isSelfFunded($affiliation)) {
                $yearData[] = $this->translate("txt-self-funded");
            } elseif (!\is_null($this->getAffiliationService()->getFundingInYear($affiliation, $projectYear))) {
                $yearData[] = $this->getAffiliationService()->getFundingInYear($affiliation, $projectYear)->getStatus()
                    ->getStatusFunding();
            } else {
                $yearData[] = '-';
            }

            $dueInYear = 0;

            switch ($invoiceMethod) {
                case Method::METHOD_PERCENTAGE:
                    if (\array_key_exists($projectYear, $versionContributionInformation->cost)) {
                        $dueInYear = $versionContributionInformation->cost[$projectYear] / 100 * $this->getProjectService()
                                ->findProjectFeeByYear($projectYear)
                                ->getPercentage();
                        $yearData[] = $this->parseCost($versionContributionInformation->cost[$projectYear]);
                    } else {
                        $yearData[] = $this->parseCost(0);
                    }

                    if ($this->getAffiliationService()->isFundedInYear($affiliation, $projectYear)) {
                        $yearData[] = $this->parsePercent(
                            $this->getProjectService()->findProjectFeeByYear($projectYear)
                                ->getPercentage()
                        );
                    } else {
                        $yearData[] = $this->parsePercent(0);
                    }

                    if ($this->getAffiliationService()->isFundedInYear($affiliation, $projectYear)) {
                        $yearData[] = $this->parseCost($dueInYear);
                    } else {
                        $yearData[] = $this->parseCost(0);
                    }

                    $yearData[] = $this->parsePercent($dueFactor * 100, 0);
                    $yearData[] = $this->parseCost($dueInYear * $dueFactor);

                    break;

                case Method::METHOD_PERCENTAGE_CONTRACT:
                    // when we have no exchange rate, add a message that the exchange rate has been fixed to one

                    $dueInYear = $contractContributionInformation->cost[$projectYear] / 100 * $this->getProjectService()->findProjectFeeByYear($projectYear)->getPercentage();


                    $yearData[] = $this->parseCost($contractContributionInformation->cost[$projectYear], $currency);

                    if ($this->getAffiliationService()->isFundedInYear($affiliation, $projectYear)) {
                        $yearData[] = $this->parsePercent(
                            $this->getProjectService()->findProjectFeeByYear($projectYear)->getPercentage()
                        );
                        $yearData[] = $this->parseCost($dueInYear, $currency);
                    } else {
                        $yearData[] = $this->parsePercent(0);
                        $yearData[] = $this->parseCost(0);
                    }

                    if ($projectYear <= $year) {
                        $yearData[] = $this->parseCost($this->getAffiliationService()->parseAmountInvoicedInYearByAffiliation($affiliation, $projectYear));
                    } else {
                        $yearData[] = null;
                    }

                    break;

                case Method::METHOD_CONTRIBUTION:
                    if (array_key_exists($projectYear, $versionContributionInformation->cost)) {
                        $dueInYear = $versionContributionInformation->effort[$projectYear] * $this->getProjectService()
                                ->findProjectFeeByYear($projectYear)
                                ->getContribution();
                        $yearData[] = $this->parseEffort($versionContributionInformation->effort[$projectYear]);
                    } else {
                        $yearData[] = 0;
                    }

                    if ($this->getAffiliationService()->isFundedInYear($affiliation, $projectYear)) {
                        $yearData[] = $this->parseCost(
                            $this->getProjectService()->findProjectFeeByYear($projectYear)->getContribution()
                        );
                    } else {
                        $yearData[] = $this->parseCost(0);
                    }


                    if ($this->getAffiliationService()->isFundedInYear($affiliation, $projectYear)) {
                        $yearData[] = $this->parseCost($dueInYear);
                    } else {
                        $yearData[] = $this->parseCost(0);
                    }

                    $yearData[] = $this->parsePercent($dueFactor * 100, 0);
                    $yearData[] = $this->parseCost($dueInYear * $dueFactor);

                    break;
            }



            $totalDueBasedOnProjectData += $dueInYear * $dueFactor;

            $fundingDetails[] = $yearData;
        }

        switch ($invoiceMethod) {
            case Method::METHOD_PERCENTAGE_CONTRACT:
                //Funding information
                $header = [
                    $this->translate("txt-period"),
                    $this->translate("txt-funding-status"),
                    $this->translate("txt-costs-local-currency"),
                    $this->translate("txt-fee-percentage"),
                    $this->translate("txt-contribution"),
                    $this->translate("txt-amount-invoiced"),
                ];

                $pdf->coloredTable($header, $fundingDetails, [20, 35, 40, 30, 30, 30], false);
                break;
            default:
                //Add the total column
                $totalColumn = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    $this->translate("txt-total"),
                    $this->parseCost($totalDueBasedOnProjectData),
                ];

                $fundingDetails[] = $totalColumn;

                $pdf->coloredTable($header, $fundingDetails, [20, 30, 30, 30, 30, 15, 30], true);
                break;
        }



        $contributionDue = $this->getAffiliationService()
            ->parseContributionDue($affiliation, $latestVersion, $year, $period, $useContractData);
        $contributionPaid = $this->getAffiliationService()->parseContributionPaid($affiliation, $year, $period);

        //Old Invoices
        $previousInvoices = [];
        foreach ($affiliation->getInvoice() as $affiliationInvoice) {
            if (!\is_null($affiliationInvoice->getInvoice()->getDayBookNumber())
                && ($affiliationInvoice->getYear() < $year
                    || ($affiliationInvoice->getYear() === $year && $affiliationInvoice->getPeriod() < $period))
            ) {
                $previousInvoices[] = $affiliationInvoice;
            }
        }

        if (\count($previousInvoices) > 0) {
            $pdf->writeHTMLCell(
                0,
                0,
                '',
                '',
                '<h3>' . sprintf($this->translate("txt-already-sent-invoices-upto-year-%s-period-%s"), $year, $period)
                . '</h3>',
                0,
                1,
                0,
                true,
                '',
                true
            );
            $pdf->Ln();


            //Old Invoices
            $header = [
                $this->translate("txt-invoice"),
                $this->translate("txt-period"),
                $this->translate("txt-date"),
                $this->translate("txt-contribution"),
                $this->translate("txt-paid"),
                $this->translate("txt-invoiced"),
            ];

            $currentInvoiceDetails = [];

            /**
             * @var $affiliationInvoice AffiliationInvoice
             */
            foreach ($previousInvoices as $affiliationInvoice) {
                $currentInvoiceDetails[] = [
                    $affiliationInvoice->getInvoice()->getInvoiceNr(),
                    sprintf("%s-%s", $affiliationInvoice->getYear(), $affiliationInvoice->getPeriod()),
                    $affiliationInvoice->getInvoice()->getDateSent()->format('d-m-Y'),
                    $this->parseCost($this->getInvoiceService()->parseSumAmount($affiliationInvoice->getInvoice())),
                    null !== $affiliationInvoice->getInvoice()->getBookingDate() ? $affiliationInvoice->getInvoice()
                        ->getBookingDate()
                        ->format('d-m-Y')
                        : '',
                    $this->parseCost($this->getInvoiceService()->parseTotal($affiliationInvoice->getInvoice())),
                ];
            }

            if ($invoiceMethod !== Method::METHOD_PERCENTAGE_CONTRACT) {
                //Add the total column
                $currentInvoiceDetails[] = [
                    '',
                    '',
                    $this->translate("txt-total"),
                    $this->parseCost($contributionPaid),
                    '',
                    '',
                ];
            }

            $pdf->coloredTable($header, $currentInvoiceDetails, [40, 35, 25, 25, 25, 35], $invoiceMethod !== Method::METHOD_PERCENTAGE_CONTRACT);
        }

        switch ($invoiceMethod) {
            case Method::METHOD_PERCENTAGE_CONTRACT:
                $pdf->writeHTMLCell(
                    0,
                    0,
                    '',
                    '',
                    '<h3>' . sprintf($this->translate("txt-invoice-for-year-%s-period-%s"), $year, $period) . '</h3>',
                    0,
                    1,
                    0,
                    true,
                    '',
                    true
                );
                $pdf->Ln();

                $header = [
                    $this->translate("txt-period"),
                    $this->translate("txt-information"),
                    $this->translate("txt-amount"),
                ];


                $upcomingDetails = [];
                foreach ($this->getAffiliationService()->findInvoiceLines(
                    $affiliation,
                    $contractVersion,
                    $year,
                    $period
                ) as $invoiceLine) {
                    $upcomingDetails[] = [
                        $invoiceLine->periodOrdinal,
                        $invoiceLine->description,
                        $this->parseCost($invoiceLine->lineTotal) . ($invoiceLine->lineTotal < -0.1 ? ' ' . $this->translate("txt-credit") : ''),

                    ];
                }

                $total = $this->getAffiliationService()->parseTotalByInvoiceLines(
                    $affiliation,
                    $contractVersion,
                    $year,
                    $period
                );



                if (\count($upcomingDetails) > 0) {
                    $upcomingDetails[] = [
                        '',
                        $this->translate("txt-total"),
                        $this->parseCost($total) . ($total < -0.1 ? ' ' . $this->translate("txt-credit") : '')

                    ];

                    $pdf->coloredTable($header, $upcomingDetails, [20, 120, 45], true, 12);
                }

                break;
            default:
                $pdf->writeHTMLCell(
                    0,
                    0,
                    '',
                    '',
                    '<h3>' . $this->translate("txt-correction-calculation") . '</h3>',
                    0,
                    1,
                    0,
                    true,
                    '',
                    true
                );

                $balance = $this->getAffiliationService()->parseBalance($affiliation, $latestVersion, $year, $period, $useContractData);
                $total = $this->getAffiliationService()->parseTotal($affiliation, $latestVersion, $year, $period);
                $contribution = $this->getAffiliationService()->parseContribution(
                    $affiliation,
                    $latestVersion,
                    null,
                    $year,
                    $period,
                    false
                );


                $correctionDetails = [
                    [
                        sprintf(
                            $this->translate("txt-total-contribution-invoiced-upto-year-%s-period-%s"),
                            $year,
                            $period
                        ),
                        $this->parseCost($contributionPaid),
                    ],
                    [
                        sprintf(
                            $this->translate("txt-total-contribution-amount-due-upto-year-%s-period-%s"),
                            $year,
                            $period
                        ),
                        $this->parseCost($contributionDue),
                    ],
                    [
                        $this->translate("txt-correction"),
                        $this->parseCost($balance),
                    ],
                ];

                $pdf->coloredTable([], $correctionDetails, [100, 85], true);


                $pdf->writeHTMLCell(
                    0,
                    0,
                    '',
                    '',
                    '<h3>' . sprintf($this->translate("txt-invoice-for-year-%s-period-%s"), $year, $period) . '</h3>',
                    0,
                    1,
                    0,
                    true,
                    '',
                    true
                );
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
                        sprintf(
                            $this->translate("txt-%s-contribution-for-%s"),
                            $this->parsePercent(
                                $this->getAffiliationService()
                                    ->parseContributionFactor($affiliation, $year, $period) * 100,
                                0
                            ),
                            $year
                        ),
                        $this->parseCost($contribution),
                    ],
                    [
                        '',
                        $this->translate("txt-correction"),
                        $this->parseCost($balance),
                    ],
                    [
                        '',
                        $this->translate("txt-total"),
                        $this->parseCost($total),
                    ],
                ];

                $pdf->coloredTable($header, $upcomingDetails, [25, 70, 90], true);

                break;
        }




        //$already sent invoices
        $header = [
            $this->translate("txt-invoice-number"),
            $this->translate("txt-period"),
            $this->translate("txt-date"),
            $this->translate("txt-paid"),
            $this->translate("txt-total-excl-vat"),
            $this->translate("txt-total"),
        ];


        //Old Invoices
        $alreadySentInvoices = [];
        foreach ($affiliation->getInvoice() as $affiliationInvoice) {
            if ($affiliationInvoice->getYear() > $year
                || ($affiliationInvoice->getYear() === $year
                    && $affiliationInvoice->getPeriod() > $period)
            ) {
                if (!\is_null($affiliationInvoice->getInvoice()->getDateSent())) {
                    $alreadySentInvoices[] = $affiliationInvoice;
                }
            }
        }

        $alreadySentInvoiceDetails = [];

        if (\count($alreadySentInvoices) > 0) {
            $pdf->writeHTMLCell(
                0,
                0,
                '',
                '',
                '<h3>' . sprintf(
                    $this->translate("txt-already-sent-invoices-after-year-%s-period-%s") . '</h3>',
                    $year,
                    $period
                ),
                0,
                1,
                0,
                true,
                '',
                true
            );
            $pdf->Ln();

            /**
             * @var $affiliationInvoice AffiliationInvoice
             */
            foreach ($alreadySentInvoices as $affiliationInvoice) {
                $alreadySentInvoiceDetails[] = [
                    $affiliationInvoice->getInvoice()->getInvoiceNr(),
                    sprintf("%s-%s", $affiliationInvoice->getYear(), $affiliationInvoice->getPeriod()),
                    $affiliationInvoice->getInvoice()->getDateSent()->format('d-m-Y'),
                    null !== $affiliationInvoice->getInvoice()->getBookingDate() ? $affiliationInvoice->getInvoice()
                        ->getBookingDate()
                        ->format('d-m-Y')
                        : '',
                    $this->parseCost($this->getInvoiceService()->parseSumAmount($affiliationInvoice->getInvoice())),
                    $this->parseCost($this->getInvoiceService()->parseTotal($affiliationInvoice->getInvoice())),
                ];
            }

            $pdf->coloredTable($header, $alreadySentInvoiceDetails, [45, 25, 25, 25, 25, 35], true);
        }


        return $pdf;
    }

    /**
     * @param $effort
     *
     * @return string
     */
    public function parseEffort($effort): string
    {
        return sprintf("%s %s", number_format($effort, 2, '.', ','), 'PY');
    }

    /**
     * @param $cost
     * @param Currency|null $currency
     * @return string
     */
    public function parseKiloCost($cost, Currency $currency = null): string
    {
        $abbreviation = 'EUR';
        if (null !== $currency) {
            $abbreviation = $currency->getIso4217();
        }

        return sprintf("%s k%s", number_format($cost / 1000, 0, '.', ','), $abbreviation);
    }

    /**
     * @param $cost
     * @param Currency|null $currency
     * @return string
     */
    public function parseCost($cost, Currency $currency = null): string
    {
        $abbreviation = 'EUR';
        if (null !== $currency) {
            $abbreviation = $currency->getIso4217();
        }

        return sprintf("%s %s", number_format($cost, 2, '.', ','), $abbreviation);
    }

    /**
     * @param $percent
     * @param int $decimals
     * @return string
     */
    public function parsePercent($percent, int $decimals = 2): string
    {
        return sprintf("%s %s", number_format((float)$percent, $decimals, '.', ','), "%");
    }
}
