<?php
/**
 * ITEA copyright message placeholder
 *
 * @category    ProjectTest
 * @package     Entity
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace AffiliationTest\Entity;

use Affiliation\Entity\Affiliation;

class AffiliationTest extends \PHPUnit_Framework_TestCase
{

    public function testCanCreateEntity()
    {
        $affiliation = new Affiliation();
        $this->assertInstanceOf("Affiliation\Entity\Affiliation", $affiliation);
    }
}
