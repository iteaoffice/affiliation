<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace AffiliationTest\Service;

use Affiliation\Entity;
use Affiliation\Repository;
use Affiliation\Service\AffiliationService;
use Contact\Entity\Contact;
use Contact\Entity\ContactOrganisation;
use Contact\Service\ContactService;
use Contact\Service\SelectionContactService;
use Deeplink\Service\DeeplinkService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use General\Service\EmailService;
use General\Service\GeneralService;
use Invoice\Service\InvoiceService;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\View\HelperPluginManager;
use Organisation\Entity\Financial;
use Organisation\Entity\ParentEntity;
use Organisation\Entity\Organisation;
use Organisation\Entity\Type;
use Organisation\Service\OrganisationService;
use Organisation\Service\ParentService;
use Project\Entity\Project;
use Project\Service\ContractService;
use Project\Service\ProjectService;
use Project\Service\VersionService;
use Testing\Util\AbstractServiceTest;

/**
 * Class AffiliationServiceTest
 *
 * @package AffiliationTest\Service
 */
class AffiliationServiceTest extends AbstractServiceTest
{
    public function testFindAffiliationBId(): void
    {
        $affiliationId = 1;
        // Create a dummy project entity
        $affiliation = new Entity\Affiliation();
        $affiliation->setId($affiliationId);
        // Mock the repository, disabling the constructor
        $affiliationRepositoryMock = $this->getMockBuilder(Repository\Affiliation::class)
            ->disableOriginalConstructor()
            ->setMethods(['find'])
            ->getMock();
        $affiliationRepositoryMock->expects(self::once())
            ->method('find')
            ->with(self::identicalTo($affiliationId))
            ->willReturn($affiliation);
        // Mock the entity manager + affiliation repository
        $entityManagerMock = $this->getEntityManagerMock(Entity\Affiliation::class, $affiliationRepositoryMock);
        $service           = new AffiliationService(
            $entityManagerMock,
            $this->getMockBuilder(SelectionContactService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(GeneralService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ProjectService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(InvoiceService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ContractService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(OrganisationService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(VersionService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ParentService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ContactService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(DeeplinkService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(EmailService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(HelperPluginManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(PluginManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(TranslatorInterface::class)->disableOriginalConstructor()->getMock()
        );
        self::assertEquals($affiliation, $service->findAffiliationById((int)$affiliationId));
    }


    public function testIsActiveInVersion(): void
    {
        $service     = new AffiliationService(
            $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(SelectionContactService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(GeneralService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ProjectService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(InvoiceService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ContractService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(OrganisationService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(VersionService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ParentService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ContactService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(DeeplinkService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(EmailService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(HelperPluginManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(PluginManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(TranslatorInterface::class)->disableOriginalConstructor()->getMock()
        );
        $affiliation = new Entity\Affiliation();
        self::assertFalse($service->isActiveInVersion($affiliation));
        $isActiveAffiliation = new Entity\Affiliation();
        $isActiveAffiliation->getVersion()->add(new Entity\Version());
        self::assertTrue($service->isActiveInVersion($isActiveAffiliation));
    }

    public function testHasDoa(): void
    {
        $service     = new AffiliationService(
            $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(SelectionContactService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(GeneralService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ProjectService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(InvoiceService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ContractService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(OrganisationService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(VersionService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ParentService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ContactService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(DeeplinkService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(EmailService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(HelperPluginManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(PluginManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(TranslatorInterface::class)->disableOriginalConstructor()->getMock()
        );
        $affiliation = new Entity\Affiliation();
        self::assertFalse($service->hasDoa($affiliation));
        //Create a version
        $doa                 = new Entity\Doa();
        $isActiveAffiliation = new Entity\Affiliation();
        $isActiveAffiliation->setDoa($doa);
        self::assertTrue($service->hasDoa($isActiveAffiliation));
    }

    public function testHasLoi(): void
    {
        $service     = new AffiliationService(
            $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(SelectionContactService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(GeneralService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ProjectService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(InvoiceService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ContractService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(OrganisationService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(VersionService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ParentService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ContactService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(DeeplinkService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(EmailService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(HelperPluginManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(PluginManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(TranslatorInterface::class)->disableOriginalConstructor()->getMock()
        );
        $affiliation = new Entity\Affiliation();
        self::assertFalse($service->hasLoi($affiliation));
        //Create a version
        $loi                 = new Entity\Loi();
        $isActiveAffiliation = new Entity\Affiliation();
        $isActiveAffiliation->setLoi($loi);
        self::assertTrue($service->hasLoi($isActiveAffiliation));
    }

    public function testFindNotValidatedSelfFundedAffiliation(): void
    {
        $affiliationRepositoryMock = $this->getMockBuilder(Repository\Affiliation::class)
            ->disableOriginalConstructor()
            ->setMethods(['findNotValidatedSelfFundedAffiliation'])
            ->getMock();
        $affiliationRepositoryMock->expects(self::once())
            ->method('findNotValidatedSelfFundedAffiliation')
            ->willReturn([new Entity\Affiliation()]);
        // Mock the entity manager + affiliation repository
        $entityManagerMock = $this->getEntityManagerMock(Entity\Affiliation::class, $affiliationRepositoryMock);
        $service           = new AffiliationService(
            $entityManagerMock,
            $this->getMockBuilder(SelectionContactService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(GeneralService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ProjectService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(InvoiceService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ContractService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(OrganisationService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(VersionService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ParentService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ContactService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(DeeplinkService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(EmailService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(HelperPluginManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(PluginManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(TranslatorInterface::class)->disableOriginalConstructor()->getMock()
        );
        $results           = $service->findNotValidatedSelfFundedAffiliation();
        self::assertCount(1, $results);
        self::assertInstanceOf(Entity\Affiliation::class, reset($results));
    }

    public function testFindMissingAffiliationParent(): void
    {
        $entityManagerConfig = new Configuration();
        $entityManagerMock1  = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfiguration'])
            ->getMock();
        $entityManagerMock1->expects(self::exactly(2))
            ->method('getConfiguration')
            ->willReturn($entityManagerConfig);
        /** @var EntityManager $entityManagerMock1 */
        $query                     = new Query($entityManagerMock1);
        $affiliationRepositoryMock = $this->getMockBuilder(Repository\Affiliation::class)
            ->disableOriginalConstructor()
            ->setMethods(['findMissingAffiliationParent'])
            ->getMock();
        $affiliationRepositoryMock->expects(self::once())
            ->method('findMissingAffiliationParent')
            ->willReturn($query);
        // Mock the entity manager + affiliation repository
        /** @var EntityManager $entityManagerMock */
        $entityManagerMock2 = $this->getEntityManagerMock(Entity\Affiliation::class, $affiliationRepositoryMock);
        $service           = new AffiliationService(
            $entityManagerMock2,
            $this->getMockBuilder(SelectionContactService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(GeneralService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ProjectService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(InvoiceService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ContractService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(OrganisationService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(VersionService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ParentService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ContactService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(DeeplinkService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(EmailService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(HelperPluginManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(PluginManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(TranslatorInterface::class)->disableOriginalConstructor()->getMock()
        );
        $result             = $service->findMissingAffiliationParent();
        self::assertSame($query, $result);
    }

    public function testFindOrganisationFinancial(): void
    {
        $service     = new AffiliationService(
            $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(SelectionContactService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(GeneralService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ProjectService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(InvoiceService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ContractService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(OrganisationService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(VersionService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ParentService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ContactService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(DeeplinkService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(EmailService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(HelperPluginManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(PluginManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(TranslatorInterface::class)->disableOriginalConstructor()->getMock()
        );

//        $affiliation  = new Entity\Affiliation();
//        $organisation = new Organisation();
//        $affiliation->setOrganisation($organisation);
//        $this->assertNull($service->parseVatNumber($affiliation));
//        $affiliation  = new Entity\Affiliation();
//        $parent       = new ParentEntity();
//        $organisation = new Organisation();
//        $parent->setOrganisation($organisation);
//        $parentOrganisation = new \Organisation\Entity\Parent\Organisation();
//        $parentOrganisation->setParent($parent);
//        $parentOrganisation->setOrganisation($organisation);
//        $affiliation->setParentOrganisation($parentOrganisation);
//        $this->assertNull($service->findOrganisationFinancial($affiliation));
//        $this->assertNull($service->parseVatNumber($affiliation));


        //Situation with a parent and no parent financial
//        $vatNumber    = 'VATNUMBER';
//        $affiliation  = new Entity\Affiliation();
//        $parent       = new ParentEntity();
//        $organisation = new Organisation();
//        $financial    = new Financial();
//        $financial->setVat($vatNumber);
//        $organisation->setFinancial($financial);
//        $parent->setOrganisation($organisation);
//        $parentOrganisation = new \Organisation\Entity\Parent\Organisation();
//        $parentOrganisation->setParent($parent);
//        $parentOrganisation->setOrganisation($organisation);
//        $affiliation->setParentOrganisation($parentOrganisation);
//        $affiliation->setOrganisation($organisation);
//        $this->assertEquals($financial, $service->findOrganisationFinancial($affiliation));
//        $this->assertEquals($vatNumber, $service->parseVatNumber($affiliation));


        //Situation with a parent and a parent financial
        $vatNumber    = 'VATNUMBER';
        $affiliation  = new Entity\Affiliation();
        $parent       = new ParentEntity();
        $organisation = new Organisation();
        $financial    = new Financial();
        $financial->setVat($vatNumber);
        $organisation->setFinancial($financial);
        $parentFinancial = new \Organisation\Entity\Parent\Financial();
        $parentFinancial->setParent($parent);
        $parentFinancial->setOrganisation($organisation);
        $parent->setOrganisation($organisation);
        $parent->setFinancial(new ArrayCollection([$parentFinancial]));
        $parentOrganisation = new \Organisation\Entity\Parent\Organisation();
        $parentOrganisation->setParent($parent);
        $parentOrganisation->setOrganisation($organisation);
        $affiliation->setParentOrganisation($parentOrganisation);
        self::assertEquals($financial, $service->findOrganisationFinancial($affiliation));
        self::assertEquals($vatNumber, $service->parseVatNumber($affiliation));
    }

    public function testCanFindFinancialContact(): void
    {
        $service     = new AffiliationService(
            $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(SelectionContactService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(GeneralService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ProjectService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(InvoiceService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ContractService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(OrganisationService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(VersionService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ParentService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ContactService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(DeeplinkService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(EmailService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(HelperPluginManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(PluginManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(TranslatorInterface::class)->disableOriginalConstructor()->getMock()
        );
        $affiliation = new Entity\Affiliation();
        self::assertNull($service->getFinancialContact($affiliation));
        $affiliation = new Entity\Affiliation();
        $financial   = new Entity\Financial();
        $contact     = new Contact();
        $financial->setContact($contact);
        $affiliation->setFinancial($financial);
        self::assertEquals($contact, $service->getFinancialContact($affiliation));
    }

    public function testCanCreateInvoices(): void
    {
        $service     = new AffiliationService(
            $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(SelectionContactService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(GeneralService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ProjectService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(InvoiceService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ContractService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(OrganisationService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(VersionService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ParentService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ContactService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(DeeplinkService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(EmailService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(HelperPluginManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(PluginManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(TranslatorInterface::class)->disableOriginalConstructor()->getMock()
        );
        $affiliation  = new Entity\Affiliation();
        $organisation = new Organisation();
        $type         = new Type();
        $type->setInvoice(Type::INVOICE);
        $organisation->setType($type);
        $affiliation->setOrganisation($organisation);
        self::assertIsArray($service->canCreateInvoice($affiliation));
    }

    public function testFindAffiliationByProjectAndContactAndWhich(): void
    {
        $service     = new AffiliationService(
            $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(SelectionContactService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(GeneralService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ProjectService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(InvoiceService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ContractService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(OrganisationService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(VersionService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ParentService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ContactService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(DeeplinkService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(EmailService::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(HelperPluginManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(PluginManager::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(TranslatorInterface::class)->disableOriginalConstructor()->getMock()
        );
        $project = new Project();
        $contact = new Contact();
        self::assertNull($service->findAffiliationByProjectAndContactAndWhich($project, $contact));
        $contact->setContactOrganisation(new ContactOrganisation());
        $affiliation1 = new Entity\Affiliation();
        $affiliation1->setContact($contact);
        $affiliation2 = new Entity\Affiliation();
        $affiliation2->setDateEnd(new \DateTime());
        $affiliation2->setContact($contact);
        $project->getAffiliation()->add($affiliation1);
        $project->getAffiliation()->add($affiliation2);
        self::assertEquals($affiliation1, $service->findAffiliationByProjectAndContactAndWhich($project, $contact));
        self::assertEquals(
            $affiliation2,
            $service->findAffiliationByProjectAndContactAndWhich(
                $project,
                $contact,
                AffiliationService::WHICH_ONLY_INACTIVE
            )
        );
    }
}
