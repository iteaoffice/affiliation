<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */

namespace Affiliation\Controller;

use Contact\Service\ContactServiceAwareInterface;
use Invoice\Service\InvoiceServiceAwareInterface;
use Organisation\Service\OrganisationServiceAwareInterface;
use Project\Acl\Assertion\Project as ProjectAssertion;
use Project\Service\ProjectServiceAwareInterface;
use Project\Service\VersionServiceAwareInterface;
use Zend\View\Model\ViewModel;

/**
 *
 */
class AffiliationManagerController extends AffiliationAbstractController implements
    ContactServiceAwareInterface,
    ProjectServiceAwareInterface,
    VersionServiceAwareInterface,
    OrganisationServiceAwareInterface,
    InvoiceServiceAwareInterface
{
    /**
     * @return ViewModel
     */
    public function viewAction()
    {
        $affiliationService = $this->getAffiliationService()->setAffiliationId(
            $this->getEvent()->getRouteMatch()->getParam('id')
        );
        $this->getProjectService()->setProject($affiliationService->getAffiliation()->getProject());

        $this->getProjectService()->addResource(
            $affiliationService->getAffiliation()->getProject(),
            ProjectAssertion::class
        );
        $hasProjectEditRights = $this->isAllowed($affiliationService->getAffiliation()->getProject(), 'edit-community');

        return new ViewModel(
            [
                'affiliationService'    => $affiliationService,
                'contactsInAffiliation' => $this->getContactService()->findContactsInAffiliation(
                    $affiliationService->getAffiliation()
                ),
                'projectService'        => $this->getProjectService(),
                'workpackageService'    => $this->getWorkpackageService(),
                'latestVersion'         => $this->getProjectService()->getLatestProjectVersion(),
                'versionType'           => $this->getProjectService()->getNextMode()->versionType,
                'hasProjectEditRights'  => $hasProjectEditRights,
                'reportService'         => $this->getReportService(),
                'versionService'        => $this->getVersionService(),
                'invoiceService'        => $this->getInvoiceService(),
                'organisationService'   => $this->getOrganisationService()->setOrganisation($affiliationService->getAffiliation()->getOrganisation())

            ]
        );
    }
}
