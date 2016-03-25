<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * PHP Version 5
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   2004-2016 ITEA Office
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */

namespace Affiliation\Search\Service;

use Affiliation\Entity\Affiliation;
use Affiliation\Service\AffiliationService;
use Contact\Service\ContactService;
use Project\Entity\Version\Type;
use Project\Service\ProjectService;
use Project\Service\VersionService;
use Search\Service\AbstractSearchService;
use Solarium\QueryType\Select\Query\Query;

/**
 * Class AffiliationSearchService
 *
 * @package Affiliation\Search\Service
 */
class AffiliationSearchService extends AbstractSearchService
{
    const SOLR_CONNECTION = 'affiliation';

    /**
     * @var AffiliationService
     */
    protected $affiliationService;

    /**
     * @var ContactService
     */
    protected $contactService;

    /**
     * @var ProjectService
     */
    protected $projectService;

    /**
     * @var VersionService
     */
    protected $versionService;

    /**
     * Update or insert an affiliation
     *
     * @param Affiliation $affiliation
     *
     * <field name="id" type="string" indexed="true" stored="true" required="true" multiValued="false" />
     *
     * <field name="affiliation_id" type="int" indexed="true" stored="true" omitNorms="true"/>
     * <field name="date_created" type="date" indexed="true" stored="true"/>
     * <field name="date_end" type="date" indexed="true" stored="true"/>
     * <field name="description" type="text_en_splitting" indexed="true" stored="true"/>
     * <field name="branch" type="c_text" indexed="true" stored="true" omitNorms="true"/>
     * <field name="value_chain" type="lowercase" indexed="true" stored="true" omitNorms="true"/>
     * <field name="market_access" type="lowercase" indexed="true" stored="true" omitNorms="true"/>
     * <field name="main_contribution" type="lowercase" indexed="true" stored="true" omitNorms="true"/>
     * <field name="date_self_funded" type="date" indexed="true" stored="true"/>
     *
     * <field name="organisation" type="c_text" indexed="true" stored="true" omitNorms="true"/>
     * <field name="organisation_type" type="lowercase" indexed="true" stored="true" omitNorms="true"/>
     * <field name="organisation_country" type="c_text" indexed="true" stored="true" omitNorms="true"/>
     *
     * <field name="project" type="c_text" indexed="true" stored="true" omitNorms="true"/>
     * <field name="project_number" type="int" indexed="true" stored="true" omitNorms="true"/>
     * <field name="project_title" type="text_en_splitting" indexed="true" stored="true"/>
     * <field name="project_status" type="lowercase" indexed="true" stored="true" omitNorms="true"/>
     * <field name="project_latest_version_id" type="int" indexed="true" stored="true" omitNorms="true"/>
     * <field name="project_latest_version_type" type="lowercase" indexed="true" stored="true" omitNorms="true"/>
     *
     * <field name="cost_draft" type="double" indexed="true" stored="true" omitNorms="true"/>
     * <field name="cost_po" type="double" indexed="true" stored="true" omitNorms="true"/>
     * <field name="cost_fpp" type="double" indexed="true" stored="true" omitNorms="true"/>
     * <field name="cost_latest" type="double" indexed="true" stored="true" omitNorms="true"/>
     *
     * <field name="effort_draft" type="double" indexed="true" stored="true" omitNorms="true"/>
     * <field name="effort_po" type="double" indexed="true" stored="true" omitNorms="true"/>
     * <field name="effort_fpp" type="double" indexed="true" stored="true" omitNorms="true"/>
     * <field name="effort_latest" type="double" indexed="true" stored="true" omitNorms="true"/>
     *
     * <field name="contact" type="c_text" indexed="true" stored="true" omitNorms="true"/>
     * <field name="contact_email" type="lowercase" indexed="true" stored="true" omitNorms="true"/>
     * <field name="contact_address" type="c_text" indexed="true" stored="true" omitNorms="true"/>
     * <field name="contact_zip" type="lowercase" indexed="true" stored="true" omitNorms="true"/>
     * <field name="contact_city" type="c_text" indexed="true" stored="true" omitNorms="true"/>
     * <field name="contact_country" type="c_text" indexed="true" stored="true" omitNorms="true"/>
     *
     * @return \Solarium\Core\Query\Result\ResultInterface
     * @throws \Solarium\Exception\HttpException
     */
    public function updateDocument($affiliation)
    {
        $update = $this->getSolrClient()->createUpdate();

        // Affiliation
        $affiliationDocument = $update->createDocument();
        $affiliationDocument->id = $affiliation->getResourceId();
        $affiliationDocument->date_created = $affiliation->getDateCreated()->format(self::DATE_SOLR);
        if (!is_null($affiliation->getDateEnd())) {
            $affiliationDocument->date_end = $affiliation->getDateEnd()->format(self::DATE_SOLR);
        }
        $affiliationDocument->description = $affiliation->getDescription();
        $affiliationDocument->branch = $affiliation->getBranch();
        $affiliationDocument->value_chain = $affiliation->getValueChain();
        $affiliationDocument->market_access = $affiliation->getMarketAccess();
        $affiliationDocument->main_contribution = $affiliation->getMainContribution();
        if (!is_null($affiliation->getDateSelfFunded())) {
            $affiliationDocument->date_self_funded = $affiliation->getDateSelfFunded()->format(self::DATE_SOLR);
        }

        // Organisation
        $affiliationDocument->organisation = (string) $affiliation->getOrganisation();
        $affiliationDocument->organisation_type = (string) $affiliation->getOrganisation()->getType();
        $affiliationDocument->organisation_country = (string) $affiliation->getOrganisation()->getCountry();

        // Project
        /** @var ProjectService $projectService */
        $projectService = $this->getProjectService()->setProject($affiliation->getProject());
        $affiliationDocument->project = $projectService->getProject()->getProject();
        $affiliationDocument->project_number = $projectService->getProject()->getNumber();
        $affiliationDocument->project_title = $projectService->getProject()->getTitle();
        $affiliationDocument->project_status = $projectService->parseStatus();
        $latestApprovedVersion = $projectService->getLatestProjectVersion(null, null, false, true);
        if (!is_null($latestApprovedVersion)) {
            $affiliationDocument->project_latest_version_id = $latestApprovedVersion->getId();
            $affiliationDocument->project_latest_version_type = $latestApprovedVersion->getVersionType()->getType();
        }

        /** @var VersionService $versionService */
        $versionService = $this->getVersionService();
        $poVersionType = $versionService->findVersionTypeByType('po');
        $poVersion = $projectService->getLatestProjectVersion($poVersionType);
        $fppVersionType = $versionService->findVersionTypeByType('fpp');
        $fppVersion = $projectService->getLatestProjectVersion($fppVersionType);
        $excludeTypes = [Type::TYPE_PO, Type::TYPE_FPP];

        // Version cost
        $affiliationDocument->cost_draft = $projectService->findTotalCostByProject($projectService);
        if (!is_null($poVersion)) {
            $affiliationDocument->cost_po =
                $versionService->findTotalCostVersionByAffiliationAndVersion($affiliation, $poVersion);
        }
        if (!is_null($fppVersion)) {
            $affiliationDocument->cost_fpp =
                $versionService->findTotalCostVersionByAffiliationAndVersion($affiliation, $fppVersion);
        }
        if (!is_null($latestApprovedVersion) && !in_array((int) $latestApprovedVersion->getVersionType()->getId(), $excludeTypes)) {
            $affiliationDocument->cost_latest =
                $versionService->findTotalCostVersionByAffiliationAndVersion($affiliation, $latestApprovedVersion);
        }

        // Version effort
        $affiliationDocument->effort_draft = $projectService->findTotalEffortByProject($projectService);
        if (!is_null($poVersion)) {
            $affiliationDocument->effort_po =
                $versionService->findTotalEffortVersionByAffiliationAndVersion($affiliation, $poVersion);
        }
        if (!is_null($fppVersion)) {
            $affiliationDocument->effort_fpp =
                $versionService->findTotalEffortVersionByAffiliationAndVersion($affiliation, $fppVersion);
        }
        if (!is_null($latestApprovedVersion) && !in_array((int) $latestApprovedVersion->getVersionType()->getId(), $excludeTypes)) {
            $affiliationDocument->effort_latest =
                $versionService->findTotalEffortVersionByAffiliationAndVersion($affiliation, $latestApprovedVersion);
        }

        // Contact
        /** @var ContactService $contactService */
        $contactService = $this->getContactService()->setContact($affiliation->getContact());
        $affiliationDocument->contact = $contactService->parseFullName();
        $affiliationDocument->contact_email = $contactService->getContact()->getEmail();
        $contactAddress = $contactService->getVisitAddress()->getAddress();
        $affiliationDocument->contact_address = $contactAddress->getAddress();
        $affiliationDocument->contact_zip = $contactAddress->getZipCode();
        $affiliationDocument->contact_city = $contactAddress->getCity();
        $affiliationDocument->contact_country = (string) $contactAddress->getCountry();

        $update->addDocument($affiliationDocument);

        return $this->executeUpdateDocument($update);
    }

    /**
     * Update the current index and optionally clear all existing data.
     *
     * @param boolean $clear
     */
    public function updateIndex($clear = false)
    {
        $this->updateIndexWithCollection($this->getAffiliationService()->findAll('Affiliation'), $clear);
    }

    /**
     * @param string $searchTerm
     * @param string $order
     * @param string $direction
     *
     * @return AffiliationSearchService
     */
    public function setSearch($searchTerm, $order = '', $direction = Query::SORT_ASC)
    {
        $this->setQuery($this->getSolrClient()->createSelect());

        /*$this->getQuery()->setQuery(static::parseQuery($searchTerm, [
            'affiliation_title',
            'short_description',
            'affiliation_leader',
            'key_selling_points',
            'already_involved',
            'looking_for',
            'keyword'
        ]));

        switch ($order) {
            case 'title':
                $this->getQuery()->addSort('affiliation_title', $direction);
                break;
            case 'contact':
                $this->getQuery()->addSort('affiliation_leader', $direction);
                break;

            default:
                $this->getQuery()->addSort('affiliation_id', Query::SORT_DESC);
                break;
        }

        $facetSet = $this->getQuery()->getFacetSet();
        $facetSet->createFacetField('keyword')->setField('keyword')->setMinCount(1)->setExcludes(['keyword']);
        $facetSet->createFacetField('programcall')->setField('programcall')->setMinCount(1)
            ->setExcludes(['programcall']);*/

        return $this;
    }

    /**
     * @return AffiliationService
     */
    public function getAffiliationService()
    {
        return $this->affiliationService;
    }

    /**
     * @param AffiliationService $affiliationService
     * @return AffiliationSearchService
     */
    public function setAffiliationService(AffiliationService $affiliationService)
    {
        $this->affiliationService = $affiliationService;

        return $this;
    }

    /**
     * @return ProjectService
     */
    public function getProjectService()
    {
        return $this->projectService;
    }

    /**
     * @param ProjectService $projectService
     * @return AffiliationSearchService
     */
    public function setProjectService($projectService)
    {
        $this->projectService = $projectService;
        return $this;
    }

    /**
     * @return VersionService
     */
    public function getVersionService()
    {
        return $this->versionService;
    }

    /**
     * @param VersionService $versionService
     * @return AffiliationSearchService
     */
    public function setVersionService($versionService)
    {
        $this->versionService = $versionService;
        return $this;
    }

    /**
     * @return ContactService
     */
    public function getContactService()
    {
        return $this->contactService;
    }

    /**
     * @param ContactService $contactService
     * @return AffiliationSearchService
     */
    public function setContactService($contactService)
    {
        $this->contactService = $contactService;
        return $this;
    }
}
