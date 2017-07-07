<?php
/**
 * ITEA Office all rights reserved
 *
 * PHP Version 7
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */

namespace Affiliation\Search\Service;

use Affiliation\Entity\Affiliation;
use Affiliation\Service\AffiliationService;
use Project\Service\ProjectService;
use Search\Service\AbstractSearchService;
use Solarium\QueryType\Select\Query\Query;

/**
 * Class AffiliationSearchService
 *
 * @package Affiliation\Search\Service
 */
final class AffiliationSearchService extends AbstractSearchService
{
    const SOLR_CONNECTION = 'affiliation';

    /**
     * @var AffiliationService
     */
    private $affiliationService;

    /**
     * @var ProjectService
     */
    private $projectService;

    /**
     * Update or insert an affiliation
     *
     * @param Affiliation $affiliation
     *
     * @return \Solarium\Core\Query\Result\ResultInterface
     * @throws \Solarium\Exception\HttpException
     */
    public function updateDocument($affiliation)
    {
        $update         = $this->getSolrClient()->createUpdate();
        $project        = $affiliation->getProject();
        $contact        = $affiliation->getContact();
        $now            = new \DateTime();

        // Affiliation
        $affiliationDocument = $update->createDocument();
        $affiliationDocument->id             = $affiliation->getResourceId();
        $affiliationDocument->affiliation_id = $affiliation->getId();
        $affiliationDocument->date_created   = $affiliation->getDateCreated()->format(static::DATE_SOLR);
        $affiliationDocument->is_active      = (is_null($affiliation->getDateEnd()) || ($affiliation->getDateEnd() > $now));

        $descriptionMerged = '';
        foreach ($affiliation->getDescription() as $description) {
            $descriptionMerged .= $description->getDescription() . "\n\n";
        }
        $affiliationDocument->description          = $descriptionMerged;
        $affiliationDocument->branch               = $affiliation->getBranch();
        $affiliationDocument->value_chain          = $affiliation->getValueChain();
        $affiliationDocument->market_access        = $affiliation->getMarketAccess();
        $affiliationDocument->main_contribution    = $affiliation->getMainContribution();
        $affiliationDocument->strategic_importance = $affiliation->getStrategicImportance();

        // Organisation
        $affiliationDocument->organisation         = (string)$affiliation->getOrganisation();
        $affiliationDocument->organisation_id      = $affiliation->getOrganisation()->getId();
        $affiliationDocument->organisation_type    = (string)$affiliation->getOrganisation()->getType();
        $affiliationDocument->organisation_country = (string)$affiliation->getOrganisation()->getCountry();

        // Project
        $affiliationDocument->project         = $project->getProject();
        $affiliationDocument->project_id      = $project->getId();
        $affiliationDocument->project_number  = $project->getNumber();
        $affiliationDocument->project_title   = $project->getTitle();
        $affiliationDocument->project_status  = $this->projectService->parseStatus($project);
        $affiliationDocument->project_call    = (string)$project->getCall()->shortName();
        $affiliationDocument->project_call_id = $project->getCall()->getId();
        $affiliationDocument->project_program = (string)$project->getCall()->getProgram();

        // Contact
        $affiliationDocument->contact    = $contact->parseFullName();
        $affiliationDocument->contact_id = $contact->getId();

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
        $this->updateIndexWithCollection($this->affiliationService->findAll(Affiliation::class), $clear);
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

        // Enable highligting
        if ($searchTerm && ($searchTerm !== '*')) {
            $highlighting = $this->getQuery()->getHighlighting();
            $highlighting->setFields([
                'description',
                'main_contribution',
                'market_access',
                'value_chain',
                'strategic_importance'
            ]);
            $highlighting->setSimplePrefix('<mark>');
            $highlighting->setSimplePostfix('</mark>');
        }

        $this->getQuery()->setQuery(static::parseQuery(
            $searchTerm,
            [
                'description',
                'main_contribution',
                'market_access',
                'value_chain',
                'strategic_importance',
                'project',
                'organisation',
            ]
        ));

        switch ($order) {
            case 'organisation_sort':
            case 'project_sort':
            case 'project_call':
            case 'contact_sort':
                $this->getQuery()->addSort($order, $direction);
                break;
            default:
                $this->getQuery()->addSort('id', Query::SORT_DESC);
                break;
        }

        $facetSet = $this->getQuery()->getFacetSet();
        $facetSet->createFacetField('is_active')->setField('is_active')->setMinCount(1)
            ->setExcludes(['is_active']);
        $facetSet->createFacetField('project_program')->setField('project_program')->setMinCount(1)
            ->setExcludes(['project_program']);
        $facetSet->createFacetField('project_call')->setField('project_call')->setMinCount(1)
            ->setExcludes(['project_call']);
        $facetSet->createFacetField('organisation_type')->setField('organisation_type')->setMinCount(1)
            ->setExcludes(['organisation_type']);
        $facetSet->createFacetField('organisation_country_group')->setField('organisation_country_group')
            ->setMinCount(1)->setExcludes(['organisation_country_group']);

        return $this;
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
     * @param AffiliationService $affiliationService
     *
     * @return AffiliationSearchService
     */
    public function setAffiliationService(AffiliationService $affiliationService)
    {
        $this->affiliationService = $affiliationService;

        return $this;
    }
}
