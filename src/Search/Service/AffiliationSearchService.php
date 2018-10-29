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

declare(strict_types=1);

namespace Affiliation\Search\Service;

use Search\Service\AbstractSearchService;
use Search\Service\SearchServiceInterface;
use Solarium\QueryType\Select\Query\Query;

/**
 * Class AffiliationSearchService
 *
 * @package Affiliation\Search\Service
 */
final class AffiliationSearchService extends AbstractSearchService
{
    public const SOLR_CONNECTION = 'affiliation_affiliation';

    public function setSearch(
        string $searchTerm,
        array $searchFields = [],
        string $order = '',
        string $direction = Query::SORT_ASC
    ): SearchServiceInterface {
        $this->setQuery($this->getSolrClient()->createSelect());

        // Enable highlighting
        if ($searchTerm && ($searchTerm !== '*')) {
            $highlighting = $this->getQuery()->getHighlighting();
            $highlighting->setFields(
                [
                    'description',
                    'main_contribution',
                    'market_access',
                    'value_chain',
                    'strategic_importance'
                ]
            );
            $highlighting->setSimplePrefix('<mark>');
            $highlighting->setSimplePostfix('</mark>');
            $highlighting->setSnippets(10);
        }

        $this->getQuery()->setQuery(static::parseQuery($searchTerm, $searchFields));

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
}
