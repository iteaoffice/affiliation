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

use Affiliation\InputFilter\AffiliationFilter;
use Doctrine\ORM\EntityManager;
use Testing\Util\AbstractInputFilterTest;

/**
 * Class AffiliationFilterTest
 *
 * @package AffiliationTest\Service
 */
class AffiliationFilterTest extends AbstractInputFilterTest
{
    /**
     * Set up basic properties
     */
    public function setUp(): void
    {
    }

    /**
     *
     */
    public function testCanCreateAffiliationInputFilter()
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getEntityManagerMock();

        $affiliationFilter = new AffiliationFilter($entityManager);

        $this->assertInstanceOf(AffiliationFilter::class, $affiliationFilter);
    }


    /**
     *
     */
    public function testAffiliationInputFilterHasElements()
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getEntityManagerMock();

        $affiliationFilter = new AffiliationFilter($entityManager);

        $this->assertNotNull($affiliationFilter->get('affiliation_entity_affiliation'));
        $this->assertNotNull($affiliationFilter->get('affiliation_entity_affiliation')->get('branch'));
        $this->assertNotNull($affiliationFilter->get('affiliation_entity_affiliation')->get('note'));
        $this->assertNotNull($affiliationFilter->get('affiliation_entity_affiliation')->get('valueChain'));
        $this->assertNotNull($affiliationFilter->get('affiliation_entity_affiliation')->get('mainContribution'));
        $this->assertNotNull($affiliationFilter->get('affiliation_entity_affiliation')->get('marketAccess'));
        $this->assertNotNull($affiliationFilter->get('affiliation_entity_affiliation')->get('dateEnd'));
        $this->assertNotNull($affiliationFilter->get('affiliation_entity_affiliation')->get('dateSelfFunded'));
        $this->assertNotNull($affiliationFilter->get('affiliation_entity_affiliation')->get('contact'));
        $this->assertNotNull($affiliationFilter->get('affiliation_entity_affiliation')->get('selfFunded'));
    }
}