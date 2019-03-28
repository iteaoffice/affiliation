<?php
/**
 * ITEA Office all rights reserved
 *
 * @category  Affiliation
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Repository;

use Affiliation\Entity;
use Affiliation\Service\AffiliationService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use General\Entity\Country;
use InvalidArgumentException;
use Organisation\Entity\OParent;
use Organisation\Entity\Organisation;
use Program\Entity\Call\Call;
use Program\Entity\Program;
use Project\Entity\Project;
use Project\Entity\Version\Version;

/**
 * Class Affiliation
 *
 * @package Affiliation\Repository
 */
final class Affiliation extends EntityRepository
{
    public function findAffiliationByProjectAndWhich(Project $project, int $which): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('affiliation_entity_affiliation');
        $qb->from(Entity\Affiliation::class, 'affiliation_entity_affiliation');
        $qb->join('affiliation_entity_affiliation.organisation', 'organisation_entity_organisation');
        $qb->where('affiliation_entity_affiliation.project = ?1');
        $qb->setParameter(1, $project);
        switch ($which) {
            case AffiliationService::WHICH_ALL:
                break;
            case AffiliationService::WHICH_ONLY_ACTIVE:
                $qb->andWhere($qb->expr()->isNull('affiliation_entity_affiliation.dateEnd'));
                break;
            case AffiliationService::WHICH_ONLY_INACTIVE:
                $qb->andWhere($qb->expr()->isNotNull('affiliation_entity_affiliation.dateEnd'));
                break;
            default:
                throw new InvalidArgumentException(sprintf('Incorrect value (%s) for which', $which));
        }
        $qb->addOrderBy('organisation_entity_organisation.organisation', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findAffiliationByProjectAndWhichAndCriterion(Project $project, int $criterion, int $which): array
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select('affiliation_entity_affiliation');
        $queryBuilder->from(Entity\Affiliation::class, 'affiliation_entity_affiliation');
        $queryBuilder->join(
            'affiliation_entity_affiliation.parentOrganisation',
            'organisation_entity_parent_organisation'
        );
        $queryBuilder->join('organisation_entity_parent_organisation.parent', 'organisation_entity_parent');
        $queryBuilder->join('organisation_entity_parent.organisation', 'organisation_entity_organisation');

        $queryBuilder->where('affiliation_entity_affiliation.project = ?1');
        $queryBuilder->setParameter(1, $project);

        switch ($which) {
            case AffiliationService::WHICH_ALL:
                break;
            case AffiliationService::WHICH_ONLY_ACTIVE:
                $queryBuilder->andWhere($queryBuilder->expr()->isNull('affiliation_entity_affiliation.dateEnd'));
                break;
            case AffiliationService::WHICH_ONLY_INACTIVE:
                $queryBuilder->andWhere($queryBuilder->expr()->isNotNull('affiliation_entity_affiliation.dateEnd'));
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Incorrect value (%s) for which', $which));
        }

        switch ($criterion) {
            case OParent::CRITERION_C_CHAMBER:
                /** @var \Organisation\Repository\OParent $parentRepository */
                $parentRepository = $this->_em->getRepository(OParent::class);
                $queryBuilder = $parentRepository->limitCChambers($queryBuilder);
                break;
            case OParent::CRITERION_FREE_RIDER:
                /** @var \Organisation\Repository\OParent $parentRepository */
                $parentRepository = $this->_em->getRepository(OParent::class);
                $queryBuilder = $parentRepository->limitFreeRiders($queryBuilder, $project->getCall()->getProgram());
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Incorrect value (%s) for which', $which));
        }


        $queryBuilder->addOrderBy('organisation_entity_organisation.organisation', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }

    public function findNotValidatedSelfFundedAffiliation(): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('affiliation_entity_affiliation');
        $qb->from(Entity\Affiliation::class, 'affiliation_entity_affiliation');
        $qb->join('affiliation_entity_affiliation.project', 'project_entity_project');
        $qb->where('affiliation_entity_affiliation.selfFunded = ?1');
        $qb->andWhere($qb->expr()->isNull('affiliation_entity_affiliation.dateSelfFunded'));
        $qb->setParameter(1, Entity\Affiliation::SELF_FUNDED);

        $projectRepository = $this->_em->getRepository(Project::class);
        $qb = $projectRepository->onlyActiveProject($qb);

        return $qb->getQuery()->getResult();
    }

    public function findMissingAffiliationParent(): Query
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('affiliation_entity_affiliation');
        $qb->from(Entity\Affiliation::class, 'affiliation_entity_affiliation');
        $qb->join('affiliation_entity_affiliation.organisation', 'organisation_entity_organisation');

        $qb->addOrderBy('organisation_entity_organisation.organisation', 'ASC');
        $qb->andWhere($qb->expr()->isNull('affiliation_entity_affiliation.parentOrganisation'));

        return $qb->getQuery();
    }

    public function findAffiliationWithMissingDoa(): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('affiliation_entity_affiliation');
        $qb->from(Entity\Affiliation::class, 'affiliation_entity_affiliation');
        $qb->join('affiliation_entity_affiliation.organisation', 'organisation_entity_organisation');
        $qb->join('affiliation_entity_affiliation.project', 'project_entity_project');

        /**
         * @var $projectRepository \Project\Repository\Project
         */
        $projectRepository = $this->_em->getRepository(Project::class);
        $qb = $projectRepository->onlyActiveProject($qb);

        /*
         * Fetch the corresponding projects
         */
        $subSelect = $this->_em->createQueryBuilder();
        $subSelect->select('project');
        $subSelect->from(Project::class, 'project');
        $subSelect->join('project.call', 'call');
        $subSelect->andWhere('call.doaRequirement = :doaRequirement');
        $qb->andWhere($qb->expr()->in('affiliation_entity_affiliation.project', $subSelect->getDQL()));

        /*
         * Exclude the found DOAs the corresponding projects
         */
        $subSelect2 = $this->_em->createQueryBuilder();
        $subSelect2->select('affiliation');
        $subSelect2->from(Entity\Doa::class, 'doa');
        $subSelect2->join('doa.affiliation', 'affiliation');

        $qb->andWhere($qb->expr()->notIn('affiliation_entity_affiliation', $subSelect2->getDQL()));

        $qb->setParameter('doaRequirement', Call::DOA_REQUIREMENT_PER_PROJECT);

        //Exclude de-activated partners
        $qb->andWhere($qb->expr()->isNull('affiliation_entity_affiliation.dateEnd'));

        $qb->addOrderBy('organisation_entity_organisation.organisation', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findAffiliationWithMissingLoi(): Query
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('affiliation_entity_affiliation');
        $qb->from(Entity\Affiliation::class, 'affiliation_entity_affiliation');
        $qb->join('affiliation_entity_affiliation.organisation', 'organisation_entity_organisation');
        $qb->join('affiliation_entity_affiliation.project', 'project_entity_project');
        $qb->join('project_entity_project.call', 'program_entity_programcall');

        $projectRepository = $this->_em->getRepository(Project::class);
        $qb = $projectRepository->onlyActiveProject($qb);

        /*
         * Exclude the found LOIs the corresponding projects
         */
        $subSelect2 = $this->_em->createQueryBuilder();
        $subSelect2->select('affiliation');
        $subSelect2->from(Entity\Loi::class, 'loi');
        $subSelect2->join('loi.affiliation', 'affiliation');
        $qb->andWhere($qb->expr()->notIn('affiliation_entity_affiliation', $subSelect2->getDQL()));

        /*
         * Exclude the found LOIs the corresponding projects
         */
        $subSelect3 = $this->_em->createQueryBuilder();
        $subSelect3->select('affiliation2');
        $subSelect3->from(Entity\Doa::class, 'doa');
        $subSelect3->join('doa.affiliation', 'affiliation2');
        $qb->andWhere($qb->expr()->notIn('affiliation_entity_affiliation', $subSelect3->getDQL()));

        //Exclude de-activated partners
        $qb->andWhere($qb->expr()->isNull('affiliation_entity_affiliation.dateEnd'));
        $qb->andWhere($qb->expr()->eq('program_entity_programcall.loiRequirement', Call::LOI_REQUIRED));

        $qb->addOrderBy('organisation_entity_organisation.organisation', 'ASC');

        return $qb->getQuery();
    }

    public function findAffiliationByProjectVersionAndWhich(Version $version, int $which): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('affiliation_entity_affiliation');
        $qb->from(Entity\Affiliation::class, 'affiliation_entity_affiliation');
        $qb->join('affiliation_entity_affiliation.organisation', 'organisation_entity_organisation');
        switch ($which) {
            case AffiliationService::WHICH_ALL:
                break;
            case AffiliationService::WHICH_ONLY_ACTIVE:
                $qb->andWhere($qb->expr()->isNull('affiliation_entity_affiliation.dateEnd'));
                break;
            case AffiliationService::WHICH_ONLY_INACTIVE:
                $qb->andWhere($qb->expr()->isNotNull('affiliation_entity_affiliation.dateEnd'));
                break;
            default:
                throw new InvalidArgumentException(sprintf('Incorrect value (%s) for which', $which));
        }

        /*
         * Fetch the affiliations from the version
         */
        $subSelect = $this->_em->createQueryBuilder();
        $subSelect->select('affiliation');
        $subSelect->from(Entity\Version::class, 'affiliationVersion');
        $subSelect->join('affiliationVersion.affiliation', 'affiliation');
        $subSelect->andWhere('affiliationVersion.version = ?5');
        $qb->andWhere($qb->expr()->in('affiliation_entity_affiliation', $subSelect->getDQL()));

        $qb->setParameter(5, $version);

        $qb->addOrderBy('organisation_entity_organisation.organisation', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findAffiliationByParentAndProgramAndWhich(
        OParent $parent,
        Program $program,
        int $which,
        ?int $year
    ): array {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('affiliation_entity_affiliation');
        $qb->from(Entity\Affiliation::class, 'affiliation_entity_affiliation');
        $qb->join('affiliation_entity_affiliation.project', 'project_entity_project');
        $qb->join('project_entity_project.call', 'program_entity_call');

        switch ($which) {
            case AffiliationService::WHICH_ALL:
                break;
            case AffiliationService::WHICH_ONLY_ACTIVE:
            case AffiliationService::WHICH_INVOICING:
                $qb->andWhere($qb->expr()->isNull('affiliation_entity_affiliation.dateEnd'));
                break;
            case AffiliationService::WHICH_ONLY_INACTIVE:
                $qb->andWhere($qb->expr()->isNotNull('affiliation_entity_affiliation.dateEnd'));
                break;
            default:
                throw new InvalidArgumentException(sprintf('Incorrect value (%s) for which', $which));
        }

        $qb->join('affiliation_entity_affiliation.parentOrganisation', 'organisation_entity_parent_organisation');
        $qb->andWhere('organisation_entity_parent_organisation.parent = :parent');
        $qb->setParameter('parent', $parent);

        $qb->andWhere('program_entity_call.program = :program');
        $qb->setParameter('program', $program);

        if ($which === AffiliationService::WHICH_INVOICING) {
            $dateTime = \DateTime::createFromFormat('d-m-Y', '01-01-' . ($year - 4));
            $qb->andWhere('project_entity_project.dateStart > :dateTime');
            $qb->setParameter('dateTime', $dateTime);
        }

        $qb->addOrderBy('program_entity_call.id', 'ASC');
        $qb->addOrderBy('project_entity_project.docRef', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findAffiliationByProjectVersionAndCountryAndWhich(
        Version $version,
        Country $country,
        int $which
    ): array {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('affiliation_entity_affiliation');
        $qb->from(Entity\Affiliation::class, 'affiliation_entity_affiliation');
        $qb->join('affiliation_entity_affiliation.organisation', 'organisation_entity_organisation');
        $qb->andWhere('organisation_entity_organisation.country = ?2');
        $qb->setParameter(2, $country);
        switch ($which) {
            case AffiliationService::WHICH_ALL:
                break;
            case AffiliationService::WHICH_ONLY_ACTIVE:
                $qb->andWhere($qb->expr()->isNull('affiliation_entity_affiliation.dateEnd'));
                break;
            case AffiliationService::WHICH_ONLY_INACTIVE:
                $qb->andWhere($qb->expr()->isNotNull('affiliation_entity_affiliation.dateEnd'));
                break;
            default:
                throw new InvalidArgumentException(sprintf('Incorrect value (%s) for which', $which));
        }

        /*
         * Fetch the affiliations from the version
         */
        $subSelect = $this->_em->createQueryBuilder();
        $subSelect->select('affiliation');
        $subSelect->from(Entity\Version::class, 'affiliationVersion');
        $subSelect->join('affiliationVersion.affiliation', 'affiliation');
        $subSelect->andWhere('affiliationVersion.version = ?5');
        $qb->andWhere($qb->expr()->in('affiliation_entity_affiliation', $subSelect->getDQL()));

        $qb->setParameter(5, $version);

        $qb->addOrderBy('organisation_entity_organisation.organisation', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findAmountOfAffiliationByProjectVersionAndCountryAndWhich(
        Version $version,
        Country $country,
        int $which
    ): int {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('COUNT(affiliation_entity_affiliation.id) amount');
        $qb->from(Entity\Affiliation::class, 'affiliation_entity_affiliation');
        $qb->join('affiliation_entity_affiliation.organisation', 'organisation_entity_organisation');
        $qb->andWhere('organisation_entity_organisation.country = ?2');
        $qb->setParameter(2, $country);
        switch ($which) {
            case AffiliationService::WHICH_ALL:
                break;
            case AffiliationService::WHICH_ONLY_ACTIVE:
                $qb->andWhere($qb->expr()->isNull('affiliation_entity_affiliation.dateEnd'));
                break;
            case AffiliationService::WHICH_ONLY_INACTIVE:
                $qb->andWhere($qb->expr()->isNotNull('affiliation_entity_affiliation.dateEnd'));
                break;
            default:
                throw new InvalidArgumentException(sprintf('Incorrect value (%s) for which', $which));
        }

        /*
         * Fetch the affiliations from the version
         */
        $subSelect = $this->_em->createQueryBuilder();
        $subSelect->select('affiliation');
        $subSelect->from(Entity\Version::class, 'affiliationVersion');
        $subSelect->join('affiliationVersion.affiliation', 'affiliation');
        $subSelect->andWhere('affiliationVersion.version = ?5');
        $qb->andWhere($qb->expr()->in('affiliation_entity_affiliation', $subSelect->getDQL()));

        $qb->setParameter(5, $version);

        $qb->addGroupBy('affiliation_entity_affiliation.project');

        return (int)$qb->getQuery()->getOneOrNullResult()['amount'];
    }

    public function findAffiliationByProjectAndCountryAndWhich(Project $project, Country $country, int $which): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('affiliation_entity_affiliation');
        $qb->from(Entity\Affiliation::class, 'affiliation_entity_affiliation');
        $qb->join('affiliation_entity_affiliation.organisation', 'organisation_entity_organisation');
        $qb->where('affiliation_entity_affiliation.project = ?1');
        $qb->addOrderBy('organisation_entity_organisation.organisation', 'ASC');
        $qb->andWhere('organisation_entity_organisation.country = ?2');
        $qb->setParameter(1, $project);
        $qb->setParameter(2, $country);
        switch ($which) {
            case AffiliationService::WHICH_ALL:
                break;
            case AffiliationService::WHICH_ONLY_ACTIVE:
                $qb->andWhere($qb->expr()->isNull('affiliation_entity_affiliation.dateEnd'));
                break;
            case AffiliationService::WHICH_ONLY_INACTIVE:
                $qb->andWhere($qb->expr()->isNotNull('affiliation_entity_affiliation.dateEnd'));
                break;
            default:
                throw new InvalidArgumentException(sprintf('Incorrect value (%s) for which', $which));
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Organisation $organisation
     *
     * @deprecated
     * @return Entity\Affiliation[]
     */
    public function findAffiliationByOrganisation(Organisation $organisation): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('affiliation_entity_affiliation');
        $qb->from(Entity\Affiliation::class, 'affiliation_entity_affiliation');
        $qb->join('affiliation_entity_affiliation.project', 'project_entity_project');

        $qb->andWhere('affiliation_entity_affiliation.organisation = ?1');
        $qb->setParameter(1, $organisation);

        $qb->addOrderBy('project_entity_project.number', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function findAffiliationByOrganisationViaParentOrganisation(Organisation $organisation): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('affiliation_entity_affiliation');
        $qb->from(Entity\Affiliation::class, 'affiliation_entity_affiliation');
        $qb->join('affiliation_entity_affiliation.project', 'project_entity_project');
        $qb->join('affiliation_entity_affiliation.parentOrganisation', 'project_entity_parent_organisation');

        $qb->andWhere('project_entity_parent_organisation.organisation = ?1');
        $qb->setParameter(1, $organisation);

        $qb->addOrderBy('project_entity_project.number', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function findAmountOfAffiliationByProjectAndCountryAndWhich(
        Project $project,
        Country $country,
        int $which
    ): int {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('COUNT(affiliation_entity_affiliation) amount');
        $qb->from(Entity\Affiliation::class, 'affiliation_entity_affiliation');
        $qb->join('affiliation_entity_affiliation.organisation', 'organisation_entity_organisation');
        $qb->where('affiliation_entity_affiliation.project = ?1');
        $qb->addOrderBy('organisation_entity_organisation.organisation', 'ASC');
        $qb->andWhere('organisation_entity_organisation.country = ?2');
        $qb->setParameter(1, $project);
        $qb->setParameter(2, $country);
        switch ($which) {
            case AffiliationService::WHICH_ALL:
                break;
            case AffiliationService::WHICH_ONLY_ACTIVE:
                $qb->andWhere($qb->expr()->isNull('affiliation_entity_affiliation.dateEnd'));
                break;
            case AffiliationService::WHICH_ONLY_INACTIVE:
                $qb->andWhere($qb->expr()->isNotNull('affiliation_entity_affiliation.dateEnd'));
                break;
            default:
                throw new InvalidArgumentException(sprintf('Incorrect value (%s) for which', $which));
        }

        $qb->addGroupBy('affiliation_entity_affiliation.project');

        return (int)$qb->getQuery()->getOneOrNullResult()['amount'];
    }

    public function findAmountOfAffiliationByCountryAndCall(Country $country, Call $call): int
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('COUNT(affiliation_entity_affiliation) amount');
        $qb->from(Entity\Affiliation::class, 'affiliation_entity_affiliation');
        $qb->join('affiliation_entity_affiliation.organisation', 'organisation_entity_organisation');
        $qb->join('affiliation_entity_affiliation.project', 'project_entity_project');
        $qb->where('project_entity_project.call = ?1');
        $qb->andWhere('organisation_entity_organisation.country = ?2');
        $qb->addOrderBy('organisation_entity_organisation.organisation', 'ASC');
        $qb->setParameter(1, $call);
        $qb->setParameter(2, $country);
        $qb->addGroupBy('organisation_entity_organisation.country');

        return (int)$qb->getQuery()->getOneOrNullResult()['amount'];
    }

    public function findAffiliationInProjectLog(): array
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select(
            'affiliation_entity_affiliation',
            'project_entity_project',
            'organisation_entity_organisation'
        );
        $queryBuilder->from(Entity\Affiliation::class, 'affiliation_entity_affiliation');

        $queryBuilder->innerJoin("affiliation_entity_affiliation.projectLog", 'project_entity_log');
        $queryBuilder->innerJoin("affiliation_entity_affiliation.project", 'project_entity_project');
        $queryBuilder->innerJoin("affiliation_entity_affiliation.organisation", 'organisation_entity_organisation');

        return $queryBuilder->getQuery()->getArrayResult();
    }
}
