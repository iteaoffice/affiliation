<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Service;

use Admin\Service\AdminService;
use Admin\Service\AdminServiceAwareInterface;
use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Doa;
use Affiliation\Entity\EntityAbstract;
use Affiliation\Entity\Loi;
use BjyAuthorize\Service\Authorize;
use Invoice\Service\InvoiceService;
use Organisation\Service\OrganisationService;
use Organisation\Service\OrganisationServiceAwareInterface;
use Project\Service\ProjectService;
use Project\Service\VersionService;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * ServiceAbstract.
 */
abstract class ServiceAbstract implements
    AdminServiceAwareInterface,
    OrganisationServiceAwareInterface,
    ServiceLocatorAwareInterface,
    ServiceInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;
    /**
     * @var AdminService;
     */
    protected $adminService;
    /**
     * @var OrganisationService;
     */
    protected $organisationService;
    /**
     * @var ProjectService;
     */
    protected $projectService;
    /**
     * @var VersionService;
     */
    protected $versionService;
    /**
     * @var AuthenticationService;
     */
    protected $authenticationService;
    /**
     * @var InvoiceService
     */
    protected $invoiceService;
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @param      $entity
     * @param bool $toArray
     *
     * @return array
     */
    public function findAll($entity, $toArray = false)
    {
        return $this->getEntityManager()->getRepository($this->getFullEntityName($entity))->findAll();
    }

    /**
     * @param string $entity
     * @param $id
     *
     * @return null|Affiliation|Doa|Loi
     */
    public function findEntityById($entity, $id)
    {
        return $this->getEntityManager()->getRepository($this->getFullEntityName($entity))->find($id);
    }

    /**
     * @param \Affiliation\Entity\EntityAbstract $entity
     *
     * @return \Affiliation\Entity\EntityAbstract
     */
    public function newEntity(EntityAbstract $entity)
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        /*
         * Update the permissions
         */
        $this->getAdminService()->flushPermitsByEntityAndId(
            $entity->get('underscore_full_entity_name'),
            $entity->getId()
        );

        return $entity;
    }

    /**
     * @param \Affiliation\Entity\EntityAbstract $entity
     *
     * @return \Affiliation\Entity\EntityAbstract
     */
    public function updateEntity(EntityAbstract $entity)
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        /*
         * Update the permissions
         */
        $this->getAdminService()->flushPermitsByEntityAndId(
            $entity->get('underscore_full_entity_name'),
            $entity->getId()
        );

        //When an an invite is updated, we need to flush the permissions for the project. Later we will use
        //The dependencies for this, but for now we can use this trick
        if ($entity->get('underscore_full_entity_name') === 'affiliation_entity_affiliation') {
            $this->getAdminService()->flushPermitsByEntityAndId(
                'project_entity_project',
                $entity->getProject()->getId()
            );
        }

        return $entity;
    }

    /**
     * @param \Affiliation\Entity\EntityAbstract $entity
     *
     * @return bool
     */
    public function removeEntity(EntityAbstract $entity)
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * Build dynamically a entity based on the full entity name.
     *
     * @param $entity
     *
     * @return mixed
     */
    public function getEntity($entity)
    {
        $entity = $this->getFullEntityName($entity);

        return new $entity();
    }

    /**
     * Create a full path to the entity for Doctrine.
     *
     * @param $entity
     *
     * @return string
     */
    public function getFullEntityName($entity)
    {
        /*
         * Convert a - to a camelCased situation
         */
        if (strpos($entity, '-') !== false) {
            $entity = explode('-', $entity);
            $entity = $entity[0] . ucfirst($entity[1]);
        }

        return ucfirst(implode('', array_slice(explode('\\', __NAMESPACE__), 0, 1))) . '\\' . 'Entity' . '\\' . ucfirst(
            $entity
        );
    }

    /**
     * @return Authorize
     */
    public function getAuthorizeService()
    {
        return $this->getServiceLocator()->get('BjyAuthorize\Service\Authorize');
    }

    /**
     * @param EntityAbstract $entity
     * @param                $assertion
     */
    public function addResource(EntityAbstract $entity, $assertion)
    {
        /*
         * @var AssertionAbstract
         */
        $assertion = $this->getServiceLocator()->get($assertion);
        if (!$this->getAuthorizeService()->getAcl()->hasResource($entity)
        ) {
            $this->getAuthorizeService()->getAcl()->addResource($entity);
            $this->getAuthorizeService()->getAcl()->allow(
                [],
                $entity,
                [],
                $assertion
            );
        }
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ServiceAbstract
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * @return \Zend\ServiceManager\ServiceManager
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        if (null === $this->entityManager) {
            $this->setEntityManager($this->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
        }

        return $this->entityManager;
    }

    /**
     * @return AdminService
     */
    public function getAdminService()
    {
        return $this->adminService;
    }

    /**
     * @param AdminService $adminService
     *
     * @return ServiceAbstract
     */
    public function setAdminService(AdminService $adminService)
    {
        $this->adminService = $adminService;

        return $this;
    }

    /**
     * @return ProjectService
     */
    public function getProjectService()
    {
        return $this->serviceLocator->get(ProjectService::class);
    }

    /**
     * @param  ProjectService  $projectService
     * @return ServiceAbstract
     */
    public function setProjectService(ProjectService $projectService)
    {
        $this->projectService = $projectService;

        return $this;
    }

    /**
     * @return OrganisationService
     */
    public function getOrganisationService()
    {
        return $this->organisationService;
    }

    /**
     * @param  OrganisationService $organisationService
     * @return ServiceAbstract
     */
    public function setOrganisationService(OrganisationService $organisationService)
    {
        $this->organisationService = $organisationService;

        return $this;
    }

    /**
     * @return InvoiceService
     */
    public function getInvoiceService()
    {
        return $this->serviceLocator->get(InvoiceService::class);
    }

    /**
     * @return VersionService
     */
    public function getVersionService()
    {
        return $this->serviceLocator->get(VersionService::class);
    }
}
