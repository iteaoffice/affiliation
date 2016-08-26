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
     * <field name="organisation_sort" type="c_text_sort" indexed="true" stored="false" multiValued="false"/>
     * <copyField source="organisation" dest="organisation_sort"/>
     * <field name="organisation_group" type="string" indexed="true" stored="false" multiValued="false"/>
     * <copyField source="organisation" dest="organisation_group"/>
     * <field name="organisation_id" type="int" indexed="true" stored="true" omitNorms="true"/>
     * <field name="organisation_type" type="lowercase" indexed="true" stored="true" omitNorms="true"/>
     * <field name="organisation_type_group" type="string" indexed="true" stored="false" multiValued="false"/>
     * <copyField source="organisation_type" dest="organisation_type_group"/>
     * <field name="organisation_country" type="c_text" indexed="true" stored="true" omitNorms="true"/>
     * <field name="organisation_country_group" type="string" indexed="true" stored="false" multiValued="false"/>
     * <copyField source="organisation_country" dest="organisation_country_group"/>
     *
     * <field name="project" type="c_text" indexed="true" stored="true" omitNorms="true"/>
     * <field name="project_sort" type="c_text_sort" indexed="true" stored="false" multiValued="false"/>
     * <copyField source="project" dest="project_sort"/>
     * <field name="project_group" type="string" indexed="true" stored="false" multiValued="false"/>
     * <copyField source="project" dest="project_group"/>
     * <field name="project_id" type="int" indexed="true" stored="true" omitNorms="true"/>
     * <field name="project_number" type="int" indexed="true" stored="true" omitNorms="true"/>
     * <field name="project_title" type="text_en_splitting" indexed="true" stored="true"/>
     * <field name="project_status" type="lowercase" indexed="true" stored="true" omitNorms="true"/>
     * <field name="project_call" type="lowercase" indexed="true" stored="true" omitNorms="true"/>
     * <field name="project_call_id" type="int" indexed="true" stored="true" omitNorms="true"/>
     * <field name="project_program" type="lowercase" indexed="true" stored="true" omitNorms="true"/>
     * <field name="project_program_group" type="string" indexed="true" stored="false" multiValued="false"/>
     * <copyField source="project_program" dest="project_program_group"/>
     * <field name="project_call_group" type="string" indexed="true" stored="false" multiValued="false"/>
     * <copyField source="project_call" dest="project_call_group"/>
     * <field name="project_draft_cost" type="double" indexed="true" stored="true" omitNorms="true">
     * <field name="project_draft_effort" type="double" indexed="true" stored="true" omitNorms="true">
     * <field name="project_latest_version_id" type="int" indexed="true" stored="true" omitNorms="true"/>
     * <field name="project_latest_version_type" type="string" indexed="true" stored="true" omitNorms="true" multiValued="false"/>
     * <field name="project_latest_version_status" type="string" indexed="true" stored="true" omitNorms="true" multiValued="false"/>
     * <field name="project_latest_version_cost" type="double" indexed="true" stored="true" omitNorms="true"/>
     * <field name="project_latest_version_effort" type="double" indexed="true" stored="true" omitNorms="true"/>
     *
     * <field name="project_version_id" type="int" indexed="true" stored="true" omitNorms="true"/>
     * <field name="project_version_type" type="string" indexed="true" stored="true" omitNorms="true" multiValued="false"/>
     * <field name="project_version_type_id" type="int" indexed="true" stored="true" omitNorms="true"/>
     * <field name="project_version_status" type="string" indexed="true" stored="true" omitNorms="true" multiValued="false"/>
     * <field name="project_version_cost" type="double" indexed="true" stored="true" omitNorms="true"/>
     * <field name="project_version_effort" type="double" indexed="true" stored="true" omitNorms="true"/>
     *
     * <field name="contact" type="c_text" indexed="true" stored="true" omitNorms="true"/>
     * <field name="contact_sort" type="c_text_sort" indexed="true" stored="false" multiValued="false"/>
     * <copyField source="contact" dest="contact_sort"/>
     * <field name="contact_id" type="int" indexed="true" stored="true" omitNorms="true"/>
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
        $documents = [];
        $update = $this->getSolrClient()->createUpdate();

        $project = $affiliation->getProject();
        $projectService = $this->getProjectService();
        $versionService = $this->getVersionService();
        $contact = $affiliation->getContact();

        // Affiliation
        $affiliationDocument = $update->createDocument();
        $affiliationDocument->affiliation_id = $affiliation->getId();
        $affiliationDocument->date_created = $affiliation->getDateCreated()->format(static::DATE_SOLR);
        if (!is_null($affiliation->getDateEnd())) {
            $affiliationDocument->date_end = $affiliation->getDateEnd()->format(static::DATE_SOLR);
        }
        $descriptionMerged = '';
        foreach ($affiliation->getDescription() as $description) {
            $descriptionMerged .= $description->getDescription() . "\n\n";
        }
        $affiliationDocument->description = $descriptionMerged;
        $affiliationDocument->branch = $affiliation->getBranch();
        $affiliationDocument->value_chain = $affiliation->getValueChain();
        $affiliationDocument->market_access = $affiliation->getMarketAccess();
        $affiliationDocument->main_contribution = $affiliation->getMainContribution();
        if (!is_null($affiliation->getDateSelfFunded())) {
            $affiliationDocument->date_self_funded = $affiliation->getDateSelfFunded()->format(static::DATE_SOLR);
        }

        // Organisation
        $affiliationDocument->organisation = (string)$affiliation->getOrganisation();
        $affiliationDocument->organisation_id = $affiliation->getOrganisation()->getId();
        $affiliationDocument->organisation_type = (string)$affiliation->getOrganisation()->getType();
        $affiliationDocument->organisation_country = (string)$affiliation->getOrganisation()->getCountry();

        // Project
        $affiliationDocument->project = $project->getProject();
        $affiliationDocument->project_id = $project->getId();
        $affiliationDocument->project_number = $project->getNumber();
        $affiliationDocument->project_title = $project->getTitle();
        $affiliationDocument->project_status = $projectService->parseStatus($project);
        $affiliationDocument->project_call = (string)$project->getCall()->shortName();
        $affiliationDocument->project_call_id = $project->getCall()->getId();
        $affiliationDocument->project_program = (string)$project->getCall()->getProgram();
        $affiliationDocument->project_draft_cost = $projectService->findTotalCostByProject($project);
        $affiliationDocument->project_draft_effort = $projectService->findTotalEffortByProject($project);

        $latestVersion = $projectService->getLatestProjectVersion($project, null, null, true, false);
        if (!is_null($latestVersion)) {
            $affiliationDocument->project_latest_version_id = $latestVersion->getId();
            $affiliationDocument->project_latest_version_type = $latestVersion->getVersionType()->getType();
            $affiliationDocument->project_latest_version_status = $versionService->parseStatus($latestVersion);
            $affiliationDocument->project_latest_version_cost =
                $versionService->findTotalCostVersionByAffiliationAndVersion($affiliation, $latestVersion);
            $affiliationDocument->project_latest_version_effort =
                $versionService->findTotalEffortVersionByAffiliationAndVersion($affiliation, $latestVersion);
        }

        // Contact
        $affiliationDocument->contact = $contact->parseFullName();
        $affiliationDocument->contact_id = $contact->getId();
        $affiliationDocument->contact_email = $contact->getEmail();
        $contactVisitAddress = $this->getContactService()->getVisitAddress($contact);
        if (!is_null($contactVisitAddress)) {
            $affiliationDocument->contact_address = $contactVisitAddress->getAddress();
            $affiliationDocument->contact_zip = $contactVisitAddress->getZipCode();
            $affiliationDocument->contact_city = $contactVisitAddress->getCity();
            $affiliationDocument->contact_country = (string)$contactVisitAddress->getCountry();
        }

        // Iterate the project versions and create documents from them
        foreach ($project->getVersion() as $version) {
            // Clone the main document for all versions
            $affiliationDocumentClone = clone $affiliationDocument;

            // Versions
            $affiliationDocumentClone->id = sprintf('%s-$s', $affiliation->getId(), $version->getId());
            $affiliationDocumentClone->project_version_id = $version->getId();
            $affiliationDocumentClone->project_version_type = $version->getVersionType()->getType();
            $affiliationDocumentClone->project_version_type_id = $version->getVersionType()->getId();
            $affiliationDocumentClone->project_version_status = $versionService->parseStatus($version);
            $affiliationDocumentClone->project_version_effort =
                $versionService->findTotalEffortVersionByAffiliationAndVersion($affiliation, $version);
            $affiliationDocumentClone->project_version_cost =
                $versionService->findTotalCostVersionByAffiliationAndVersion($affiliation, $version);

            // Add the version to the documents
            $documents[] = $affiliationDocumentClone;
        }

        $update->addDocuments($documents);

        return $this->executeUpdateDocument($update);
    }

    /**
     * Update the current index and optionally clear all existing data.
     *
     * @param boolean $clear
     */
    public function updateIndex($clear = false)
    {
        $this->updateIndexWithCollection($this->getAffiliationService()->findAll(Affiliation::class), $clear);
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

        $this->getQuery()->setQuery(static::parseQuery($searchTerm, [
            'organisation',
            'contact',
            'project',
        ]));
        $hasTerm = !in_array($searchTerm, ['*','']);
        $hasSort = ($order !== '');

        if ($hasSort) {
            switch ($order) {
                case 'organisation_sort':
                case 'cost_draft':
                case 'effort_draft':
                case 'project_version_type':
                case 'project_version_status':
                case 'project_version_cost':
                case 'project_version_effort':
                case 'project_latest_version_type':
                case 'project_latest_version_cost':
                case 'project_latest_version_effort':
                case 'project_sort':
                case 'project_call':
                case 'contact_sort':
                    $this->getQuery()->addSort($order, $direction);
                    break;
                default:
                    $this->getQuery()->addSort('id', Query::SORT_DESC);
                    break;
            }
        }
        if($hasTerm){
            $this->getQuery()->addSort('score', Query::SORT_DESC);
        } else {
            $this->getQuery()->addSort('id', Query::SORT_DESC);
        }

        $facetSet = $this->getQuery()->getFacetSet();
        $facetSet->createFacetField('project_program')->setField('project_program')->setMinCount(1)
            ->setExcludes(['project_program']);
        $facetSet->createFacetField('project_call')->setField('project_call')->setMinCount(1)
            ->setExcludes(['project_call']);
        $facetSet->createFacetField('project_version_type')->setField('project_version_type')->setMinCount(1)
            ->setExcludes(['project_version_type']);
        $facetSet->createFacetField('project_latest_version_status')->setField('project_latest_version_status')->setMinCount(1)
            ->setExcludes(['project_latest_version_status']);
        $facetSet->createFacetField('organisation_type')->setField('organisation_type')->setMinCount(1)
            ->setExcludes(['organisation_type']);
        $facetSet->createFacetField('organisation_country_group')->setField('organisation_country_group')
            ->setMinCount(1)->setExcludes(['organisation_country_group']);

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
     *
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
     *
     * @return AffiliationSearchService
     */
    public function setProjectService(ProjectService $projectService)
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
     *
     * @return AffiliationSearchService
     */
    public function setVersionService(VersionService $versionService)
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
     *
     * @return AffiliationSearchService
     */
    public function setContactService(ContactService $contactService)
    {
        $this->contactService = $contactService;

        return $this;
    }
}
