<?php

/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */

namespace Affiliation\View\Helper;

use Affiliation\Acl\Assertion\Doa as DoaAssertion;
use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Doa;

/**
 * Create a link to an doa.
 *
 * @category    Affiliation
 */
class DoaLink extends LinkAbstract
{
    /**
     * @var Doa
     */
    protected $doa;
    /**
     * @var Affiliation
     */
    protected $affiliation;

    /**
     * @param Doa $doa
     * @param string $action
     * @param string $show
     * @param Affiliation $affiliation
     *
     * @return string
     */
    public function __invoke(Doa $doa = null, $action = 'view', $show = 'name', Affiliation $affiliation = null)
    {
        $this->setDoa($doa);
        $this->setAction($action);
        $this->setShow($show);
        $this->setAffiliation($affiliation);
        /*
         * Set the non-standard options needed to give an other link value
         */
        $this->setShowOptions(
            [
                'name' => $this->getDoa(),
            ]
        );
        if (!$this->hasAccess(
            $this->getDoa(),
            DoaAssertion::class,
            $this->getAction()
        )
        ) {
            return '';
        }
        $this->addRouterParam('entity', 'Doa');
        if (!is_null($doa)) {
            $this->addRouterParam('id', $this->getDoa()->getId());
            $this->addRouterParam('ext', $this->getDoa()->getContentType()->getExtension());
        }
        $this->addRouterParam('affiliation-id', $this->getAffiliation()->getId());

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
            case 'upload':
                $this->setRouter('community/affiliation/doa/upload');
                $this->setText(
                    sprintf(
                        $this->translate("txt-upload-doa-for-organisation-%s-in-project-%s-link-title"),
                        $this->getAffiliation()->getOrganisation(),
                        $this->getAffiliation()->getProject()
                    )
                );
                break;
            case 'render':
                $this->setRouter('community/affiliation/doa/render');
                $this->setText(
                    sprintf(
                        $this->translate("txt-render-doa-for-organisation-%s-in-project-%s-link-title"),
                        $this->getDoa()->getAffiliation()->getOrganisation(),
                        $this->getDoa()->getAffiliation()->getProject()
                    )
                );
                break;
            case 'replace':
                $this->setRouter('community/affiliation/doa/replace');
                $this->setText(
                    sprintf(
                        $this->translate("txt-replace-doa-for-organisation-%s-in-project-%s-link-title"),
                        $this->getDoa()->getAffiliation()->getOrganisation(),
                        $this->getDoa()->getAffiliation()->getProject()
                    )
                );
                break;
            case 'download':
                $this->setRouter('community/affiliation/doa/download');
                $this->setText(
                    sprintf(
                        $this->translate("txt-download-doa-for-organisation-%s-in-project-%s-link-title"),
                        $this->getDoa()->getAffiliation()->getOrganisation(),
                        $this->getDoa()->getAffiliation()->getProject()
                    )
                );
                break;
            case 'approval-admin':
                $this->setRouter('zfcadmin/affiliation-manager/doa/approval');
                $this->setText($this->translate("txt-approval-doa"));
                break;
            case 'missing-admin':
                $this->setRouter('zfcadmin/affiliation-manager/doa/missing');
                $this->setText($this->translate("txt-missing-doa"));
                break;
            case 'remind-admin':
                $this->setRouter('zfcadmin/affiliation-manager/doa/remind');
                $this->setText($this->translate("txt-send-reminder"));
                break;
            case 'reminders-admin':
                $this->setRouter('zfcadmin/affiliation-manager/doa/reminders');
                $this->setText(
                    sprintf(
                        $this->translate("txt-see-reminders-%s-sent"),
                        $this->getAffiliation()->getDoaReminder()->count()
                    )
                );
                break;
            case 'view-admin':
                $this->setRouter('zfcadmin/affiliation-manager/doa/view');
                $this->setText(
                    sprintf(
                        $this->translate("txt-view-doa-for-organisation-%s-in-project-%s-link-title"),
                        $this->getDoa()->getAffiliation()->getOrganisation(),
                        $this->getDoa()->getAffiliation()->getProject()
                    )
                );
                break;
            case 'edit-admin':
                $this->setRouter('zfcadmin/affiliation-manager/doa/edit');
                $this->setText(
                    sprintf(
                        $this->translate("txt-edit-doa-for-organisation-%s-in-project-%s-link-title"),
                        $this->getDoa()->getAffiliation()->getOrganisation(),
                        $this->getDoa()->getAffiliation()->getProject()
                    )
                );
                break;
            default:
                throw new \Exception(sprintf("%s is an incorrect action for %s", $this->getAction(), __CLASS__));
        }
    }

    /**
     * @return Doa
     */
    public function getDoa()
    {
        if (is_null($this->doa)) {
            $this->doa = new Doa();
        }

        return $this->doa;
    }

    /**
     * @param Doa $doa
     */
    public function setDoa($doa)
    {
        $this->doa = $doa;
    }

    /**
     * @return Affiliation
     */
    public function getAffiliation()
    {
        if (is_null($this->affiliation)) {
            $this->affiliation = new Affiliation();
        }

        return $this->affiliation;
    }

    /**
     * @param Affiliation $affiliation
     */
    public function setAffiliation($affiliation)
    {
        $this->affiliation = $affiliation;
        /*
         * Only overwrite the the Affiliation in the LOI when this is not is_null
         */
        if (!is_null($affiliation)) {
            $this->getDoa()->setAffiliation($affiliation);
        }
    }
}
