<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category Affiliation
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */

namespace Affiliation\Repository;

use Affiliation\Entity;
use Affiliation\Service\AffiliationService;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use General\Entity\Country;
use InvalidArgumentException;
use Program\Entity\Call\Call;
use Project\Entity\Project;
use Project\Entity\Version\Version;

/**
 * @category    Affiliation
 */
class Affiliation extends EntityRepository
{
    /**
     * Returns the affiliations based on the which.
     *
     * @param Project $project
     * @param int $which
     *
     * @throws InvalidArgumentException
     *
     * @return Entity\Affiliation[]
     */
    public function findAffiliationByProjectAndWhich(Project $project, $which)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a');
        $qb->from('Affiliation\Entity\Affiliation', 'a');
        $qb->join('a.organisation', 'o');
        $qb->where('a.project = ?1');
        $qb->setParameter(1, $project);
        switch ($which) {
            case AffiliationService::WHICH_ALL:
                break;
            case AffiliationService::WHICH_ONLY_ACTIVE:
                $qb->andWhere($qb->expr()->isNull('a.dateEnd'));
                break;
            case AffiliationService::WHICH_ONLY_INACTIVE:
                $qb->andWhere($qb->expr()->isNotNull('a.dateEnd'));
                break;
            default:
                throw new InvalidArgumentException(sprintf("Incorrect value (%s) for which", $which));
        }
        $qb->addOrderBy('o.organisation', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns a list of affiliations which do not have an DOA.
     *
     * @throws InvalidArgumentException
     *
     * @return Entity\Affiliation[]
     */
    public function findAffiliationWithMissingDoa()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a');
        $qb->from('Affiliation\Entity\Affiliation', 'a');
        $qb->join('a.organisation', 'o');
        $qb->join('a.project', 'p');

        /**
         * @var $projectRepository \Project\Repository\Project
         */
        $projectRepository = $this->getEntityManager()->getRepository('Project\Entity\Project');
        $qb = $projectRepository->onlyActiveProject($qb);

        /*
         * Fetch the corresponding projects
         */
        $subSelect = $this->_em->createQueryBuilder();
        $subSelect->select('project');
        $subSelect->from('Project\Entity\Project', 'project');
        $subSelect->join('project.call', 'call');
        $subSelect->andWhere('call.doaRequirement = :doaRequirement');
        $qb->andWhere($qb->expr()->in('a.project', $subSelect->getDQL()));

        /*
         * Exclude the found DOAs the corresponding projects
         */
        $subSelect2 = $this->_em->createQueryBuilder();
        $subSelect2->select('affiliation');
        $subSelect2->from('Affiliation\Entity\Doa', 'doa');
        $subSelect2->join('doa.affiliation', 'affiliation');


        $qb->andWhere($qb->expr()->notIn('a', $subSelect2->getDQL()));

        $qb->setParameter('doaRequirement', Call::DOA_REQUIREMENT_PER_PROJECT);

        //Exclude de-activated partners
        $qb->andWhere($qb->expr()->isNull('a.dateEnd'));

        $qb->addOrderBy('o.organisation', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns a list of affiliations which do not have an Loi.
     *
     * @throws InvalidArgumentException
     *
     * @return QueryBuilder
     */
    public function findAffiliationWithMissingLoi()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a');
        $qb->from('Affiliation\Entity\Affiliation', 'a');
        $qb->join('a.organisation', 'o');
        $qb->join('a.project', 'p');

        /**
         * @var $projectRepository \Project\Repository\Project
         */
        $projectRepository = $this->getEntityManager()->getRepository('Project\Entity\Project');
        $qb = $projectRepository->onlyActiveProject($qb);

        /*
         * Exclude the found LOIs the corresponding projects
         */
        $subSelect2 = $this->_em->createQueryBuilder();
        $subSelect2->select('affiliation');
        $subSelect2->from('Affiliation\Entity\Loi', 'loi');
        $subSelect2->join('loi.affiliation', 'affiliation');
        $qb->andWhere($qb->expr()->notIn('a', $subSelect2->getDQL()));

        //Exclude de-activated partners
        $qb->andWhere($qb->expr()->isNull('a.dateEnd'));

        $qb->addOrderBy('o.organisation', 'ASC');

        return $qb->getQuery();
    }

    /**
     * Returns the affiliations based on the which.
     *
     * @param Version $version
     * @param int $which
     *
     * @throws InvalidArgumentException
     *
     * @return Entity\Affiliation[]
     */
    public function findAffiliationByProjectVersionAndWhich(Version $version, $which)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a');
        $qb->from('Affiliation\Entity\Affiliation', 'a');
        $qb->join('a.organisation', 'o');
        switch ($which) {
            case AffiliationService::WHICH_ALL:
                break;
            case AffiliationService::WHICH_ONLY_ACTIVE:
                $qb->andWhere($qb->expr()->isNull('a.dateEnd'));
                break;
            case AffiliationService::WHICH_ONLY_INACTIVE:
                $qb->andWhere($qb->expr()->isNotNull('a.dateEnd'));
                break;
            default:
                throw new InvalidArgumentException(sprintf("Incorrect value (%s) for which", $which));
        }

        /*
         * Fetch the affiliations from the version
         */
        $subSelect = $this->_em->createQueryBuilder();
        $subSelect->select('affiliation');
        $subSelect->from('Affiliation\Entity\Version', 'affiliationVersion');
        $subSelect->join('affiliationVersion.affiliation', 'affiliation');
        $subSelect->andWhere('affiliationVersion.version = ?5');
        $qb->andWhere($qb->expr()->in('a', $subSelect->getDQL()));

        $qb->setParameter(5, $version);

        $qb->addOrderBy('o.organisation', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns the affiliations based on the which.
     *
     * @param Version $version
     * @param Country $country
     * @param int $which
     *
     * @throws InvalidArgumentException
     *
     * @return Entity\Affiliation[]
     */
    public function findAffiliationByProjectVersionAndCountryAndWhich(Version $version, Country $country, $which)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a');
        $qb->from('Affiliation\Entity\Affiliation', 'a');
        $qb->join('a.organisation', 'o');
        $qb->andWhere('o.country = ?2');
        $qb->setParameter(2, $country);
        switch ($which) {
            case AffiliationService::WHICH_ALL:
                break;
            case AffiliationService::WHICH_ONLY_ACTIVE:
                $qb->andWhere($qb->expr()->isNull('a.dateEnd'));
                break;
            case AffiliationService::WHICH_ONLY_INACTIVE:
                $qb->andWhere($qb->expr()->isNotNull('a.dateEnd'));
                break;
            default:
                throw new InvalidArgumentException(sprintf("Incorrect value (%s) for which", $which));
        }

        /*
         * Fetch the affiliations from the version
         */
        $subSelect = $this->_em->createQueryBuilder();
        $subSelect->select('affiliation');
        $subSelect->from('Affiliation\Entity\Version', 'affiliationVersion');
        $subSelect->join('affiliationVersion.affiliation', 'affiliation');
        $subSelect->andWhere('affiliationVersion.version = ?5');
        $qb->andWhere($qb->expr()->in('a', $subSelect->getDQL()));

        $qb->setParameter(5, $version);

        $qb->addOrderBy('o.organisation', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns the affiliations based on the which.
     *
     * @param Version $version
     * @param Country $country
     * @param int $which
     *
     * @throws InvalidArgumentException
     *
     * @return int
     */
    public function findAmountOfAffiliationByProjectVersionAndCountryAndWhich(
        Version $version,
        Country $country,
        $which
    ) {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('COUNT(a) amount');
        $qb->from('Affiliation\Entity\Affiliation', 'a');
        $qb->join('a.organisation', 'o');
        $qb->andWhere('o.country = ?2');
        $qb->setParameter(2, $country);
        switch ($which) {
            case AffiliationService::WHICH_ALL:
                break;
            case AffiliationService::WHICH_ONLY_ACTIVE:
                $qb->andWhere($qb->expr()->isNull('a.dateEnd'));
                break;
            case AffiliationService::WHICH_ONLY_INACTIVE:
                $qb->andWhere($qb->expr()->isNotNull('a.dateEnd'));
                break;
            default:
                throw new InvalidArgumentException(sprintf("Incorrect value (%s) for which", $which));
        }

        /*
         * Fetch the affiliations from the version
         */
        $subSelect = $this->_em->createQueryBuilder();
        $subSelect->select('affiliation');
        $subSelect->from('Affiliation\Entity\Version', 'affiliationVersion');
        $subSelect->join('affiliationVersion.affiliation', 'affiliation');
        $subSelect->andWhere('affiliationVersion.version = ?5');
        $qb->andWhere($qb->expr()->in('a', $subSelect->getDQL()));

        $qb->setParameter(5, $version);

        $qb->addOrderBy('o.organisation', 'ASC');
        $qb->addGroupBy('a.project');

        return (int)$qb->getQuery()->getOneOrNullResult()['amount'];
    }

    /**
     * Returns the affiliations based on the which and country.
     *
     * @param Project $project
     * @param Country $country
     * @param int $which
     *
     * @throws InvalidArgumentException
     *
     * @return Entity\Affiliation[]
     */
    public function findAffiliationByProjectAndCountryAndWhich(Project $project, Country $country, $which)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a');
        $qb->from('Affiliation\Entity\Affiliation', 'a');
        $qb->join('a.organisation', 'o');
        $qb->where('a.project = ?1');
        $qb->addOrderBy('o.organisation', 'ASC');
        $qb->andWhere('o.country = ?2');
        $qb->setParameter(1, $project);
        $qb->setParameter(2, $country);
        switch ($which) {
            case AffiliationService::WHICH_ALL:
                break;
            case AffiliationService::WHICH_ONLY_ACTIVE:
                $qb->andWhere($qb->expr()->isNull('a.dateEnd'));
                break;
            case AffiliationService::WHICH_ONLY_INACTIVE:
                $qb->andWhere($qb->expr()->isNotNull('a.dateEnd'));
                break;
            default:
                throw new InvalidArgumentException(sprintf("Incorrect value (%s) for which", $which));
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns the affiliations based on Project, which and country.
     *
     * @param Project $project
     * @param Country $country
     * @param int $which
     *
     * @throws InvalidArgumentException
     *
     * @return int
     */
    public function findAmountOfAffiliationByProjectAndCountryAndWhich(Project $project, Country $country, $which)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('COUNT(a) amount');
        $qb->from('Affiliation\Entity\Affiliation', 'a');
        $qb->join('a.organisation', 'o');
        $qb->where('a.project = ?1');
        $qb->addOrderBy('o.organisation', 'ASC');
        $qb->andWhere('o.country = ?2');
        $qb->setParameter(1, $project);
        $qb->setParameter(2, $country);
        switch ($which) {
            case AffiliationService::WHICH_ALL:
                break;
            case AffiliationService::WHICH_ONLY_ACTIVE:
                $qb->andWhere($qb->expr()->isNull('a.dateEnd'));
                break;
            case AffiliationService::WHICH_ONLY_INACTIVE:
                $qb->andWhere($qb->expr()->isNotNull('a.dateEnd'));
                break;
            default:
                throw new InvalidArgumentException(sprintf("Incorrect value (%s) for which", $which));
        }

        $qb->addGroupBy('a.project');

        return (int)$qb->getQuery()->getOneOrNullResult()['amount'];
    }

    /**
     * Returns the number of affiliations per Country and Call.
     *
     * @param Country $country
     * @param Call $call
     *
     * @throws InvalidArgumentException
     *
     * @return int
     */
    public function findAmountOfAffiliationByCountryAndCall(Country $country, Call $call)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('COUNT(a) amount');
        $qb->from('Affiliation\Entity\Affiliation', 'a');
        $qb->join('a.organisation', 'o');
        $qb->join('a.project', 'p');
        $qb->where('p.call = ?1');
        $qb->andWhere('o.country = ?2');
        $qb->addOrderBy('o.organisation', 'ASC');
        $qb->setParameter(1, $call);
        $qb->setParameter(2, $country);
        $qb->addGroupBy('o.country');

        return (int)$qb->getQuery()->getOneOrNullResult()['amount'];
    }
}
