<?php
/**
 * ITEA Office all rights reserved
 *
 * @category   Content
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  Copyright (c) 2019 ITEA Office (https://itea3.org)
 * @license    https://itea3.org/license.txt proprietary
 *
 * @link       https://itea3.org
 */

declare(strict_types=1);

namespace Affiliation\View\Helper;

use Affiliation\Entity\Affiliation;
use Affiliation\Service\AffiliationService;
use Contact\Service\ContactService;
use General\Entity\Currency;
use General\Entity\ExchangeRate;
use Invoice\Service\InvoiceService;
use Organisation\Service\OrganisationService;
use Project\Service\ContractService;
use Project\Service\ProjectService;
use Project\Service\VersionService;
use Laminas\View\Helper\AbstractHelper;
use ZfcTwig\View\TwigRenderer;

/**
 * Class PaymentSheet
 *
 * @package Affiliation\View\Helper
 */
final class PaymentSheet extends AbstractHelper
{
    private ProjectService $projectService;
    private ContractService $contractService;
    private InvoiceService $invoiceService;
    private AffiliationService $affiliationService;
    private ContactService $contactService;
    private OrganisationService $organisationService;
    private VersionService $versionService;
    private TwigRenderer $renderer;

    public function __construct(
        ProjectService $projectService,
        ContractService $contractService,
        InvoiceService $invoiceService,
        AffiliationService $affiliationService,
        ContactService $contactService,
        OrganisationService $organisationService,
        VersionService $versionService,
        TwigRenderer $renderer
    ) {
        $this->projectService = $projectService;
        $this->contractService = $contractService;
        $this->invoiceService = $invoiceService;
        $this->affiliationService = $affiliationService;
        $this->contactService = $contactService;
        $this->organisationService = $organisationService;
        $this->versionService = $versionService;
        $this->renderer = $renderer;
    }


    public function __invoke(Affiliation $affiliation, int $year, int $period, bool $useContractData = true): string
    {
        $latestVersion = $this->projectService->getLatestNotRejectedProjectVersion($affiliation->getProject());

        /**
         * We don't need a payment sheet, when we have no versions
         */
        if (null === $latestVersion) {
            return '';
        }

        $contractVersion = $this->contractService->findLatestContractVersionByAffiliation($affiliation);

        //Create a default currency
        $currency = new Currency();
        $currency->setName('EUR');
        $currency->setSymbol('&euro;');

        $exchangeRate = new ExchangeRate();
        $exchangeRate->setRate(1);

        if (null !== $contractVersion && $useContractData) {
            $currency = $contractVersion->getContract()->getCurrency();
            $exchangeRate = $this->contractService->findExchangeRateInInvoicePeriod($currency, $year, $period);
        }

        $invoiceMethod = $affiliation->getInvoiceMethod();
        if (null === $invoiceMethod) {
            $invoiceMethod = $this->invoiceService->findInvoiceMethod(
                $affiliation->getProject()
                    ->getCall()->getProgram()
            );
        }

        return $this->renderer->render(
            'affiliation/partial/payment-sheet',
            [
                'year'                            => $year,
                'period'                          => $period,
                'useContractData'                 => $useContractData,
                'affiliation'                     => $affiliation,
                'project'                         => $affiliation->getProject(),
                'affiliationService'              => $this->affiliationService,
                'version'                         => $latestVersion,
                'projectService'                  => $this->projectService,
                'contractService'                 => $this->contractService,
                'contractVersion'                 => $contractVersion,
                'contractContributionInformation' => null === $contractVersion
                    ? null
                    : $this->contractService->getContractVersionContributionInformation(
                        $affiliation,
                        $contractVersion
                    ),
                'exchangeRate'                    => $exchangeRate,
                'currency'                        => $currency,
                'contactService'                  => $this->contactService,
                'financialContact'                => $this->affiliationService->getFinancialContact($affiliation),
                'organisationService'             => $this->organisationService,
                'invoiceMethod'                   => $invoiceMethod,
                'invoiceService'                  => $this->invoiceService,
                'versionService'                  => $this->versionService,
                'versionContributionInformation'  => $this->versionService
                    ->getProjectVersionContributionInformation(
                        $affiliation,
                        $latestVersion
                    ),
            ]
        );
    }
}
