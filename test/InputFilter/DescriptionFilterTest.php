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

use Affiliation\InputFilter\DescriptionFilter;
use Testing\Util\AbstractInputFilterTest;

/**
 * Class DescriptionFilterTest
 *
 * @package AffiliationTest\Service
 */
class DescriptionFilterTest extends AbstractInputFilterTest
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
        $descriptionFilter = new DescriptionFilter();

        $this->assertInstanceOf(DescriptionFilter::class, $descriptionFilter);
    }


    /**
     *
     */
    public function testAffiliationInputFilterHasElements()
    {
        $descriptionFilter = new DescriptionFilter();

        $this->assertNotNull($descriptionFilter->get('affiliation_entity_description'));
        $this->assertNotNull($descriptionFilter->get('affiliation_entity_description')->get('description'));
    }
}