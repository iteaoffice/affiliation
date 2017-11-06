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
     */
    public function __invoke(Affiliation $affiliation, int $year, int $period, bool $useContractData = true): string
    {
        $latestVersion = $this->getProjectService()->getLatestProjectVersion($affiliation->getProject());

        /**
         * We don't need a payment sheet, when we have no versions
         */
        if (is_null($latestVersion)) {
            return '';
        }

        $contractVersion = $this->getContractService()
            ->findLatestContractVersionByCountryAndProject(
                $affiliation->getOrganisation()->getCountry(),
                $affiliation->getProject()
            );

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
                        $contractVersion,
                        $latestVersion
                    ),
                'contactService'                  => $this->getContactService(),
                'financialContact'                => $this->getAffiliationService()->getFinancialContact($affiliation),
                'organisationService'             => $this->getOrganisationService(),
                'invoiceMethod'                   => $this->getInvoiceService()->findInvoiceMethod(
                    $affiliation->getProject()
                        ->getCall()->getProgram()
                ),
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
