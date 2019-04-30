<?php

/**
 * ITEA Office all rights reserved
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\View\Helper;

use Affiliation\Entity\Affiliation;
use Contact\Entity\Contact;

/**
 * Create a link to an affiliation.
 *
 * @category    Affiliation
 */
class AssociateLink extends LinkAbstract
{
    public function __invoke(
        Affiliation $affiliation,
        $action = 'view',
        $show = 'name',
        Contact $contact = null
    ): string {
        $this->setAffiliation($affiliation);
        $this->setAction($action);
        $this->setShow($show);
        $this->setContact($contact);
        /*
         * Set the non-standard options needed to give an other link value
         */
        $this->setShowOptions(
            [
                'contact' => $this->getContact()->getDisplayName(),
            ]
        );

        $this->addRouterParam('id', $this->getAffiliation()->getId());
        $this->addRouterParam('contact', $this->getContact()->getId());

        $this->setRouter('zfcadmin/affiliation/edit-associate');

        return $this->createLink();
    }
}
