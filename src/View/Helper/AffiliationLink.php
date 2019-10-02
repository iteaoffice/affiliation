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

use Affiliation\Acl\Assertion\Affiliation as AffiliationAssertion;
use Affiliation\Entity\Affiliation;

/**
 * Class AffiliationLink
 *
 * @package Affiliation\View\Helper
 */
final class AffiliationLink extends LinkAbstract
{
    public function __invoke(
        Affiliation $affiliation,
        $action = 'view',
        $show = 'organisation-branch',
        $year = null,
        $period = null,
        $fragment = null
    ): string {
        $this->classes = [];
        $this->routerParams = [];

        $this->setAffiliation($affiliation);
        $this->setAction($action);
        $this->setShow($show);
        $this->setYear($year);
        $this->setPeriod($period);
        $this->setFragment($fragment);
        /*
         * Set the non-standard options needed to give an other link value
         */
        $this->setShowOptions(
            [
                'name'                => $this->getAffiliation(),
                'organisation'        => $this->getAffiliation()->getOrganisation()->getOrganisation(),
                'parent-organisation' =>
                    null === $this->getAffiliation()->getParentOrganisation()
                        ? 'Parent not known'
                        : $this->getAffiliation()->getParentOrganisation()->getOrganisation(),
                'organisation-branch' => $this->getAffiliation()->parseBranchedName(),
            ]
        );

        if (!$this->hasAccess($this->getAffiliation(), AffiliationAssertion::class, $this->getAction())) {
            return $this->getAction() !== 'view-community' ? ''
                : $this->getAffiliation()->getOrganisation()->getOrganisation();
        }

        $this->addRouterParam('year', $this->getYear());
        $this->addRouterParam('period', $this->getPeriod());
        $this->addRouterParam('id', $this->getAffiliation()->getId());

        return $this->createLink();
    }

    public function parseAction(): void
    {
        switch ($this->getAction()) {
            case 'view-community':
                $this->setRouter('community/affiliation/affiliation');
                $this->setText(sprintf($this->translator->translate('txt-view-affiliation-%s'), $this->getAffiliation()));
                break;
            case 'edit-community':
                $this->setRouter('community/affiliation/edit/affiliation');
                $this->setText(sprintf($this->translator->translate('txt-edit-affiliation-%s'), $this->getAffiliation()));
                break;
            case 'edit-financial':
                $this->setRouter('community/affiliation/edit/financial');
                $this->setText(sprintf($this->translator->translate('txt-edit-financial-affiliation-%s'), $this->getAffiliation()));
                break;
            case 'add-associate':
                $this->setRouter('community/affiliation/edit/add-associate');
                $this->setText(sprintf($this->translator->translate('txt-add-associate')));
                break;
            case 'manage-associate':
                $this->setRouter('community/affiliation/edit/manage-associate');
                $this->setText(sprintf($this->translator->translate('txt-manage-associates')));
                break;
            case 'edit-cost-and-effort':
                $this->setRouter('community/affiliation/edit/cost-and-effort');
                $this->setText(sprintf($this->translator->translate('txt-edit-cost-and-effort-of-%s'), $this->getAffiliation()));
                break;
            case 'add-associate-admin':
                $this->setRouter('zfcadmin/affiliation/add-associate');
                $this->setText(sprintf($this->translator->translate('txt-add-associate')));
                break;
            case 'edit-description':
                $this->setRouter('community/affiliation/edit/description');
                $this->setText(
                    sprintf(
                        $this->translator->translate('txt-edit-description-affiliation-%s'),
                        $this->getAffiliation()
                    )
                );
                break;
            case 'view-admin':
                $this->setRouter('zfcadmin/affiliation/view');
                $this->setText(sprintf($this->translator->translate('txt-view-affiliation-in-admin-%s'), $this->getAffiliation()));
                break;
            case 'edit-admin':
                $this->setRouter('zfcadmin/affiliation/edit');
                $this->setText(sprintf($this->translator->translate('txt-edit-affiliation-in-admin-%s'), $this->getAffiliation()));
                break;
            case 'merge-admin':
                $this->setRouter('zfcadmin/affiliation/merge');
                $this->setText(sprintf($this->translator->translate('txt-merge-affiliation-in-admin-%s'), $this->getAffiliation()));
                break;
            case 'payment-sheet':
                $this->setRouter('community/affiliation/payment-sheet');
                $this->setText(
                    sprintf(
                        $this->translator->translate('txt-show-payment-sheet-of-affiliation-%s-for-%s-%s'),
                        $this->getAffiliation(),
                        $this->getYear(),
                        $this->getPeriod()
                    )
                );
                break;
            case 'payment-sheet-contract':
                $this->setRouter('community/affiliation/payment-sheet');
                $this->addRouterParam('contract', 'contract');
                $this->setText(
                    sprintf(
                        $this->translator->translate(
                            'txt-show-contract-based-payment-sheet-of-affiliation-%s-for-%s-%s'
                        ),
                        $this->getAffiliation(),
                        $this->getYear(),
                        $this->getPeriod()
                    )
                );
                break;
            case 'payment-sheet-pdf':
                $this->setRouter('community/affiliation/payment-sheet-pdf');
                $this->setText(
                    sprintf(
                        $this->translator->translate('txt-download-payment-sheet-of-affiliation-%s-for-%s-%s'),
                        $this->getAffiliation(),
                        $this->getYear(),
                        $this->getPeriod()
                    )
                );
                break;
            case 'payment-sheet-pdf-contract':
                $this->setRouter('community/affiliation/payment-sheet-pdf');
                $this->addRouterParam('contract', 'contract');
                $this->setText(
                    sprintf(
                        $this->translator->translate('txt-download-contract-payment-sheet-of-affiliation-%s-for-%s-%s'),
                        $this->getAffiliation(),
                        $this->getYear(),
                        $this->getPeriod()
                    )
                );
                break;
            default:
                throw new \Exception(sprintf('%s is an incorrect action for %s', $this->getAction(), __CLASS__));
        }
    }
}
