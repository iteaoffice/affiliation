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

use Affiliation\Acl\Assertion\Loi as LoiAssertion;
use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Loi;

/**
 * Create a link to an loi.
 *
 * @category    Affiliation
 */
class LoiLink extends LinkAbstract
{
    /**
     * @param Loi $loi
     * @param string $action
     * @param string $show
     * @param Affiliation $affiliation
     *
     * @return string
     */
    public function __invoke(Loi $loi = null, $action = 'view', $show = 'name', Affiliation $affiliation = null): string
    {
        $this->setLoi($loi);
        $this->setAffiliation($affiliation);
        $this->setAction($action);
        $this->setShow($show);
        /*
         * Set the non-standard options needed to give an other link value
         */
        $this->setShowOptions(
            [
                'name' => $this->getLoi(),
            ]
        );
        if (!$this->hasAccess(
            $this->getLoi(),
            LoiAssertion::class,
            $this->getAction()
        )
        ) {
            return '';
        }

        $this->addRouterParam('id', $this->getLoi()->getId());
        $this->addRouterParam('affiliationId', $this->getAffiliation()->getId());

        return $this->createLink();
    }

    /**
     * @param Affiliation $affiliation
     */
    public function setAffiliation($affiliation): void
    {
        $this->affiliation = $affiliation;
        /*
         * Only overwrite the the Affiliation in the LOI when this is not is_null
         */
        if (!\is_null($affiliation)) {
            $this->getLoi()->setAffiliation($affiliation);
        }
    }

    /**
     * Extract the relevant parameters based on the action.
     *
     * @throws \Exception
     */
    public function parseAction(): void
    {
        switch ($this->getAction()) {
            case 'submit':
                $this->setRouter('community/affiliation/loi/submit');
                $this->setText(
                    sprintf(
                        $this->translate("txt-submit-loi-for-organisation-%s-in-project-%s-link-title"),
                        $this->getAffiliation()->parseBranchedName(),
                        $this->getAffiliation()->getProject()
                    )
                );
                break;
            case 'render':
                $this->setRouter('community/affiliation/loi/render');
                $this->setText(
                    sprintf(
                        $this->translate("txt-render-loi-for-organisation-%s-in-project-%s-link-title"),
                        $this->getLoi()->getAffiliation()->parseBranchedName(),
                        $this->getLoi()->getAffiliation()->getProject()
                    )
                );
                break;
            case 'replace':
                $this->setRouter('community/affiliation/loi/replace');
                $this->setText(
                    sprintf(
                        $this->translate("txt-replace-loi-for-organisation-%s-in-project-%s-link-title"),
                        $this->getLoi()->getAffiliation()->parseBranchedName(),
                        $this->getLoi()->getAffiliation()->getProject()
                    )
                );
                break;
            case 'download':
                $this->setRouter('community/affiliation/loi/download');
                $this->setText(
                    sprintf(
                        $this->translate("txt-download-loi-for-organisation-%s-in-project-%s-link-title"),
                        $this->getLoi()->getAffiliation()->getOrganisation(),
                        $this->getLoi()->getAffiliation()->getProject()
                    )
                );
                break;
            case 'remind-admin':
                $this->setRouter('zfcadmin/affiliation/loi/remind');
                $this->setText($this->translate("txt-send-reminder"));
                break;
            case 'approval-admin':
                $this->setRouter('zfcadmin/affiliation/loi/approval');
                $this->setText($this->translate("txt-approval-loi"));
                break;
            case 'missing-admin':
                $this->setRouter('zfcadmin/affiliation/loi/missing');
                $this->setText($this->translate("txt-missing-loi"));
                break;
            case 'reminders-admin':
                $this->setRouter('zfcadmin/affiliation/loi/reminders');
                $this->setText(
                    sprintf(
                        $this->translate("txt-see-reminders-%s-sent"),
                        $this->getAffiliation()->getLoiReminder()->count()
                    )
                );
                break;
            case 'view-admin':
                $this->setRouter('zfcadmin/affiliation/loi/view');
                $this->setText(
                    sprintf(
                        $this->translate("txt-view-loi-for-organisation-%s-in-project-%s-link-title"),
                        $this->getLoi()->getAffiliation()->parseBranchedName(),
                        $this->getLoi()->getAffiliation()->getProject()
                    )
                );
                break;
            case 'edit-admin':
                $this->setRouter('zfcadmin/affiliation/loi/edit');
                $this->setText(
                    sprintf(
                        $this->translate("txt-edit-loi-for-organisation-%s-in-project-%s-link-title"),
                        $this->getLoi()->getAffiliation()->parseBranchedName(),
                        $this->getLoi()->getAffiliation()->getProject()
                    )
                );
                break;
            default:
                throw new \Exception(sprintf("%s is an incorrect action for %s", $this->getAction(), __CLASS__));
        }
    }
}
