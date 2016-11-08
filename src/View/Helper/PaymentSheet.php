<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category   Content
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  2004-2015 ITEA Office
 * @license    https://itea3.org/license.txt proprietary
 *
 * @link       https://itea3.org
 */

namespace Affiliation\View\Helper;

use Affiliation\Entity\Affiliation;

/**
 * Create a link to an document.
 *
 * @category   Affiliation
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  2004-2015 ITEA Office
 * @license    https://itea3.org/license.txt proprietary
 *
 * @link       https://itea3.org
 */
class PaymentSheet extends LinkAbstract
{
    /**
     * @param Affiliation $affiliation
     * @param             $year
     * @param             $period
     *
     * @return null|string
     * @throws \Exception
     */
    public function __invoke(Affiliation $affiliation, $year, $period)
    {
        $latestVersion = $this->getProjectService()->getLatestProjectVersion($affiliation->getProject());

        /**
         * We don't need a payment sheet, when we have no versions
         */
        if (is_null($latestVersion)) {
            return '';
        }

        return $this->getRenderer()->render(
            'affiliation/partial/payment-sheet',
            [
            'year'                           => $year,
            'period'                         => $period,
            'affiliation'                    => $affiliation,
            'project'                        => $affiliation->getProject(),
            'affiliationService'             => $this->getAffiliationService(),
            'version'                        => $latestVersion,
            'projectService'                 => $this->getProjectService(),
            'contactService'                 => $this->getContactService(),
            'financialContact'               => $this->getAffiliationService()->getFinancialContact($affiliation),
            'organisationService'            => $this->getOrganisationService(),
            'invoiceMethod'                  => $this->getInvoiceService()->findInvoiceMethod(
                $affiliation->getProject()
                    ->getCall()->getProgram()
            ),
            'invoiceService'                 => $this->getInvoiceService(),
            'versionService'                 => $this->getVersionService(),
            'versionContributionInformation' => $this->getVersionService()
                ->getProjectVersionContributionInformation($affiliation, $latestVersion),
            ]
        );
    }
}
