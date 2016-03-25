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
use Affiliation\Service\AffiliationService;
use Project\Service\ProjectService;
use ZfcTwig\View\TwigRenderer;

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
        /** @var AffiliationService $affiliationService */
        $affiliationService = $this->getAffiliationService()->setAffiliation($affiliation);

        /** @var ProjectService $projectService */
        $projectService = $this->getProjectService()->setProject($affiliationService->getAffiliation()->getProject());

        $latestVersion = $projectService->getLatestProjectVersion();

        /**
         * We don't need a payment sheet, when we have no versions
         */
        if (is_null($latestVersion)) {
            return '';
        }

        $versionService = $this->getVersionService()->setVersion($latestVersion);

        $contactService = $this->getContactService()->setContact($affiliationService->getAffiliation()->getContact());

        if (!is_null($affiliationService->getFinancialContact($affiliationService->getAffiliation()))) {
            $financialContactService = $this->getContactService()
                ->setContact($affiliationService->getFinancialContact($affiliationService->getAffiliation()));
        } else {
            $financialContactService = null;
        }

        return $this->getZfcTwigRenderer()->render('affiliation/partial/payment-sheet', [
            'year'                           => $year,
            'period'                         => $period,
            'affiliationService'             => $affiliationService,
            'projectService'                 => $projectService,
            'contactService'                 => $contactService,
            'financialContactService'        => $financialContactService,
            'organisationService'            => $this->getOrganisationService(),
            'invoiceMethod'                  => $this->getInvoiceService()
                ->findInvoiceMethod($projectService->getProject()->getCall()->getProgram()),
            'invoiceService'                 => clone $this->getInvoiceService(),
            'versionService'                 => $versionService,
            'versionContributionInformation' => $versionService->getProjectVersionContributionInformation(
                $affiliationService->getAffiliation(),
                $latestVersion,
                $year
            )
        ]);
    }

    /**
     * @return TwigRenderer
     */
    public function getZfcTwigRenderer()
    {
        return $this->getServiceLocator()->get('ZfcTwigRenderer');
    }
}
