<?php
/**
 * ITEA Office all rights reserved
 *
 * @category   Content
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 * @license    https://itea3.org/license.txt proprietary
 *
 * @link       https://itea3.org
 */

declare(strict_types=1);

namespace Affiliation\View\Helper;

use Affiliation\Entity\Affiliation;
use General\Entity\Currency;
use General\Entity\ExchangeRate;

/**
 * Class PaymentSheet
 *
 * @package Affiliation\View\Helper
 */
class PaymentSheet extends LinkAbstract
{
    /**
     * @param Affiliation $affiliation
     * @param int $year
     * @param int $period
     * @param bool $useContractData
     * @return string
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(Affiliation $affiliation, int $year, int $period, bool $useContractData = true): string
    {
        $latestVersion = $this->getProjectService()->getLatestProjectVersion($affiliation->getProject());

        /**
         * We don't need a payment sheet, when we have no versions
         */
        if (null === $latestVersion) {
            return '';
        }

        $contractVersion = $this->getContractService()->findLatestContractVersionByAffiliation($affiliation);

        //Create a default currency
        $currency = new Currency();
        $currency->setName('EUR');
        $currency->setSymbol('&euro;');

        $exchangeRate = new ExchangeRate();
        $exchangeRate->setRate(1);

        if (null !== $contractVersion && $useContractData) {
            $currency = $contractVersion->getContract()->getCurrency();
            $exchangeRate = $this->getContractService()->findExchangeRateInInvoicePeriod($currency, $year, $period);
        }

        $invoiceMethod = $affiliation->getInvoiceMethod();
        if (null === $invoiceMethod) {
            $invoiceMethod = $this->getInvoiceService()->findInvoiceMethod(
                $affiliation->getProject()
                    ->getCall()->getProgram()
            );
        }

        return $this->getRenderer()->render(
            'affiliation/partial/payment-sheet',
            [
                'year'                            => $year,
                'period'                          => $period,
                'useContractData'                 => $useContractData,
                'affiliation'                     => $affiliation,
                'project'                         => $affiliation->getProject(),
                'affiliationService'              => $this->getAffiliationService(),
                'version'                         => $latestVersion,
                'projectService'                  => $this->getProjectService(),
                'contractService'                 => $this->getContractService(),
                'contractVersion'                 => $contractVersion,
                'contractContributionInformation' => null === $contractVersion ? null
                    : $this->getContractService()->getContractVersionContributionInformation(
                        $affiliation,
                        $contractVersion
                    ),
                'exchangeRate'                    => $exchangeRate,
                'currency'                        => $currency,
                'contactService'                  => $this->getContactService(),
                'financialContact'                => $this->getAffiliationService()->getFinancialContact($affiliation),
                'organisationService'             => $this->getOrganisationService(),
                'invoiceMethod'                   => $invoiceMethod,
                'invoiceService'                  => $this->getInvoiceService(),
                'versionService'                  => $this->getVersionService(),
                'versionContributionInformation'  => $this->getVersionService()
                    ->getProjectVersionContributionInformation(
                        $affiliation,
                        $latestVersion
                    ),
            ]
        );
    }
}
