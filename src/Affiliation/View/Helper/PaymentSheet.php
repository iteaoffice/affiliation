<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category   Content
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  2004-2014 ITEA Office
 * @license    http://debranova.org/license.txt proprietary
 *
 * @link       http://debranova.org
 */

namespace Affiliation\View\Helper;

use Affiliation\Entity\Affiliation;
use ZfcTwig\View\TwigRenderer;

/**
 * Create a link to an document.
 *
 * @category   Affiliation
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  2004-2014 ITEA Office
 * @license    http://debranova.org/license.txt proprietary
 *
 * @link       http://debranova.org
 */
class PaymentSheet extends LinkAbstract
{
    /**
     * @param Affiliation $affiliation
     * @param $year
     * @param $period
     * @return null|string
     * @throws \Exception
     */
    public function __invoke(Affiliation $affiliation, $year, $period, $forEmail = false)
    {
        $affiliationService = $this->getAffiliationService()->setAffiliation($affiliation);

        $projectService = $this->getProjectService()->setProject($affiliationService->getAffiliation()->getProject());

        $latestVersion = $projectService->getLatestProjectVersion();
        $versionService = $this->getVersionService()->setVersion($latestVersion);

        $contactService = $this->getContactService()->setContact($affiliationService->getAffiliation()->getContact());
        $financialContactService = $this->getContactService()->setContact($affiliationService->getAffiliation()->getFinancial()->getContact());

        if ($forEmail) {
            $template = 'affiliation/partial/payment-sheet-email';
        } else {
            $template = 'affiliation/partial/payment-sheet';
        }

        return $this->getZfcTwigRenderer()->render(
            $template,
            [
                'year'                           => $year,
                'period'                         => $period,
                'affiliationService'             => $affiliationService,
                'projectService'                 => $projectService,
                'contactService'                 => $contactService,
                'financialContactService'        => $financialContactService,
                'organisationService'            => $this->getOrganisationService(),
                'invoiceMethod'                  => $this->getInvoiceService()->findInvoiceMethod($projectService->getProject()->getCall()->getProgram()),
                'invoiceService'                 => $this->getInvoiceService(),
                'versionService'                 => $versionService,
                'versionContributionInformation' => $versionService->getProjectVersionContributionInformation(
                    $affiliationService->getAffiliation(),
                    $latestVersion,
                    $year
                )
            ]
        );
    }

    /**
     * @return TwigRenderer
     */
    public function getZfcTwigRenderer()
    {
        return $this->getServiceLocator()->get('ZfcTwigRenderer');
    }
}
