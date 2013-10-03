<?php
/**
 * Japaveh Webdesign copyright message placeholder
 *
 * @category    Affiliation
 * @package     Repository
 * @author      Johan van der Heide <info@japaveh.nl>
 * @copyright   Copyright (c) 2004-2013 Japaveh Webdesign (http://japaveh.nl)
 */
namespace Affiliation\Repository;

use Doctrine\ORM\EntityRepository;
use InvalidArgumentException;

use Project\Entity\Project;

use Affiliation\Entity;
use Affiliation\Service\AffiliationService;

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
     * @param  int    $which
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

        return $qb->getQuery()->getResult();
    }
}
