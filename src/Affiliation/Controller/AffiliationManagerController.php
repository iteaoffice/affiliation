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
        return new ViewModel();
    }
}
