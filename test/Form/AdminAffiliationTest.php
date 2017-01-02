<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

namespace AffiliationTest\Service;

use Affiliation\Entity\Affiliation;
use Affiliation\Form\AdminAffiliation;
use Testing\Util\AbstractFormTest;
use Doctrine\ORM\EntityManager;
use Organisation\Entity\Organisation;

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
        $organisation      = new Organisation();
        $organisation->setId(1);
        $this->affiliation->setOrganisation($organisation);
    }

    /**
     *
     */
    public function testCanCreateAdminAffiliationForm()
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getEntityManagerMock();

        $adminAffiliation = new AdminAffiliation($this->affiliation, $entityManager);

        $this->assertInstanceOf(AdminAffiliation::class, $adminAffiliation);
        $this->assertArrayHasKey('parentOrganisation', $adminAffiliation->getInputFilterSpecification());
    }


}