<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Acl\Assertion;

use Admin\Entity\Access;
use Affiliation\Entity\Doa as DoaEntity;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\DoaService;
use Interop\Container\ContainerInterface;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;

/**
 * Class DoaAssertion
 * @package Affiliation\Acl\Assertion
 */
final class DoaAssertion extends AbstractAssertion
{
    private DoaService $doaService;
    private AffiliationService $affiliationService;
    private AffiliationAssertion $affiliationAssertion;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->doaService           = $container->get(DoaService::class);
        $this->affiliationService   = $container->get(AffiliationService::class);
        $this->affiliationAssertion = $container->get(AffiliationAssertion::class);
    }

    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $doa = null, $privilege = null): bool
    {
        $this->setPrivilege($privilege);
        $id = $this->getId();

        if (! $doa instanceof DoaEntity && null !== $id) {
            /** @var DoaEntity $doa */
            $doa = $this->doaService->findDoaById((int)$id);
        }

        switch ($this->getPrivilege()) {
            case 'submit':
                /*
                 * For the upload we need to see if the user has access on the editing of the affiliation
                 * The affiliation can already be known, but if not grab it from the routeMatch
                 */
                $affiliation = null;
                if ($doa instanceof DoaEntity) {
                    $affiliation = $doa->getAffiliation();
                }
                if (null === $affiliation) {
                    $affiliationId = $this->getRouteMatch()->getParam('affiliationId');
                    $affiliation   = $this->affiliationService->findAffiliationById((int)$affiliationId);
                }
                return $this->affiliationAssertion->assert($acl, $role, $affiliation, 'edit-community');
            case 'replace':
                /*
                 * For the replace we need to see if the user has access on the editing of the affiliation
                 * and the acl should not be approved
                 */

                return null === $doa->getDateApproved()
                    && $this->affiliationAssertion->assert($acl, $role, $doa->getAffiliation(), 'edit-community');
            case 'download':
                if (! $doa->hasObject()) {
                    return false;
                }
                return $this->affiliationAssertion->assert($acl, $role, $doa->getAffiliation(), 'edit-community');
            case 'view-admin':
            case 'edit-admin':
            case 'missing-admin':
            case 'remind-admin':
            case 'reminders-admin':
            case 'approval-admin':
                return $this->rolesHaveAccess([Access::ACCESS_OFFICE]);
        }

        return false;
    }
}
