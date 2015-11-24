<?php

/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (https://itea3.org)
 */

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
    /**
     * @param Affiliation $affiliation
     * @param string      $action
     * @param string      $show
     * @param Contact     $contact
     *
     * @return string
     *
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function __invoke(
        Affiliation $affiliation,
        $action = 'view',
        $show = 'name',
        Contact $contact = null
    ) {
        $this->setAffiliation($affiliation);
        $this->setAction($action);
        $this->setShow($show);
        $this->setContact($contact);
        /*
         * Set the non-standard options needed to give an other link value
         */
        $this->setShowOptions([
            'contact' => $this->getContact()->getDisplayName(),
        ]);

        $this->addRouterParam('affiliation', $this->getAffiliation()->getId());
        $this->addRouterParam('contact', $this->getContact()->getId());

        return $this->createLink();
    }

    /**
     * Extract the relevant parameters based on the action.
     *
     * @throws \Exception
     */
    public function parseAction()
    {
        switch ($this->getAction()) {
            case 'edit':
                $this->setRouter('zfcadmin/affiliation/edit-associate');
                break;
            default:
                throw new \Exception(sprintf(
                    "%s is an incorrect action for %s",
                    $this->getAction(),
                    __CLASS__
                ));
        }
    }
}