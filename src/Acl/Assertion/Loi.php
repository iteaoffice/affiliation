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
use Affiliation\Entity\Loi as LoiEntity;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\LoiService;
use Interop\Container\ContainerInterface;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

/**
 * Class Loi
 *
 * @package Affiliation\Acl\Assertion
 */
final class Loi extends AbstractAssertion
{
    /**
     * @var LoiService
     */
    private $loiService;
    /**
     * @var AffiliationService
     */
    private $affiliationService;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->loiService = $container->get(LoiService::class);
        $this->affiliationService = $container->get(AffiliationService::class);
    }

    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $loi = null, $privilege = null): bool
    {
        $this->setPrivilege($privilege);
        $id = $this->getId();

        if (!$loi instanceof LoiEntity && null !== $id) {
            $loi = $this->loiService->findLoiById((int)$id);
        }

        switch ($this->getPrivilege()) {
            case 'submit':
                if (null === $id) {
                    $id = $this->getRouteMatch()->getParam('affiliationId');
                }
                /*
                 * For the upload we need to see if the user has access on the editing of the affiliation
                 */
                $affiliation = $this->affiliationService->findAffiliationById((int)$id);

                return $this->contactService->contactHasPermit($this->contact, 'edit', $affiliation);
            case 'render':
                /*
                 * For the upload we need to see if the user has access on the editing of the affiliation
                 * The affiliation can already be known, but if not grab it from the getRouteMatch
                 */
                $affiliation = null;
                if ($loi instanceof LoiEntity) {
                    $affiliation = $loi->getAffiliation();
                }
                if (null === $affiliation) {
                    if (null === $id) {
                        $id = $this->getRouteMatch()->getParam('affiliationId');
                    }
                    /*
                     * For the upload we need to see if the user has access on the editing of the affiliation
                     */
                    $affiliation = $this->affiliationService->findAffiliationById((int)$id);
                }

                return $this->contactService->contactHasPermit($this->contact, 'view', $affiliation);
            case 'replace':
                if ($this->rolesHaveAccess([Access::ACCESS_OFFICE])) {
                    return true;
                }

                /*
                 * For the replace we need to see if the user has access on the editing of the affiliation
                 * and the acl should not be approved
                 */

                return null === $loi->getDateApproved()
                    && $this->contactService->contactHasPermit($this->contact, 'edit', $loi->getAffiliation());
            case 'download':
                return $this->contactService->contactHasPermit($this->contact, 'view', $loi->getAffiliation());
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
