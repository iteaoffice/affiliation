<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2016 ITEA Office (https://itea3.org)
 */

namespace AffiliationTest\Service;

use Affiliation\Entity\Affiliation;
use Affiliation\Controller\Plugin\MergeAffiliation;
use Affiliation\Entity\Invoice;
use Affiliation\Entity\Version as AffiliationVersion;
use Affiliation\Service\AffiliationService;
use ApplicationTest\Util\AbstractServiceTest;
use Contact\Entity\Contact;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Project\Entity\Cost\Cost;
use Project\Entity\Cost\Version as CostVersion;
use Project\Entity\Effort\Version as EffortVersion;
use Project\Entity\Version\Version as ProjectVersion;
use Project\Entity\Effort\Effort;
use Project\Entity\Workpackage\Workpackage;
use Project\Service\ProjectService;

class MergeAffiliationTest extends AbstractServiceTest
{
    /**
     * Test merging two affiliations adding cost and effort
     * @covers \Affiliation\Controller\Plugin\MergeAffiliation
     * @covers \Affiliation\Controller\Plugin\AbstractPlugin::setAffiliationService
     * @covers \Affiliation\Controller\Plugin\AbstractPlugin::getAffiliationService
     * @covers \Affiliation\Controller\Plugin\AbstractPlugin::setProjectService
     * @covers \Affiliation\Controller\Plugin\AbstractPlugin::getProjectService
     */
    public function testMergeAffiliationSum()
    {
        $mainAffiliation = $this->createMainAffiliation();
        $otherAffiliation = $this->createOtherAffiliation();
        $mergeAffiliation = new MergeAffiliation();

        // Set mocked affiliation service
        $mergeAffiliation->setAffiliationService($this->setUpAffiliationServiceMock($otherAffiliation));

        // Set mocked project service
        $projectServiceMock = $this->setUpProjectServiceMock(
            $mainAffiliation,
            $otherAffiliation,
            MergeAffiliation::STRATEGY_SUM
        );

        $mergeAffiliation->setProjectService($projectServiceMock);

        // Run code and assert return value
        $mergedAffiliation = $mergeAffiliation($mainAffiliation, $otherAffiliation, MergeAffiliation::STRATEGY_SUM);

        $this->assertEquals($mainAffiliation->getId(), $mergedAffiliation->getId());

        // Assert cost
        $this->assertInstanceOf(Cost::class, $mergedAffiliation->getCost()->first());
        $this->assertEquals(30, $mergedAffiliation->getCost()->first()->getCosts());

        // Assert effort
        $this->assertInstanceOf(Effort::class, $mergedAffiliation->getEffort()->first());
        $this->assertEquals(1.50, $mergedAffiliation->getEffort()->first()->getEffort());

        // Assert version cost & effort
        /** @var AffiliationVersion $affiliationVersion1 */
        $affiliationVersion1 = $mergedAffiliation->getVersion()->get(0);
        $this->assertInstanceOf(AffiliationVersion::class, $affiliationVersion1);

        /** @var CostVersion $costVersion1 */
        $costVersion1 = $affiliationVersion1->getCostVersion()->get(0);
        $this->assertInstanceOf(CostVersion::class, $costVersion1);
        $this->assertEquals(35, $costVersion1->getCosts());

        /** @var EffortVersion $effortVersion1 */
        $effortVersion1 = $affiliationVersion1->getEffortVersion()->get(0);
        $this->assertInstanceOf(EffortVersion::class, $effortVersion1);
        $this->assertEquals(0.40, $effortVersion1->getEffort());

        /** @var CostVersion $costVersion2 */
        $costVersion2 = $affiliationVersion1->getCostVersion()->get(1);
        $this->assertInstanceOf(CostVersion::class, $costVersion2);
        $this->assertEquals(30, $costVersion2->getCosts());

        /** @var EffortVersion $effortVersion2 */
        $effortVersion2 = $affiliationVersion1->getEffortVersion()->get(1);
        $this->assertInstanceOf(EffortVersion::class, $effortVersion2);
        $this->assertEquals(0.20, $effortVersion2->getEffort());

        /** @var AffiliationVersion $affiliationVersion2 */
        $affiliationVersion2 = $mergedAffiliation->getVersion()->get(1);
        $this->assertInstanceOf(AffiliationVersion::class, $affiliationVersion2);

        /** @var CostVersion $costVersion1 */
        $costVersion3 = $affiliationVersion2->getCostVersion()->get(0);
        $this->assertInstanceOf(CostVersion::class, $costVersion3);
        $this->assertEquals(40, $costVersion3->getCosts());

        /** @var EffortVersion $effortVersion3 */
        $effortVersion3 = $affiliationVersion2->getEffortVersion()->get(0);
        $this->assertInstanceOf(EffortVersion::class, $effortVersion3);
        $this->assertEquals(0.40, $effortVersion3->getEffort());


    }

    /**
     * Test merging two affiliations using cost eand effort of the main affiliation
     * @covers MergeAffiliation::__invoke
     */
    /*public function testMergeAffiliationUseMain()
    {
        $mainAffiliation = $this->createMainAffiliation();
        $otherAffiliation = $this->createMergedAffiliation();

        $mergeAffiliation = new MergeAffiliation();
        $mergeAffiliation->setProjectService($this->getProjectServiceMock());

    }*/

    /**
     * Test merging two affiliations using cost eand effort of the merged (other) affiliation
     * @covers MergeAffiliation::__invoke
     */
    /*public function testMergeAffiliationUseOther()
    {
        $mainAffiliation = $this->createMainAffiliation();
        $mergedAffiliation = $this->createMergedAffiliation();

        $plugin = new MergeAffiliation();
        $projectServiceMock = $this->getProjectServiceMock();


        $plugin->setProjectService($this->getProjectServiceMock());

    }*/

    /**
     * Create a main affiliation entity data will be merged into
     * @return Affiliation
     */
    private function createMainAffiliation(): Affiliation
    {
        $contact = new Contact();

        $costMatched = new Cost();
        $costMatched->setId(1);
        $costMatched->setCosts(10);
        $costMatched->setDateStart(new \DateTime('2014-01-01'));
        $costMatched->setDateEnd(new \DateTime('2014-12-31'));

        $workpackageMatched = new Workpackage();
        $workpackageMatched->setId(1);

        $effortMatched = new Effort();
        $effortMatched->setId(1);
        $effortMatched->setWorkpackage($workpackageMatched);
        $effortMatched->setEffort(0.5);
        $effortMatched->setDateStart(new \DateTime('2014-01-01'));
        $effortMatched->setDateEnd(new \DateTime('2014-12-31'));

        $affiliation = new Affiliation();
        $affiliation->setId(1);
        $affiliation->setContact($contact);
        $affiliation->setCost(new ArrayCollection([$costMatched]));
        $affiliation->setEffort(new ArrayCollection([$effortMatched]));

        $projectVersionAlsoInOtherAffiliation = new ProjectVersion();
        $projectVersionAlsoInOtherAffiliation->setId(1);

        $affiliationCostVersionMatched = new CostVersion();
        $affiliationCostVersionMatched->setId(1);
        $affiliationCostVersionMatched->setCosts(15.00);
        $affiliationCostVersionMatched->setDateStart(new \DateTime('2014-01-01'));
        $affiliationCostVersionMatched->setDateEnd(new \DateTime('2014-12-31'));

        $affiliationEffortVersionMatched = new EffortVersion();
        $affiliationEffortVersionMatched->setId(1);
        $affiliationEffortVersionMatched->setWorkpackage($workpackageMatched);
        $affiliationEffortVersionMatched->setEffort(0.30);
        $affiliationEffortVersionMatched->setDateStart(new \DateTime('2014-01-01'));
        $affiliationEffortVersionMatched->setDateEnd(new \DateTime('2014-12-31'));

        $affiliationVersion = new AffiliationVersion();
        $affiliationVersion->setId(1);
        $affiliationVersion->setAffiliation($affiliation);
        $affiliationVersion->setVersion($projectVersionAlsoInOtherAffiliation);
        $affiliationVersion->setContact($contact);
        $affiliationVersion->setCostVersion(new ArrayCollection([
            $affiliationCostVersionMatched
        ]));
        $affiliationVersion->setEffortVersion(new ArrayCollection([
            $affiliationEffortVersionMatched
        ]));


        $affiliation->setVersion(new ArrayCollection([$affiliationVersion]));

        return $affiliation;

    }

    /**
     * Create a merged (other) affiliation entity of which the data will be used to merge into the main affiliation
     * @return Affiliation
     */
    private function createOtherAffiliation(): Affiliation
    {
        $affiliation = new Affiliation();
        $affiliation->setId(2);

        // Init cost
        $costMatched = new Cost();
        $costMatched->setId(2);
        $costMatched->setCosts(20.00);
        $costMatched->setDateStart(new \DateTime('2014-01-01'));
        $costMatched->setDateEnd(new \DateTime('2014-12-31'));

        $costNew = new Cost();
        $costNew->setId(3);
        $costNew->setCosts(30.00);
        $costNew->setDateStart(new \DateTime('2015-01-01'));
        $costNew->setDateEnd(new \DateTime('2015-12-31'));

        // Init effort
        $workpackageMatched = new Workpackage();
        $workpackageMatched->setId(1);

        $effortMatched = new Effort();
        $effortMatched->setId(2);
        $effortMatched->setWorkpackage($workpackageMatched);
        $effortMatched->setEffort(1.00);
        $effortMatched->setDateStart(new \DateTime('2014-01-01'));
        $effortMatched->setDateEnd(new \DateTime('2014-12-31'));

        $workpackageNew = new Workpackage();
        $workpackageNew->setId(2);

        $effortNew = new Effort();
        $effortNew->setId(3);
        $effortNew->setWorkpackage($workpackageNew);
        $effortNew->setEffort(1.00);
        $effortNew->setDateStart(new \DateTime('2015-01-01'));
        $effortNew->setDateEnd(new \DateTime('2015-12-31'));

        // Init versions
        $versionContact = new Contact();

        $projectVersionAlsoInMainAffiliation = new ProjectVersion();
        $projectVersionAlsoInMainAffiliation->setId(1);

        $projectVersionNew = new ProjectVersion();
        $projectVersionNew->setId(2);

        // Cost versions
        $affiliationCostVersionMatched = new CostVersion();
        $affiliationCostVersionMatched->setId(2);
        $affiliationCostVersionMatched->setCosts(20.00);
        $affiliationCostVersionMatched->setDateStart(new \DateTime('2014-01-01'));
        $affiliationCostVersionMatched->setDateEnd(new \DateTime('2014-12-31'));

        $affiliationCostVersionNew1 = new CostVersion();
        $affiliationCostVersionNew1->setId(3);
        $affiliationCostVersionNew1->setCosts(30.00);
        $affiliationCostVersionNew1->setDateStart(new \DateTime('2015-01-01'));
        $affiliationCostVersionNew1->setDateEnd(new \DateTime('2015-12-31'));

        $affiliationCostVersionNew2 = new CostVersion();
        $affiliationCostVersionNew2->setId(4);
        $affiliationCostVersionNew2->setCosts(40.00);
        $affiliationCostVersionNew2->setDateStart(new \DateTime('2016-01-01'));
        $affiliationCostVersionNew2->setDateEnd(new \DateTime('2016-12-31'));

        // Effort versions
        $affiliationEffortVersionMatched = new EffortVersion();
        $affiliationEffortVersionMatched->setId(2);
        $affiliationEffortVersionMatched->setWorkpackage($workpackageMatched);
        $affiliationEffortVersionMatched->setEffort(0.10);
        $affiliationEffortVersionMatched->setDateStart(new \DateTime('2014-01-01'));
        $affiliationEffortVersionMatched->setDateEnd(new \DateTime('2014-12-31'));

        $affiliationEffortVersionNew1 = new EffortVersion();
        $affiliationEffortVersionNew1->setId(3);
        $affiliationEffortVersionNew1->setWorkpackage($workpackageMatched);
        $affiliationEffortVersionNew1->setEffort(0.20);
        $affiliationEffortVersionNew1->setDateStart(new \DateTime('2015-01-01'));
        $affiliationEffortVersionNew1->setDateEnd(new \DateTime('2015-12-31'));

        $affiliationEffortVersionNew2 = new EffortVersion();
        $affiliationEffortVersionNew2->setId(4);
        $affiliationEffortVersionNew2->setWorkpackage($workpackageNew);
        $affiliationEffortVersionNew2->setEffort(0.40);
        $affiliationEffortVersionNew2->setDateStart(new \DateTime('2016-01-01'));
        $affiliationEffortVersionNew2->setDateEnd(new \DateTime('2016-12-31'));

        // Matched affiliation version with same project version as main affiliation
        $affiliationVersionMatched = new AffiliationVersion();
        $affiliationVersionMatched->setId(2);
        $affiliationVersionMatched->setAffiliation($affiliation);
        $affiliationVersionMatched->setVersion($projectVersionAlsoInMainAffiliation);
        $affiliationVersionMatched->setContact($versionContact);
        $affiliationVersionMatched->setCostVersion(new ArrayCollection([
            $affiliationCostVersionMatched, $affiliationCostVersionNew1
        ]));
        $affiliationVersionMatched->setEffortVersion(new ArrayCollection([
            $affiliationEffortVersionMatched, $affiliationEffortVersionNew1
        ]));

        // A new affiliation version
        $affiliationVersionNew = new AffiliationVersion();
        $affiliationVersionNew->setId(3);
        $affiliationVersionNew->setAffiliation($affiliation);
        $affiliationVersionNew->setVersion($projectVersionNew);
        $affiliationVersionNew->setContact($versionContact);
        $affiliationVersionNew->setCostVersion(new ArrayCollection([
            $affiliationCostVersionNew2
        ]));
        $affiliationVersionNew->setEffortVersion(new ArrayCollection([
            $affiliationEffortVersionNew2
        ]));

        //

        $invoice = new Invoice();


        $affiliation->setCost(new ArrayCollection([$costMatched, $costNew]));
        $affiliation->setEffort(new ArrayCollection([$effortMatched, $effortNew]));
        $affiliation->setVersion(new ArrayCollection([$affiliationVersionMatched, $affiliationVersionNew]));
        $affiliation->setInvoice(new ArrayCollection([$invoice]));

        return $affiliation;
    }

    /**
     * @param Affiliation $mainAffiliation
     * @param Affiliation $otherAffiliation
     * @param int $strategy
     * @return ProjectService|MockObject
     */
    private function setUpProjectServiceMock(
        Affiliation $mainAffiliation,
        Affiliation $otherAffiliation,
        int $strategy): MockObject
    {
        $projectServiceMock = $this->getMockBuilder(ProjectService::class)
            ->setMethods(['updateEntity', 'removeEntity'])
            ->getMock();

        switch ($strategy) {
            case MergeAffiliation::STRATEGY_SUM:
                break;
        }

        return $projectServiceMock;
    }

    /**
     * Set up the affiliation service mock object and expectations
     *
     * @param Affiliation $otherAffiliation
     * @return AffiliationService|MockObject
     */
    private function setUpAffiliationServiceMock(Affiliation $otherAffiliation): MockObject
    {
        $affiliationServiceMock = $this->getMockBuilder(AffiliationService::class)
            ->setMethods(['updateEntity', 'removeEntity'])
            ->getMock();

        /** @var AffiliationVersion $version */
        //$version = $otherAffiliation->getVersion()->first();
        /** @var Invoice $invoice */
        //$invoice = $otherAffiliation->getInvoice()->first();

        // Expect 2 removals
        $affiliationServiceMock->expects($this->exactly(2)) // 2
            ->method('removeEntity')
            ->withConsecutive(
                [$this->isInstanceOf(AffiliationVersion::class)],
                [$this->equalTo($otherAffiliation)]
            )
            ->will($this->returnValue(true));

        // Expect 2 updates
        $affiliationServiceMock->expects($this->once()) //2
            ->method('updateEntity')
            ->withConsecutive(
                [$this->isInstanceOf(AffiliationVersion::class)]
            )
            ->will($this->returnArgument(0));

        return $affiliationServiceMock;
    }
}