<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * PHP Version 5
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   2004-2015 ITEA Office
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/affiliation for the canonical source repository
 */

namespace Affiliation\Controller\Plugin;

use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Version;

/**
 * Class MergeAffiliation
 * @package Affiliation\Controller\Plugin
 */
class MergeAffiliation extends AbstractPlugin
{
    // Cost and effort merge strategies
    const STRATEGY_SUM = 0;       // Add other cost and effort to main
    const STRATEGY_USE_MAIN = 1;  // Use cost and effort of main affiliation
    const STRATEGY_USE_OTHER = 2; // Use cost and effort of other affiliation

    /**
     * @var Affiliation
     */
    private $affiliation;

    /**
     * @var Affiliation
     */
    private $mainAffiliation;

    /**
     * @var int
     */
    private $costAndEffortStrategy;

    /**
     * @var array
     */
    private $mergedAffiliationVersions = [];

    /**
     * @var array
     */
    protected $debug = [];


    /**
     * MergeAffiliation magic invokable
     *
     * @param Affiliation $mainAffiliation
     * @param Affiliation $affiliation
     * @param int $costAndEffortStrategy
     *
     * @return bool
     */
    public function __invoke(
        Affiliation $mainAffiliation,
        Affiliation $affiliation,
        int $costAndEffortStrategy = self::STRATEGY_SUM
    ): bool
    {
        print("This is not working yet, effort is not correctly transferred");

        $this->setMainAffiliation($mainAffiliation);
        $this->setAffiliation($affiliation);
        $this->setCostAndEffortStrategy($costAndEffortStrategy);

        // Step 1: Transfer cost
        $this->transferCost();

        // Step 2: Transfer effort
        $this->transferEffort();

        // Step 3: Merge affiliation versions
        $this->mergeAffiliationVersions();

        // Step 4: Transfer version cost
        $this->transferVersionCost();

        // Step 5: Transfer version effort
        $this->transferVersionEffort();

        // Step 6: Remove the leftover versions
        foreach ($affiliation->getVersion() as $version) {
            $this->getAffiliationService()->removeEntity($version);
        }

        // Step 7: Move the achievements
        foreach ($affiliation->getAchievement() as $achievement) {
            $achievement->addAffiliation($mainAffiliation);
            $achievement->removeAffiliation($affiliation);
            $this->getProjectService()->updateEntity($achievement);
        }

        // Step 8: Move the cost changes
        foreach ($affiliation->getChangeRequestCostChange() as $costChange) {
            $costChange->setAffiliation($mainAffiliation);
            $this->getProjectService()->updateEntity($costChange);
        }

        // Step 9: Move the effort spent
        foreach ($affiliation->getSpent() as $effortSpent) {
            $effortSpent->setAffiliation($mainAffiliation);
            $this->getProjectService()->updateEntity($effortSpent);
        }

        // Step 10: Move the effort spent from the PPR
        foreach ($affiliation->getProjectReportEffortSpent() as $reportEffortSpent) {
            $reportEffortSpent->setAffiliation($mainAffiliation);
            $this->getProjectService()->updateEntity($reportEffortSpent);
        }

        // Step 11: Move the dedicated project logs
        foreach ($affiliation->getProjectLog() as $projectLog) {
            $projectLog->getAffiliation()->add($mainAffiliation);
            $projectLog->getAffiliation()->removeElement($affiliation);
            $this->getProjectService()->updateEntity($projectLog);
        }

        // Step 12: Move the invoices
        foreach ($affiliation->getInvoice() as $invoice) {
            $invoice->setAffiliation($mainAffiliation);
            $this->getAffiliationService()->updateEntity($invoice);
        }

        // Step 13: Move the associates
        foreach ($affiliation->getAssociate() as $associate) {
            if (! $mainAffiliation->getAssociate()->contains($associate)) {
                $mainAffiliation->getAssociate()->add($associate);
                $this->getAffiliationService()->updateEntity($mainAffiliation);
            }
        }

        // Step 14: Remove the merged affiliation
        $this->getAffiliationService()->removeEntity($affiliation);
    }




    /**
     * Transfer the cost
     */
    protected function transferCost()
    {
        if ($this->getCostAndEffortStrategy() !== self::STRATEGY_USE_MAIN) {
            foreach ($this->getAffiliation()->getCost() as $cost) {

                // We need to check if the $mainAffiliation has already a cost in the given period
                $found = false;
                foreach ($this->getMainAffiliation()->getCost() as $mainCost) {
                    if ($cost->getDateStart() == $mainCost->getDateStart()
                        && $cost->getDateEnd() == $mainCost->getDateEnd()
                    ) {
                        switch ($this->getCostAndEffortStrategy()) {
                            case self::STRATEGY_SUM:
                                $mainCost->setCosts($mainCost->getCosts() + $cost->getCosts());
                                $this->getProjectService()->updateEntity($mainCost);
                                break;
                            case self::STRATEGY_USE_MAIN: // Do nothing
                                break;
                            case self::STRATEGY_USE_OTHER:
                                $mainCost->setCosts($cost->getCosts());
                                $this->getProjectService()->updateEntity($mainCost);
                                break;
                        }
                        $this->getProjectService()->removeEntity($cost);
                        $found = true;

                        break;
                    }
                }

                // Not found in the original table, do a move or delete
                if (!$found) {
                    // Strictly use data of main affiliation, just delete the other cost
                    if ($this->getCostAndEffortStrategy() === self::STRATEGY_USE_MAIN) {
                        $this->getProjectService()->removeEntity($cost);
                    }
                    // We have no costs in the main affiliation, replace the affiliation
                    else {
                        $cost->setAffiliation($this->getMainAffiliation());
                        $this->getProjectService()->updateEntity($cost);
                    }
                }
            }
        }
    }

    /**
     * Transfer the effort
     */
    protected function transferEffort()
    {
        foreach ($this->getAffiliation()->getEffort() as $effort) {
            // We need to check if the $mainAffiliation has already a effort in the given period
            $found = false;
            foreach ($this->getMainAffiliation()->getEffort() as $mainEffort) {
                if ($effort->getDateStart() == $mainEffort->getDateStart()
                    && $effort->getDateEnd() == $mainEffort->getDateEnd()
                    && $effort->getWorkpackage()->getId() == $mainEffort->getWorkpackage()->getId()
                ) {
                    $originalEffort = $mainEffort->getEffort();
                    switch ($this->getCostAndEffortStrategy()) {
                        case self::STRATEGY_SUM:
                            $mainEffort->setEffort($originalEffort + $effort->getEffort());
                            $this->getProjectService()->updateEntity($mainEffort);
                            $debugTemplate = 'Effort found and added %f (main) + %f (other) = %f';
                            break;
                        case self::STRATEGY_USE_MAIN: // Do nothing
                            $debugTemplate = 'Used main effort %f';
                            break;
                        case self::STRATEGY_USE_OTHER:
                            $mainEffort->setEffort($effort->getEffort());
                            $this->getProjectService()->updateEntity($mainEffort);
                            $debugTemplate = 'Used other effort %2$f';
                            break;
                    }
                    $this->getProjectService()->removeEntity($effort);
                    $found = true;

                    $this->debug[] = sprintf(
                        $debugTemplate, $originalEffort, $effort->getEffort(), $mainEffort->getEffort()
                    );

                    break;
                }
            }

            // Not found in the original table, do a move
            if (! $found) {
                // Strictly use data of main affiliation, just delete the other cost
                if ($this->getCostAndEffortStrategy() === self::STRATEGY_USE_MAIN) {
                    $this->getProjectService()->removeEntity($effort);
                    $debugTemplate = 'Removed effort from other affiliation %s';
                }
                // We have no effort in the main affiliation, replace the affiliation
                else {
                    $effort->setAffiliation($this->getMainAffiliation());
                    $this->getProjectService()->updateEntity($effort);
                    $debugTemplate = 'Effort not found in main affiliation, moved from %s to %s';
                }

                $this->debug[] = sprintf(
                    $debugTemplate, $this->getAffiliation()->getId(), $this->getMainAffiliation()->getId()
                );
            }
        }
    }

    /**
     * Merge the affiliation versions
     */
    protected function mergeAffiliationVersions()
    {
        // We need to find the suited mainAffiliationVersion, which is the instance of the mainAffiliation for the given version
        $mergedAffiliationVersionList = [];
        $mainAffiliation = $this->getMainAffiliation();
        foreach ($this->getAffiliation()->getVersion() as $affiliationVersion) { // Affiliation is active in some versions
            // We have the version now, check which affiliation are also active in this version
            foreach ($affiliationVersion->getVersion()->getAffiliationVersion() as $affiliationVersionResult) {
                if ($affiliationVersionResult->getAffiliation()->getId() === $mainAffiliation->getId()) {
                    $mergedAffiliationVersionList[$affiliationVersion->getVersion()->getId()] = $affiliationVersionResult;
                }
            }
        }

        // Check if all versions are present (if the affiliation has all the partners) and if not, create it
        foreach ($this->getAffiliation()->getVersion() as $affiliationVersion) {
            if (! array_key_exists($affiliationVersion->getVersion()->getId(), $mergedAffiliationVersionList)) {
                $mainAffiliationVersion = new Version();
                $mainAffiliationVersion->setAffiliation($mainAffiliation);
                $mainAffiliationVersion->setContact($mainAffiliation->getContact());
                $mainAffiliationVersion->setVersion($affiliationVersion->getVersion());
                $mainAffiliationVersion = $this->getAffiliationService()->newEntity($mainAffiliationVersion);
                $mergedAffiliationVersionList[$affiliationVersion->getVersion()->getId()] = $mainAffiliationVersion;
            }
        }

        $this->setMergedAffiliationVersions($mergedAffiliationVersionList);
    }

    /**
     * Transfer the version cost
     */
    protected function transferVersionCost()
    {
        foreach ($this->getAffiliation()->getVersion() as $affiliationVersion) {

            $mergedAffiliationVersions = $this->getMergedAffiliationVersions();
            /** @var Version $mainAffiliationVersion */
            $mainAffiliationVersion = $mergedAffiliationVersions[$affiliationVersion->getVersion()->getId()];

            foreach ($affiliationVersion->getCostVersion() as $cost) {
                // We need to check if the $mainAffiliation has already a cost in the given period
                $found = false;
                foreach ($mainAffiliationVersion->getCostVersion() as $mainCost) {
                    if ($cost->getDateStart() == $mainCost->getDateStart()
                        && $cost->getDateEnd() == $mainCost->getDateEnd()
                    ) {
                        // We have a cost in the main affiliation, so we need to add the cost of the $cost
                        $mainCost->setCosts($mainCost->getCosts() + $cost->getCosts());
                        $this->getProjectService()->updateEntity($mainCost);
                        $this->getProjectService()->removeEntity($cost);
                        $found = true;
                    }
                }

                if (!$found) {
                    // We have no costs in the main affiliation, replace the affiliation
                    $cost->setAffiliationVersion($mainAffiliationVersion);
                    $this->getProjectService()->updateEntity($cost);
                }
            }
        }
    }

    /**
     * Transfer the version effort
     */
    protected function transferVersionEffort()
    {
        foreach ($this->getAffiliation()->getVersion() as $affiliationVersion) {

            $mergedAffiliationVersions = $this->getMergedAffiliationVersions();
            /** @var Version $mainAffiliationVersion */
            $mainAffiliationVersion = $mergedAffiliationVersions[$affiliationVersion->getVersion()->getId()];

            foreach ($affiliationVersion->getEffortVersion() as $effort) {
                // We need to check if the $mainAffiliation has already a effort in the given period
                $found = false;
                foreach ($mainAffiliationVersion->getEffortVersion() as $mainEffort) {
                    if ($effort->getDateStart() == $mainEffort->getDateStart()
                        && $effort->getDateEnd() == $mainEffort->getDateEnd()
                        && $effort->getWorkpackage()->getId() == $mainEffort->getWorkpackage()->getId()
                    ) {
                        // We have a effort in the main affiliation, so we need to add the effort of the $effort
                        $originalEffort = $mainEffort->getEffort();
                        $mainEffort->setEffort($originalEffort + $effort->getEffort());

                        $this->getProjectService()->updateEntity($mainEffort);
                        $this->getProjectService()->removeEntity($effort);
                        $found = true;

                        $this->debug[] = sprintf(
                            "Version-effort found and added %s + %s = %s",
                            $originalEffort, $effort->getEffort(), $mainEffort->getEffort()
                        );
                    }
                }

                if (! $found) {
                    // We have no efforts in the main affiliation, replace the affiliation
                    $effort->setAffiliationVersion($mainAffiliationVersion);
                    $this->getProjectService()->updateEntity($effort);

                    $this->debug[] = sprintf(
                        "Version-effort not found and added moved from %s to %s",
                        $effort->getAffiliationVersion()->getId(), $mainAffiliationVersion->getId()
                    );
                }
            }
        }
    }

    /**
     * @return Affiliation
     */
    protected function getAffiliation(): Affiliation
    {
        return $this->affiliation;
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return MergeAffiliation
     */
    protected function setAffiliation(Affiliation $affiliation): MergeAffiliation
    {
        $this->affiliation = $affiliation;
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
     * @return MergeAffiliation
     */
    protected function setCostAndEffortStrategy(int $costAndEffortStrategy): MergeAffiliation
    {
        $this->costAndEffortStrategy = $costAndEffortStrategy;
        return $this;
    }

    /**
     * @return array
     */
    protected function getMergedAffiliationVersions(): array
    {
        return $this->mergedAffiliationVersions;
    }

    /**
     * @param array $mergedAffiliationVersions
     * @return MergeAffiliation
     */
    protected function setMergedAffiliationVersions(array $mergedAffiliationVersions): MergeAffiliation
    {
        $this->mergedAffiliationVersions = $mergedAffiliationVersions;
        return $this;
    }
}
