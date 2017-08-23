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

namespace AffiliationTest\Form;

use Affiliation\Entity\Affiliation;
use Affiliation\Form\AdminAffiliation;
use Doctrine\Common\Collections\ArrayCollection;
use General\Entity\Country;
use Organisation\Entity\OParent;
use Organisation\Entity\Organisation;
use Organisation\Service\ParentService;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Testing\Util\AbstractFormTest;

/**
 * Class AdminAffiliationTest
 *
 * @package AffiliationTest\Service
 */
class AdminAffiliationTest extends AbstractFormTest
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
        $organisation->setOrganisation('organisation');
        $this->affiliation->setOrganisation($organisation);
    }

    /**
     *
     */
    public function testCanCreateAdminAffiliationForm()
    {
        $adminAffiliation = new AdminAffiliation($this->affiliation, $this->setUpParentServiceMock());

        $this->assertInstanceOf(AdminAffiliation::class, $adminAffiliation);
        $this->assertArrayHasKey('parentOrganisation', $adminAffiliation->getInputFilterSpecification());
    }

    /**
     * Set up the contact service mock object.
     *
     * @return ParentService|MockObject
     */
    private function setUpParentServiceMock(): MockObject
    {
        $country = new Country();
        $country->setId(1);

        $organisation1 = new Organisation();
        $organisation1->setId(1);
        $organisation1->setOrganisation('Organisation 1');
        $organisation1->setCountry($country);

        $organisation2 = new Organisation();
        $organisation2->setId(2);
        $organisation2->setOrganisation('Organisation 2');
        $organisation2->setCountry($country);

        $parentOrganisation = new \Organisation\Entity\Parent\Organisation();
        $parentOrganisation->setOrganisation($organisation1);

        $oParent = new OParent();
        $oParent->setOrganisation($organisation2);
        $oParent->setParentOrganisation(new ArrayCollection([$parentOrganisation]));

        $parentServiceMock = $this->getMockBuilder(ParentService::class)
            ->setMethods(['findParentOrganisationByNameLike', 'findAll'])
            ->getMock();

        $parentServiceMock->expects($this->once())
            ->method('findParentOrganisationByNameLike')
            ->with($this->affiliation->getOrganisation())
            ->will($this->returnValue([$parentOrganisation]));

        $parentServiceMock->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue([$oParent]));

        $parentServiceMock->setEntityManager($this->getEntityManagerMock(OParent::class));

        return $parentServiceMock;
    }

}