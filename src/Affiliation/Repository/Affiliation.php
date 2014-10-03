<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Affiliation
 * @package     Repository
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Affiliation\Repository;

use Affiliation\Entity;
use Affiliation\Service\AffiliationService;
use Doctrine\ORM\EntityRepository;
use General\Entity\Country;
use InvalidArgumentException;
use Project\Entity\Project;
use Project\Entity\Version\Version;

/**
 * @category    Affiliation
 * @package     Repository
 */
class Affiliation extends EntityRepository
{
    /**
     * Returns the affiliations based on the which
     *
     * @param Project $project
     * @param int     $which
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
     * Returns the affiliations based on the which
     *
     * @param Version $version
     * @param int     $which
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

        /**
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
     * Returns the affiliations based on the which
     *
     * @param Version $version
     * @param Country $country
     * @param int     $which
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

        /**
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
     * Returns the affiliations based on the which and country
     *
     * @param Project $project
     * @param Country $country
     * @param int     $which
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
}
