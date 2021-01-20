<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
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
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Class MergeAffiliation
 *
 * @package Affiliation\Controller\Plugin
 */
final class MergeAffiliation extends AbstractPlugin
{
    // Cost and effort merge strategies
    public const STRATEGY_SUM = 0;       // Add other cost and effort to main
    public const STRATEGY_USE_MAIN = 1;  // Use cost and effort of main affiliation
    public const STRATEGY_USE_OTHER = 2; // Use cost and effort of other affiliation

    protected array $debug = [];
    protected AdminService $adminService;
    protected EntityManager $entityManager;
    private Affiliation $otherAffiliation;
    private Affiliation $mainAffiliation;
    private int $costAndEffortStrategy;

    public function __construct(AdminService $adminService, EntityManager $entityManager)
    {
        $this->adminService = $adminService;
        $this->entityManager = $entityManager;
    }

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
                $this->entityManager->persist($achievement);
                $mainAffiliation->getAchievement()->add($achievement);
                $otherAffiliation->getAchievement()->remove($key);
            }

            // Step 6: Move the cost changes
            foreach ($otherAffiliation->getChangeRequestCostChange() as $key => $costChange) {
                $costChange->setAffiliation([$mainAffiliation]);
                $this->entityManager->persist($costChange);
                $mainAffiliation->getChangeRequestCostChange()->add($costChange);
                $otherAffiliation->getChangeRequestCostChange()->remove($key);
            }

            // Step 7: Move the effort spent from the PPR
            foreach ($otherAffiliation->getProjectReportEffortSpent() as $key => $reportEffortSpent) {
                $reportEffortSpent->setAffiliation($mainAffiliation);
                $this->entityManager->persist($reportEffortSpent);
                $mainAffiliation->getProjectReportEffortSpent()->add($reportEffortSpent);
                $otherAffiliation->getProjectReportEffortSpent()->remove($key);
            }

            // Step 8: Move the dedicated project logs
            foreach ($otherAffiliation->getProjectLog() as $key => $projectLog) {
                $projectLog->getAffiliation()->add($mainAffiliation);
                $projectLog->getAffiliation()->removeElement($otherAffiliation);
                $this->entityManager->persist($projectLog);
                $mainAffiliation->getProjectLog()->add($projectLog);
                $otherAffiliation->getProjectLog()->remove($key);
            }

            // Step 9: Move the affiliation logs
            foreach ($otherAffiliation->getLog() as $key => $affiliationLog) {
                $affiliationLog->setAffiliation($mainAffiliation);
                $this->entityManager->persist($affiliationLog);
                $mainAffiliation->getLog()->add($affiliationLog);
                $otherAffiliation->getLog()->remove($key);
            }

            // Step 10: Move the invoices
            foreach ($otherAffiliation->getInvoice() as $key => $invoice) {
                $invoice->setAffiliation($mainAffiliation);
                $this->entityManager->persist($invoice);
                $mainAffiliation->getInvoice()->add($invoice);
                $otherAffiliation->getInvoice()->remove($key);
            }

            // Step 11: Move the associates
            foreach ($otherAffiliation->getAssociate() as $key => $associate) {
                if (! $mainAffiliation->getAssociate()->contains($associate)) {
                    $mainAffiliation->getAssociate()->add($associate);
                }
                $otherAffiliation->getAssociate()->remove($key);
            }

            // Step 12: Persist main affiliation, remove the other + flush and update permissions
            $this->entityManager->persist($mainAffiliation);
            $this->entityManager->remove($otherAffiliation);
            $this->entityManager->flush();
            $this->adminService->flushPermitsByEntityAndId(
                $mainAffiliation,
                $mainAffiliation->getId()
            );
        } catch (ORMException $e) {
            $response = ['success' => false, 'errorMessage' => $e->getMessage()];
        }

        return $response;
    }

    protected function transferCost(): void
    {
        foreach ($this->getOtherAffiliation()->getCost() as $otherKey => $otherCost) {
            // Check whether the main affiliation already has cost in the same period
            $matched = false;
            foreach ($this->getMainAffiliation()->getCost() as &$mainCost) {
                if (
                    $otherCost->getDateStart()->format('dmY') === $mainCost->getDateStart()->format('dmY')
                    && $otherCost->getDateEnd()->format('dmY') === $mainCost->getDateEnd()->format('dmY')
                ) {
                    switch ($this->getCostAndEffortStrategy()) {
                        case self::STRATEGY_SUM:
                            $mainCost->setCosts($mainCost->getCosts() + $otherCost->getCosts());
                            $this->entityManager->persist($mainCost);
                            break;
                        case self::STRATEGY_USE_MAIN: // Do nothing
                            break;
                        case self::STRATEGY_USE_OTHER:
                            $mainCost->setCosts($otherCost->getCosts());
                            $this->entityManager->persist($mainCost);
                            break;
                    }
                    $this->entityManager->remove($otherCost);
                    $matched = true;

                    break;
                }
            }

            // No match, just transfer to main affiliation
            if (! $matched) {
                $otherCost->setAffiliation($this->getMainAffiliation());
                $this->entityManager->persist($otherCost);
                $this->getMainAffiliation()->getCost()->add($otherCost);
            }

            $this->getOtherAffiliation()->getCost()->remove($otherKey);
        }
    }

    protected function getOtherAffiliation(): Affiliation
    {
        return $this->otherAffiliation;
    }

    protected function setOtherAffiliation(Affiliation $otherAffiliation): MergeAffiliation
    {
        $this->otherAffiliation = $otherAffiliation;

        return $this;
    }

    protected function getMainAffiliation(): Affiliation
    {
        return $this->mainAffiliation;
    }

    protected function setMainAffiliation(Affiliation $mainAffiliation): MergeAffiliation
    {
        $this->mainAffiliation = $mainAffiliation;

        return $this;
    }

    protected function getCostAndEffortStrategy(): int
    {
        return $this->costAndEffortStrategy;
    }

    protected function setCostAndEffortStrategy(int $costAndEffortStrategy): MergeAffiliation
    {
        $this->costAndEffortStrategy = $costAndEffortStrategy;

        return $this;
    }

    protected function transferEffort(): void
    {
        foreach ($this->getOtherAffiliation()->getEffort() as $otherKey => $otherEffort) {
            // Check whether the main affiliation already has effort in the same period for the same workpackage
            $matched = false;
            foreach ($this->getMainAffiliation()->getEffort() as &$mainEffort) {
                if (
                    $otherEffort->getDateStart()->format('dmY') === $mainEffort->getDateStart()->format('dmY')
                    && $otherEffort->getDateEnd()->format('dmY') === $mainEffort->getDateEnd()->format('dmY')
                    && $otherEffort->getWorkpackage()->getId() === $mainEffort->getWorkpackage()->getId()
                ) {
                    $originalEffort = $mainEffort->getEffort();
                    $debugTemplate = '';
                    switch ($this->getCostAndEffortStrategy()) {
                        case self::STRATEGY_SUM:
                            $mainEffort->setEffort($originalEffort + $otherEffort->getEffort());
                            $this->entityManager->persist($mainEffort);
                            $debugTemplate = 'Effort found and added %f (main) + %f (other) = %f';
                            break;
                        case self::STRATEGY_USE_MAIN: // Do nothing
                            $debugTemplate = 'Used main effort %f';
                            break;
                        case self::STRATEGY_USE_OTHER:
                            $mainEffort->setEffort($otherEffort->getEffort());
                            $this->entityManager->persist($mainEffort);
                            $debugTemplate = 'Used other effort %2$f';
                            break;
                    }
                    $this->entityManager->remove($otherEffort);
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
            if (! $matched) {
                $otherEffort->setAffiliation($this->getMainAffiliation());
                $this->entityManager->persist($otherEffort);
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

    protected function transferEffortSpent(): void
    {
        foreach ($this->getOtherAffiliation()->getSpent() as $otherKey => $otherSpent) {
            // Check whether the main affiliation has already an effort spent in the
            // same period for the same workpackage
            $matched = false;
            foreach ($this->getMainAffiliation()->getSpent() as &$mainSpent) {
                if (
                    $otherSpent->getDateStart()->format('dmY') === $mainSpent->getDateStart()->format('dmY')
                    && $otherSpent->getDateEnd()->format('dmY') === $mainSpent->getDateEnd()->format('dmY')
                    && $otherSpent->getWorkpackage()->getId() === $mainSpent->getWorkpackage()->getId()
                ) {
                    $originalSpent = $mainSpent->getEffort();
                    $debugTemplate = '';
                    switch ($this->getCostAndEffortStrategy()) {
                        case self::STRATEGY_SUM:
                            $mainSpent->setEffort($originalSpent + $otherSpent->getEffort());
                            $this->entityManager->persist($mainSpent);
                            $debugTemplate = 'Effort spent found and added %f (main) + %f (other) = %f';
                            break;
                        case self::STRATEGY_USE_MAIN: // Do nothing
                            $debugTemplate = 'Used main effort spent %f';
                            break;
                        case self::STRATEGY_USE_OTHER:
                            $mainSpent->setEffort($otherSpent->getEffort());
                            $this->entityManager->persist($mainSpent);
                            $debugTemplate = 'Used other effort spent %2$f';
                            break;
                    }
                    $this->entityManager->remove($otherSpent);
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
            if (! $matched) {
                $otherSpent->setAffiliation($this->getMainAffiliation());
                $this->entityManager->persist($otherSpent);
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

    protected function transferAffiliationVersions(): void
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
                    $this->entityManager->remove($otherAffiliationVersion);
                    $this->getOtherAffiliation()->getVersion()->remove($otherKey);

                    $matched = true;
                    break;
                }
            }

            // Not matched with main affiliation version, add it to main
            if (! $matched) {
                // No match with a main affiliation version, add it to main affiliation
                $otherAffiliationVersion->setAffiliation($this->getMainAffiliation());
                $this->getMainAffiliation()->getVersion()->add($otherAffiliationVersion);
                $this->entityManager->persist($otherAffiliationVersion);
            }
        }
    }

    protected function transferVersionCost(AffiliationVersion $mainVersion, AffiliationVersion $otherVersion): void
    {
        /** @var CostVersion $otherCostVersion */
        foreach ($otherVersion->getCostVersion() as $otherCostVersion) {
            $matched = false;
            /** @var CostVersion $mainCostVersion */
            foreach ($mainVersion->getCostVersion() as &$mainCostVersion) {
                // Check for a match with main cost version on start and end date
                if (
                    $otherCostVersion->getDateStart()->format('dmY') === $mainCostVersion->getDateStart()
                        ->format('dmY')
                    && $otherCostVersion->getDateEnd()->format('dmY') === $mainCostVersion->getDateEnd()
                        ->format('dmY')
                ) {
                    switch ($this->getCostAndEffortStrategy()) {
                        case self::STRATEGY_SUM:
                            $mainCostVersion->setCosts($mainCostVersion->getCosts() + $otherCostVersion->getCosts());
                            $this->entityManager->persist($mainCostVersion);
                            break;
                        case self::STRATEGY_USE_MAIN: // Do nothing
                            break;
                        case self::STRATEGY_USE_OTHER:
                            $mainCostVersion->setCosts($otherCostVersion->getCosts());
                            $this->entityManager->persist($mainCostVersion);
                            break;
                    }
                    $this->entityManager->remove($otherCostVersion);
                    $matched = true;
                    break;
                }
            }

            if (! $matched) {
                // No match with a main affiliation version cost, add it to main version
                $otherCostVersion->setAffiliationVersion($mainVersion);
                $mainVersion->getCostVersion()->add($otherCostVersion);
                $this->entityManager->persist($otherCostVersion);
            }
        }
    }

    protected function transferVersionEffort(AffiliationVersion $mainVersion, AffiliationVersion $otherVersion): void
    {
        /** @var EffortVersion $otherCostVersion */
        foreach ($otherVersion->getEffortVersion() as $otherEffortVersion) {
            $matched = false;
            /** @var EffortVersion $mainCostVersion */
            foreach ($mainVersion->getEffortVersion() as &$mainEffortVersion) {
                // Check for a match with main effort version on start date, end date and workpackage
                if (
                    $otherEffortVersion->getDateStart()->format('dmY') === $mainEffortVersion->getDateStart()
                        ->format('dmY')
                    && $otherEffortVersion->getDateEnd()->format('dmY') === $mainEffortVersion->getDateEnd()
                        ->format('dmY')
                    && $otherEffortVersion->getWorkpackage()->getId() === $mainEffortVersion->getWorkpackage()->getId()
                ) {
                    switch ($this->getCostAndEffortStrategy()) {
                        case self::STRATEGY_SUM:
                            $mainEffortVersion->setEffort(
                                $mainEffortVersion->getEffort()
                                + $otherEffortVersion->getEffort()
                            );
                            $this->entityManager->persist($mainEffortVersion);
                            break;
                        case self::STRATEGY_USE_MAIN: // Do nothing
                            break;
                        case self::STRATEGY_USE_OTHER:
                            $mainEffortVersion->setEffort($otherEffortVersion->getEffort());
                            $this->entityManager->persist($mainEffortVersion);
                            break;
                    }
                    $this->entityManager->remove($otherEffortVersion);
                    $matched = true;
                    break;
                }
            }

            if (! $matched) {
                // No match with a main affiliation version effort, add it to main version
                $otherEffortVersion->setAffiliationVersion($mainVersion);
                $mainVersion->getEffortVersion()->add($otherEffortVersion);
                $this->entityManager->persist($otherEffortVersion);
            }
        }
    }
}
