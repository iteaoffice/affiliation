<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */

namespace Affiliation\Service;

interface FormServiceAwareInterface
{
    /**
     * Get formService.
     *
     * @return FormService
     */
    public function getFormService();

    /**
     * Set formService.
     *
     * @param FormService $formService
     *
     * @return \Affiliation\Controller\AffiliationAbstractController
     */
    public function setFormService($formService);
}
