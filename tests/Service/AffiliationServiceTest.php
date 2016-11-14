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
use Doctrine\ORM\EntityManager;

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

        $emMock = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()->getMock();
        $emMock->expects($this->any())->method('getRepository')->will($this->returnValue($userRepositoryMock));

        $service->setEntityManager($emMock);

        $affiliation = $service->findAffiliationById(1);

        $this->assertEquals($affiliation->getId(), 1);

    }


}