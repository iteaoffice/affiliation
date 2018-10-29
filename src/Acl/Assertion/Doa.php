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

namespace Affiliation\Acl\Assertion;

use Admin\Entity\Access;
use Affiliation\Entity\Doa as DoaEntity;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\DoaService;
use Interop\Container\ContainerInterface;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

/**
 * Class Doa
 *
 * @package Affiliation\Acl\Assertion
 */
final class Doa extends AbstractAssertion
{
    /**
     * @var DoaService
     */
    private $doaService;
    /**
     * @var AffiliationService
     */
    private $affiliationService;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->doaService = $container->get(DoaService::class);
        $this->affiliationService = $container->get(AffiliationService::class);
    }

    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $doa = null, $privilege = null): bool
    {
        $this->setPrivilege($privilege);
        $id = $this->getId();

        if (!$doa instanceof DoaEntity && null !== $id) {
            /** @var DoaEntity $doa */
            $doa = $this->doaService->findDoaById((int)$id);
        }

        switch ($this->getPrivilege()) {
            case 'upload':
                /*
                 * For the upload we need to see if the user has access on the editing of the affiliation
                 * The affiliation can already be known, but if not grab it from the routeMatch
                 */
                $affiliation = null;
                if ($doa instanceof DoaEntity) {
                    $affiliation = $doa->getAffiliation();
                }
                if (null === $affiliation) {
                    /*
                     * The id can originate from two different params
                     */
                    if (null !== $this->getRouteMatch()->getParam('id')) {
                        $affiliationId = $this->getRouteMatch()->getParam('id');
                    } else {
                        $affiliationId = $this->getRouteMatch()->getParam('affiliationId');
                    }
                    $affiliation = $this->affiliationService->findAffiliationById((int)$affiliationId);
                }

                return $this->contactService->contactHasPermit($this->contact, 'edit', $affiliation);
            case 'replace':
                /*
                 * For the replace we need to see if the user has access on the editing of the affiliation
                 * and the acl should not be approved
                 */

                return null === $doa->getDateApproved()
                    && $this->contactService->contactHasPermit($this->contact, 'edit', $doa->getAffiliation());
            case 'render':
                /*
                 * For the upload we need to see if the user has access on the editing of the affiliation
                 */
                if (null !== $this->getRouteMatch()->getParam('id')) {
                    $affiliationId = $this->getRouteMatch()->getParam('id');
                } else {
                    $affiliationId = $this->getRouteMatch()->getParam('affiliationId');
                }
                $affiliation = $this->doaService->findAffiliationById((int)$affiliationId);

                return $this->contactService->contactHasPermit($this->contact, 'view', $affiliation);
            case 'download':
                return $this->contactService->contactHasPermit($this->contact, 'edit', $doa->getAffiliation());
            case 'view-admin':
            case 'edit-admin':
            case 'list-admin':
            case 'missing-admin':
            case 'remind-admin':
            case 'reminders-admin':
            case 'approval-admin':
                return $this->rolesHaveAccess([Access::ACCESS_OFFICE]);
        }

        return false;
    }
}
