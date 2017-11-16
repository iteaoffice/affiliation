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

namespace AffiliationTest\Controller\Plugin;

use Admin\Service\AdminService;
use Affiliation\Controller\Plugin\MergeAffiliation;
use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Invoice;
use Affiliation\Entity\Log as AffiliationLog;
use Affiliation\Entity\Version as AffiliationVersion;
use Contact\Entity\Contact;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Project\Entity\Achievement;
use Project\Entity\ChangeRequest\CostChange;
use Project\Entity\Cost\Cost;
use Project\Entity\Cost\Version as CostVersion;
use Project\Entity\Effort\Effort;
use Project\Entity\Effort\Spent;
use Project\Entity\Effort\Version as EffortVersion;
use Project\Entity\Log as ProjectLog;
use Project\Entity\Report\EffortSpent;
use Project\Entity\Version\Version as ProjectVersion;
use Project\Entity\Workpackage\Workpackage;
use Testing\Util\AbstractServiceTest;

/**
 * Class MergeAffiliationTest
 *
 * @package AffiliationTest\Service
 */
class MergeAffiliationTest extends AbstractServiceTest
{
    /** @var Affiliation */
    protected $mainAffiliation;

    /** @var Affiliation */
    protected $otherAffiliation;

    /** @var MergeAffiliation */
    protected $mergeAffiliation;

    /**
     * Set up basic properties
     */
    public function setUp()
    {
        $this->mainAffiliation = $this->createMainAffiliation();
        $this->otherAffiliation = $this->createOtherAffiliation();
        $this->mergeAffiliation = new MergeAffiliation();
    }

    /**
     * Create a main affiliation entity data will be merged into
     *
     * @return Affiliation
     */
    private function createMainAffiliation(): Affiliation
    {
        $contact = new Contact();

        // Init cost
        $costMatched = new Cost();
        $costMatched->setId(1);
        $costMatched->setCosts(10);
        $costMatched->setDateStart(new \DateTime('2014-01-01'));
        $costMatched->setDateEnd(new \DateTime('2014-12-31'));

        // Init effort
        $workpackageMatched = new Workpackage();
        $workpackageMatched->setId(1);

        $effortMatched = new Effort();
        $effortMatched->setId(1);
        $effortMatched->setWorkpackage($workpackageMatched);
        $effortMatched->setEffort(0.5);
        $effortMatched->setDateStart(new \DateTime('2014-01-01'));
        $effortMatched->setDateEnd(new \DateTime('2014-12-31'));

        $effortSpentMatched = new Spent();
        $effortSpentMatched->setId(1);
        $effortSpentMatched->setWorkpackage($workpackageMatched);
        $effortSpentMatched->setEffort(0.4);
        $effortSpentMatched->setDateStart(new \DateTime('2014-01-01'));
        $effortSpentMatched->setDateEnd(new \DateTime('2014-12-31'));

        // Create affiliation
        $affiliation = new Affiliation();
        $affiliation->setId(1);
        $affiliation->setContact($contact);
        $affiliation->setCost(new ArrayCollection([$costMatched]));
        $affiliation->setEffort(new ArrayCollection([$effortMatched]));
        $affiliation->setSpent(new ArrayCollection([$effortSpentMatched]));

        // Init cost version
        $affiliationCostVersionMatched = new CostVersion();
        $affiliationCostVersionMatched->setId(1);
        $affiliationCostVersionMatched->setCosts(15.00);
        $affiliationCostVersionMatched->setDateStart(new \DateTime('2014-01-01'));
        $affiliationCostVersionMatched->setDateEnd(new \DateTime('2014-12-31'));

        // Init effort version
        $affiliationEffortVersionMatched = new EffortVersion();
        $affiliationEffortVersionMatched->setId(1);
        $affiliationEffortVersionMatched->setWorkpackage($workpackageMatched);
        $affiliationEffortVersionMatched->setEffort(0.30);
        $affiliationEffortVersionMatched->setDateStart(new \DateTime('2014-01-01'));
        $affiliationEffortVersionMatched->setDateEnd(new \DateTime('2014-12-31'));

        // Create main affiliation
        $affiliation = new Affiliation();
        $affiliation->setId(1);
        $affiliation->setContact($contact);
        $affiliation->setCost(new ArrayCollection([$costMatched]));
        $affiliation->setEffort(new ArrayCollection([$effortMatched]));
        $affiliation->setSpent(new ArrayCollection([$effortSpentMatched]));

        // Init affiliation version
        $projectVersionAlsoInOtherAffiliation = new ProjectVersion();
        $projectVersionAlsoInOtherAffiliation->setId(1);

        $affiliationVersion = new AffiliationVersion();
        $affiliationVersion->setId(1);
        $affiliationVersion->setAffiliation($affiliation);
        $affiliationVersion->setVersion($projectVersionAlsoInOtherAffiliation);
        $affiliationVersion->setContact($contact);
        $affiliationVersion->setCostVersion(new ArrayCollection([$affiliationCostVersionMatched]));
        $affiliationVersion->setEffortVersion(new ArrayCollection([$affiliationEffortVersionMatched]));

        $affiliation->setVersion(new ArrayCollection([$affiliationVersion]));

        return $affiliation;
    }

    /**
     * Create a merged (other) affiliation entity of which the data will be used to merge into the main affiliation
     *
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
        $costNew->setCosts(40.00);
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

        $effortSpentMatched = new Spent();
        $effortSpentMatched->setId(2);
        $effortSpentMatched->setWorkpackage($workpackageMatched);
        $effortSpentMatched->setEffort(0.60);
        $effortSpentMatched->setDateStart(new \DateTime('2014-01-01'));
        $effortSpentMatched->setDateEnd(new \DateTime('2014-12-31'));

        $workpackageNew = new Workpackage();
        $workpackageNew->setId(2);

        $effortNew = new Effort();
        $effortNew->setId(3);
        $effortNew->setWorkpackage($workpackageNew);
        $effortNew->setEffort(1.00);
        $effortNew->setDateStart(new \DateTime('2015-01-01'));
        $effortNew->setDateEnd(new \DateTime('2015-12-31'));

        $effortSpentNew = new Spent();
        $effortSpentNew->setId(3);
        $effortSpentNew->setWorkpackage($workpackageNew);
        $effortSpentNew->setEffort(0.10);
        $effortSpentNew->setDateStart(new \DateTime('2015-01-01'));
        $effortSpentNew->setDateEnd(new \DateTime('2015-12-31'));

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
            $affiliationCostVersionMatched,
            $affiliationCostVersionNew1,
        ]));
        $affiliationVersionMatched->setEffortVersion(new ArrayCollection([
            $affiliationEffortVersionMatched,
            $affiliationEffortVersionNew1,
        ]));

        // A new affiliation version
        $affiliationVersionNew = new AffiliationVersion();
        $affiliationVersionNew->setId(3);
        $affiliationVersionNew->setAffiliation($affiliation);
        $affiliationVersionNew->setVersion($projectVersionNew);
        $affiliationVersionNew->setContact($versionContact);
        $affiliationVersionNew->setCostVersion(new ArrayCollection([$affiliationCostVersionNew2]));
        $affiliationVersionNew->setEffortVersion(new ArrayCollection([$affiliationEffortVersionNew2]));

        // Achievements
        $achievement = new Achievement();
        $achievement->setId(1);
        $achievement->setAffiliation(new ArrayCollection([$affiliation]));

        // Cost changes
        $costChange = new CostChange();
        $costChange->setId(1);
        $costChange->setAffiliation($affiliation);

        // Report effort spent
        $reportEffortSpent = new EffortSpent();
        $reportEffortSpent->setId(1);
        $reportEffortSpent->setAffiliation($affiliation);

        // Project log
        $projectLog = new ProjectLog();
        $projectLog->setId(1);
        $projectLog->setAffiliation(new ArrayCollection([$affiliation]));

        // Affiliation log
        $affiliationLog = new AffiliationLog();
        $affiliationLog->setId(1);
        $affiliationLog->setAffiliation($affiliation);

        // Invoice
        $invoice = new Invoice();
        $invoice->setId(1);
        $invoice->setAffiliation($affiliation);

        // Associate
        $associate = new Contact();
        $associate->setId(1);
        $associate->setAffiliation(new ArrayCollection([$affiliation]));

        // Set affiliation properties
        $affiliation->setCost(new ArrayCollection([$costMatched, $costNew]));
        $affiliation->setEffort(new ArrayCollection([$effortMatched, $effortNew]));
        $affiliation->setSpent(new ArrayCollection([$effortSpentMatched, $effortSpentNew]));
        $affiliation->setVersion(new ArrayCollection([$affiliationVersionMatched, $affiliationVersionNew]));
        $affiliation->setAchievement(new ArrayCollection([$achievement]));
        $affiliation->setChangeRequestCostChange(new ArrayCollection([$costChange]));
        $affiliation->setProjectReportEffortSpent(new ArrayCollection([$reportEffortSpent]));
        $affiliation->setProjectLog(new ArrayCollection([$projectLog]));
        $affiliation->setLog(new ArrayCollection([$affiliationLog]));
        $affiliation->setInvoice(new ArrayCollection([$invoice]));
        $affiliation->setAssociate(new ArrayCollection([$associate]));

        return $affiliation;
    }

    /**
     * Test merging of entities that are not affected by the chosen merge strategy
     *
     */
    public function testMergeAffiliationGeneral()
    {
        $mergeAffiliation = $this->mergeAffiliation;
        $strategy = MergeAffiliation::STRATEGY_USE_MAIN;
        $mergeAffiliation->setEntityManager($this->setUpEntityManagerMock($strategy));
        $mergeAffiliation->setAdminService($this->setUpAdminServiceMock());

        // Run the merge
        $response = $mergeAffiliation($this->mainAffiliation, $this->otherAffiliation, $strategy);
        $this->assertEquals(true, $response['success']);

        // Assert achievements
        /** @var Achievement $achievement */
        $achievement = $this->mainAffiliation->getAchievement()->first();
        $this->assertInstanceOf(Achievement::class, $achievement);
        $this->assertEquals(1, $achievement->getId());

        // Assert cost changes
        /** @var CostChange $costChange */
        $costChange = $this->mainAffiliation->getChangeRequestCostChange()->first();
        $this->assertInstanceOf(CostChange::class, $costChange);
        $this->assertEquals(1, $costChange->getId());

        // Assert report effort spent
        /** @var EffortSpent $reportEffortSpent */
        $reportEffortSpent = $this->mainAffiliation->getProjectReportEffortSpent()->first();
        $this->assertInstanceOf(EffortSpent::class, $reportEffortSpent);
        $this->assertEquals(1, $reportEffortSpent->getId());

        // Assert project log
        /** @var ProjectLog $projectLog */
        $projectLog = $this->mainAffiliation->getProjectLog()->first();
        $this->assertInstanceOf(ProjectLog::class, $projectLog);
        $this->assertEquals(1, $projectLog->getId());

        // Assert affiliation log
        /** @var AffiliationLog $affiliationLog */
        $affiliationLog = $this->mainAffiliation->getLog()->first();
        $this->assertInstanceOf(AffiliationLog::class, $affiliationLog);
        $this->assertEquals(1, $affiliationLog->getId());

        // Assert invoices
        /** @var Invoice $invoice */
        $invoice = $this->mainAffiliation->getInvoice()->first();
        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals(1, $invoice->getId());

        // Assert associates
        /** @var Contact $associate */
        $associate = $this->mainAffiliation->getAssociate()->first();
        $this->assertInstanceOf(Contact::class, $associate);
        $this->assertEquals(1, $associate->getId());
    }

    /**
     * Set up the entity manager mock object with expectations depending on the chosen merge strategy.
     *
     * @param integer $strategy
     * @param bool $throwException
     *
     * @return EntityManager|MockObject
     */
    private function setUpEntityManagerMock(int $strategy, $throwException = false)
    {
        $entityManagerMock = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['persist', 'remove', 'flush'])
            ->getMock();

        // Short circuit when an exception should be thrown
        if ($throwException) {
            $exception = new ORMException('Oops!');
            $entityManagerMock->expects($this->any())->method('persist')->will($this->throwException($exception));
            $entityManagerMock->expects($this->any())->method('remove')->will($this->throwException($exception));
            $entityManagerMock->expects($this->any())->method('flush')->will($this->throwException($exception));

            return $entityManagerMock;
        }

        // Setup the parameters depending on merge strategy
        $params = [];
        switch ($strategy) {
            case MergeAffiliation::STRATEGY_SUM:
            case MergeAffiliation::STRATEGY_USE_OTHER:
                $params = [
                    [$this->identicalTo($this->mainAffiliation->getCost()->first())],
                    [$this->identicalTo($this->otherAffiliation->getCost()->get(1))],
                    [$this->identicalTo($this->mainAffiliation->getEffort()->first())],
                    [$this->identicalTo($this->otherAffiliation->getEffort()->get(1))],
                    [$this->identicalTo($this->mainAffiliation->getSpent()->first())],
                    [$this->identicalTo($this->otherAffiliation->getSpent()->get(1))],
                    [$this->identicalTo($this->mainAffiliation->getVersion()->first()->getCostVersion()->first())],
                    [$this->identicalTo($this->otherAffiliation->getVersion()->first()->getCostVersion()->get(1))],
                    [$this->identicalTo($this->mainAffiliation->getVersion()->first()->getEffortVersion()->first())],
                    [$this->identicalTo($this->otherAffiliation->getVersion()->first()->getEffortVersion()->get(1))],
                    [$this->identicalTo($this->otherAffiliation->getVersion()->get(1))],
                    [$this->identicalTo($this->otherAffiliation->getAchievement()->first())],
                    [$this->identicalTo($this->otherAffiliation->getChangeRequestCostChange()->first())],
                    [$this->identicalTo($this->otherAffiliation->getProjectReportEffortSpent()->first())],
                    [$this->identicalTo($this->otherAffiliation->getProjectLog()->first())],
                    [$this->identicalTo($this->otherAffiliation->getLog()->first())],
                    [$this->identicalTo($this->otherAffiliation->getInvoice()->first())],
                    [$this->identicalTo($this->mainAffiliation)],
                ];
                break;
            case MergeAffiliation::STRATEGY_USE_MAIN:
                $params = [
                    [$this->identicalTo($this->otherAffiliation->getCost()->get(1))],
                    [$this->identicalTo($this->otherAffiliation->getEffort()->get(1))],
                    [$this->identicalTo($this->otherAffiliation->getSpent()->get(1))],
                    [$this->identicalTo($this->otherAffiliation->getVersion()->first()->getCostVersion()->get(1))],
                    [$this->identicalTo($this->otherAffiliation->getVersion()->first()->getEffortVersion()->get(1))],
                    [$this->identicalTo($this->otherAffiliation->getVersion()->get(1))],
                    [$this->identicalTo($this->otherAffiliation->getAchievement()->first())],
                    [$this->identicalTo($this->otherAffiliation->getChangeRequestCostChange()->first())],
                    [$this->identicalTo($this->otherAffiliation->getProjectReportEffortSpent()->first())],
                    [$this->identicalTo($this->otherAffiliation->getProjectLog()->first())],
                    [$this->identicalTo($this->otherAffiliation->getLog()->first())],
                    [$this->identicalTo($this->otherAffiliation->getInvoice()->first())],
                    [$this->identicalTo($this->mainAffiliation)],
                ];
                break;
        }

        $entityManagerMock->expects($this->exactly(\count($params)))
            ->method('persist')
            ->withConsecutive(...$params);

        $params = [
            [$this->identicalTo($this->otherAffiliation->getCost()->first())],
            [$this->identicalTo($this->otherAffiliation->getEffort()->first())],
            [$this->identicalTo($this->otherAffiliation->getSpent()->first())],
            [$this->identicalTo($this->otherAffiliation->getVersion()->first()->getCostVersion()->first())],
            [$this->identicalTo($this->otherAffiliation->getVersion()->first()->getEffortVersion()->first())],
            [$this->identicalTo($this->otherAffiliation->getVersion()->first())],
            [$this->identicalTo($this->otherAffiliation)],
        ];
        $entityManagerMock->expects($this->exactly(\count($params)))
            ->method('remove')
            ->withConsecutive(...$params);

        $entityManagerMock->expects($this->once())->method('flush');

        return $entityManagerMock;
    }

    /**
     * Set up the admin service mock object.
     *
     * @return AdminService|MockObject
     */
    private function setUpAdminServiceMock()
    {
        $adminServiceMock = $this->getMockBuilder(AdminService::class)
            ->setMethods(['flushPermitsByEntityAndId'])
            ->getMock();

        $adminServiceMock->expects($this->exactly(1))
            ->method('flushPermitsByEntityAndId')
            ->with(
                $this->identicalTo($this->mainAffiliation->get('underscore_entity_name')),
                $this->identicalTo($this->mainAffiliation->getId())
            )
            ->will($this->returnValue(true));

        return $adminServiceMock;
    }

    /**
     * Test merging two affiliations using the SUM of cost and effort from both affiliations
     *
     * @covers \Affiliation\Controller\Plugin\MergeAffiliation
     */
    public function testMergeAffiliationSum()
    {
        $mergeAffiliation = $this->mergeAffiliation;
        $strategy = MergeAffiliation::STRATEGY_SUM;
        $mergeAffiliation->setEntityManager($this->setUpEntityManagerMock($strategy));
        $mergeAffiliation->setAdminService($this->setUpAdminServiceMock());

        // Run the merge
        $response = $mergeAffiliation($this->mainAffiliation, $this->otherAffiliation, $strategy);
        $this->assertEquals(true, $response['success']);

        // Assert cost
        $cost = $this->mainAffiliation->getCost();
        $this->assertInstanceOf(Cost::class, $cost->first());
        $this->assertEquals(30, $cost->first()->getCosts());
        $this->assertInstanceOf(Cost::class, $cost->get(1));
        $this->assertEquals(40, $cost->get(1)->getCosts());

        // Assert effort
        $effort = $this->mainAffiliation->getEffort();
        $this->assertInstanceOf(Effort::class, $effort->first());
        $this->assertEquals(1.50, $effort->first()->getEffort());
        $this->assertInstanceOf(Effort::class, $effort->get(1));
        $this->assertEquals(1.00, $effort->get(1)->getEffort());

        // Assert effort spent
        $effortSpent = $this->mainAffiliation->getSpent();
        $this->assertInstanceOf(Spent::class, $effortSpent->first());
        $this->assertEquals(1.00, $effortSpent->first()->getEffort());
        $this->assertInstanceOf(Spent::class, $effortSpent->get(1));
        $this->assertEquals(0.10, $effortSpent->get(1)->getEffort());

        // Assert version cost & effort
        /** @var AffiliationVersion $affiliationVersion1 */
        $affiliationVersion1 = $this->mainAffiliation->getVersion()->get(0);
        $this->assertInstanceOf(AffiliationVersion::class, $affiliationVersion1);

        /** @var CostVersion $costVersion1 */
        $costVersion1 = $affiliationVersion1->getCostVersion()->get(0);
        $this->assertInstanceOf(CostVersion::class, $costVersion1);
        $this->assertEquals(35, $costVersion1->getCosts());

        /** @var CostVersion $costVersion2 */
        $costVersion2 = $affiliationVersion1->getCostVersion()->get(1);
        $this->assertInstanceOf(CostVersion::class, $costVersion2);
        $this->assertEquals(30, $costVersion2->getCosts());

        /** @var EffortVersion $effortVersion1 */
        $effortVersion1 = $affiliationVersion1->getEffortVersion()->get(0);
        $this->assertInstanceOf(EffortVersion::class, $effortVersion1);
        $this->assertEquals(0.40, $effortVersion1->getEffort());

        /** @var EffortVersion $effortVersion2 */
        $effortVersion2 = $affiliationVersion1->getEffortVersion()->get(1);
        $this->assertInstanceOf(EffortVersion::class, $effortVersion2);
        $this->assertEquals(0.20, $effortVersion2->getEffort());

        /** @var AffiliationVersion $affiliationVersion2 */
        $affiliationVersion2 = $this->mainAffiliation->getVersion()->get(1);
        $this->assertInstanceOf(AffiliationVersion::class, $affiliationVersion2);

        /** @var CostVersion $costVersion3 */
        $costVersion3 = $affiliationVersion2->getCostVersion()->get(0);
        $this->assertInstanceOf(CostVersion::class, $costVersion3);
        $this->assertEquals(40, $costVersion3->getCosts());

        /** @var EffortVersion $effortVersion3 */
        $effortVersion3 = $affiliationVersion2->getEffortVersion()->get(0);
        $this->assertInstanceOf(EffortVersion::class, $effortVersion3);
        $this->assertEquals(0.40, $effortVersion3->getEffort());
    }

    /**
     * Test merging two affiliations using cost and effort from the MAIN affiliation
     *
     * @covers \Affiliation\Controller\Plugin\MergeAffiliation
     */
    public function testMergeAffiliationUseMain()
    {
        $mergeAffiliation = $this->mergeAffiliation;
        $strategy = MergeAffiliation::STRATEGY_USE_MAIN;
        $mergeAffiliation->setEntityManager($this->setUpEntityManagerMock($strategy));
        $mergeAffiliation->setAdminService($this->setUpAdminServiceMock());

        // Run the merge
        $response = $mergeAffiliation($this->mainAffiliation, $this->otherAffiliation, $strategy);
        $this->assertEquals(true, $response['success']);

        // Assert cost
        $cost = $this->mainAffiliation->getCost();
        $this->assertInstanceOf(Cost::class, $cost->first());
        $this->assertEquals(10, $cost->first()->getCosts());
        $this->assertInstanceOf(Cost::class, $cost->get(1));
        $this->assertEquals(40, $cost->get(1)->getCosts());

        // Assert effort
        $effort = $this->mainAffiliation->getEffort();
        $this->assertInstanceOf(Effort::class, $effort->first());
        $this->assertEquals(0.50, $effort->first()->getEffort());
        $this->assertInstanceOf(Effort::class, $effort->get(1));
        $this->assertEquals(1.00, $effort->get(1)->getEffort());

        // Assert effort spent
        $effortSpent = $this->mainAffiliation->getSpent();
        $this->assertInstanceOf(Spent::class, $effortSpent->first());
        $this->assertEquals(0.40, $effortSpent->first()->getEffort());
        $this->assertInstanceOf(Spent::class, $effortSpent->get(1));
        $this->assertEquals(0.10, $effortSpent->get(1)->getEffort());

        // Assert version cost & effort
        /** @var AffiliationVersion $affiliationVersion1 */
        $affiliationVersion1 = $this->mainAffiliation->getVersion()->get(0);
        $this->assertInstanceOf(AffiliationVersion::class, $affiliationVersion1);

        // Affiliation version 1
        /** @var CostVersion $costVersion1 */
        $costVersion1 = $affiliationVersion1->getCostVersion()->get(0);
        $this->assertInstanceOf(CostVersion::class, $costVersion1);
        $this->assertEquals(15, $costVersion1->getCosts());

        /** @var CostVersion $costVersion2 */
        $costVersion2 = $affiliationVersion1->getCostVersion()->get(1);
        $this->assertInstanceOf(CostVersion::class, $costVersion2);
        $this->assertEquals(30, $costVersion2->getCosts());

        /** @var EffortVersion $effortVersion1 */
        $effortVersion1 = $affiliationVersion1->getEffortVersion()->get(0);
        $this->assertInstanceOf(EffortVersion::class, $effortVersion1);
        $this->assertEquals(0.30, $effortVersion1->getEffort());

        /** @var EffortVersion $effortVersion2 */
        $effortVersion2 = $affiliationVersion1->getEffortVersion()->get(1);
        $this->assertInstanceOf(EffortVersion::class, $effortVersion2);
        $this->assertEquals(0.20, $effortVersion2->getEffort());

        // Affiliation version 2
        /** @var AffiliationVersion $affiliationVersion2 */
        $affiliationVersion2 = $this->mainAffiliation->getVersion()->get(1);
        $this->assertInstanceOf(AffiliationVersion::class, $affiliationVersion2);

        /** @var CostVersion $costVersion3 */
        $costVersion3 = $affiliationVersion2->getCostVersion()->get(0);
        $this->assertInstanceOf(CostVersion::class, $costVersion3);
        $this->assertEquals(40, $costVersion3->getCosts());

        /** @var EffortVersion $effortVersion3 */
        $effortVersion3 = $affiliationVersion2->getEffortVersion()->get(0);
        $this->assertInstanceOf(EffortVersion::class, $effortVersion3);
        $this->assertEquals(0.40, $effortVersion3->getEffort());
    }

    /**
     * Test merging two affiliations using cost and effort from the OTHER affiliation
     *
     * @covers \Affiliation\Controller\Plugin\MergeAffiliation
     */
    public function testMergeAffiliationUseOther()
    {
        $mergeAffiliation = $this->mergeAffiliation;
        $strategy = MergeAffiliation::STRATEGY_USE_OTHER;
        $mergeAffiliation->setEntityManager($this->setUpEntityManagerMock($strategy));
        $mergeAffiliation->setAdminService($this->setUpAdminServiceMock());

        // Run the merge
        $response = $mergeAffiliation($this->mainAffiliation, $this->otherAffiliation, $strategy);
        $this->assertEquals(true, $response['success']);

        // Assert cost
        $cost = $this->mainAffiliation->getCost();
        $this->assertInstanceOf(Cost::class, $cost->first());
        $this->assertEquals(20, $cost->first()->getCosts());
        $this->assertInstanceOf(Cost::class, $cost->get(1));
        $this->assertEquals(40, $cost->get(1)->getCosts());

        // Assert effort
        $effort = $this->mainAffiliation->getEffort();
        $this->assertInstanceOf(Effort::class, $effort->first());
        $this->assertEquals(1.00, $effort->first()->getEffort());
        $this->assertInstanceOf(Effort::class, $effort->get(1));
        $this->assertEquals(1.00, $effort->get(1)->getEffort());

        // Assert effort spent
        $effortSpent = $this->mainAffiliation->getSpent();
        $this->assertInstanceOf(Spent::class, $effortSpent->first());
        $this->assertEquals(0.60, $effortSpent->first()->getEffort());
        $this->assertInstanceOf(Spent::class, $effortSpent->get(1));
        $this->assertEquals(0.10, $effortSpent->get(1)->getEffort());

        // Assert version cost & effort
        /** @var AffiliationVersion $affiliationVersion1 */
        $affiliationVersion1 = $this->mainAffiliation->getVersion()->get(0);
        $this->assertInstanceOf(AffiliationVersion::class, $affiliationVersion1);

        // Affiliation version 1
        /** @var CostVersion $costVersion1 */
        $costVersion1 = $affiliationVersion1->getCostVersion()->get(0);
        $this->assertInstanceOf(CostVersion::class, $costVersion1);
        $this->assertEquals(20, $costVersion1->getCosts());

        /** @var CostVersion $costVersion2 */
        $costVersion2 = $affiliationVersion1->getCostVersion()->get(1);
        $this->assertInstanceOf(CostVersion::class, $costVersion2);
        $this->assertEquals(30, $costVersion2->getCosts());

        /** @var EffortVersion $effortVersion1 */
        $effortVersion1 = $affiliationVersion1->getEffortVersion()->get(0);
        $this->assertInstanceOf(EffortVersion::class, $effortVersion1);
        $this->assertEquals(0.10, $effortVersion1->getEffort());

        /** @var EffortVersion $effortVersion2 */
        $effortVersion2 = $affiliationVersion1->getEffortVersion()->get(1);
        $this->assertInstanceOf(EffortVersion::class, $effortVersion2);
        $this->assertEquals(0.20, $effortVersion2->getEffort());

        // Affiliation version 2
        /** @var AffiliationVersion $affiliationVersion2 */
        $affiliationVersion2 = $this->mainAffiliation->getVersion()->get(1);
        $this->assertInstanceOf(AffiliationVersion::class, $affiliationVersion2);

        /** @var CostVersion $costVersion3 */
        $costVersion3 = $affiliationVersion2->getCostVersion()->get(0);
        $this->assertInstanceOf(CostVersion::class, $costVersion3);
        $this->assertEquals(40, $costVersion3->getCosts());

        /** @var EffortVersion $effortVersion3 */
        $effortVersion3 = $affiliationVersion2->getEffortVersion()->get(0);
        $this->assertInstanceOf(EffortVersion::class, $effortVersion3);
        $this->assertEquals(0.40, $effortVersion3->getEffort());
    }

    /**
     * Test merging two affiliations during which an exception occurs
     *
     * @covers \Affiliation\Controller\Plugin\MergeAffiliation::__invoke
     */
    public function testMergeAffiliationException()
    {
        $mergeAffiliation = $this->mergeAffiliation;
        $strategy = MergeAffiliation::STRATEGY_USE_MAIN;
        $mergeAffiliation->setEntityManager($this->setUpEntityManagerMock($strategy, true));

        // Run the merge
        $response = $mergeAffiliation($this->mainAffiliation, $this->otherAffiliation, $strategy);
        $this->assertEquals(false, $response['success']);
        $this->assertEquals('Oops!', $response['errorMessage']);
    }
}