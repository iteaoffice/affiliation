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

namespace AffiliationTest\Service;

use Affiliation\Entity\Affiliation;
use Affiliation\Form\AddAssociate;
use Contact\Entity\Contact;
use Contact\Service\ContactService;
use Doctrine\ORM\EntityManager;
use Organisation\Entity\Organisation;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Testing\Util\AbstractFormTest;

/**
 * Class AddAssociateTest
 *
 * @package AffiliationTest\Service
 */
class AddAssociateTest extends AbstractFormTest
{
    /**
     * @var Affiliation
     */
    protected $affiliation;

    /**
     * Set up basic properties
     */
    public function setUp()
    {
        $this->affiliation = new Affiliation();
        $organisation = new Organisation();
        $organisation->setId(1);
        $this->affiliation->setOrganisation($organisation);
    }

    /**
     *
     */
    public function testCanCreateAddAffiliationForm()
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getEntityManagerMock();

        $contactService = $this->setUpContactServiceMock();
        $addAssociate = new AddAssociate($this->affiliation, $contactService);

        $this->assertInstanceOf(AddAssociate::class, $addAssociate);
        $this->assertArrayHasKey('contact', $addAssociate->getInputFilterSpecification());
    }


    /**
     * Set up the contact service mock object.
     *
     * @return ContactService|MockObject
     */
    private function setUpContactServiceMock()
    {
        $contact = new Contact();
        $contact->setId(1);

        $contactServiceMock = $this->getMockBuilder(ContactService::class)
            ->setMethods(['findContactsInOrganisation'])
            ->getMock();

        $contactServiceMock->expects($this->exactly(1))
            ->method('findContactsInOrganisation')
            ->with($this->affiliation->getOrganisation())
            ->will($this->returnValue([$contact]
            ));

        return $contactServiceMock;
    }
}