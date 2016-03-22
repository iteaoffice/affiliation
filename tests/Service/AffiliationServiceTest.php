<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * PHP Version 5
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   2004-2016 ITEA Office
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */
namespace AffiliationTest\Service;

use Affiliation\Entity\Affiliation;
use Affiliation\Service\AffiliationService;

class AffiliationServiceTest extends \PHPUnit_Framework_TestCase
{

    public function testCanCreateService()
    {
        $service = new AffiliationService();
        $this->assertInstanceOf(AffiliationService::class, $service);
    }

    public function testCanSetAffiliationId()
    {
        $service = new AffiliationService();

        // Create a dummy user entity
        $affiliation = new Affiliation();
        $affiliation->setId(1);


        // Mock the repository, disabling the constructor
        $userRepositoryMock = $this->getMockBuilder(\Affiliation\Repository\Affiliation::class)
            ->disableOriginalConstructor()->getMock();
        $userRepositoryMock->expects($this->once())->method('find')->will($this->returnValue($affiliation));

        // Mock the entity manager
        $emMock = $this->getMock('EntityManager', ['getRepository'], [], '', false);
        $emMock->expects($this->any())->method('getRepository')->will($this->returnValue($userRepositoryMock));

        $service->setEntityManager($emMock);

        $affiliation = $service->setAffiliationId(1)->getAffiliation();

        $this->assertEquals($affiliation->getId(), 1);

    }

    /**
     *
     */
    public function testCanSetAffiliations()
    {
        $service = new AffiliationService();

        // Create a dummy user entity
        $affiliation = new Affiliation();
        $affiliation->setId(1);

        // Create a dummy user entity
        $affiliation2 = new Affiliation();
        $affiliation2->setId(2);

        // Mock the repository, disabling the constructor
        $userRepositoryMock = $this->getMockBuilder(\Affiliation\Repository\Affiliation::class)
            ->disableOriginalConstructor()->getMock();
        $userRepositoryMock->expects($this->once())->method('findAll')->will($this->returnValue([
            $affiliation,
            $affiliation2
        ]));

        // Mock the entity manager
        $emMock = $this->getMock('EntityManager', ['getRepository'], [], '', false);
        $emMock->expects($this->any())->method('getRepository')->will($this->returnValue($userRepositoryMock));

        $service->setEntityManager($emMock);


        $this->assertEquals(2, sizeof($service->findAll('affiliation')));
    }
}