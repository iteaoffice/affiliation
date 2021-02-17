<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace AffiliationTest\Form;

use Affiliation\Entity\Affiliation;
use Affiliation\Form\Admin\AffiliationForm;
use Doctrine\Common\Collections\ArrayCollection;
use General\Entity\Country;
use Organisation\Entity\Organisation;
use Organisation\Entity\ParentEntity;
use Organisation\Service\ParentService;
use Testing\Util\AbstractFormTest;

/**
 * Class AdminAffiliationTest
 *
 * @package AffiliationTest\Service
 */
class AffiliationFormTest extends AbstractFormTest
{
    protected Affiliation $affiliation;

    public function setUp(): void
    {
        $this->affiliation = new Affiliation();
        $organisation      = new Organisation();
        $organisation->setId(1);
        $organisation->setOrganisation('organisation');
        $this->affiliation->setOrganisation($organisation);
    }

    public function testCanCreateAdminAffiliationForm(): void
    {
        $adminAffiliation = new AffiliationForm($this->affiliation, $this->setUpParentServiceMock(), $this->getEntityManagerMock());
        self::assertInstanceOf(AffiliationForm::class, $adminAffiliation);
        self::assertArrayHasKey('parentOrganisation', $adminAffiliation->getInputFilterSpecification());
    }

    private function setUpParentServiceMock()
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
        $ParentEntity = new ParentEntity();
        $ParentEntity->setOrganisation($organisation2);
        $ParentEntity->setParentOrganisation(new ArrayCollection([$parentOrganisation]));
        $parentServiceMock = $this->getMockBuilder(ParentService::class)
            ->disableOriginalConstructor()
            ->setMethods(['findParentOrganisationByNameLike', 'findAll'])
            ->getMock();
        $parentServiceMock->expects(self::once())
            ->method('findParentOrganisationByNameLike')
            ->with($this->affiliation->getOrganisation())
            ->willReturn(new ArrayCollection([$parentOrganisation]));
        $parentServiceMock->expects(self::once())
            ->method('findAll')
            ->willReturn([$ParentEntity]);
//  $parentServiceMock->setEntityManager($this->getEntityManagerMock(ParentEntity::class));

        return $parentServiceMock;
    }
}
