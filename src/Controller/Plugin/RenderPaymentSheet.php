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
use Affiliation\Options\ModuleOptions;
use Affiliation\Service\AffiliationService;
use Contact\Service\ContactService;
use General\Entity\Currency;
use General\Entity\ExchangeRate;
use InvalidArgumentException;
use Invoice\Entity\Invoice;
use Invoice\Entity\Method;
use Invoice\Service\InvoiceService;
use Organisation\Entity\Financial;
use Organisation\Service\OrganisationService;
use Project\Service\ContractService;
use Project\Service\ProjectService;
use Project\Service\VersionService;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use function array_key_exists;
use function count;
use function implode;
use function sprintf;
use function strtoupper;
use function trim;

/**
 * Class RenderPaymentSheet
 *
 * @package Affiliation\Controller\Plugin
 */
final class RenderPaymentSheet extends AbstractPlugin
{
    /**
     * @var AffiliationService
     */
    private $affiliationService;
    /**
     * @var ModuleOptions
     */
    private $moduleOptions;
    /**
     * @var ProjectService
     */
    private $projectService;
    /**
     * @var VersionService
     */
    private $versionService;
    /**
     * @var ContractService
     */
    private $contractService;
    /**
     * @var ContactService
     */
    private $contactService;
    /**
     * @var OrganisationService
     */
    private $organisationService;
    /**
     * @var InvoiceService
     */
    private $invoiceService;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        AffiliationService $affiliationService,
        ModuleOptions $moduleOptions,
        ProjectService $projectService,
        VersionService $versionService,
        ContractService $contractService,
        ContactService $contactService,
        OrganisationService $organisationService,
        InvoiceService $invoiceService,
        TranslatorInterface $translator
    ) {
        $this->affiliationService = $affiliationService;
        $this->moduleOptions = $moduleOptions;
        $this->projectService = $projectService;
        $this->versionService = $versionService;
        $this->contractService = $contractService;
        $this->contactService = $contactService;
        $this->organisationService = $organisationService;
        $this->invoiceService = $invoiceService;
        $this->translator = $translator;
    }


    public function __invoke(
        Affiliation $affiliation,
        int $year,
        int $period,
        bool $useContractData = true
    ): AffiliationPdf {
        $project = $affiliation->getProject();
        $contact = $affiliation->getContact();
        $latestVersion = $this->projectService->getLatestSubmittedProjectVersion($project);

        if (null === $latestVersion) {
            throw new InvalidArgumentException('No latest version could be found, no payment sheet can be created');
        }

        $financialContact = $this->affiliationService->getFinancialContact($affiliation);

        $versionContributionInformation = $this->versionService
            ->getProjectVersionContributionInformation($affiliation, $latestVersion);

        $contractVersion = $this->contractService->findLatestContractVersionByAffiliation($affiliation);

        //Create a default currency
        $currency = new Currency();
        $currency->setName('EUR');
        $currency->setSymbol('&euro;');

        $exchangeRate = new ExchangeRate();
        $exchangeRate->setRate(1);

        if (null !== $contractVersion && $useContractData) {
            $currency = $contractVersion->getContract()->getCurrency();
        }

        $contractContributionInformation = null;
        if (null !== $contractVersion) {
            $contractContributionInformation = $this->contractService->getContractVersionContributionInformation(
                $affiliation,
                $contractVersion
            );
        }

        $invoiceMethod = $this->affiliationService->parseInvoiceMethod(
            $affiliation,
            $useContractData
        );

        $pdf = new AffiliationPdf();
        $pdf->setTemplate($this->moduleOptions->getPaymentSheetTemplate());
        $pdf->AddPage();
        $pdf->SetFontSize(9);
        $pdf->SetTopMargin(55);

        $pdf->writeHTMLCell(
            0,
            0,
            '',
            '',
            '<h1 style="color: #00a651">' . sprintf(
                $this->translator->translate('txt-payment-sheet-year-%s-period-%s'),
                $year,
                $period
            ) . '</h1>',
            0,
            1,
            0
        );
        $pdf->Ln();
        $pdf->Line(10, 65, 190, 65, ['color' => [0, 166, 81]]);


        //Project information
        $pdf->writeHTMLCell(
            0,
            0,
            '',
            '',
            sprintf('<h3>%s</h3>', $this->translator->translate('txt-project-details')),
            0,
            1,
            0
        );

        $projectDetails = [
            [
                $this->translator->translate('txt-project-number'),
                $project->getNumber(),
            ],
            [
                $this->translator->translate('txt-project-name'),
                $project->getProject(),
            ],
            [
                $this->translator->translate('txt-start-date'),
                $this->projectService->parseOfficialDateStart($project)->format('d-m-Y'),
            ],
            [
                $this->translator->translate('txt-start-end'),
                $this->projectService->parseOfficialDateEnd($project)->format('d-m-Y'),
            ],
            [
                $this->translator->translate('txt-version-name'),
                $latestVersion->getVersionType(),
            ],
            [
                $this->translator->translate('txt-version-status'),
                $this->versionService->parseStatus($latestVersion),
            ],
            [
                $this->translator->translate('txt-version-date'),
                null !== $latestVersion->getDateReviewed() ? $latestVersion->getDateReviewed()->format('d-m-Y')
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
            sprintf('<h3>%s</h3>', $this->translator->translate('txt-project-partner')),
            0,
            1,
            0
        );

        $partnersDetails = [
            [
                $this->translator->translate('txt-organisation'),
                $affiliation->getOrganisation(),
            ],
            [
                $this->translator->translate('txt-organisation-type'),
                $affiliation->getOrganisation()->getType(),
            ],
            [
                $this->translator->translate('txt-country'),
                $affiliation->getOrganisation()->getCountry(),
            ],
            [
                $this->translator->translate('txt-total-person-years'),
                $this->parseEffort($versionContributionInformation->totalEffort),
            ]
        ];

        $pdf->coloredTable([], $partnersDetails, [55, 130]);

        //Technical contact
        $pdf->writeHTMLCell(
            0,
            0,
            '',
            '',
            sprintf('<h3>%s</h3>', $this->translator->translate('txt-technical-contact')),
            0,
            1,
            0
        );
        $partnersDetails = [
            [
                $this->translator->translate('txt-name'),
                trim($this->contactService->parseAttention($contact) . ' ' . $contact->parseFullName()),
            ],
            [
                $this->translator->translate('txt-email'),
                $contact->getEmail(),
            ],
        ];

        $pdf->coloredTable([], $partnersDetails, [55, 130]);


        if (null !== $financialContact) {
            //Financial contact
            $pdf->writeHTMLCell(
                0,
                0,
                '',
                '',
                sprintf('<h3>%s</h3>', $this->translator->translate('txt-financial-contact')),
                0,
                1,
                0
            );

            $preferredDelivery = 'No billing organisation known';

            if (null !== $affiliation->getFinancial()
                && null !== $affiliation->getFinancial()->getOrganisation()->getFinancial()
            ) {
                $preferredDelivery = $this->translator->translate('txt-by-postal-mail');

                if ($affiliation->getFinancial()->getOrganisation()->getFinancial()->getEmail()
                    === Financial::EMAIL_DELIVERY
                ) {
                    $preferredDelivery = sprintf(
                        $this->translator->translate('txt-by-email-to-%s'),
                        $financialContact->getEmail()
                    );
                }
            }

            $financialAddress = $this->contactService->getFinancialAddress($financialContact);
            $financialDetails = [
                [
                    $this->translator->translate('txt-name'),
                    trim(
                        $this->contactService->parseAttention($financialContact) . ' '
                        . $financialContact->parseFullName()
                    ),
                ],
                [
                    $this->translator->translate('txt-email'),
                    $financialContact->getEmail(),
                ],
                [
                    $this->translator->translate('txt-vat-number'),
                    $this->affiliationService->parseVatNumber($affiliation),
                ],
                [
                    $this->translator->translate('txt-billing-address'),
                    null !== $financialAddress ? trim(
                        sprintf(
                            '
                                    %s
                                    %s
                                    %s
                                    %s %s
                                    %s',
                            $this->organisationService->parseOrganisationWithBranch(
                                $affiliation->getFinancial()
                                    ->getBranch(),
                                $affiliation->getFinancial()->getOrganisation()
                            ),
                            ($affiliation->getFinancial()->getOrganisation()->getFinancial()->hasOmitContact()
                                ? ''
                                : trim(
                                    $this->contactService->parseAttention($financialContact) . ' '
                                    . $financialContact->parseFullName()
                                )),
                            $financialAddress->getAddress(),
                            $financialAddress->getZipCode(),
                            $financialAddress->getCity(),
                            strtoupper($financialAddress->getCountry()->getCountry())
                        )
                    ) : 'No billing address could be found',
                ],
                [
                    $this->translator->translate('txt-preferred-delivery'),
                    $preferredDelivery

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
            '<h3>' . $this->translator->translate('txt-contribution-overview') . '</h3>',
            0,
            1,
            0
        );
        $pdf->Ln();

        switch ($invoiceMethod) {
            case Method::METHOD_PERCENTAGE_CONTRACT:
                //Funding information
                $header = [
                    $this->translator->translate('txt-period'),
                    $this->translator->translate('txt-funding-status'),
                    $this->translator->translate('txt-costs-local-currency'),
                    $this->translator->translate('txt-fee-percentage'),
                    $this->translator->translate('txt-contribution'),
                    $this->translator->translate('txt-amount-invoiced'),
                ];
                break;
            default:
                $header = [
                    $this->translator->translate('txt-period'),
                    $this->translator->translate('txt-funding-status'),
                    $this->translator->translate('txt-costs'),
                    $this->translator->translate('txt-fee-percentage'),
                    $this->translator->translate('txt-contribution'),
                    $this->translator->translate('txt-due'),
                    $this->translator->translate('txt-amount-due'),
                ];
                break;
        }

        $fundingDetails = [];

        $totalDueBasedOnProjectData = 0;
        foreach ($this->projectService->parseYearRange($project) as $projectYear) {
            $dueFactor = $this->affiliationService
                ->parseContributionFactorDue($affiliation, $projectYear, $year, $period);

            $yearData = [];

            $yearData[] = $projectYear;

            if ($this->affiliationService->isSelfFunded($affiliation)) {
                $yearData[] = $this->translator->translate('txt-self-funded');
            } elseif (null !== $this->affiliationService->getFundingInYear($affiliation, $projectYear)) {
                $yearData[] = $this->affiliationService->getFundingInYear($affiliation, $projectYear)->getStatus()
                    ->getStatusFunding();
            } else {
                $yearData[] = '-';
            }

            $dueInYear = 0;

            switch ($invoiceMethod) {
                case Method::METHOD_PERCENTAGE:
                    if (array_key_exists($projectYear, $versionContributionInformation->cost)) {
                        $dueInYear = $versionContributionInformation->cost[$projectYear] / 100
                            * $this->projectService
                                ->findProjectFeeByYear($projectYear)
                                ->getPercentage();
                        $yearData[] = $this->parseCost($versionContributionInformation->cost[$projectYear]);
                    } else {
                        $yearData[] = $this->parseCost(0);
                    }

                    if ($this->affiliationService->isFundedInYear($affiliation, $projectYear)) {
                        $yearData[] = $this->parsePercent(
                            $this->projectService->findProjectFeeByYear($projectYear)
                                ->getPercentage()
                        );
                    } else {
                        $yearData[] = $this->parsePercent(0);
                    }

                    if ($this->affiliationService->isFundedInYear($affiliation, $projectYear)) {
                        $yearData[] = $this->parseCost($dueInYear);
                    } else {
                        $yearData[] = $this->parseCost(0);
                    }

                    $yearData[] = $this->parsePercent($dueFactor * 100, 0);
                    $yearData[] = $this->parseCost($dueInYear * $dueFactor);

                    break;

                case Method::METHOD_PERCENTAGE_CONTRACT:
                    // when we have no exchange rate, add a message that the exchange rate has been fixed to one

                    //Check first if we have info in this year
                    if (array_key_exists($projectYear, $contractContributionInformation->cost)) {
                        $dueInYear = $contractContributionInformation->cost[$projectYear] / 100
                            * $this->projectService->findProjectFeeByYear($projectYear)->getPercentage();


                        $yearData[] = $this->parseCost($contractContributionInformation->cost[$projectYear], $currency);

                        if ($this->affiliationService->isFundedInYear($affiliation, $projectYear)) {
                            $yearData[] = $this->parsePercent(
                                $this->projectService->findProjectFeeByYear($projectYear)->getPercentage()
                            );
                            $yearData[] = $this->parseCost($dueInYear, $currency);
                        } else {
                            $yearData[] = $this->parsePercent(0);
                            $yearData[] = $this->parseCost(0);
                        }

                        if ($projectYear <= $year) {
                            $yearData[] = $this->parseCost(
                                $this->affiliationService->parseAmountInvoicedInYearByAffiliation(
                                    $affiliation,
                                    $projectYear
                                )
                            );
                        } else {
                            $yearData[] = null;
                        }
                    } else {
                        $yearData[] = null;
                    }

                    break;

                case Method::METHOD_CONTRIBUTION:
                    if (array_key_exists($projectYear, $versionContributionInformation->cost)) {
                        $dueInYear = $versionContributionInformation->effort[$projectYear] * $this->projectService
                                ->findProjectFeeByYear($projectYear)
                                ->getContribution();
                        $yearData[] = $this->parseEffort($versionContributionInformation->effort[$projectYear]);
                    } else {
                        $yearData[] = 0;
                    }

                    if ($this->affiliationService->isFundedInYear($affiliation, $projectYear)) {
                        $yearData[] = $this->parseCost(
                            $this->projectService->findProjectFeeByYear($projectYear)->getContribution()
                        );
                    } else {
                        $yearData[] = $this->parseCost(0);
                    }


                    if ($this->affiliationService->isFundedInYear($affiliation, $projectYear)) {
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
                    $this->translator->translate('txt-period'),
                    $this->translator->translate('txt-funding-status'),
                    $this->translator->translate('txt-costs-local-currency'),
                    $this->translator->translate('txt-fee-percentage'),
                    $this->translator->translate('txt-contribution'),
                    $this->translator->translate('txt-amount-invoiced'),
                ];

                $pdf->coloredTable($header, $fundingDetails, [20, 35, 40, 30, 30, 30]);
                break;
            default:
                //Add the total column
                $totalColumn = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    $this->translator->translate('txt-total'),
                    $this->parseCost($totalDueBasedOnProjectData),
                ];

                $fundingDetails[] = $totalColumn;

                $pdf->coloredTable($header, $fundingDetails, [20, 30, 30, 30, 30, 15, 30], true);
                break;
        }


        $contributionDue = $this->affiliationService->parseContributionDue(
            $affiliation,
            $latestVersion,
            $year,
            $period
        );
        $contributionPaid = $this->affiliationService->parseContributionPaid($affiliation, $year, $period);

        //Old Invoices
        $previousInvoices = [];
        foreach ($affiliation->getInvoice() as $affiliationInvoice) {
            if (null !== $affiliationInvoice->getInvoice()->getDayBookNumber()
                && ($affiliationInvoice->getYear() < $year
                    || ($affiliationInvoice->getYear() === $year && $affiliationInvoice->getPeriod() < $period))
            ) {
                $previousInvoices[] = $affiliationInvoice;
            }
        }

        if (count($previousInvoices) > 0) {
            $pdf->writeHTMLCell(
                0,
                0,
                '',
                '',
                '<h3>' . sprintf(
                    $this->translator->translate('txt-already-sent-invoices-upto-year-%s-period-%s'),
                    $year,
                    $period
                )
                . '</h3>',
                0,
                1,
                0
            );
            $pdf->Ln();


            //Old Invoices
            $header = [
                $this->translator->translate('txt-invoice'),
                $this->translator->translate('txt-period'),
                $this->translator->translate('txt-date'),
                $this->translator->translate('txt-contribution'),
                $this->translator->translate('txt-paid'),
                $this->translator->translate('txt-invoiced'),
            ];

            $currentInvoiceDetails = [];

            /**
             * @var $affiliationInvoice AffiliationInvoice
             */
            foreach ($previousInvoices as $affiliationInvoice) {
                $currentInvoiceDetails[] = [
                    $affiliationInvoice->getInvoice()->getInvoiceNr(),
                    sprintf('%s-%s', $affiliationInvoice->getYear(), $affiliationInvoice->getPeriod()),
                    $affiliationInvoice->getInvoice()->getDateSent()->format('d-m-Y'),
                    $this->parseCost($this->invoiceService->parseSumAmount($affiliationInvoice->getInvoice())),
                    null !== $affiliationInvoice->getInvoice()->getBookingDate() ? $affiliationInvoice->getInvoice()
                        ->getBookingDate()
                        ->format('d-m-Y')
                        : '',
                    $this->parseCost($this->invoiceService->parseTotal($affiliationInvoice->getInvoice())),
                ];
            }

            if ($invoiceMethod !== Method::METHOD_PERCENTAGE_CONTRACT) {
                //Add the total column
                $currentInvoiceDetails[] = [
                    '',
                    '',
                    $this->translator->translate('txt-total'),
                    $this->parseCost($contributionPaid),
                    '',
                    '',
                ];
            }

            $pdf->coloredTable(
                $header,
                $currentInvoiceDetails,
                [40, 35, 25, 25, 25, 35],
                $invoiceMethod !== Method::METHOD_PERCENTAGE_CONTRACT
            );
        }

        switch ($invoiceMethod) {
            case Method::METHOD_PERCENTAGE_CONTRACT:
                $invoiceLines = $this->affiliationService->findInvoiceLines(
                    $affiliation,
                    $contractVersion,
                    $year,
                    $period
                );

                if (count($invoiceLines) > 0) {
                    $pdf->writeHTMLCell(
                        0,
                        0,
                        '',
                        '',
                        '<h3>' . sprintf(
                            $this->translator->translate('txt-invoice-for-year-%s-period-%s'),
                            $year,
                            $period
                        )
                        . '</h3>',
                        0,
                        1,
                        0
                    );
                    $pdf->Ln();
                }

                $header = [
                    $this->translator->translate('txt-period'),
                    $this->translator->translate('txt-information'),
                    $this->translator->translate('txt-amount'),
                ];


                $upcomingDetails = [];
                foreach ($invoiceLines as $invoiceLine) {
                    $upcomingDetails[] = [
                        $invoiceLine->periodOrdinal,
                        $invoiceLine->description,
                        $this->parseCost($invoiceLine->lineTotal) . ($invoiceLine->lineTotal < -0.1 ? ' '
                            . $this->translator->translate('txt-credit') : ''),

                    ];
                }

                $total = $this->affiliationService->parseTotalByInvoiceLines(
                    $affiliation,
                    $contractVersion,
                    $year,
                    $period
                );

                if (count($upcomingDetails) > 0) {
                    $upcomingDetails[] = [
                        '',
                        $this->translator->translate('txt-total'),
                        $this->parseCost($total) . ($total < -0.1 ? ' ' . $this->translator->translate('txt-credit')
                            : '')

                    ];

                    $pdf->coloredTable($header, $upcomingDetails, [20, 120, 45], true);
                }


                $pdf->Ln();

                //Find the invoice
                //@todo, this does not include the credit invoice!!!
                $affiliationInvoice = $this->affiliationService->findAffiliationInvoiceInYearAndPeriod(
                    $affiliation,
                    $year,
                    $period
                );

                if (null !== $affiliationInvoice) {
                    $pdf->writeHTMLCell(
                        0,
                        0,
                        '',
                        '',
                        '<h3>' . sprintf(
                            $this->translator->translate('txt-invoice-sent-in-year-%s-period-%s'),
                            $year,
                            $period
                        )
                        . '</h3>',
                        0,
                        1,
                        0
                    );
                    $pdf->Ln();

                    /** @var Invoice $invoice */
                    $invoice = $affiliationInvoice->getInvoice();

                    $years = '';
                    foreach ($affiliationInvoice->getYears() as $invoiceYear => $invoicePeriod) {
                        $years .= sprintf('%d (%s) ', $invoiceYear, implode(', ', $invoicePeriod));
                    }

                    $header = [
                        $this->translator->translate("txt-invoice"),
                        $this->translator->translate("txt-period"),
                        $this->translator->translate("txt-invoice-period"),
                        $this->translator->translate("txt-date"),
                        $this->translator->translate("txt-contribution-euro"),
                        $this->translator->translate("txt-paid"),
                        $this->translator->translate("txt-invoiced-euro") . ($this->invoiceService->hasVat($invoice)
                            ? ' (' . $this->translator->translate('txt-including-vat') . ')' : '')
                    ];

                    $upcomingDetails[] = [
                        $invoice->getInvoiceNr(),
                        $affiliationInvoice->getYear() . '-' . $affiliationInvoice->getPeriod() . 'H',
                        $years,
                        $invoice->getDateSent() !== null ? $invoice->getDateSent()->format('d-m-Y') : '',
                        $this->parseCost($this->invoiceService->parseSumAmount($invoice)),
                        $invoice->getBookingDate() !== null ? $invoice->getBookingDate()->format('d-m-Y') : '',
                        $this->parseCost($this->invoiceService->parseTotal($invoice))

                    ];


                    $pdf->coloredTable($header, $upcomingDetails, [20, 15, 35, 18, 35, 18, 45]);

                    $pdf->Ln();

                    $upcomingDetails = [];

                    $header = [sprintf($this->translator->translate('txt-invoiced-in-%s'), $invoice->getInvoiceNr()),
                               ''];

                    foreach ($invoice->getRow() as $row) {
                        $upcomingDetails[] = [
                            $row->getRow(),
                            $this->parseCost($row->getQuantity() * $row->getAmount())
                        ];
                    }

                    $upcomingDetails[] = [
                        $this->translator->translate('txt-total-excl-vat'),
                        $this->parseCost($this->invoiceService->parseSumAmount($invoice))
                    ];

                    $pdf->coloredTable(
                        $header,
                        $upcomingDetails,
                        [75, 45]
                    );
                }


                $pdf->writeHTMLCell(
                    0,
                    0,
                    '',
                    '',
                    $this->invoiceService->parseExchangeRateLine(null, $currency, $year),
                    0,
                    0,
                    0
                );


                break;
            default:
                $pdf->writeHTMLCell(
                    0,
                    0,
                    '',
                    '',
                    '<h3>' . $this->translator->translate('txt-correction-calculation') . '</h3>',
                    0,
                    1,
                    0
                );

                $balance = $this->affiliationService->parseBalance(
                    $affiliation,
                    $latestVersion,
                    $year,
                    $period
                );
                $total = $this->affiliationService->parseTotal($affiliation, $latestVersion, $year, $period);
                $contribution = $this->affiliationService->parseContribution(
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
                            $this->translator->translate('txt-total-contribution-invoiced-upto-year-%s-period-%s'),
                            $year,
                            $period
                        ),
                        $this->parseCost($contributionPaid),
                    ],
                    [
                        sprintf(
                            $this->translator->translate('txt-total-contribution-amount-due-upto-year-%s-period-%s'),
                            $year,
                            $period
                        ),
                        $this->parseCost($contributionDue),
                    ],
                    [
                        $this->translator->translate('txt-correction'),
                        $this->parseCost($balance),
                    ],
                ];

                $pdf->coloredTable([], $correctionDetails, [100, 85], true);


                $pdf->writeHTMLCell(
                    0,
                    0,
                    '',
                    '',
                    '<h3>' . sprintf($this->translator->translate('txt-invoice-for-year-%s-period-%s'), $year, $period)
                    . '</h3>',
                    0,
                    1,
                    0
                );
                $pdf->Ln();

                //Partner information
                $header = [
                    $this->translator->translate('txt-period'),
                    $this->translator->translate('txt-contribution'),
                    $this->translator->translate('txt-amount'),

                ];

                $upcomingDetails = [
                    [
                        sprintf('%s-%s', $year, $period),
                        sprintf(
                            $this->translator->translate('txt-%s-contribution-for-%s'),
                            $this->parsePercent(
                                $this->affiliationService
                                    ->parseContributionFactor($affiliation, $year, $period) * 100,
                                0
                            ),
                            $year
                        ),
                        $this->parseCost($contribution),
                    ],
                    [
                        '',
                        $this->translator->translate('txt-correction'),
                        $this->parseCost($balance),
                    ],
                    [
                        '',
                        $this->translator->translate('txt-total'),
                        $this->parseCost($total),
                    ],
                ];

                $pdf->coloredTable($header, $upcomingDetails, [25, 70, 90], true);

                break;
        }


        //$already sent invoices
        $header = [
            $this->translator->translate('txt-invoice-number'),
            $this->translator->translate('txt-period'),
            $this->translator->translate('txt-date'),
            $this->translator->translate('txt-paid'),
            $this->translator->translate('txt-total-excl-vat'),
            $this->translator->translate('txt-total'),
        ];


        //Old Invoices
        $alreadySentInvoices = [];
        foreach ($affiliation->getInvoice() as $affiliationInvoice) {
            if ($affiliationInvoice->getYear() > $year
                || ($affiliationInvoice->getYear() === $year
                    && $affiliationInvoice->getPeriod() > $period)
            ) {
                if (null !== $affiliationInvoice->getInvoice()->getDateSent()) {
                    $alreadySentInvoices[] = $affiliationInvoice;
                }
            }
        }

        $alreadySentInvoiceDetails = [];

        if (count($alreadySentInvoices) > 0) {
            $pdf->writeHTMLCell(
                0,
                0,
                '',
                '',
                '<h3>' . sprintf(
                    $this->translator->translate('txt-already-sent-invoices-after-year-%s-period-%s') . '</h3>',
                    $year,
                    $period
                ),
                0,
                1,
                0
            );
            $pdf->Ln();

            /**
             * @var $affiliationInvoice AffiliationInvoice
             */
            foreach ($alreadySentInvoices as $affiliationInvoice) {
                $alreadySentInvoiceDetails[] = [
                    $affiliationInvoice->getInvoice()->getInvoiceNr(),
                    sprintf('%s-%s', $affiliationInvoice->getYear(), $affiliationInvoice->getPeriod()),
                    $affiliationInvoice->getInvoice()->getDateSent()->format('d-m-Y'),
                    null !== $affiliationInvoice->getInvoice()->getBookingDate() ? $affiliationInvoice->getInvoice()
                        ->getBookingDate()
                        ->format('d-m-Y')
                        : '',
                    $this->parseCost($this->invoiceService->parseSumAmount($affiliationInvoice->getInvoice())),
                    $this->parseCost($this->invoiceService->parseTotal($affiliationInvoice->getInvoice())),
                ];
            }

            $pdf->coloredTable($header, $alreadySentInvoiceDetails, [45, 25, 25, 25, 25, 35], true);
        }


        return $pdf;
    }

    public function parseEffort($effort): string
    {
        return sprintf('%s %s', number_format($effort, 2), 'PY');
    }

    public function parseCost($cost, Currency $currency = null): string
    {
        $abbreviation = 'EUR';
        if (null !== $currency) {
            $abbreviation = $currency->getIso4217();
        }

        return sprintf('%s %s', number_format($cost, 2), $abbreviation);
    }

    public function parsePercent($percent, int $decimals = 2): string
    {
        return sprintf('%s %s', number_format((float)$percent, $decimals), '%');
    }

    public function parseKiloCost($cost, Currency $currency = null): string
    {
        $abbreviation = 'EUR';
        if (null !== $currency) {
            $abbreviation = $currency->getIso4217();
        }

        return sprintf('%s k%s', number_format($cost / 1000), $abbreviation);
    }
}
