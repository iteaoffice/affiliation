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
use Affiliation\Service\AffiliationService;
use Contact\Service\ContactService;
use Project\Service\ProjectService;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\Controller\PluginManager;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Special plugin to produce an array with the evaluation.
 *
 * Class Checklist
 */
class MergeAffiliation extends AbstractPlugin implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface|PluginManager
     */
    protected $serviceLocator;
    /**
     * @var Affiliation
     */
    protected $affiliation;
    /**
     * @var Affiliation
     */
    protected $mainAffiliation;
    /**
     * @var array
     */
    protected $debug = [];


    /**
     * MergeAffiliation constructor.
     *
     * @param Affiliation $mainAffiliation
     * @param Affiliation $affiliation
     *
     * @return bool
     */
    public function __invoke(Affiliation $mainAffiliation, Affiliation $affiliation)
    {
        print("This is not working yet, effort is not correctly transferred");

        $this->setMainAffiliation($mainAffiliation);
        $this->setAffiliation($affiliation);


        //The merge is handled in several steps
        //Move the cost
        foreach ($affiliation->getCost() as $cost) {
            //We need to check if the $mainAffiliation has already a cost in the given period
            $found = false;

            foreach ($mainAffiliation->getCost() as $mainCost) {
                if ($cost->getDateStart() == $mainCost->getDateStart()
                    && $cost->getDateEnd() == $mainCost->getDateEnd()
                ) {
                    //We have a cost in the main affiliation, so we need to add the cost of the $cost
                    $mainCost->setCosts($mainCost->getCosts() + $cost->getCosts());
                    $this->getProjectService()->updateEntity($mainCost);
                    $this->getProjectService()->removeEntity($cost);
                    $found = true;
                }
            }


            //Not found in the original table, do a move
            if (!$found) {
                //We have no costs in the main affiliation, replace the affiliation
                $cost->setAffiliation($mainAffiliation);
                $this->getProjectService()->updateEntity($cost);
            }
        }

        //Move the effort
        foreach ($affiliation->getEffort() as $effort) {
            //We need to check if the $mainAffiliation has already a effort in the given period
            $found = false;
            foreach ($mainAffiliation->getEffort() as $mainEffort) {
                if ($effort->getDateStart() == $mainEffort->getDateStart()
                    && $effort->getDateEnd() == $mainEffort->getDateEnd()
                    && $effort->getWorkpackage()->getId() == $mainEffort->getWorkpackage()->getId()
                ) {
                    //We have a effort in the main affiliation, so we need to add the effort of the $effort
                    $originalEffort = $mainEffort->getEffort();
                    $mainEffort->setEffort($originalEffort + $effort->getEffort());
                    $this->getProjectService()->updateEntity($mainEffort);
                    $this->getProjectService()->removeEntity($effort);
                    $found = true;

                    $this->debug[] = sprintf(
                        "effort found and added %s + %s = %s",
                        $originalEffort,
                        $effort->getEffort(),
                        $mainEffort->getEffort()
                    );
                }
            }

            //Not found in the original table, do a move
            if (!$found) {
                //We have no efforts in the main affiliation, replace the affiliation
                $effort->setAffiliation($mainAffiliation);
                $this->getProjectService()->updateEntity($effort);


                $this->debug[] = sprintf(
                    "effort not found and added moved from %s to %s",
                    $affiliation->getId(),
                    $mainAffiliation->getId()
                );
            }
        }

        //We need to find the suited mainAffiliationVersion, which is the instance of the mainAffiliation for the given version
        $newAffiliationVersionList = [];
        foreach ($affiliation->getVersion() as $affiliationVersion) { //Affiliation is active in some versions
            //We have the version now, check which affiliation are also active in this version
            foreach ($affiliationVersion->getVersion()->getAffiliationVersion() as $affiliationVersionResult) {
                if ($affiliationVersionResult->getAffiliation()->getId() === $mainAffiliation->getId()) {
                    $newAffiliationVersionList[$affiliationVersion->getVersion()->getId()] = $affiliationVersionResult;
                }
            }
        }

        //Check if all versions are present (if the affiliation has all the partners) and if not, create it
        foreach ($affiliation->getVersion() as $affiliationVersion) {
            if (!array_key_exists($affiliationVersion->getVersion()->getId(), $newAffiliationVersionList)) {
                $mainAffiliationVersion = new Version();
                $mainAffiliationVersion->setAffiliation($mainAffiliation);
                $mainAffiliationVersion->setContact($mainAffiliation->getContact());
                $mainAffiliationVersion->setVersion($affiliationVersion->getVersion());
                $mainAffiliationVersion = $this->getAffiliationService()->newEntity($mainAffiliationVersion);
                $newAffiliationVersionList[$affiliationVersion->getVersion()->getId()] = $mainAffiliationVersion;
            }
        }


        //Move the cost in the version
        foreach ($affiliation->getVersion() as $affiliationVersion) {

            /** @var Version $mainAffiliationVersion */
            $mainAffiliationVersion = $newAffiliationVersionList[$affiliationVersion->getVersion()->getId()];

            foreach ($affiliationVersion->getCostVersion() as $cost) {

                //We need to check if the $mainAffiliation has already a cost in the given period
                $found = false;
                foreach ($mainAffiliationVersion->getCostVersion() as $mainCost) {
                    if ($cost->getDateStart() == $mainCost->getDateStart()
                        && $cost->getDateEnd() == $mainCost->getDateEnd()
                    ) {
                        //We have a cost in the main affiliation, so we need to add the cost of the $cost
                        $mainCost->setCosts($mainCost->getCosts() + $cost->getCosts());
                        $this->getProjectService()->updateEntity($mainCost);
                        $this->getProjectService()->removeEntity($cost);
                        $found = true;
                    }
                }


                if (!$found) {
                    //We have no costs in the main affiliation, replace the affiliation
                    $cost->setAffiliationVersion($mainAffiliationVersion);
                    $this->getProjectService()->updateEntity($cost);
                }
            }
        }

        //Move the effort in the version
        foreach ($affiliation->getVersion() as $affiliationVersion) {

            /** @var Version $mainAffiliationVersion */
            $mainAffiliationVersion = $newAffiliationVersionList[$affiliationVersion->getVersion()->getId()];

            foreach ($affiliationVersion->getEffortVersion() as $effort) {
                //We need to check if the $mainAffiliation has already a effort in the given period
                $found = false;
                foreach ($mainAffiliationVersion->getEffortVersion() as $mainEffort) {
                    if ($effort->getDateStart() == $mainEffort->getDateStart()
                        && $effort->getDateEnd() == $mainEffort->getDateEnd()
                        && $effort->getWorkpackage()->getId() == $mainEffort->getWorkpackage()->getId()
                    ) {
                        //We have a effort in the main affiliation, so we need to add the effort of the $effort
                        $originalEffort = $mainEffort->getEffort();
                        $mainEffort->setEffort($originalEffort + $effort->getEffort());

                        $this->getProjectService()->updateEntity($mainEffort);
                        $this->getProjectService()->removeEntity($effort);
                        $found = true;

                        $this->debug[] = sprintf(
                            "version-effort found and added %s + %s = %s",
                            $originalEffort,
                            $effort->getEffort(),
                            $mainEffort->getEffort()
                        );
                    }
                }

                if (!$found) {
                    //We have no efforts in the main affiliation, replace the affiliation
                    $effort->setAffiliationVersion($mainAffiliationVersion);
                    $this->getProjectService()->updateEntity($effort);

                    $this->debug[] = sprintf(
                        "version-effort not found and added moved from %s to %s",
                        $effort->getAffiliationVersion()->getId(),
                        $mainAffiliationVersion->getId()
                    );
                }
            }
        }

        //Remove the versions
        foreach ($affiliation->getVersion() as $version) {
            $this->getAffiliationService()->removeEntity($version);
        }

        //move the achievements
        foreach ($affiliation->getAchievement() as $achievement) {
            $achievement->addAffiliation($mainAffiliation);
            $achievement->removeAffiliation($affiliation);
            $this->getProjectService()->updateEntity($achievement);
        }

        //move the cost changes
        foreach ($affiliation->getChangeRequestCostChange() as $costChange) {
            $costChange->setAffiliation($mainAffiliation);
            $this->getProjectService()->updateEntity($costChange);
        }

        //move the effort spent
        foreach ($affiliation->getSpent() as $effortSpent) {
            $effortSpent->setAffiliation($mainAffiliation);
            $this->getProjectService()->updateEntity($effortSpent);
        }

        //move the effort spent from the PPR
        foreach ($affiliation->getProjectReportEffortSpent() as $reportEffortSpent) {
            $reportEffortSpent->setAffiliation($mainAffiliation);
            $this->getProjectService()->updateEntity($reportEffortSpent);
        }

        //move the dedicated project logs
        foreach ($affiliation->getProjectLog() as $projectLog) {
            $projectLog->getAffiliation()->add($mainAffiliation);
            $projectLog->getAffiliation()->removeElement($affiliation);
            $this->getProjectService()->updateEntity($projectLog);
        }

        //Move the invoices
        foreach ($affiliation->getInvoice() as $invoice) {
            $invoice->setAffiliation($mainAffiliation);
            $this->getAffiliationService()->updateEntity($invoice);
        }

        //Move the associates
        foreach ($affiliation->getAssociate() as $associate) {
            if (!$mainAffiliation->getAssociate()->contains($associate)) {
                $mainAffiliation->getAssociate()->add($associate);
                $this->getAffiliationService()->updateEntity($mainAffiliation);
            }
        }

        $this->getAffiliationService()->removeEntity($affiliation);
    }

    /**
     * Gateway to the Affiliation Service.
     *
     * @return AffiliationService
     */
    public function getAffiliationService()
    {
        return $this->getServiceLocator()->getServiceLocator()->get(AffiliationService::class);
    }

    /**
     * Gateway to the ContactService Service.
     *
     * @return ContactService
     */
    public function getContactService()
    {
        return $this->getServiceLocator()->getServiceLocator()->get(ContactService::class);
    }

    /**
     * Gateway to the Project Service.
     *
     * @return ProjectService
     */
    public function getProjectService()
    {
        return $this->getServiceLocator()->getServiceLocator()->get(ProjectService::class);
    }

    /**
     * @return ServiceLocatorInterface|PluginManager
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return $this
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * @return Affiliation
     */
    public function getAffiliation()
    {
        return $this->affiliation;
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return MergeAffiliation
     */
    public function setAffiliation($affiliation)
    {
        $this->affiliation = $affiliation;

        return $this;
    }

    /**
     * @return Affiliation
     */
    public function getMainAffiliation()
    {
        return $this->mainAffiliation;
    }

    /**
     * @param Affiliation $mainAffiliation
     *
     * @return MergeAffiliation
     */
    public function setMainAffiliation($mainAffiliation)
    {
        $this->mainAffiliation = $mainAffiliation;

        return $this;
    }
}
