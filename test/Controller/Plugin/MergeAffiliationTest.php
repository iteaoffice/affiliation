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
use Project\Entity\Version\Version as ProjectVersion;
use Project\Entity\Effort\Effort;
use Project\Entity\Workpackage\Workpackage;
use Project\Service\ProjectService;

class MergeAffiliationTest extends AbstractServiceTest
{
    /**
     * Test merging two affiliations adding cost and effort
     * @covers MergeAffiliation::__invoke
     */
    public function testMergeAffiliationSum()
    {
        $mainAffiliation = $this->createMainAffiliation();
        $otherAffiliation = $this->createOtherAffiliation();
        $mergeAffiliation = new MergeAffiliation();

        // Set mocked affiliation service
        $mergeAffiliation->setAffiliationService(
            $this->setUpAffiliationServiceMock($otherAffiliation)
        );

        // Set mocked project service
        $projectServiceMock = $this->setUpProjectServiceMock(
            $mainAffiliation,
            $otherAffiliation,
            MergeAffiliation::STRATEGY_SUM
        );
        //$projectServiceMock->expects();

        $mergeAffiliation->setProjectService($projectServiceMock);

        // Run code and assert return value
        $mergedAffiliation = $mergeAffiliation($mainAffiliation, $otherAffiliation, MergeAffiliation::STRATEGY_SUM);

        $this->assertEquals($mainAffiliation->getId(), $mergedAffiliation->getId());
        $this->assertInstanceOf(Cost::class, $mergedAffiliation->getCost()->first());
        $this->assertEquals(30, $mergedAffiliation->getCost()->first()->getCosts());
        $this->assertInstanceOf(Effort::class, $mergedAffiliation->getEffort()->first());
        $this->assertEquals(1.50, $mergedAffiliation->getEffort()->first()->getEffort());

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
        $costMatched->setDateStart(new \DateTime('2015-01-01'));
        $costMatched->setDateEnd(new \DateTime('2015-12-31'));

        $workpackageMatched = new Workpackage();
        $workpackageMatched->setId(1);

        $effortMatched = new Effort();
        $effortMatched->setId(1);
        $effortMatched->setWorkpackage($workpackageMatched);
        $effortMatched->setEffort(0.5);
        $effortMatched->setDateStart(new \DateTime('2015-01-01'));
        $effortMatched->setDateEnd(new \DateTime('2015-12-31'));

        $affiliation = new Affiliation();
        $affiliation->setId(1);
        $affiliation->setContact($contact);
        $affiliation->setCost(new ArrayCollection([$costMatched]));
        $affiliation->setEffort(new ArrayCollection([$effortMatched]));

        $affiliationVersion = new AffiliationVersion();
        $affiliationVersion->setId(3);
        $affiliationVersion->setAffiliation($affiliation);
        $affiliationVersion->setContact($contact);

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
        $costMatched->setCosts(20);
        $costMatched->setDateStart(new \DateTime('2015-01-01'));
        $costMatched->setDateEnd(new \DateTime('2015-12-31'));

        $costNew = new Cost();
        $costNew->setId(3);
        $costNew->setCosts(30);
        $costNew->setDateStart(new \DateTime('2016-01-01'));
        $costNew->setDateEnd(new \DateTime('2016-12-31'));

        // Init effort
        $workpackageMatched = new Workpackage();
        $workpackageMatched->setId(1);

        $effortMatched = new Effort();
        $effortMatched->setId(2);
        $effortMatched->setWorkpackage($workpackageMatched);
        $effortMatched->setEffort(1.00);
        $effortMatched->setDateStart(new \DateTime('2015-01-01'));
        $effortMatched->setDateEnd(new \DateTime('2015-12-31'));

        $workpackageNew = new Workpackage();
        $workpackageNew->setId(2);

        $effortNew = new Effort();
        $effortNew->setId(3);
        $effortNew->setWorkpackage($workpackageNew);
        $effortNew->setEffort(1.00);
        $effortNew->setDateStart(new \DateTime('2016-01-01'));
        $effortNew->setDateEnd(new \DateTime('2016-12-31'));

        // Init versions
        $versionContact = new Contact();

        $projectVersionAlsoInMainAffiliation = new ProjectVersion();
        $projectVersionAlsoInMainAffiliation->setId(1);

        $projectVersionNew = new ProjectVersion();
        $projectVersionNew->setId(2);

        $affiliationCostVersionMatched = new CostVersion();
        $affiliationCostVersionMatched->setId(1);
        $affiliationCostVersionMatched->setCosts(20.00);
        $affiliationCostVersionMatched->setDateStart(new \DateTime('2015-01-01'));
        $affiliationCostVersionMatched->setDateEnd(new \DateTime('2015-12-31'));

        $affiliationVersionMatched = new AffiliationVersion();
        $affiliationVersionMatched->setId(1);
        $affiliationVersionMatched->setAffiliation($affiliation);
        $affiliationVersionMatched->setVersion($projectVersionAlsoInMainAffiliation);
        $affiliationVersionMatched->setContact($versionContact);
        $affiliationVersionMatched->setCostVersion(new ArrayCollection([
            $affiliationCostVersionMatched
        ]));

        $affiliationVersionNew = new AffiliationVersion();
        $affiliationVersionNew->setId(2);
        $affiliationVersionNew->setAffiliation($affiliation);
        $affiliationVersionNew->setVersion($projectVersionNew);
        $affiliationVersionNew->setContact($versionContact);

        /** @var AffiliationVersion $affiliationVersionMain */
        $affiliationVersionMain = $this->createMainAffiliation()->getVersion()->first();
        $affiliationVersionMain->setVersion($projectVersionAlsoInMainAffiliation);
        $affiliationVersionMain->setCostVersion(new ArrayCollection([
            $affiliationCostVersionMatched
        ]));

        $projectVersionAlsoInMainAffiliation->setAffiliationVersion(new ArrayCollection([
            $affiliationVersionMatched, $affiliationVersionMain
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
     * @return MockObject
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
     * @return MockObject
     */
    private function setUpAffiliationServiceMock(Affiliation $otherAffiliation): MockObject
    {
        $affiliationServiceMock = $this->getMockBuilder(AffiliationService::class)
            ->setMethods(['newEntity','updateEntity', 'removeEntity'])
            ->getMock();

        /** @var AffiliationVersion $version */
        $version = $otherAffiliation->getVersion()->first();
        /** @var Invoice $invoice */
        $invoice = $otherAffiliation->getInvoice()->first();

        // Expect 2 removals
        $affiliationServiceMock->expects($this->exactly(1)) // 2
            ->method('removeEntity')
            ->withConsecutive(
                //[$this->equalTo($version)],
                [$this->equalTo($otherAffiliation)]
            )
            ->will($this->returnValue(true));

        // Expect 2 updates
        $affiliationServiceMock->expects($this->exactly(0)) //2
            ->method('updateEntity')
            ->withConsecutive(
                //[$this->equalTo($invoice)],
                [$this->equalTo($otherAffiliation)]
            )
            ->will($this->returnArgument(0));

        // Expect 1 new entity
        $affiliationServiceMock->expects($this->once()) // once
            ->method('newEntity')
            ->with($this->isInstanceOf(AffiliationVersion::class))
            ->will($this->returnArgument(0));

        return $affiliationServiceMock;
    }
}