<?php
/**
 * A derivative of the Search Object for use with the Solr course reserves index.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind
 * @package  SearchObject
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_search_object Wiki
 */
require_once 'sys/SearchObject/Base.php';

/**
 * A derivative of the Search Object for use with the Solr course reserves index.
 *
 * @category VuFind
 * @package  SearchObject
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_search_object Wiki
 */
class SearchObject_SolrReserves extends SearchObject_Base
{
    // SOLR QUERY
    // Parsed query
    private $_query = null;

    // Facets
    private $_facetLimit = 10;

    // HTTP method
    private $_method = HTTP_REQUEST_METHOD_POST;

    // Result
    private $_indexResult;

    // Index
    private $_indexEngine = null;

    // Used to pass hidden filter queries to Solr
    private $_hiddenFilters = array();

    // Index
    private $_index = null;

    // Field List
    private $_fields = 'score';

    /**
     * Constructor. Initialise some details about the server
     *
     * @access public
     */
    public function __construct()
    {
        // Standard logic from parent class:
        parent::__construct();

        global $configArray;

        // Connect to the index
        $this->_indexEngine = ConnectionManager::connectToIndex('SolrReserves');

        // Set up appropriate results action:
        $this->resultsModule = 'Search';
        $this->resultsAction = 'Reserves';

        // Set up basic and advanced search types; default to basic.
        $this->searchType = $this->basicSearchType = 'Reserves';

        // Get default facet settings
        $this->facetConfig = array();
        $this->recommendIni = 'reserves';

        // Load search preferences:
        $searchSettings = getExtraConfigArray('reserves');
        if (isset($searchSettings['General']['facet_limit'])) {
            $this->_facetLimit = $searchSettings['General']['facet_limit'];
        }
        if (isset($searchSettings['General']['default_handler'])) {
            $this->defaultIndex = $searchSettings['General']['default_handler'];
        }
        if (isset($searchSettings['General']['default_sort'])) {
            $this->defaultSort = $searchSettings['General']['default_sort'];
        }
        if (isset($searchSettings['Basic_Searches'])) {
            $this->basicTypes = $searchSettings['Basic_Searches'];
        }
        if (isset($searchSettings['Advanced_Searches'])) {
            $this->advancedTypes = $searchSettings['Advanced_Searches'];
        }
        if (isset($searchSettings['Autocomplete']['enabled'])) {
            $this->autocompleteStatus = $searchSettings['Autocomplete']['enabled'];
        }

        // Load sort preferences (or defaults if none in .ini file):
        if (isset($searchSettings['Sorting'])) {
            $this->sortOptions = $searchSettings['Sorting'];
        } else {
            $this->sortOptions = array(
                'instructor_str' => 'sort_instructor',
                'course_str'     => 'sort_course',
                'department_str' => 'sort_department'
            );
        }
    }

    /**
     * Initialise the object from the global
     *  search parameters in $_REQUEST.
     *
     * @return boolean
     * @access public
     */
    public function init()
    {
        // Call the standard initialization routine in the parent:
        parent::init();

        $this->initView();
        $this->initPage();
        $this->initSort();
        $this->initFilters();

        //********************
        // Basic Search logic
        return $this->initBasicSearch();
    }

    /**
     * Actually process and submit the search
     *
     * @param bool $returnIndexErrors Should we die inside the index code if we
     * encounter an error (false) or return it for access via the getIndexError()
     * method (true)?
     * @param bool $recommendations   Should we process recommendations along with
     * the search itself?
     *
     * @return object                 WorldCat result structure.
     * @access public
     */
    public function processSearch(
        $returnIndexErrors = false, $recommendations = false
    ) {
        // Our search has already been processed in init()
        $search = $this->searchTerms;

        // Build a recommendations module appropriate to the current search:
        if ($recommendations) {
            $this->initRecommendations();
        }

        // Build Query
        $query = $this->_indexEngine->buildQuery($search);
        if (PEAR::isError($query)) {
            return $query;
        }

        // Only use the query we just built if there isn't an override in place.
        if ($this->_query == null) {
            $this->_query = $query;
        }

        // Define Filter Query
        $filterQuery = $this->_hiddenFilters;
        foreach ($this->filterList as $field => $filter) {
            foreach ($filter as $value) {
                // Special case -- allow trailing wildcards:
                if (substr($value, -1) == '*') {
                    $filterQuery[] = "$field:$value";
                } else {
                    $filterQuery[] = "$field:\"$value\"";
                }
            }
        }

        // If we are only searching one field use the DisMax handler
        //    for that field. If left at null let solr take care of it
        if (count($search) == 1 && isset($search[0]['index'])) {
            $this->_index = $search[0]['index'];
        }

        // Build a list of facets we want from the index
        $facetSet = array();
        if (!empty($this->facetConfig)) {
            $facetSet['limit'] = $this->_facetLimit;
            foreach ($this->facetConfig as $facetField => $facetName) {
                $facetSet['field'][] = $facetField;
            }
        }

        // Get time before the query
        $this->startQueryTimer();

        // The "relevance" sort option is a VuFind reserved word; we need to make
        // this null in order to achieve the desired effect with Solr:
        $finalSort = ($this->sort == 'relevance') ? null : $this->sort;

        // The first record to retrieve:
        //  (page - 1) * limit = start
        $recordStart = ($this->page - 1) * $this->limit;
        $this->_indexResult = $this->_indexEngine->search(
            $this->_query,     // Query string
            $this->_index,     // DisMax Handler
            $filterQuery,      // Filter query
            $recordStart,      // Starting record
            $this->limit,      // Records per page
            $facetSet,         // Fields to facet on
            '',                // Spellcheck query
            '',                // Spellcheck dictionary
            $finalSort,        // Field to sort on
            $this->_fields,    // Fields to return
            $this->_method,    // HTTP Request method
            $returnIndexErrors // Include errors in response?
        );

        // Get time after the query
        $this->stopQueryTimer();

        // How many results were there?
        $this->resultsTotal = $this->_indexResult['response']['numFound'];

        // If extra processing is needed for recommendations, do it now:
        if ($recommendations && is_array($this->recommend)) {
            foreach ($this->recommend as $currentSet) {
                foreach ($currentSet as $current) {
                    $current->process();
                }
            }
        }

        // Return the result set
        return $this->_indexResult;
    }

    /**
     * Get error message from index response, if any.  This will only work if
     * processSearch was called with $returnIndexErrors set to true!
     *
     * @return mixed false if no error, error string otherwise.
     * @access public
     */
    public function getIndexError()
    {
        return isset($this->_indexResult['errors']) ?
            $this->_indexResult['errors'] : false;
    }

    /**
     * Turn the list of spelling suggestions into an array of urls
     *   for on-screen use to implement the suggestions.
     *
     * @return array Spelling suggestion data arrays
     * @access public
     */
    public function getSpellingSuggestions()
    {
        // Not currently supported.
        return array();
    }

    /**
     * Returns the stored list of facets for the last search
     *
     * @param array $filter         Array of field => on-screen description listing
     * all of the desired facet fields; set to null to get all configured values.
     * @param bool  $expandingLinks If true, we will include expanding URLs (i.e.
     * get all matches for a facet, not just a limit to the current search) in the
     * return array.
     *
     * @return array                Facets data arrays
     * @access public
     */
    public function getFacetList($filter = null, $expandingLinks = false)
    {
        // If there is no filter, we'll use all facets as the filter:
        if (is_null($filter)) {
            $filter = $this->facetConfig;
        }

        // Start building the facet list:
        $list = array();

        // If we have no facets to process, give up now
        if (!is_array($this->_indexResult['facet_counts']['facet_fields'])) {
            return $list;
        }

        // Loop through every field returned by the result set
        $validFields = array_keys($filter);
        $allFields = $this->_indexResult['facet_counts']['facet_fields'];
        foreach ($allFields as $field => $data) {
            // Skip filtered fields and empty arrays:
            if (!in_array($field, $validFields) || count($data) < 1) {
                continue;
            }
            // Initialize the settings for the current field
            $list[$field] = array();
            // Add the on-screen label
            $list[$field]['label'] = $filter[$field];
            // Build our array of values for this field
            $list[$field]['list']  = array();
            foreach ($data as $facet) {
                // Initialize the array of data about the current facet:
                $currentSettings = array();
                $currentSettings['value'] = $facet[0];
                $currentSettings['count'] = $facet[1];
                $currentSettings['isApplied'] = false;
                $currentSettings['url']
                    = $this->renderLinkWithFilter("$field:".$facet[0]);
                // If we want to have expanding links (all values matching the facet)
                // in addition to limiting links (filter current search with facet),
                // do some extra work:
                if ($expandingLinks) {
                    $currentSettings['expandUrl']
                        = $this->getExpandingFacetLink($field, $facet[0]);
                }
                // Is this field a current filter?
                if (in_array($field, array_keys($this->filterList))) {
                    // and is this value a selected filter?
                    if (in_array($facet[0], $this->filterList[$field])) {
                        $currentSettings['isApplied'] = true;
                    }
                }

                // Store the collected values:
                $list[$field]['list'][] = $currentSettings;
            }
        }
        return $list;
    }
}

?>
