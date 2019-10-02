<?php

/**
 * ITEA Office all rights reserved
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\View\Helper;

use Affiliation\Acl\Assertion\Doa as DoaAssertion;
use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Doa;

/**
 * Class DoaLink
 *
 * @package Affiliation\View\Helper
 */
class DoaLink extends LinkAbstract
{
    public function __invoke(Doa $doa = null, $action = 'view', $show = 'name', Affiliation $affiliation = null): string
    {
        $this->setDoa($doa);
        $this->setAction($action);
        $this->setShow($show);
        $this->setAffiliation($affiliation);

        if (!$this->hasAccess($this->getDoa(), DoaAssertion::class, $this->getAction())) {
            return '';
        }

        /*
         * Set the non-standard options needed to give an other link value
         */
        $this->setShowOptions(
            [
                'name' => (string)$this->getDoa(),
            ]
        );


        if (null !== $doa) {
            $this->addRouterParam('id', $this->getDoa()->getId());

            if ($doa->hasObject()) {
                $this->addRouterParam('ext', $this->getDoa()->getContentType()->getExtension());
            }
        }
        $this->addRouterParam('affiliationId', $this->getAffiliation()->getId());

        return $this->createLink();
    }

    public function setAffiliation($affiliation)
    {
        $this->affiliation = $affiliation;
        /*
         * Only overwrite the the Affiliation in the LOI when this is not is_null
         */
        if (null !== $affiliation) {
            $this->getDoa()->setAffiliation($affiliation);
        }
    }

    public function parseAction(): void
    {
        switch ($this->getAction()) {
            case 'submit':
                $this->setRouter('community/affiliation/doa/submit');
                $this->setText(
                    sprintf(
                        $this->translator->translate('txt-submit-doa-for-organisation-%s-in-project-%s-link-title'),
                        $this->getAffiliation()->parseBranchedName(),
                        $this->getAffiliation()->getProject()
                    )
                );
                break;
            case 'replace':
                $this->setRouter('community/affiliation/doa/replace');
                $this->setText(
                    sprintf(
                        $this->translator->translate('txt-replace-doa-for-organisation-%s-in-project-%s-link-title'),
                        $this->getDoa()->getAffiliation()->parseBranchedName(),
                        $this->getDoa()->getAffiliation()->getProject()
                    )
                );
                break;
            case 'download':
                $this->setRouter('community/affiliation/doa/download');
                $this->setText(
                    sprintf(
                        $this->translator->translate('txt-download-doa-for-organisation-%s-in-project-%s-link-title'),
                        $this->getDoa()->getAffiliation()->parseBranchedName(),
                        $this->getDoa()->getAffiliation()->getProject()
                    )
                );
                break;
            case 'approval-admin':
                $this->setRouter('zfcadmin/affiliation/doa/approval');
                $this->setText($this->translator->translate('txt-approval-doa'));
                break;
            case 'missing-admin':
                $this->setRouter('zfcadmin/affiliation/doa/missing');
                $this->setText($this->translator->translate('txt-missing-doa'));
                break;
            case 'remind-admin':
                $this->setRouter('zfcadmin/affiliation/doa/remind');
                $this->setText($this->translator->translate('txt-send-reminder'));
                break;
            case 'reminders-admin':
                $this->setRouter('zfcadmin/affiliation/doa/reminders');
                $this->setText(
                    sprintf(
                        $this->translator->translate('txt-see-reminders-%s-sent'),
                        $this->getAffiliation()->getDoaReminder()->count()
                    )
                );
                break;
            case 'view-admin':
                $this->setRouter('zfcadmin/affiliation/doa/view');
                $this->setText(
                    sprintf(
                        $this->translator->translate('txt-view-doa-for-organisation-%s-in-project-%s-link-title'),
                        $this->getDoa()->getAffiliation()->parseBranchedName(),
                        $this->getDoa()->getAffiliation()->getProject()
                    )
                );
                break;
            case 'edit-admin':
                $this->setRouter('zfcadmin/affiliation/doa/edit');
                $this->setText(
                    sprintf(
                        $this->translator->translate('txt-edit-doa-for-organisation-%s-in-project-%s-link-title'),
                        $this->getDoa()->getAffiliation()->parseBranchedName(),
                        $this->getDoa()->getAffiliation()->getProject()
                    )
                );
                break;
        }
    }
}
