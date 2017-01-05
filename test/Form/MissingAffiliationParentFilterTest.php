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

use Affiliation\Form\MissingAffiliationParentFilter;
use Testing\Util\AbstractFormTest;

/**
 * Class AddAssociateTest
 *
 * @package AffiliationTest\Service
 */
class MissingAffiliationParentFilterTest extends AbstractFormTest
{
    /**
     *
     */
    public function testCanCreateAddAffiliationForm()
    {
        $missingAffiliationParent = new MissingAffiliationParentFilter;

        $this->assertInstanceOf(MissingAffiliationParentFilter::class, $missingAffiliationParent);
    }
}