<?php
/**
 * ITEA copyright message placeholder
 *
 * @category    ProjectTest
 * @package     Entity
 * @author      Johan van der Heide <info@japaveh.nl>
 * @copyright   Copyright (c) 2004-2013 ITEA
 */
namespace AffiliationTest\Entity;

use Affiliation\Entity\Affiliation;

class ProjectTest extends \PHPUnit_Framework_TestCase
{

    public function testCanCreateEntity()
    {
        $affiliation = new Affiliation();
        $this->assertInstanceOf("Affiliation\Entity\Affiliation", $affiliation);
    }
}
