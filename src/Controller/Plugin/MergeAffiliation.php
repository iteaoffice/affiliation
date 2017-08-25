<?php
/**
 * ITEA Office all rights reserved
 *
 * PHP Version 7
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/affiliation for the canonical source repository
 */

declare(strict_types=1);

namespace Affiliation\Controller\Plugin;

use Admin\Service\AdminService;
use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Version as AffiliationVersion;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Project\Entity\Cost\Version as CostVersion;
use Project\Entity\Effort\Version as EffortVersion;
use Project\Entity\Version\Version as ProjectVersion;

/**
 * Class MergeAffiliation
 *
 * @package Affiliation\Controller\Plugin
 */
class MergeAffiliation extends AbstractPlugin
{
    // Cost and effort merge strategies
    const STRATEGY_SUM = 0;       // Add other cost and effort to main
    const STRATEGY_USE_MAIN = 1;  // Use cost and effort of main affiliation
    const STRATEGY_USE_OTHER = 2; // Use cost and effort of other affiliation
    /**
     * @var array
     */
    protected $debug = [];
    /**
     * @var AdminService
     */
    protected $adminService;
    /**
     * @var EntityManager
     */
    protected $entityManager;
    /**
     * @var Affiliation
     */
    private $otherAffiliation;
    /**
     * @var Affiliation
     */
    private $mainAffiliation;
    /**
     * @var int
     */
    private $costAndEffortStrategy;

    /**
     * MergeAffiliation magic invokable
     *
     * @param Affiliation $mainAffiliation
     * @param Affiliation $otherAffiliation
     * @param int $costAndEffortStrategy
     *
     * @return array
     */
    public function __invoke(
        Affiliation $mainAffiliation,
        Affiliation $otherAffiliation,
        int $costAndEffortStrategy = self::STRATEGY_SUM
    ): array {
        $this->setMainAffiliation($mainAffiliation);
        $this->setOtherAffiliation($otherAffiliation);
        $this->setCostAndEffortStrategy($costAndEffortStrategy);

        $response = ['success' => true, 'errorMessage' => ''];

        try {
            // Step 1: Transfer cost
            $this->transferCost();

            // Step 2: Transfer effort
            $this->transferEffort();

            // Step 3: Transfer the effort spent
            $this->transferEffortSpent();

            // Step 4: Transfer affiliation versions incl. version cost and version effort
            $this->transferAffiliationVersions();

            // Step 5: Move the achievements
            foreach ($otherAffiliation->getAchievement() as $key => $achievement) {
                $achievement->getAffiliation()->add($mainAffiliation);
                $achievement->getAffiliation()->removeElement($otherAffiliation);
                $this->getEntityManager()->persist($achievement);
                $mainAffiliation->getAchievement()->add($achievement);
                $otherAffiliation->getAchievement()->remove($key);
            }

            // Step 6: Move the cost changes
            foreach ($otherAffiliation->getChangeRequestCostChange() as $key => $costChange) {
                $costChange->setAffiliation($mainAffiliation);
                $this->getEntityManager()->persist($costChange);
                $mainAffiliation->getChangeRequestCostChange()->add($costChange);
                $otherAffiliation->getChangeRequestCostChange()->remove($key);
            }

            // Step 7: Move the effort spent from the PPR
            foreach ($otherAffiliation->getProjectReportEffortSpent() as $key => $reportEffortSpent) {
                $reportEffortSpent->setAffiliation($mainAffiliation);
                $this->getEntityManager()->persist($reportEffortSpent);
                $mainAffiliation->getProjectReportEffortSpent()->add($reportEffortSpent);
                $otherAffiliation->getProjectReportEffortSpent()->remove($key);
            }

            // Step 8: Move the dedicated project logs
            foreach ($otherAffiliation->getProjectLog() as $key => $projectLog) {
                $projectLog->getAffiliation()->add($mainAffiliation);
                $projectLog->getAffiliation()->removeElement($otherAffiliation);
                $this->getEntityManager()->persist($projectLog);
                $mainAffiliation->getProjectLog()->add($projectLog);
                $otherAffiliation->getProjectLog()->remove($key);
            }

            // Step 9: Move the affiliation logs
            foreach ($otherAffiliation->getLog() as $key => $affiliationLog) {
                $affiliationLog->setAffiliation($mainAffiliation);
                $this->getEntityManager()->persist($affiliationLog);
                $mainAffiliation->getLog()->add($affiliationLog);
                $otherAffiliation->getLog()->remove($key);
            }

            // Step 10: Move the invoices
            foreach ($otherAffiliation->getInvoice() as $key => $invoice) {
                $invoice->setAffiliation($mainAffiliation);
                $this->getEntityManager()->persist($invoice);
                $mainAffiliation->getInvoice()->add($invoice);
                $otherAffiliation->getInvoice()->remove($key);
            }

            // Step 11: Move the associates
            foreach ($otherAffiliation->getAssociate() as $key => $associate) {
                if (!$mainAffiliation->getAssociate()->contains($associate)) {
                    $mainAffiliation->getAssociate()->add($associate);
                }
                $otherAffiliation->getAssociate()->remove($key);
            }

            // Step 12: Persist main affiliation, remove the other + flush and update permissions
            $this->getEntityManager()->persist($mainAffiliation);
            $this->getEntityManager()->remove($otherAffiliation);
            $this->getEntityManager()->flush();
            $this->getAdminService()->flushPermitsByEntityAndId(
                $mainAffiliation->get('underscore_entity_name'),
                $mainAffiliation->getId()
            );
        } catch (ORMException $e) {
            $response = ['success' => false, 'errorMessage' => $e->getMessage()];
            error_log($e->getFile() . ':' . $e->getLine() . ' ' . $e->getMessage());
        }

        return $response;
    }


    /**
     * Transfer the cost
     */
    protected function transferCost()
    {
        foreach ($this->getOtherAffiliation()->getCost() as $otherKey => $otherCost) {
            // Check whether the main affiliation already has cost in the same period
            $matched = false;
            foreach ($this->getMainAffiliation()->getCost() as &$mainCost) {
                if ($otherCost->getDateStart()->format('dmY') === $mainCost->getDateStart()->format('dmY')
                    && $otherCost->getDateEnd()->format('dmY') === $mainCost->getDateEnd()->format('dmY')
                ) {
                    switch ($this->getCostAndEffortStrategy()) {
                        case self::STRATEGY_SUM:
                            $mainCost->setCosts($mainCost->getCosts() + $otherCost->getCosts());
                            $this->getEntityManager()->persist($mainCost);
                            break;
                        case self::STRATEGY_USE_MAIN: // Do nothing
                            break;
                        case self::STRATEGY_USE_OTHER:
                            $mainCost->setCosts($otherCost->getCosts());
                            $this->getEntityManager()->persist($mainCost);
                            break;
                    }
                    $this->getEntityManager()->remove($otherCost);
                    $matched = true;

                    break;
                }
            }

            // No match, just transfer to main affiliation
            if (!$matched) {
                $otherCost->setAffiliation($this->getMainAffiliation());
                $this->getEntityManager()->persist($otherCost);
                $this->getMainAffiliation()->getCost()->add($otherCost);
            }

            $this->getOtherAffiliation()->getCost()->remove($otherKey);
        }
    }

    /**
     * @return Affiliation
     */
    protected function getOtherAffiliation(): Affiliation
    {
        return $this->otherAffiliation;
    }

    /**
     * @param Affiliation $otherAffiliation
     *
     * @return MergeAffiliation
     */
    protected function setOtherAffiliation(Affiliation $otherAffiliation): MergeAffiliation
    {
        $this->otherAffiliation = $otherAffiliation;

        return $this;
    }

    /**
     * @return Affiliation
     */
    protected function getMainAffiliation(): Affiliation
    {
        return $this->mainAffiliation;
    }

    /**
     * @param Affiliation $mainAffiliation
     *
     * @return MergeAffiliation
     */
    protected function setMainAffiliation(Affiliation $mainAffiliation): MergeAffiliation
    {
        $this->mainAffiliation = $mainAffiliation;

        return $this;
    }

    /**
     * @return int
     */
    protected function getCostAndEffortStrategy(): int
    {
        return $this->costAndEffortStrategy;
    }

    /**
     * @param int $costAndEffortStrategy
     *
     * @return MergeAffiliation
     */
    protected function setCostAndEffortStrategy(int $costAndEffortStrategy): MergeAffiliation
    {
        $this->costAndEffortStrategy = $costAndEffortStrategy;

        return $this;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     *
     * @return MergeAffiliation
     */
    public function setEntityManager(EntityManager $entityManager): MergeAffiliation
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    /**
     * Transfer the effort
     */
    protected function transferEffort()
    {
        foreach ($this->getOtherAffiliation()->getEffort() as $otherKey => $otherEffort) {
            // Check whether the main affiliation already has effort in the same period for the same workpackage
            $matched = false;
            foreach ($this->getMainAffiliation()->getEffort() as &$mainEffort) {
                if ($otherEffort->getDateStart()->format('dmY') === $mainEffort->getDateStart()->format('dmY')
                    && $otherEffort->getDateEnd()->format('dmY') === $mainEffort->getDateEnd()->format('dmY')
                    && $otherEffort->getWorkpackage()->getId() === $mainEffort->getWorkpackage()->getId()
                ) {
                    $originalEffort = $mainEffort->getEffort();
                    $debugTemplate = '';
                    switch ($this->getCostAndEffortStrategy()) {
                        case self::STRATEGY_SUM:
                            $mainEffort->setEffort($originalEffort + $otherEffort->getEffort());
                            $this->getEntityManager()->persist($mainEffort);
                            $debugTemplate = 'Effort found and added %f (main) + %f (other) = %f';
                            break;
                        case self::STRATEGY_USE_MAIN: // Do nothing
                            $debugTemplate = 'Used main effort %f';
                            break;
                        case self::STRATEGY_USE_OTHER:
                            $mainEffort->setEffort($otherEffort->getEffort());
                            $this->getEntityManager()->persist($mainEffort);
                            $debugTemplate = 'Used other effort %2$f';
                            break;
                    }
                    $this->getEntityManager()->remove($otherEffort);
                    $matched = true;

                    $this->debug[] = sprintf(
                        $debugTemplate,
                        $originalEffort,
                        $otherEffort->getEffort(),
                        $mainEffort->getEffort()
                    );

                    break;
                }
            }

            // No match, just transfer to main affiliation
            if (!$matched) {
                $otherEffort->setAffiliation($this->getMainAffiliation());
                $this->getEntityManager()->persist($otherEffort);
                $this->getMainAffiliation()->getEffort()->add($otherEffort);
                $debugTemplate = 'Effort not found in main affiliation, moved from %s to %s';

                $this->debug[] = sprintf(
                    $debugTemplate,
                    $this->getOtherAffiliation()->getId(),
                    $this->getMainAffiliation()->getId()
                );
            }

            $this->getOtherAffiliation()->getEffort()->remove($otherKey);
        }
    }

    /**
     * Transfer the effort spent
     */
    protected function transferEffortSpent()
    {
        foreach ($this->getOtherAffiliation()->getSpent() as $otherKey => $otherSpent) {
            // Check whether the main affiliation has already an effort spent in the
            // same period for the same workpackage
            $matched = false;
            foreach ($this->getMainAffiliation()->getSpent() as &$mainSpent) {
                if ($otherSpent->getDateStart()->format('dmY') === $mainSpent->getDateStart()->format('dmY')
                    && $otherSpent->getDateEnd()->format('dmY') === $mainSpent->getDateEnd()->format('dmY')
                    && $otherSpent->getWorkpackage()->getId() === $mainSpent->getWorkpackage()->getId()
                ) {
                    $originalSpent = $mainSpent->getEffort();
                    $debugTemplate = '';
                    switch ($this->getCostAndEffortStrategy()) {
                        case self::STRATEGY_SUM:
                            $mainSpent->setEffort($originalSpent + $otherSpent->getEffort());
                            $this->getEntityManager()->persist($mainSpent);
                            $debugTemplate = 'Effort spent found and added %f (main) + %f (other) = %f';
                            break;
                        case self::STRATEGY_USE_MAIN: // Do nothing
                            $debugTemplate = 'Used main effort spent %f';
                            break;
                        case self::STRATEGY_USE_OTHER:
                            $mainSpent->setEffort($otherSpent->getEffort());
                            $this->getEntityManager()->persist($mainSpent);
                            $debugTemplate = 'Used other effort spent %2$f';
                            break;
                    }
                    $this->getEntityManager()->remove($otherSpent);
                    $matched = true;

                    $this->debug[] = sprintf(
                        $debugTemplate,
                        $originalSpent,
                        $otherSpent->getEffort(),
                        $mainSpent->getEffort()
                    );

                    break;
                }
            }

            // No match, just transfer to main affiliation
            if (!$matched) {
                $otherSpent->setAffiliation($this->getMainAffiliation());
                $this->getEntityManager()->persist($otherSpent);
                $this->getMainAffiliation()->getSpent()->add($otherSpent);
                $debugTemplate = 'Effort spent not found in main affiliation, moved from %s to %s';

                $this->debug[] = sprintf(
                    $debugTemplate,
                    $this->getOtherAffiliation()->getId(),
                    $this->getMainAffiliation()->getId()
                );
            }

            $this->getOtherAffiliation()->getSpent()->remove($otherKey);
        }
    }

    /**
     * Transfer the affiliation versions and underlying version costs
     */
    protected function transferAffiliationVersions()
    {
        // Iterate other affiliation versions to find a match with main on project version ID
        /** @var AffiliationVersion $otherAffiliationVersion */
        foreach ($this->getOtherAffiliation()->getVersion() as $otherKey => &$otherAffiliationVersion) {
            /** @var ProjectVersion $otherProjectVersion */
            $otherProjectVersion = $otherAffiliationVersion->getVersion();
            $matched = false;

            /** @var AffiliationVersion $mainAffiliationVersion */
            foreach ($this->getMainAffiliation()->getVersion() as &$mainAffiliationVersion) {
                // Matched with a main affiliation version. Transfer cost and effort and delete
                if ($mainAffiliationVersion->getVersion()->getId() === $otherProjectVersion->getId()) {
                    // Transfer cost versions
                    $this->transferVersionCost($mainAffiliationVersion, $otherAffiliationVersion);

                    // Transfer effort versions
                    $this->transferVersionEffort($mainAffiliationVersion, $otherAffiliationVersion);

                    // Remove leftover other affiliation version
                    $this->getEntityManager()->remove($otherAffiliationVersion);
                    $this->getOtherAffiliation()->getVersion()->remove($otherKey);

                    $matched = true;
                    break;
                }
            }

            // Not matched with main affiliation version, add it to main
            if (!$matched) {
                // No match with a main affiliation version, add it to main affiliation
                $otherAffiliationVersion->setAffiliation($this->getMainAffiliation());
                $this->getMainAffiliation()->getVersion()->add($otherAffiliationVersion);
                $this->getEntityManager()->persist($otherAffiliationVersion);
            }
        }
    }

    /**
     * Transfer/merge the version cost from $otherVersion to $mainVersion
     *
     * @param AffiliationVersion $mainVersion
     * @param AffiliationVersion $otherVersion
     */
    protected function transferVersionCost(AffiliationVersion $mainVersion, AffiliationVersion $otherVersion)
    {
        /** @var CostVersion $otherCostVersion */
        foreach ($otherVersion->getCostVersion() as $otherCostVersion) {
            $matched = false;
            /** @var CostVersion $mainCostVersion */
            foreach ($mainVersion->getCostVersion() as &$mainCostVersion) {
                // Check for a match with main cost version on start and end date
                if ($otherCostVersion->getDateStart()->format('dmY') === $mainCostVersion->getDateStart()
                        ->format('dmY')
                    && $otherCostVersion->getDateEnd()->format('dmY') === $mainCostVersion->getDateEnd()
                        ->format('dmY')
                ) {
                    switch ($this->getCostAndEffortStrategy()) {
                        case self::STRATEGY_SUM:
                            $mainCostVersion->setCosts($mainCostVersion->getCosts() + $otherCostVersion->getCosts());
                            $this->getEntityManager()->persist($mainCostVersion);
                            break;
                        case self::STRATEGY_USE_MAIN: // Do nothing
                            break;
                        case self::STRATEGY_USE_OTHER:
                            $mainCostVersion->setCosts($otherCostVersion->getCosts());
                            $this->getEntityManager()->persist($mainCostVersion);
                            break;
                    }
                    $this->getEntityManager()->remove($otherCostVersion);
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                // No match with a main affiliation version cost, add it to main version
                $otherCostVersion->setAffiliationVersion($mainVersion);
                $mainVersion->getCostVersion()->add($otherCostVersion);
                $this->getEntityManager()->persist($otherCostVersion);
            }
        }
    }

    /**
     * Transfer/merge the version effort from $otherVersion to $mainVersion
     *
     * @param AffiliationVersion $mainVersion
     * @param AffiliationVersion $otherVersion
     */
    protected function transferVersionEffort(AffiliationVersion $mainVersion, AffiliationVersion $otherVersion)
    {
        /** @var EffortVersion $otherCostVersion */
        foreach ($otherVersion->getEffortVersion() as $otherEffortVersion) {
            $matched = false;
            /** @var EffortVersion $mainCostVersion */
            foreach ($mainVersion->getEffortVersion() as &$mainEffortVersion) {
                // Check for a match with main effort version on start date, end date and workpackage
                if ($otherEffortVersion->getDateStart()->format('dmY') === $mainEffortVersion->getDateStart()
                        ->format('dmY')
                    && $otherEffortVersion->getDateEnd()->format('dmY') === $mainEffortVersion->getDateEnd()
                        ->format('dmY')
                    && $otherEffortVersion->getWorkpackage()->getId() === $mainEffortVersion->getWorkpackage()->getId()
                ) {
                    switch ($this->getCostAndEffortStrategy()) {
                        case self::STRATEGY_SUM:
                            $mainEffortVersion->setEffort($mainEffortVersion->getEffort()
                                + $otherEffortVersion->getEffort());
                            $this->getEntityManager()->persist($mainEffortVersion);
                            break;
                        case self::STRATEGY_USE_MAIN: // Do nothing
                            break;
                        case self::STRATEGY_USE_OTHER:
                            $mainEffortVersion->setEffort($otherEffortVersion->getEffort());
                            $this->getEntityManager()->persist($mainEffortVersion);
                            break;
                    }
                    $this->getEntityManager()->remove($otherEffortVersion);
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                // No match with a main affiliation version effort, add it to main version
                $otherEffortVersion->setAffiliationVersion($mainVersion);
                $mainVersion->getEffortVersion()->add($otherEffortVersion);
                $this->getEntityManager()->persist($otherEffortVersion);
            }
        }
    }

    /**
     * @return AdminService
     */
    public function getAdminService(): AdminService
    {
        return $this->adminService;
    }

    /**
     * @param AdminService $adminService
     *
     * @return MergeAffiliation
     */
    public function setAdminService(AdminService $adminService): MergeAffiliation
    {
        $this->adminService = $adminService;

        return $this;
    }
}
