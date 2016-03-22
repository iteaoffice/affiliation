<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Controller;

use Zend\View\Model\ViewModel;

/**
 *
 */
class AffiliationController extends AffiliationAbstractController
{
    /**
     * @return ViewModel
     */
    public function viewAction()
    {
        return new ViewModel();
    }
}
