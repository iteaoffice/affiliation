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

use Affiliation\Entity\Affiliation;
use Affiliation\Form\AddAssociateForm;
use Contact\Entity\Contact;
use Contact\Service\ContactService;
use Doctrine\ORM\EntityManager;
use Organisation\Entity\Organisation;
use Testing\Util\AbstractFormTest;

/**
 * Class AddAssociateTest
 * @package AffiliationTest\Service
 */
class AddAssociateTest extends AbstractFormTest
{
    protected Affiliation $affiliation;

    public function setUp(): void
    {
        $this->affiliation = new Affiliation();
        $organisation      = new Organisation();
        $organisation->setId(1);
        $this->affiliation->setOrganisation($organisation);
    }

    public function testCanCreateAddAffiliationForm(): void
    {
        /** @var EntityManager $entityManager */
        $entityManager  = $this->getEntityManagerMock();
        $contactService = $this->setUpContactServiceMock();
        $addAssociate   = new AddAssociateForm($this->affiliation, $contactService);
        self::assertInstanceOf(AddAssociateForm::class, $addAssociate);
        self::assertArrayHasKey('contact', $addAssociate->getInputFilterSpecification());
    }


    private function setUpContactServiceMock()
    {
        $contact = new Contact();
        $contact->setId(1);
        $contactServiceMock = $this->getMockBuilder(ContactService::class)->disableOriginalConstructor()
            ->onlyMethods(['findContactsInOrganisation'])
            ->getMock();
        $contactServiceMock->expects(self::once())
            ->method('findContactsInOrganisation')
            ->with($this->affiliation->getOrganisation())
            ->willReturn([$contact]);
        return $contactServiceMock;
    }
}
