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

namespace Affiliation\Service;

use Admin\Service\AdminService;
use Affiliation\Entity\Affiliation;
use Affiliation\Entity\EntityAbstract;
use BjyAuthorize\Service\Authorize;
use Contact\Service\ContactService;
use Deeplink\Service\DeeplinkService;
use General\Service\EmailService;
use General\Service\GeneralService;
use Interop\Container\ContainerInterface;
use Invoice\Service\InvoiceService;
use Organisation\Service\OrganisationService;
use Organisation\Service\ParentService;
use Project\Entity\Project;
use Project\Service\ContractService;
use Project\Service\ProjectService;
use Project\Service\VersionService;
use Zend\I18n\View\Helper\Translate;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * ServiceAbstract.
 */
abstract class ServiceAbstract implements ServiceInterface
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
     * @var Authorize
     */
    protected $authorizeService;
    /**
     * @var OrganisationService;
     */
    protected $organisationService;
    /**
     * @var DeeplinkService
     */
    protected $deeplinkService;
    /**
     * @var ProjectService;
     */
    protected $projectService;
    /**
     * @var ContactService
     */
    protected $contactService;
    /**
     * @var EmailService
     */
    protected $emailService;
    /**
     * @var ContractService;
     */
    protected $contractService;
    /**
     * @var VersionService;
     */
    protected $versionService;
    /**
     * @var InvoiceService
     */
    protected $invoiceService;
    /**
     * @var GeneralService
     */
    protected $generalService;
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;
    /**
     * @var Affiliation
     */
    protected $affiliation;
    /**
     * @var ParentService
     */
    protected $parentService;


    /**
     * @param      $entity
     * @param bool $toArray
     *
     * @return array
     */
    public function findAll($entity, $toArray = false)
    {
        return $this->getEntityManager()->getRepository($entity)->findAll();
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager(): \Doctrine\ORM\EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     *
     * @return ServiceAbstract
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    /**
     * @param string $entity
     * @param        $id
     *
     * @return null|EntityAbstract|object
     */
    public function findEntityById($entity, $id)
    {
        return $this->getEntityManager()->getRepository($entity)->find($id);
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
        $this->getAdminService()->flushPermitsByEntityAndId($entity, (int)$entity->getId());

        return $entity;
    }

    /**
     * @return AdminService
     */
    public function getAdminService(): AdminService
    {
        return $this->adminService;
    }

    /**
     * @param AdminService $adminService
     *
     * @return ServiceAbstract
     */
    public function setAdminService($adminService): ServiceAbstract
    {
        $this->adminService = $adminService;

        return $this;
    }

    /**
     * @param EntityAbstract $entity
     *
     * @return EntityAbstract
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateEntity(EntityAbstract $entity)
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        /*
         * Update the permissions
         */
        $this->getAdminService()->flushPermitsByEntityAndId($entity, $entity->getId());

        //When an an invite is updated, we need to flush the permissions for the project. Later we will use
        //The dependencies for this, but for now we can use this trick
        if ($entity instanceof Affiliation) {
            $this->getAdminService()->flushPermitsByEntityAndId(Project::class, $entity->getProject()->getId());
        }

        return $entity;
    }

    /**
     * @param EntityAbstract $entity
     *
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function removeEntity(EntityAbstract $entity): bool
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @param EntityAbstract $entity
     * @param string         $assertion
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function addResource(EntityAbstract $entity, string $assertion): void
    {
        /*
         * @var AssertionAbstract
         */
        $assertion = $this->getServiceLocator()->get($assertion);
        if (!$this->getAuthorizeService()->getAcl()->hasResource($entity)) {
            $this->getAuthorizeService()->getAcl()->addResource($entity);
            $this->getAuthorizeService()->getAcl()->allow([], $entity, [], $assertion);
        }
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator(): ContainerInterface
    {
        return $this->serviceLocator;
    }

    /**
     * @param ServiceLocatorInterface|ContainerInterface $serviceLocator
     *
     * @return ServiceAbstract
     */
    public function setServiceLocator($serviceLocator): ServiceAbstract
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * @return Authorize
     */
    public function getAuthorizeService(): Authorize
    {
        return $this->authorizeService;
    }

    /**
     * @param Authorize $authorizeService
     *
     * @return ServiceAbstract
     */
    public function setAuthorizeService($authorizeService): ServiceAbstract
    {
        $this->authorizeService = $authorizeService;

        return $this;
    }

    /**
     * @return OrganisationService
     */
    public function getOrganisationService(): OrganisationService
    {
        return $this->organisationService;
    }

    /**
     * @param OrganisationService $organisationService
     *
     * @return ServiceAbstract
     */
    public function setOrganisationService($organisationService): ServiceAbstract
    {
        $this->organisationService = $organisationService;

        return $this;
    }

    /**
     * @return ProjectService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getProjectService(): ProjectService
    {
        if (\is_null($this->projectService)) {
            $this->projectService = $this->getServiceLocator()->get(ProjectService::class);
        }

        return $this->projectService;
    }

    /**
     * @param ProjectService $projectService
     *
     * @return ServiceAbstract
     */
    public function setProjectService($projectService): ServiceAbstract
    {
        $this->projectService = $projectService;

        return $this;
    }

    /**
     * @return ContactService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getContactService(): ContactService
    {
        if (\is_null($this->contactService)) {
            $this->contactService = $this->getServiceLocator()->get(ContactService::class);
        }

        return $this->contactService;
    }

    /**
     * @param ContactService $contactService
     *
     * @return ServiceAbstract
     */
    public function setContactService($contactService): ServiceAbstract
    {
        $this->contactService = $contactService;

        return $this;
    }

    public function getEmailService(): EmailService
    {
        return $this->emailService;
    }

    /**
     * @param EmailService $emailService
     *
     * @return ServiceAbstract
     */
    public function setEmailService($emailService): ServiceAbstract
    {
        $this->emailService = $emailService;

        return $this;
    }

    /**
     * @return DeeplinkService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getDeeplinkService(): DeeplinkService
    {
        if (\is_null($this->deeplinkService)) {
            $this->deeplinkService = $this->getServiceLocator()->get(DeeplinkService::class);
        }

        return $this->deeplinkService;
    }

    /**
     * @param DeeplinkService $deeplinkService
     *
     * @return ServiceAbstract
     */
    public function setDeeplinkService($deeplinkService): ServiceAbstract
    {
        $this->deeplinkService = $deeplinkService;

        return $this;
    }

    /**
     * @return VersionService
     */
    public function getVersionService(): VersionService
    {
        return $this->versionService;
    }

    /**
     * @param VersionService $versionService
     *
     * @return ServiceAbstract
     */
    public function setVersionService($versionService): ServiceAbstract
    {
        $this->versionService = $versionService;

        return $this;
    }

    /**
     * @return InvoiceService
     */
    public function getInvoiceService(): InvoiceService
    {
        return $this->invoiceService;
    }

    /**
     * @param InvoiceService $invoiceService
     *
     * @return ServiceAbstract
     */
    public function setInvoiceService($invoiceService): ServiceAbstract
    {
        $this->invoiceService = $invoiceService;

        return $this;
    }

    /**
     * @return ParentService
     */
    public function getParentService(): ParentService
    {
        return $this->parentService;
    }

    /**
     * @param ParentService $parentService
     *
     * @return ServiceAbstract
     */
    public function setParentService(ParentService $parentService): ServiceAbstract
    {
        $this->parentService = $parentService;

        return $this;
    }

    /**
     * @return GeneralService
     */
    public function getGeneralService(): GeneralService
    {
        return $this->generalService;
    }

    /**
     * @param GeneralService $generalService
     *
     * @return ServiceAbstract
     */
    public function setGeneralService(GeneralService $generalService): ServiceAbstract
    {
        $this->generalService = $generalService;

        return $this;
    }

    /**
     * @return ContractService
     */
    public function getContractService(): ?ContractService
    {
        return $this->contractService;
    }

    /**
     * @param ContractService $contractService
     *
     * @return ServiceAbstract
     */
    public function setContractService(ContractService $contractService): ServiceAbstract
    {
        $this->contractService = $contractService;

        return $this;
    }

    /**
     * @param $string
     *
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function translate($string): string
    {
        /**
         * @var $translate Translate
         */
        $translate = $this->getServiceLocator()->get('ViewHelperManager')->get('translate');

        return $translate($string);
    }
}
