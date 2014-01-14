<?php
/**
 * Solr Search Object class
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
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_search_object Wiki
 */
require_once 'sys/Proxy_Request.php';   // needed for constant definitions
require_once 'sys/SearchObject/Base.php';
require_once 'RecordDrivers/Factory.php';

/**
 * Solr Search Object class
 *
 * This is the default implementation of the SearchObjectBase class, providing the
 * Solr-driven functionality used by VuFind's standard Search module.
 *
 * @category VuFind
 * @package  SearchObject
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_search_object Wiki
 */
class SearchObject_Solr extends SearchObject_Base
{
    // SOLR QUERY
    // Parsed query
    protected $query = null;
    // Facets
    protected $facetLimit = 30;
    protected $facetOffset = null;
    protected $facetPrefix = null;
    protected $facetSort = null;
    protected $sortedByIndex = null;

    // Parameter added by Frank Morgner
    // to change facet filter by one value
    protected $facetPart = null;
    protected $changeFacet = null;

    // Index
    protected $index = null;
    // Field List
    protected $fields = '*,score';
    // HTTP Method
    //protected $method = HTTP_REQUEST_METHOD_GET;
    protected $method = HTTP_REQUEST_METHOD_POST;
    // Result
    protected $indexResult;

    // OTHER VARIABLES
    // Index
    protected $indexEngine = null;
    // Facets information
    protected $allFacetSettings = array();    // loaded from facets.ini
    // Optional, used on author screen for example
    protected $searchSubType  = '';
    // Used to pass hidden filter queries to Solr
    protected $hiddenFilters = array();
    protected $defaultFilter = array();
    protected $defFilterFields = array();
    // Multiselect facets
    protected $multiSelectFacets = array();

    // Spelling
    protected $spellingLimit = 3;
    protected $dictionary    = 'default';
    protected $spellSimple   = false;
    protected $spellSkipNumeric = true;

    protected $stopwordlist = array();

    /**
     * Constructor. Initialise some details about the server
     *
     * @access public
     */
    public function __construct()
    {
        // Call base class constructor
        parent::__construct();

        global $configArray;
        global $user;

        $stopwordlist_csv = 'a,able,about,across,after,all,almost,also,am,among,an,and,any,are,as,at,be,because,been,but,by,can,cannot,could,dear,did,do,does,either,else,ever,every,for,from,get,got,had,has,have,he,her,hers,him,his,how,however,i,if,in,into,is,it,its,just,least,let,like,likely,may,me,might,most,must,my,neither,no,nor,not,of,off,often,on,only,or,other,our,own,rather,said,say,says,she,should,since,so,some,than,that,the,their,them,then,there,these,they,this,tis,to,too,twas,us,wants,was,we,were,what,when,where,which,while,who,whom,why,will,with,would,yet,you,your';
        $stopwordlist_de_csv = 'ab aber ähnlich alle allein allem aller alles allg allgemein als also am an and andere anderes auch auf aus außer been bei beim besonders bevor bietet bis bzw da dabei dadurch dafür daher dann daran darauf daraus das daß davon davor dazu dem den denen denn dennoch der derem deren des deshalb die dies diese diesem diesen dieser dieses doch dort durch eben ein eine einem einen einer eines einfach er es etc etwa etwas for für ganz ganze ganzem ganzen ganzer ganzes gar gleich gute hat hinter ihm ihr ihre ihrem ihren ihrer ihres im in ist ja je jede jedem jeden jeder jedes jene jenem jenen jener jenes jetzt kann kein keine keinem keinen keiner keines kommen kommt können leicht machen man mehr mehrere meist mit muß nach neu neue neuem neuen neuer neues nicht noch nur ob oder of ohne per schwierig sehr sein seinem seinen seiner seines seit selbst sich sie sind so sodaß solch solche solchem solchen solcher solches sollte sollten soviel sowohl statt über um und uns unser unsere unseren unseres unter viel viele vom von vor wann war was wenig wenige weniger wenn wer wie wieder wieviel wird wirklich wo wurde wurden zu zum zur zwischen';
        $stopwordlist_en_array = explode(',', $stopwordlist_csv);
        $stopwordlist_de_array = explode(' ', $stopwordlist_de_csv);
        $this->stopwordlist = array_merge($stopwordlist_en_array, $stopwordlist_de_array);
        // Initialise the index
        $this->indexEngine = ConnectionManager::connectToIndex();

        // Get default facet settings
        $this->allFacetSettings = getExtraConfigArray('facets');
        $this->facetConfig = array();
        $facetLimit = $this->getFacetSetting('Results_Settings', 'facet_limit');
        $this->multiSelectFacets = explode(',', $this->getFacetSetting(
            'Results_Settings', 'multiselect_facets'
        ));
        if (is_numeric($facetLimit)) {
            $this->facetLimit = $facetLimit;
        }
        $translatedFacets = $this->getFacetSetting(
            'Advanced_Settings', 'translated_facets'
        );
        if (is_array($translatedFacets)) {
            $this->translatedFacets = $translatedFacets;
        }
        // To allow sorting by index. #sortingindex
        if (is_array($this->getFacetSetting('SortedByIndex', 'index'))) {
            $this->sortedByIndex = $this->getFacetSetting(
                'SortedByIndex', 'index'
            );
        }
        // To hidden single facets topic from a facet. #hidefacettopics
        if (is_array($this->getFacetSetting('HideFacetTopics'))) {
            foreach ($this->getFacetSetting('HideFacetTopics') as $key => $value) {
                $this->hideFacetTopics[$key] = (explode(',', $value));
            }
        }
        // End

        // Load search preferences:
        $iniName = 'searches';
        if (in_array('Primo Central', $_SESSION['shards']) === true) $iniName = 'searches_primocentral';
        $searchSettings = getExtraConfigArray($iniName);
        if (isset($searchSettings['General']['default_handler'])) {
            $this->defaultIndex = $searchSettings['General']['default_handler'];
        }
        if (isset($searchSettings['General']['default_sort'])) {
            $this->defaultSort = $searchSettings['General']['default_sort'];
        }
        if (isset($searchSettings['General']['default_view'])) {
            $this->defaultView = $searchSettings['General']['default_view'];
        }
        if (isset($searchSettings['General']['default_limit'])) {
            $this->defaultLimit = $searchSettings['General']['default_limit'];
        }
        if (isset($searchSettings['General']['retain_filters_by_default'])) {
            $this->retainFiltersByDefault
                = $searchSettings['General']['retain_filters_by_default'];
        }
        if (isset($searchSettings['DefaultSortingByType'])
            && is_array($searchSettings['DefaultSortingByType'])
        ) {
            $this->defaultSortByType = $searchSettings['DefaultSortingByType'];
        }
        if (isset($searchSettings['DefaultFilters'])) {
            foreach ($searchSettings['DefaultFilters'] as $defFilter) {
                $this->defaultFilter[] = $defFilter;
            }
        }
        if (isset($searchSettings['HiddenFilters'])) {
            foreach ($searchSettings['HiddenFilters'] as $field => $subfields) {
                $this->addHiddenFilter($field.':'.'"'.$subfields.'"');
            }
        }
        if (isset($searchSettings['RawHiddenFilters'])) {
            foreach ($searchSettings['RawHiddenFilters'] as $rawFilter) {
                $this->addHiddenFilter($rawFilter);
            }
        }
        if (isset($searchSettings['DefaultFilterFields'])) {
            foreach ($searchSettings['DefaultFilterFields'] as $defFilterField) {
                $this->defFilterFields[] = $defFilterField;
            }
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
        // Add by Oliver Goldschmidt to read usage of AuthorityData
        if (isset($searchSettings['AuthorityData']['authorityUsage'])) {
            $this->authorityUsage = $searchSettings['AuthorityData']['authorityUsage'];
        }

        // Load sort preferences (or defaults if none in .ini file):
        if (isset($searchSettings['Sorting'])) {
            $this->sortOptions = $searchSettings['Sorting'];
        } else {
            $this->sortOptions = array('relevance' => 'sort_relevance',
                'year' => 'sort_year', 'year asc' => 'sort_year asc',
                'callnumber' => 'sort_callnumber', 'author' => 'sort_author',
                'title' => 'sort_title');
        }

        // Load view preferences (or defaults if none in .ini file):
        if (isset($searchSettings['Views'])) {
            $this->viewOptions = $searchSettings['Views'];
        } elseif (isset($searchSettings['General']['default_view'])) {
            $this->viewOptions = array($this->defaultView => $this->defaultView);
        } else {
            $this->viewOptions = array('list' => 'List');
        }

        // Load limit preferences (or defaults if none in .ini file):
        if (isset($searchSettings['General']['limit_options'])) {
            $this->limitOptions
                = explode(",", $searchSettings['General']['limit_options']);
        } elseif (isset($searchSettings['General']['default_limit'])) {
            $this->limitOptions = array($this->defaultLimit);
        } else {
            $this->limitOptions = array(20);
        }

        // Load Spelling preferences
        $this->spellcheck    = $configArray['Spelling']['enabled'];
        $this->spellingLimit = $configArray['Spelling']['limit'];
        $this->spellSimple   = $configArray['Spelling']['simple'];
        $this->spellSkipNumeric = isset($configArray['Spelling']['skip_numeric']) ?
            $configArray['Spelling']['skip_numeric'] : true;
    }

    /**
     * Add filters to the object based on values found in the $_REQUEST superglobal.
     *
     * @return void
     * @access protected
     */
    protected function initFilters()
    {
        // Use the default behavior of the parent class, but add support for the
        // special illustrations filter.
        parent::initFilters();
        if (isset($_REQUEST['illustration'])) {
            if ($_REQUEST['illustration'] == 1) {
                $this->addFilter('illustrated:Illustrated');
            } else if ($_REQUEST['illustration'] == 0) {
                $this->addFilter('illustrated:"Not Illustrated"');
            }
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
        global $module;
        global $action;

        // Call the standard initialization routine in the parent:
        parent::init();

        //********************
        // Check if we have a saved search to restore -- if restored successfully,
        // our work here is done; if there is an error, we should report failure;
        // if restoreSavedSearch returns false, we should proceed as normal.
        $restored = $this->restoreSavedSearch();
        if ($restored === true) {
            return true;
        } else if (PEAR::isError($restored)) {
            return false;
        }
        
        //********************
        // Initialize standard search parameters
        $this->initView();
        $this->initPage();
        $this->initSort();
        $this->initFilters();
        $this->initLimit();

        foreach ($this->defaultFilter as $rawFilter) {
            $this->addHiddenFilter($rawFilter);
        }
        //********************
        // Basic Search logic
        if ($this->initBasicSearch()) {
            // If we found a basic search, we don't need to do anything further.
        } else if (isset($_REQUEST['tag']) && $module != 'MyResearch') {
            // Tags, just treat them as normal searches for now.
            // The search processer knows what to do with them.
            if ($_REQUEST['tag'] != '') {
                $this->searchTerms[] = array(
                    'index'   => 'tag',
                    'lookfor' => $_REQUEST['tag']
                );
            }
        } else {
            $this->initAdvancedSearch();
        }

        //********************
        // Author screens - handled slightly differently
        if ($module == 'Author') {
            // *** Things in common to both screens
            // Log a special type of search
            $this->searchType = 'author';
            // We don't spellcheck this screen
            //   it's not for free user intput anyway
            $this->spellcheck  = false;

            // *** Author/Home
            if ($action == 'Home') {
                $this->searchSubType = 'home';
                // Remove our empty basic search (default)
                $this->searchTerms = array();
                // Prepare the search as a normal author search (with escaped quotes)
                $escapedAuthor = str_replace('"', '\"', $_REQUEST['author']);
                $this->searchTerms[] = array(
                    'index'   => 'Author',
                    // Force phrase search for improved accuracy:
                    'lookfor' => '"' . $escapedAuthor . '"'
                );
            }

            // *** Author/Search
            if ($action == 'Search') {
                $this->searchSubType = 'search';
                // We already have the 'lookfor', just set the index
                $this->searchTerms[0]['index'] = 'Author';
                // We really want author facet data
                $this->facetConfig = array();
                $this->addFacet('authorStr');
                // Offset the facet list by the current page of results, and
                // allow up to ten total pages of results -- since we can't
                // get a total facet count, this at least allows the paging
                // mechanism to automatically add more pages to the end of the
                // list so that users can browse deeper and deeper as they go.
                // TODO: Make this better in the future if Solr offers a way
                //       to get a total facet count (currently not possible).
                $this->facetOffset = ($this->page - 1) * $this->limit;
                $this->facetLimit = $this->limit * 10;
                // Sorting - defaults to off with unlimited facets, so let's
                //           be explicit here for simplicity.
                if (isset($_REQUEST['sort']) && ($_REQUEST['sort'] == 'author')) {
                    $this->setFacetSortOrder('index');
                } else {
                    $this->setFacetSortOrder('count');
                }
            }
        } else if ($module == 'Search'
            && ($action == 'NewItem' || $action == 'Reserves')
        ) {
            // We don't need spell checking
            $this->spellcheck = false;
            $this->searchType = strtolower($action);
            // Start:
            // New look up for multipart releated children
        } else if ($module == 'Search'
            && ($action == 'Results' && isset($_REQUEST['multipart']))
        ) {
            $this->spellcheck = false;
            $this->setQueryString('multipart_link:'.$_REQUEST['multipart']);
            // End
        } else if ($module == 'MyResearch') {
            $this->spellcheck = false;
            $this->searchType = ($action == 'Favorites') ? 'favorites' : 'list';
        } else if ($module == 'AJAX') {
            //special AJAX Search check if it's for MapInfo
            if ($action == 'ResultGoogleMapInfo') {
                $this->limitOptions = array(5);
                $this->initLimit();
                // We don't spellcheck this screen
                //it's not for free user intput anyway
                $this->spellcheck  = false;
                // Only get what's needed:
                $this->fields = array('id, title, author, format, issn' );
            }
        }
        //echo "<pre>"; print_r($this); echo "</pre>";
        // If a query override has been specified, log it here
        if (isset($_REQUEST['q'])) {
            $this->query = $_REQUEST['q'];
        }
        //  initalize new parameters
        if (isset($_REQUEST['facetPart'])) {
            $this->facetPart = $_REQUEST['facetPart'];
        }
        if (isset($_REQUEST['addFacet'])) {
            $this->changeFacet = $_REQUEST['addFacet'];
        }
        //echo "<pre>"; print_r($this->changeFacet); echo "</pre>";
        $this->setUserRestriction();

        return true;
    } // End init()

    /**
     * Initialise the object for retrieving advanced
     *   search screen facet data from inside solr.
     *
     * @return boolean
     * @access public
     */
    public function initAdvancedFacets()
    {
        // Call the standard initialization routine in the parent:
        parent::init();

        //********************
        // Adjust facet options to use advanced settings
        $this->facetConfig = isset($this->allFacetSettings['Advanced']) ?
            $this->allFacetSettings['Advanced'] : array();
        $facetLimit = $this->getFacetSetting('Advanced_Settings', 'facet_limit');
        if (is_numeric($facetLimit)) {
            $this->facetLimit = $facetLimit;
        }

        // Spellcheck is not needed for facet data!
        $this->spellcheck = false;

        //********************
        // Basic Search logic
        $this->searchTerms[] = array(
            'index'   => $this->defaultIndex,
            'lookfor' => ""
        );

        return true;
    }

    /**
     * Initialise the object for retrieving dynamic data
     *    for the browse screen to function.
     *
     * We don't know much at this stage, the browse AJAX
     *   calls need to supply the queries and facets.
     *
     * @return boolean
     * @access public
     */
    public function initBrowseScreen()
    {
        global $configArray;

        // Call the standard initialization routine in the parent:
        parent::init();

        $this->facetConfig = array();
        // Use the facet limit specified in config.ini (or default to 100):
        $this->facetLimit = isset($configArray['Browse']['result_limit']) ?
            $configArray['Browse']['result_limit'] : 100;
        // Sorting defaults to off with unlimited facets
        $this->setFacetSortOrder('count');

        // We don't need spell checking
        $this->spellcheck = false;

        //********************
        // Basic Search logic
        $this->searchTerms[] = array(
            'index'   => $this->defaultIndex,
            'lookfor' => ""
        );

        return true;
    }

    /**
     * Return the specified setting from the facets.ini file.
     *
     * @param string $section The section of the facets.ini file to look at.
     * @param string $setting The setting within the specified file to return.
     *
     * @return string         The value of the setting (blank if none).
     * @access public
     */
    public function getFacetSetting($section, $setting = null)
    {
        if (is_null($setting)) {
            return isset($this->allFacetSettings[$section]) ?
            $this->allFacetSettings[$section] : '';
        }
        return isset($this->allFacetSettings[$section][$setting]) ?
            $this->allFacetSettings[$section][$setting] : '';
    }

    /**
     * Used during repeated deminification (such as search history).
     *   To scrub fields populated above.
     *
     * @return void
     * @access private
     */
    protected function purge()
    {
        // Call standard purge:
        parent::purge();

        // Make some Solr-specific adjustments:
        $this->query        = null;
    }

    /**
     * Switch the spelling dictionary to basic
     *
     * @return void
     * @access public
     */
    public function useBasicDictionary()
    {
        $this->dictionary = 'basicSpell';
    }

    /**
     * Basic 'getter' for query string.
     *
     * @return string
     * @access public
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Basic 'getter' for index engine.
     *
     * @return object
     * @access public
     */
    public function getIndexEngine()
    {
        return $this->indexEngine;
    }

    /**
     * Return the field (index) searched by a basic search
     *
     * @return string The searched index
     * @access public
     */
    public function getSearchIndex()
    {
        // Use normal parent method for non-advanced searches.
        if ($this->searchType == $this->basicSearchType
            || $this->searchType == 'author'
        ) {
            return parent::getSearchIndex();
        } else {
            return null;
        }
    }

    /**
     * Use the record driver to build an array of HTML displays from the search
     * results suitable for use on a user's "favorites" page.
     *
     * @param object $user      User object owning tag/note metadata.
     * @param int    $listId    ID of list containing desired tags/notes (or
     * null to show tags/notes from all user's lists).
     * @param bool   $allowEdit Should we display edit controls?
     *
     * @return array            Array of HTML chunks for individual records.
     * @access public
     */
    public function getResultListHTML($user, $listId = null, $allowEdit = true)
    {
        global $interface;

        $html = array();
        for ($x = 0; $x < count($this->indexResult['response']['docs']); $x++) {
            $current = & $this->indexResult['response']['docs'][$x];
            $record = RecordDriverFactory::initRecordDriver($current);
            $html[] = $interface->fetch(
                $record->getListEntry($user, $listId, $allowEdit)
            );
        }
        return $html;
    }

    /**
     * Use the record driver to build an array of HTML displays from the search
     * results.
     *
     * @return array Array of HTML chunks for individual records.
     * @access public
     */
    public function getResultRecordHTML()
    {
        global $interface;

        $currentView = $this->getView();
        $html = array();
        for ($x = 0; $x < count($this->indexResult['response']['docs']); $x++) {
            $current = & $this->indexResult['response']['docs'][$x];
            $record = RecordDriverFactory::initRecordDriver($current);
            $html[] = $interface->fetch($record->getSearchResult($currentView));
        }
        return $html;
    }


    /**
     * Use the record driver to build an array with the VuFind is as keys.
     *
     * @return array Array of HTML chunks for individual records.
     * @access public
     */
    public function getResultIds()
    {
        $ids = array();
        for ($x = 0; $x < count($this->indexResult['response']['docs']); $x++) {
            $ids [($this->indexResult['response']['docs'][$x]['id'])] = 
                $this->indexResult['response']['docs'][$x];
        }
        return $ids;
    }



    /**
     * Set an overriding array of record IDs.
     *
     * @param array $ids Record IDs to load
     *
     * @return bool      True if all IDs can be loaded, false if boolean clause
     * limit is exceeded (in which case a partial list will still be loaded).
     * @access public
     */
    public function setQueryIDs($ids)
    {
        // Assume we will succeed:
        $retVal = true;

        // Limit the ID list if it exceeds the clause limit, and adjust the return
        // value to reflect the problem:
        $limit = $this->indexEngine->getBooleanClauseLimit();
        if (count($ids) > $limit) {
            $ids = array_slice($ids, 0, $limit);
            $retVal = false;
        }
        // Build the query:
        $this->query = 'id:(' . implode(' OR ', $ids) . ')';

        // Report success or failure:
        return $retVal;
    }

    /**
     * Set an overriding string.
     *
     * @param string $newQuery Query string
     *
     * @return void
     * @access public
     */
    public function setQueryString($newQuery)
    {
        $this->query = $newQuery;
    }

    /**
     * Set overriding filtering attributes to a query.
     *
     * @param array $fields Array with new fields to set. 
     *
     * @return void
     * @access public
     */
    public function setFilterFields($fields)
    {
        if (is_array($fields) && count($fields) > 0 ) {
            $this->fields = $fields;
        } else {
            $this->fields = array('score');
        }
    }

    /**
     * Set an overriding facet sort order.
     *
     * @param string $newSort Sort string
     *
     * @return void
     * @access public
     */
    public function setFacetSortOrder($newSort)
    {
        // As of Solr 1.4 valid values are:
        // 'count' = relevancy ranked
        // 'index' = index order, most likely alphabetical
        // more info : http://wiki.apache.org/solr/SimpleFacetParameters#facet.sort
        if ($newSort == 'count' || $newSort == 'index') {
            $this->facetSort = $newSort;
        }
    }

    /**
     * Add a prefix to facet requirements. Serves to
     *    limits facet sets to smaller subsets.
     *
     *  eg. all facet data starting with 'R'
     *
     * @param string $prefix Data for prefix
     *
     * @return void
     * @access public
     */
    public function addFacetPrefix($prefix)
    {
        $this->facetPrefix = $prefix;
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
        global $configArray;

        $returnArray = array();
        if (count($this->suggestions) == 0) {
            return $returnArray;
        }
        $tokens = $this->spellingTokens($this->_buildSpellingQuery());

        foreach ($this->suggestions as $term => $details) {
            // Find out if our suggestion is part of a token
            $inToken = false;
            $targetTerm = "";
            foreach ($tokens as $token) {
                // TODO - Do we need stricter matching here?
                //   Similar to that in replaceSearchTerm()?
                if (stripos($token, $term) !== false) {
                    $inToken = true;
                    // We need to replace the whole token
                    $targetTerm = $token;
                    // Go and replace this token
                    $returnArray = $this->_doSpellingReplace(
                        $term, $targetTerm, $inToken, $details, $returnArray
                    );
                }
            }
            // If no tokens we found, just look
            //    for the suggestion 'as is'
            if ($targetTerm == "") {
                $targetTerm = $term;
                $returnArray = $this->_doSpellingReplace(
                    $term, $targetTerm, $inToken, $details, $returnArray
                );
            }
        }
        return $returnArray;
    }

    /**
     * Process one instance of a spelling replacement and modify the return
     *   data structure with the details of what was done.
     *
     * @param string $term        The actually term we're replacing
     * @param string $targetTerm  The term above, or the token it is inside
     * @param bool   $inToken     Flag for whether the token or term is used
     * @param array  $details     The spelling suggestions
     * @param array  $returnArray Return data structure so far
     *
     * @return array              $returnArray modified
     * @access public
     */
    private function _doSpellingReplace($term, $targetTerm, $inToken, $details,
        $returnArray
    ) {
        global $configArray;

        $returnArray[$targetTerm]['freq'] = $details['freq'];
        foreach ($details['suggestions'] as $word => $freq) {
            // If the suggested word is part of a token
            if ($inToken) {
                // We need to make sure we replace the whole token
                $replacement = str_replace($term, $word, $targetTerm);
            } else {
                $replacement = $word;
            }
            //  Do we need to show the whole, modified query?
            if ($configArray['Spelling']['phrase']) {
                $label = $this->getDisplayQueryWithReplacedTerm(
                    $targetTerm, $replacement
                );
            } else {
                $label = $replacement;
            }
            // Basic spelling suggestion data
            $returnArray[$targetTerm]['suggestions'][$label] = array(
                'freq'        => $freq,
                'replace_url' => $this->renderLinkWithReplacedTerm(
                    $targetTerm, $replacement
                )
            );
            // Only generate expansions if enabled in config
            if ($configArray['Spelling']['expand']) {
                // Parentheses differ for shingles
                if (strstr($targetTerm, " ") !== false) {
                    $replacement = "(($targetTerm) OR ($replacement))";
                } else {
                    $replacement = "($targetTerm OR $replacement)";
                }
                $returnArray[$targetTerm]['suggestions'][$label]['expand_url']
                    = $this->renderLinkWithReplacedTerm($targetTerm, $replacement);
            }
        }

        return $returnArray;
    }

    /**
     * Return a list of valid sort options -- overrides the base class with
     * custom behavior for Author/Search screen.
     *
     * @return array Sort value => description array.
     * @access public
     */
    protected function getSortOptions()
    {
        // Author/Search screen
        if ($this->searchType == 'author' && $this->searchSubType == 'search') {
            // It's important to remember here we are talking about on-screen
            //   sort values, not what is sent to Solr, since this screen
            //   is really using facet sorting.
            return array('relevance' => 'sort_author_relevance',
                'author' => 'sort_author_author');
        }

        // Everywhere else -- use normal default behavior
        return parent::getSortOptions();
    }

    /**
     * Return a url of the current search as an RSS feed.
     *
     * @return string URL
     * @access public
     */
    public function getRSSUrl()
    {
        // Stash our old data for a minute
        $oldView = $this->view;
        $oldPage = $this->page;
        // Add the new view
        $this->view = 'rss';
        // Remove page number
        $this->page = 1;
        // Get the new url
        $url = $this->renderSearchUrl();
        // Restore the old data
        $this->view = $oldView;
        $this->page = $oldPage;
        // Return the URL
        return $url;
    }

    /**
     * Get the base URL for search results (including ? parameter prefix).
     *
     * @return string Base URL
     * @access protected
     */
    protected function getBaseUrl()
    {
        // Base URL is different for author searches:
        if ($this->searchType == 'author') {
            if ($this->searchSubType == 'home') {
                return $this->serverUrl."/Author/Home?";
            }
            if ($this->searchSubType == 'search') {
                return $this->serverUrl."/Author/Search?";
            }
        } else if ($this->searchType == 'newitem') {
            return $this->serverUrl . '/Search/NewItem?';
        } else if ($this->searchType == 'reserves') {
            return $this->serverUrl . '/Search/Reserves?';
        } else if ($this->searchType == 'favorites') {
            return $this->serverUrl . '/MyResearch/Favorites?';
        } else if ($this->searchType == 'list') {
            return $this->serverUrl . '/MyResearch/MyList/' .
                urlencode($_GET['id']) . '?';
        }

        // If none of the special cases were met, use the default from the parent:
        return parent::getBaseUrl();
    }

    /**
     * Get an array of strings to attach to a base URL in order to reproduce the
     * current search.
     *
     * @return array Array of URL parameters (key=url_encoded_value format)
     * @access protected
     */
    protected function getSearchParams()
    {
        $params = array();

        switch ($this->searchType) {
        // Author Home screen
        case "author":
            if ($this->searchSubType == 'home') {
                // Reverse query manipulation from init() for consistent paging:
                $term = str_replace('\"', '"', $this->searchTerms[0]['lookfor']);
                $params[] = "author="  .
                    urlencode(substr($term, 1, strlen($term) - 2));
            }
            if ($this->searchSubType == 'search') {
                $params[] = "lookfor=" .
                    urlencode($this->searchTerms[0]['lookfor']);
            }
            break;
        // New Items or Reserves modules may have a few extra parameters to preserve
        case "newitem":
        case "reserves":
        case "favorites":
        case "list":
            $preserveParams = array(
                // for newitem:
                'range', 'department',
                // for reserves:
                'course', 'inst', 'dept',
                // for favorites/list:
                'tag'
            );
            foreach ($preserveParams as $current) {
                if (isset($_GET[$current])) {
                    if (is_array($_GET[$current])) {
                        foreach ($_GET[$current] as $value) {
                            $params[] = $current . '[]=' . urlencode($value);
                        }
                    } else {
                        $params[] = $current . '=' . urlencode($_GET[$current]);
                    }
                }
            }
            break;
        // Basic search -- use default from parent class.
        default:
            $params = parent::getSearchParams();
            break;
        }

        return $params;
    }

    /**
     * Process a search for a particular tag.
     *
     * @param string $lookfor The tag to search for
     *
     * @return array          A revised searchTerms array to get matching Solr
     * records (empty if no tag matches found).
     * @access private
     */
    private function _processTagSearch($lookfor, $recordStart, $recordCount)
    {
        // Include the app database objects
        include_once 'services/MyResearch/lib/Tags.php';
        include_once 'services/MyResearch/lib/Resource.php';

        $limit = $this->indexEngine->getBooleanClauseLimit();
        // Find our tag in the database
        $tag = new Tags();
        $tag->tag = $lookfor;
        $tagList = array();
        if ($tag->find(true)) {
            // Grab the list of records tagged with this tag
            $resourceList = array();
            if ($limit > $tag->getTotalResources()) {
                $resourceList = $tag->getAllResources();
            }
            else {
                $resourceList = $tag->getResources($recordStart, $recordCount);
                $this->totalTagResults = $tag->getTotalResources();
            }
            if (count($resourceList)) {
                foreach ($resourceList as $resource) {
                    $tagList[] = $resource->record_id;
                }
            }
        }

        return $tagList;
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
        return isset($this->indexResult['error']) ?
            $this->indexResult['error'] : false;
    }

    /**
     * Load all recommendation settings from the relevant ini file.  Returns an
     * associative array where the key is the location of the recommendations (top
     * or side) and the value is the settings found in the file (which may be either
     * a single string or an array of strings).
     *
     * @return array associative: location (top/side) => search settings
     * @access protected
     */
    protected function getRecommendationSettings()
    {
        // Special case for author module.  Use settings from searches.ini if
        // present; default to old hard-coded defaults otherwise for legacy
        // compatibility.
        if ($this->searchType == 'author') {
            $searchSettings = getExtraConfigArray('searches');
            return isset($searchSettings['AuthorModuleRecommendations'])
                ? $searchSettings['AuthorModuleRecommendations']
                : array('side' => array('ExpandFacets:Author'));
        }

        // Use default case from parent class the rest of the time:
        return parent::getRecommendationSettings();
    }

    /**
     * Add a hidden (i.e. not visible in facet controls) filter query to the object.
     *
     * @param string $fq Filter query for Solr.
     *
     * @return void
     * @access public
     */
    public function addHiddenFilter($fq)
    {
        $this->hiddenFilters[] = $fq;
    }

    /**
     * Actually process and submit the search
     *
     * @param bool $returnIndexErrors Should we die inside the index code if we
     * encounter an error (false) or return it for access via the getIndexError()
     * method (true)?
     * @param bool $recommendations     Should we process recommendations along with
     * the search itself?
     * @param bool $withAuthorityData   Should we enrich the query with normdata. Default true.
     *
     * @return object                   Solr result structure (for now)
     * @access public
     */
    public function processSearch($returnIndexErrors = false,
        $recommendations = false, $withAuthorityData = true
    ) {

        global $configArray;
        global $module, $action;

        $clean = true;
        if ($module == 'MyResearch' && ($action == 'Favorites' || $action == 'MyList')) { $clean = false; }

        // Our search has already been processed in init()

        $search = $this->searchTerms;
        // _enrichSearchTerm is buggy and ruins query (search term is thrown away)
        // _enrichSearchTerm is only needed for authority data integration into query
        //$search=$this->_enrichSearchTerm();

        // echo "<pre>"; print_r($this->searchTerms); echo "</pre>";
        // Build a recommendations module appropriate to the current search:
        if ($recommendations) {
            $this->initRecommendations();
        }

        // Tag searches need to be handled differently
        if (count($search) == 1 && isset($search[0]['index'])
            && $search[0]['index'] == 'tag'
        ) {
            // If we managed to find some tag matches, let's override the search
            // array.  If we didn't find any tag matches, we should return an
            // empty record set.
            $limit = $this->limit;
            // Do not use boolean clause limit here, we need to break down to page limit (otherwise we could not find out where to start)
            //$limit = $this->indexEngine->getBooleanClauseLimit();
            $recordStart = ($this->page - 1) * $this->limit;
            $tagList = $this->_processTagSearch($search[0]['lookfor'], $recordStart, $limit);
            // Save search so it displays correctly on the "no hits" page:
            if (empty($tagList)) {
                return array(
                    'response' => array('numFound' => 0, 'docs' => array())
                );
            } else {
                $this->setQueryIDs($tagList);
            }
        }

        // Build Query
        $query = $this->indexEngine->buildQuery($search);
        if (PEAR::isError($query)) {
            return $query;
        }

        // Only use the query we just built if there isn't an override in place.
        if ($this->query == null) {
            $this->query = $query;
        }

        if ($clean === true) {
        // Define Filter Query
        $filterQuery = $this->hiddenFilters;
        $orFilterQuery = array();

        if (array_key_exists('localonly', $_REQUEST) === true && $_REQUEST['localonly'] == "0") {
            $this->addFilter('showAll:true');
        }

        $removeDefaultFilter = false;
        foreach ($this->filterList as $field => $filter) {
            if (in_array($field, $this->defFilterFields) === true) {
                $removeDefaultFilter = true;
                continue;
            }
            foreach ($filter as $value) {
                // Special case -- allow trailing wildcards and ranges:
                if (substr($value, -1) == '*'
                    || preg_match('/\[[^\]]+\s+TO\s+[^\]]+\]/', $value)
                ) {
                    //$filterQuery[] = "$field:$value";
                    if (in_array($field, $this->multiSelectFacets)) {
                        $orFilterQuery[$field][] = "$field:$value";
                    } else {
                        $filterQuery[] = "$field:$value";
                    }
                } else {
                    // $filterQuery[] = "$field:\"$value\"";
                    if (in_array($field, $this->multiSelectFacets)) {
                        $orFilterQuery[$field][] = "$field:\"$value\"";
                    } else {
                        $filterQuery[] = "$field:\"$value\"";
                    }
                }
            }
        }
        if ($removeDefaultFilter === true) {
            foreach ($this->defaultFilter as $defKey => $defValue) {
                $key = array_search($defValue, $filterQuery);
                if ($key !== false) {
                    $new = array_splice($filterQuery, $key, 1);
                }
            }
        }
        if(!empty($orFilterQuery)) {
            foreach ($orFilterQuery as $field => $filter) {
                $filterQuery[] = '{!tag=' . $field . '_filter}' 
                    . '('. implode(" OR ", $filter) . ')';
            }
        }
        }

        // If we are only searching one field use the DisMax handler
        //    for that field. If left at null let solr take care of it
        if (count($search) == 1 && isset($search[0]['index'])) {
            $this->index = $search[0]['index'];
        }

        // Build a list of facets we want from the index
        $facetSet = array();

        if (!empty($this->facetConfig)) {
            $facetSet['limit'] = $this->facetLimit;
            foreach ($this->facetConfig as $facetField => $facetName) {
                if (in_array($facetField, $this->multiSelectFacets)) {
                    $facetField = '{!ex=' . $facetField . '_filter}' . $facetField;
                }
                $facetSet['field'][] = $facetField;
            }
            if ($this->facetOffset != null) {
                $facetSet['offset'] = $this->facetOffset;
            }
            if ($this->facetPrefix != null) {
                $facetSet['prefix'] = $this->facetPrefix;
            }
            if ($this->facetSort != null) {
                $facetSet['sort'] = $this->facetSort;
            } else {
                // backport from VuFind 2, see http://vufind.org/jira/browse/VUFIND-769
                //
                // No explicit setting? Set one based on the documented Solr behavior
                // (index order for limit = -1, count order for limit > 0)
                // Later Solr versions may have different defaults than earlier ones,
                // so making this explicit ensures consistent behavior.
                $facetSet['sort'] = ($this->facetLimit > 0) ? 'count' : 'index';
            }
        }
        // Hack by Frank Morgner to sort single facets
        // alphabetically. Sorting by count is default.
        if ($this->sortedByIndex != null) {
            foreach ($this->sortedByIndex as $facetField) {
                $facetSet['f.'.$facetField.'.facet.sort'] = 'index';
           }
        }
        
        // End

        // Build our spellcheck query
        if ($this->spellcheck) {
            if ($this->spellSimple) {
                $this->useBasicDictionary();
            }
            $spellcheck = $this->_buildSpellingQuery();

            // If the spellcheck query is purely numeric, skip it if
            // the appropriate setting is turned on.
            if ($this->spellSkipNumeric && is_numeric($spellcheck)) {
                $spellcheck = "";
            }
        } else {
            $spellcheck = "";
        }

        // Get time before the query
        $this->startQueryTimer();

        // The "relevance" sort option is a VuFind reserved word; we need to make
        // this null in order to achieve the desired effect with Solr:
        $finalSort = ($this->sort == 'relevance') ? null : $this->sort;

        // Load additional indices for distributed search
        if (isset($_SESSION['shards'])) {
            $this->fields = '*,score';
        }

       // --- TUHH Normdaten ---
       //if ($this->authorityUsage == 'expand') {
       if ($configArray['AuthorityData']['enabled'] == true) {
           $terms = array();
           $skip=0;
           if (trim($this->query) !== '*:*') {
           foreach ($this->searchTerms as &$v) {
               if (isset($v['index']) && $v['index']=="rvk_facet") {
                   $skip=1;
                }
            }
            if (isset($configArray['AuthorityData']['service_url']) && $skip == 0) {
                if (in_array($this->query,$this->stopwordlist) === false) {
                $ch=curl_init();
                curl_setopt($ch,CURLOPT_URL,$configArray['AuthorityData']['service_url']);
                curl_setopt($ch,CURLOPT_POSTFIELDS,"qs=".urlencode(str_replace("\"","",trim($this->query))));
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                if( ! $enhanced = curl_exec($ch)) {
                    trigger_error(curl_error($ch));
                }
                curl_close($ch);

                if(strlen($enhanced)>0) {
                    $enhanced=str_replace(","," ",$enhanced);

                    $t1=mb_split("[\s\,\;\!\.\"]+",$this->query,-1);
                    $t2="";
                    foreach($t1 as $t) {
                        if(strlen($t2)>0) $t2.=" ";
                        $bool = array("AND","OR","NOT");
                        if(in_array($t,$bool)) {
                            $t2.=mb_strtoupper($t,'UTF-8');
                        } else {
                            $t2.=mb_strtolower($t,'UTF-8');
                        }
                    }

                    $this->query="( ".$t2." ) ".trim($enhanced);
                    }
                    // Falls mehrere Suchbegriffe eingegeben wurden, gehe jetzt alle nochmal durch
                    $sts = explode(' ', $this->query);
                    if (count($sts) > 1) {
                        foreach ($sts as $st) {
                            // Wenn der Suchbegriff nicht in der Stopwortliste steht, geh ihm nach und suche ihn im Normdatenindex
                            if (in_array($st,$this->stopwordlist) === false) {
                                $ch=curl_init();
                                curl_setopt($ch,CURLOPT_URL,$configArray['AuthorityData']['service_url']);
                                curl_setopt($ch,CURLOPT_POSTFIELDS,"qs=".urlencode(str_replace("\"","",trim($st))));
                                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                                if( ! $enhanced = curl_exec($ch)) {
                                    trigger_error(curl_error($ch));
                                }
                                curl_close($ch);

                                if (trim($enhanced) != '') {
                                    $terms[] = '('.trim($enhanced).')';
                                }
                            }
                        }
                    }
                }
            }
            }
        // print_r($terms);
        $this->query .= ' OR ('.implode(' AND ', $terms).')';
        }
        //  echo "<pre>".$this->query."</pre>";
        // --- HACK END ---

        if (isset($this->totalTagResults)) {
            $recordStart = 0;
        }

        // The first record to retrieve:
        //  (page - 1) * limit = start
        $recordStart = ($this->page - 1) * $this->limit;
        $this->indexResult = $this->indexEngine->search(
            $this->query, // "author_id:16247640X*",      // Query string
            $this->index,      // DisMax Handler
            $filterQuery,      // Filter query
            $recordStart,      // Starting record
            $this->limit,      // Records per page
            $facetSet,         // Fields to facet on
            $spellcheck,       // Spellcheck query
            $this->dictionary, // Spellcheck dictionary
            $finalSort,        // Field to sort on
            $this->fields,     // Fields to return
            $this->method,     // HTTP Request method
            $returnIndexErrors,// Include errors in response?
            $clean             // should the input be cleaned up? (Not for Favorite-search)
        );
        // Get time after the query
        $this->stopQueryTimer();

        // How many results were there?
        $this->resultsTotal = isset($this->indexResult['response']['numFound'])
            ? $this->indexResult['response']['numFound'] : 0;

        // Process spelling suggestions if no index error resulted from the query
        if ($this->spellcheck && !isset($this->indexResult['error'])) {
            // Shingle dictionary
            $this->_processSpelling();
            // Make sure we don't endlessly loop
            if ($this->dictionary == 'default') {
                // Expand against the basic dictionary
                $this->_basicSpelling();
            }
        }
        // If extra processing is needed for recommendations, do it now:
        if ($recommendations && is_array($this->recommend)) {
            foreach ($this->recommend as $currentSet) {
                foreach ($currentSet as $current) {
                    $current->process();
                }
            }
        }

        // Return the result set
        return $this->indexResult;
    }

    /**
     * Adapt the search query to a spelling query
     *
     * @return string Spelling query
     * @access private
     */
    private function _buildSpellingQuery()
    {
        // Basic search
        if ($this->searchType == $this->basicSearchType) {
            // Just the search query is fine
            return $this->query;
        } else {
            // Advanced search
            return $this->extractAdvancedTerms();
        }
    }

    /**
     * Process spelling suggestions from the results object
     *
     * @return void
     * @access private
     */
    private function _processSpelling()
    {
        global $configArray;

        // Do nothing if spelling is disabled
        if (!$configArray['Spelling']['enabled']) {
            return;
        }

        // Do nothing if there are no suggestions
        $suggestions = isset($this->indexResult['spellcheck']['suggestions']) ?
            $this->indexResult['spellcheck']['suggestions'] : array();
        if (count($suggestions) == 0) {
            return;
        }

        // Loop through the array of search terms we have suggestions for
        $suggestionList = array();
        foreach ($suggestions as $suggestion) {
            $ourTerm = $suggestion[0];

            // Skip numeric terms if numeric suggestions are disabled
            if ($this->spellSkipNumeric && is_numeric($ourTerm)) {
                continue;
            }

            $ourHit  = $suggestion[1]['origFreq'];
            $count   = $suggestion[1]['numFound'];
            $newList = $suggestion[1]['suggestion'];

            $validTerm = true;

            // Make sure the suggestion is for a valid search term.
            // Sometimes shingling will have bridged two search fields (in
            // an advanced search) or skipped over a stopword.
            if (!$this->findSearchTerm($ourTerm)) {
                $validTerm = false;
            }

            // Unless this term had no hits
            if ($ourHit != 0) {
                // Filter out suggestions we are already using
                $newList = $this->_filterSpellingTerms($newList);
            }

            // Make sure it has suggestions and is valid
            if (count($newList) > 0 && $validTerm) {
                // Did we get more suggestions then our limit?
                if ($count > $this->spellingLimit) {
                    // Cut the list at the limit
                    array_splice($newList, $this->spellingLimit);
                }
                $suggestionList[$ourTerm]['freq'] = $ourHit;
                // Format the list nicely
                foreach ($newList as $item) {
                    if (is_array($item)) {
                        $suggestionList[$ourTerm]['suggestions'][$item['word']]
                            = $item['freq'];
                    } else {
                        $suggestionList[$ourTerm]['suggestions'][$item] = 0;
                    }
                }
            }
        }
        $this->suggestions = $suggestionList;
    }

    /**
     * Filter a list of spelling suggestions to remove suggestions
     *   we are already searching for
     *
     * @param array $termList List of suggestions
     *
     * @return array          Filtered list
     * @access private
     */
    private function _filterSpellingTerms($termList)
    {
        $newList = array();
        if (count($termList) == 0) {
            return $newList;
        }

        foreach ($termList as $term) {
            if (!$this->findSearchTerm($term['word'])) {
                $newList[] = $term;
            }
        }
        return $newList;
    }

    /**
     * Try running spelling against the basic dictionary.
     *   This function should ensure it doesn't return
     *   single word suggestions that have been accounted
     *   for in the shingle suggestions above.
     *
     * @return array Suggestions array
     * @access private
     */
    private function _basicSpelling()
    {
        // TODO: There might be a way to run the
        //   search against both dictionaries from
        //   inside solr. Investigate. Currently
        //   submitting a second search for this.

        // Create a new search object
        $type = str_replace('SearchObject_', '', get_class($this));
        $newSearch = SearchObjectFactory::initSearchObject($type);
        $newSearch->deminify($this->minify());

        // Activate the basic dictionary
        $newSearch->useBasicDictionary();
        // We don't want it in the search history
        $newSearch->disableLogging();

        // Run the search
        $newSearch->processSearch();
        // Get the spelling results
        $newList = $newSearch->getRawSuggestions();

        // If there were no shingle suggestions
        if (count($this->suggestions) == 0) {
            // Just use the basic ones as provided
            $this->suggestions = $newList;
        } else {
            // Otherwise...
            // For all the new suggestions
            foreach ($newList as $word => $data) {
                // Check the old suggestions
                $found = false;
                foreach ($this->suggestions as $k => $v) {
                    // Make sure it wasn't part of a shingle
                    //   which has been suggested at a higher
                    //   level.
                    $found = preg_match("/\b$word\b/", $k) ? true : $found;
                }
                if (!$found) {
                    $this->suggestions[$word] = $data;
                }
            }
        }
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
        if (!isset($this->indexResult['facet_counts']['facet_fields'])
            || !is_array($this->indexResult['facet_counts']['facet_fields'])
        ) {
            return $list;
        }

        // Loop through every field returned by the result set
        $validFields = array_keys($filter);
        foreach ($this->indexResult['facet_counts']['facet_fields'] as $field => $data) {
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
            // Should we translate values for the current facet?
            $translate = in_array($field, $this->translatedFacets);

            // Loop through values:
            foreach ($data as $facet) {
                //echo "FacetsList: <pre>"; print_r($facet); echo "<pre>";
                //echo "FacetsList: <pre>"; print_r($field); echo "<pre>";

                // Initialize the array of data about the current facet:
                $currentSettings = array();
                $currentSettings['value']
                    = $translate ? translate(utf8_decode($facet[0])) : $facet[0];
                $currentSettings['untranslated'] = $facet[0];
                $currentSettings['count'] = $facet[1];
                $currentSettings['isApplied'] = false;
                $currentSettings['url']
                    = $this->renderLinkWithFilter("$field:".$facet[0]);
                // If we want to have expanding links (all values matching the
                // facet) in addition to limiting links (filter current search
                // with facet), do some extra work:
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

                // Check if there're single facet values which should be hidden
                // to display the facet. #hidefacettopic
                $storeValueInList = true;
                if (isset($this->hideFacetTopics[$field]) 
                    && in_array($facet[0], $this->hideFacetTopics[$field])) { 
                    $storeValueInList = false;
                }
                // Store the collected values:
                if (true === $storeValueInList) { 
                    $list[$field]['list'][] = $currentSettings;
                }
            }
        }
        return $list;
    }

    /**
     * Load all available facet settings.  This is mainly useful for showing
     * appropriate labels when an existing search has multiple filters associated
     * with it.
     *
     * @param string $preferredSection Section to favor when loading settings; if
     * multiple sections contain the same facet, this section's description will
     * be favored.
     *
     * @return void
     * @access public
     */
    public function activateAllFacets($preferredSection = false)
    {
        foreach ($this->allFacetSettings as $section => $values) {
            foreach ($values as $key => $value) {
                $this->addFacet($key, $value);
            }
        }

        if ($preferredSection
            && is_array($this->allFacetSettings[$preferredSection])
        ) {
            foreach ($this->allFacetSettings[$preferredSection] as $key => $value) {
                $this->addFacet($key, $value);
            }
        }
    }

    /**
     * Turn our results into an RSS feed
     *
     * @param array $result Existing result set (null to do new search)
     *
     * @return string       XML document
     * @access public
     */
    public function buildRSS($result = null)
    {
        // XML HTTP header
        header('Content-type: text/xml', true);

        // First, get the search results if none were provided
        if (is_null($result)) {
            // Let's do 50 at a time...
            $this->limit = 50;

            // If an RSS-specific search option is configured, override the current
            // setting by prepending the specified value (unless the request
            // specifically says not to):
            $searchSettings = getExtraConfigArray('searches');
            if (isset($searchSettings['RSS']['sort'])
                && !empty($searchSettings['RSS']['sort'])
                && !isset($_REQUEST['skip_rss_sort'])
            ) {
                $this->sort = (empty($this->sort) || $this->sort == 'relevance') ?
                    $searchSettings['RSS']['sort'] :
                    $searchSettings['RSS']['sort'] . ',' . $this->sort;
            }

            // Get the results:
            $result = $this->processSearch(false, false);
        }

        // Now prepare the serializer
        $serializer_options = array (
            'addDecl'  => true,
            'encoding' => 'UTF-8',
            'indent'   => '  ',
            'rootName' => 'json',
            'mode'     => 'simplexml'
        );
        $serializer = new XML_Serializer($serializer_options);

        // The XML parsers have trouble with the control characters
        //   inside the marc data, so lets get rid of the 'fullrecord'
        //   nodes. Not sure what we'll do if these are needed for some
        //   reason
        // The marc_error nodes can also cause problems, so let's get rid
        //   of them at the same time.
        for ($i = 0; $i < count($result['response']['docs']); $i++) {
            if (isset($result['response']['docs'][$i]['fullrecord'])) {
                unset($result['response']['docs'][$i]['fullrecord']);
            }
            if (isset($result['response']['docs'][$i]['marc_error'])) {
                unset($result['response']['docs'][$i]['marc_error']);
            }
        }

        // Serialize our results from PHP arrays to XML
        if ($serializer->serialize($result)) {
            $xmlResults = $serializer->getSerializedData();
        }

        // Prepare an XSLT processor and pass it some variables
        $xsl = new XSLTProcessor();
        $xsl->registerPHPFunctions('urlencode');
        $xsl->registerPHPFunctions('translate');
        $xsl->registerPHPFunctions('xslRssDate');

        // On-screen display value for our search
        if ($this->searchType == 'newitem') {
            $lookfor = translate('New Items');
        } else if ($this->searchType == 'reserves') {
            $lookfor = translate('Course Reserves');
        } else {
            $lookfor = $this->displayQuery();
        }
        if (count($this->filterList) > 0) {
            // TODO : better display of filters
            $xsl->setParameter(
                '', 'lookfor', $lookfor . " (" . translate('with filters') . ")"
            );
        } else {
            $xsl->setParameter('', 'lookfor', $lookfor);
        }
        // The full url to recreate this search
        $xsl->setParameter('', 'searchUrl', $this->renderSearchUrl());
        // Stub of a url for a records screen
        $xsl->setParameter('', 'baseUrl', $this->serverUrl . "/Record/");

        // Load up the style sheet
        $style = new DOMDocument;
        $style->load('services/Search/xsl/json-rss.xsl');
        $xsl->importStyleSheet($style);

        // Load up the XML document
        $xml = new DOMDocument;
        $xml->loadXML($xmlResults);

        // Process and return the xml through the style sheet
        return $xsl->transformToXML($xml);
    }

    /**
     * Get complete facet counts for several index fields
     *
     * @param array $facetfields  name of the Solr fields to return facets for
     * @param bool  $removeFilter Clear existing filters from selected fields (true)
     * or retain them (false)?
     *
     * @return array an array with the facet values for each index field
     * @access public
     */
    public function getFullFieldFacets($facetfields, $removeFilter = true)
    {
        // Save prior facet configuration:
        $oldConfig = $this->facetConfig;
        $oldList = $this->filterList;
        $oldLimit = $this->facetLimit;

        // Manipulate facet settings temporarily:
        $this->facetConfig = array();
        $this->facetLimit = -1;
        foreach ($facetfields as $facetName) {
            $this->addFacet($facetName);

            // Clear existing filters for the selected field if necessary:
            if ($removeFilter) {
                $this->filterList[$facetName] = array();
            }
        }

        // Do search
        $result = $this->processSearch();

        // Reformat into a hash:
        $returnFacets = $result['facet_counts']['facet_fields'];
        foreach ($returnFacets as $key => $value) {
            unset($returnFacets[$key]);
            $returnFacets[$key]['data'] = $value;
        }

        // Restore saved information:
        $this->facetConfig = $oldConfig;
        $this->filterList = $oldList;
        $this->facetLimit = $oldLimit;

        // Send back data:
        return $returnFacets;
    }

    /* UNNECESSARY? */
    private function _escapeSolrChars($in) {
        $match = array('\\', '+', '-', '&', '|', '!', '(', ')', '{', '}', '[', ']', '^', '~', ':', '"', ';', ' ');
        $replace = array('\\\\', '\\+', '\\-', '\\&', '\\|', '\\!', '\\(', '\\)', '\\{', '\\}', '\\[', '\\]', '\\^', '\\~', '\\:', '\\"', '\\;', '\\ ');
       
        $in = str_replace($match, $replace, $in);
        return $in;
    }

    /**
     * Enrich data to match barcodes and signatures (callnumbers) from different
     * library clusters. Added before all barcodes and signatures the library seal
     * to match it with the enriched marc data.
     *
     * @param obj $this->searchTerms    The search term with the index and lookfor
     * @param obj $this->libraryGroup   The cluster of libraries.
     *
     * @return obj $this->searchTerms   Return of the search term enriched with values
     * @access private
     */

    private function _enrichSearchTermLookfor($name,$lookfor) {
        
        if ($name == 'rvk_facet') {
            return(str_replace(" ","?",$lookfor));
        } 

        if ($name == 'Barcode' || $name == 'Signatur') {
            if(strpos($name,"\(DE\-")!==false)
                continue;
            if ($name == 'Barcode') {
                $items = explode(' ', trim($lookfor));
            } else {
                $items=array();
                $items[]=trim($lookfor);
            }
            $libraries = explode(',', $this->libraryGroup);
            foreach ($items as $item) {
                foreach ($libraries as $library) {
                    $term='('.$library . ')' . $item;
                    $term=str_replace(array(" ",":"),array("?","?"),$term);
                    $term=$this->_escapeSolrChars($term);
                    $terms[]=$term;
                }
                $terms=array_unique($terms);
            } // end foreach
            return(implode(' OR ', $terms));
        }

        return $lookfor;
    }


    private function _enrichSearchTerm() {

        if (is_null($this->libraryGroup)){
            return;
        }
      
        $myv=$this->searchTerms;
         
        foreach ($myv as &$v) {
            // echo "<pre>".print_r($v,true)."</pre>";
            if(isset($v['index'])){
                $v['lookfor']=$this->_enrichSearchTermLookfor($v['index'],$v['lookfor']);
            } else if(isset($v['group'])) {
                foreach ($v['group'] as &$vv) {
                        $vv['lookfor']=$this->_enrichSearchTermLookfor($vv['field'],$vv['lookfor']);
                    }
            }
        }

        return $myv;
    }  


}

/**
 * Support function for RSS XSLT transformation -- convert ISO-8601 date to RFC 822.
 *
 * @param string $in ISO-8601 date
 *
 * @return string    RFC 822 date
 */
function xslRssDate($in)
{
    static $months = array(
        1 => "Jan", 2 => "Feb", 3 => "Mar", 4 => "Apr", 5 => "May", 6 => "Jun",
        7 => "Jul", 8 => "Aug", 9 => "Sep", 10 => "Oct", 11 => "Nov", 12 => "Dec"
    );

    $regEx = '/([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-9]{2}:[0-9]{2}:[0-9]{2})Z/';
    preg_match($regEx, $in, $matches);

    $year = $matches[1];
    $month = $months[intval($matches[2])];
    $day = $matches[3];
    $time = $matches[4];

    return "{$day} {$month} {$year} {$time} GMT";
}
?>