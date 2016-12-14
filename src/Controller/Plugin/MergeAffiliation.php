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
use Affiliation\Entity\Version as AffiliationVersion;
use Program\Version\Version as ProjectVersion;
use Project\Entity\Cost\Version as CostVersion;
use Project\Entity\Effort\Version as EffortVersion;

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
     * @var array
     */
    protected $debug = [];


    /**
     * MergeAffiliation magic invokable
     *
     * @param Affiliation $mainAffiliation
     * @param Affiliation $otherAffiliation
     * @param int $costAndEffortStrategy
     *
     * @return Affiliation
     */
    public function __invoke(
        Affiliation $mainAffiliation,
        Affiliation $otherAffiliation,
        int $costAndEffortStrategy = self::STRATEGY_SUM
    ): Affiliation {
        //print("This is not working yet, effort is not correctly transferred");

        $this->setMainAffiliation($mainAffiliation);
        $this->setOtherAffiliation($otherAffiliation);
        $this->setCostAndEffortStrategy($costAndEffortStrategy);

        // Step 1: Transfer cost
        $this->transferCost();

        // Step 2: Transfer effort
        $this->transferEffort();

        // Step 3: Transfer affiliation versions incl. version cost and version effort
        $this->transferAffiliationVersions();

        // Step 4: Move the achievements
        /*foreach ($affiliation->getAchievement() as $achievement) {
            $achievement->addAffiliation($mainAffiliation);
            $achievement->removeAffiliation($affiliation);
            $this->getProjectService()->updateEntity($achievement);
        }*/

        // Step 5: Move the cost changes
        /*foreach ($affiliation->getChangeRequestCostChange() as $costChange) {
            $costChange->setAffiliation($mainAffiliation);
            $this->getProjectService()->updateEntity($costChange);
        }*/

        // Step 6: Move the effort spent
        /*foreach ($affiliation->getSpent() as $effortSpent) {
            $effortSpent->setAffiliation($mainAffiliation);
            $this->getProjectService()->updateEntity($effortSpent);
        }*/

        // Step 7: Move the effort spent from the PPR
        /*foreach ($affiliation->getProjectReportEffortSpent() as $reportEffortSpent) {
            $reportEffortSpent->setAffiliation($mainAffiliation);
            $this->getProjectService()->updateEntity($reportEffortSpent);
        }*/

        // Step 8: Move the dedicated project logs
        /*foreach ($affiliation->getProjectLog() as $projectLog) {
            $projectLog->getAffiliation()->add($mainAffiliation);
            $projectLog->getAffiliation()->removeElement($affiliation);
            $this->getProjectService()->updateEntity($projectLog);
        }*/

        // Step 9: Move the invoices
        /*foreach ($affiliation->getInvoice() as $invoice) {
            $invoice->setAffiliation($mainAffiliation);
            $this->getAffiliationService()->updateEntity($invoice);
        }*/

        // Step 10: Move the associates
        /*foreach ($affiliation->getAssociate() as $associate) {
            if (! $mainAffiliation->getAssociate()->contains($associate)) {
                $mainAffiliation->getAssociate()->add($associate);
                $this->getAffiliationService()->updateEntity($mainAffiliation);
            }
        }*/

        // Step 11: Remove the merged affiliation
        $this->getAffiliationService()->removeEntity($otherAffiliation);

        return $mainAffiliation;
    }


    /**
     * Transfer the cost
     */
    protected function transferCost()
    {
        foreach ($this->getOtherAffiliation()->getCost() as $otherKey => $otherCost) {
            // We need to check if the $mainAffiliation has already a cost in the given period
            $matched = false;
            foreach ($this->getMainAffiliation()->getCost() as &$mainCost) {
                if ($otherCost->getDateStart()->getTimestamp() === $mainCost->getDateStart()->getTimestamp()
                    && $otherCost->getDateEnd()->getTimestamp() === $mainCost->getDateEnd()->getTimestamp()
                ) {
                    switch ($this->getCostAndEffortStrategy()) {
                        case self::STRATEGY_SUM:
                            $mainCost->setCosts($mainCost->getCosts() + $otherCost->getCosts());
                            $this->getProjectService()->updateEntity($mainCost);
                            break;
                        case self::STRATEGY_USE_MAIN: // Do nothing
                            break;
                        case self::STRATEGY_USE_OTHER:
                            $mainCost->setCosts($otherCost->getCosts());
                            $this->getProjectService()->updateEntity($mainCost);
                            break;
                    }
                    $this->getProjectService()->removeEntity($otherCost);
                    $this->getOtherAffiliation()->getCost()->remove($otherKey);
                    $matched = true;

                    break;
                }
            }

            // Not found in the original table, do a move or delete
            if (!$matched) {
                $otherCost->setAffiliation($this->getMainAffiliation());
                $this->getProjectService()->updateEntity($otherCost);
                $this->getMainAffiliation()->getCost()->add($otherCost);
            }
        }
    }

    /**
     * Transfer the effort
     */
    protected function transferEffort()
    {
        foreach ($this->getOtherAffiliation()->getEffort() as $otherKey => $otherEffort) {
            // We need to check if the $mainAffiliation has already a effort in the given period
            $matched = false;
            foreach ($this->getMainAffiliation()->getEffort() as &$mainEffort) {
                if ($otherEffort->getDateStart()->getTimestamp() === $mainEffort->getDateStart()->getTimestamp()
                    && $otherEffort->getDateEnd()->->getTimestamp() === $mainEffort->getDateEnd()->getTimestamp()
                    && $otherEffort->getWorkpackage()->getId() === $mainEffort->getWorkpackage()->getId()
                ) {
                    $originalEffort = $mainEffort->getEffort();
                    switch ($this->getCostAndEffortStrategy()) {
                        case self::STRATEGY_SUM:
                            $mainEffort->setEffort($originalEffort + $otherEffort->getEffort());
                            $this->getProjectService()->updateEntity($mainEffort);
                            $debugTemplate = 'Effort found and added %f (main) + %f (other) = %f';
                            break;
                        case self::STRATEGY_USE_MAIN: // Do nothing
                            $debugTemplate = 'Used main effort %f';
                            break;
                        case self::STRATEGY_USE_OTHER:
                            $mainEffort->setEffort($otherEffort->getEffort());
                            $this->getProjectService()->updateEntity($mainEffort);
                            $debugTemplate = 'Used other effort %2$f';
                            break;
                    }
                    $this->getProjectService()->removeEntity($otherEffort);
                    $this->getOtherAffiliation()->getEffort()->remove($otherKey);
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

            // Not found in the original table, do a move
            if (!$matched) {
                $otherEffort->setAffiliation($this->getMainAffiliation());
                $this->getProjectService()->updateEntity($otherEffort);
                $this->getMainAffiliation()->getEffort()->add($otherEffort);
                $debugTemplate = 'Effort not found in main affiliation, moved from %s to %s';

                $this->debug[] = sprintf(
                    $debugTemplate,
                    $this->getOtherAffiliation()->getId(),
                    $this->getMainAffiliation()->getId()
                );
            }
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
                    $this->getAffiliationService()->removeEntity($otherAffiliationVersion);
                    $this->getOtherAffiliation()->getVersion()->remove($otherKey);

                    $matched = true;
                    break;
                }
            }

            // Not matched with main affiliation version, add it to main
            if (!$matched) {
                // No match with a main affiliation version, add it to main affiliation
                $otherAffiliationVersion->setAffiliation($this->getMainAffiliation());
                $this->getAffiliationService()->updateEntity($otherAffiliationVersion);
                $this->getMainAffiliation()->getVersion()->add($otherAffiliationVersion);
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
                if ($otherCostVersion->getDateStart()->getTimestamp() === $mainCostVersion->getDateStart()->getTimestamp()
                    && $otherCostVersion->getDateEnd()->getTimestamp() === $mainCostVersion->getDateEnd()->getTimestamp()
                ) {
                    switch ($this->getCostAndEffortStrategy()) {
                        case self::STRATEGY_SUM:
                            $mainCostVersion->setCosts($mainCostVersion->getCosts() + $otherCostVersion->getCosts());
                            $this->getProjectService()->updateEntity($mainCostVersion);
                            break;
                        case self::STRATEGY_USE_MAIN: // Do nothing
                            break;
                        case self::STRATEGY_USE_OTHER:
                            $mainCostVersion->setCosts($otherCostVersion->getCosts());
                            $this->getProjectService()->updateEntity($mainCostVersion);
                            break;
                    }
                    $this->getProjectService()->removeEntity($otherCostVersion);
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                // No match with a main affiliation version cost, add it to main version
                $otherCostVersion->setAffiliationVersion($mainVersion);
                $this->getProjectService()->updateEntity($otherCostVersion);
                $mainVersion->getCostVersion()->add($otherCostVersion);
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
                if ($otherEffortVersion->getDateStart()->getTimestamp() === $mainEffortVersion->getDateStart()->getTimestamp()
                    && $otherEffortVersion->getDateEnd()->getTimestamp() === $mainEffortVersion->getDateEnd()->getTimestamp()
                    && $otherEffortVersion->getWorkpackage()->getId() === $mainEffortVersion->getWorkpackage()->getId()
                ) {
                    switch ($this->getCostAndEffortStrategy()) {
                        case self::STRATEGY_SUM:
                            $mainEffortVersion->setEffort($mainEffortVersion->getEffort() + $otherEffortVersion->getEffort());
                            $this->getProjectService()->updateEntity($mainEffortVersion);
                            break;
                        case self::STRATEGY_USE_MAIN: // Do nothing
                            break;
                        case self::STRATEGY_USE_OTHER:
                            $mainEffortVersion->setEffort($otherEffortVersion->getEffort());
                            $this->getProjectService()->updateEntity($mainEffortVersion);
                            break;
                    }
                    $this->getProjectService()->removeEntity($otherEffortVersion);
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                // No match with a main affiliation version effort, add it to main version
                $otherEffortVersion->setAffiliationVersion($mainVersion);
                $this->getProjectService()->updateEntity($otherEffortVersion);
                $mainVersion->getEffortVersion()->add($otherEffortVersion);
            }
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
     * @return MergeAffiliation
     */
    protected function setCostAndEffortStrategy(int $costAndEffortStrategy): MergeAffiliation
    {
        $this->costAndEffortStrategy = $costAndEffortStrategy;
        return $this;
    }
}
