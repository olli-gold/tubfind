<?php
/**
 * AuthorFacets Recommendations Module
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2009.
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
 * @package  Recommendations
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_recommendations_module Wiki
 */
require_once 'sys/Recommend/Interface.php';

/**
 * AuthorFacets Recommendations Module
 *
 * This class provides recommendations by taking advantage of author faceting.
 *
 * @category VuFind
 * @package  Recommendations
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_recommendations_module Wiki
 */
class AuthorFacets implements RecommendationInterface
{
    private $_searchObject;
    private $_params;

    /**
     * Constructor
     *
     * Establishes base settings for making recommendations.
     *
     * @param object $searchObject The SearchObject requesting recommendations.
     * @param string $params       Additional settings from searches.ini.
     *
     * @access public
     */
    public function __construct($searchObject, $params)
    {
        // Save the basic parameters:
        $this->_searchObject = $searchObject;
        $this->_params = $params;
    }

    /**
     * init
     *
     * Called before the SearchObject performs its main search.  This may be used
     * to set SearchObject parameters in order to generate recommendations as part
     * of the search.
     *
     * @return void
     * @access public
     */
    public function init()
    {
        // No action needed here.
    }

    /**
     * Process similar authors from an author search
     *
     * @return array Facets data arrays
     * @access private
     */
    private function _processAuthors()
    {
        // Grab some necessary values from the SearchObject and config array:
        global $configArray;
        $indexEngine = $this->_searchObject->getIndexEngine();
        $serverUrl = $configArray['Site']['url'];
        $search = $this->_searchObject->getSearchTerms();
        $lookfor = isset($search[0]['lookfor']) ? $search[0]['lookfor'] : '';

        // Clean up the input -- if it's an invalid or empty search,
        // skip the author faceting.
        $query = $indexEngine->validateInput($lookfor);
        if (empty($query)) {
            return array();
        }

        // Run a query for the author. We only want the facets of the result set.
        $facetSettings = array(
            'field' => 'authorStr', 'limit' => 10, 'sort' => 'count'
        );
        $result = $indexEngine->search(
            $lookfor,             // Query string
            'Author',             // DisMax Handler : null = standard
            null,                 // Filter query
            0,                    // Starting record
            null,                 // Records per page
            $facetSettings,       // Fields to facet on
            null,                 // Spellcheck value
            null,                 // Spellcheck dictionary
            null,                 // Field to sort on
            'score',              // Fields to return
            HTTP_REQUEST_METHOD_POST,
            true                  // Return error messages so we don't blow up!
        );

        // Now go and pull the facets apart (if we encountered an error, we'll
        // just end up with an empty response...  no point in throwing a fatal
        // error just because we're having trouble with recommendations).
        $list = array();
        $data = isset($result['facet_counts']['facet_fields']['authorStr']) ?
            $result['facet_counts']['facet_fields']['authorStr'] : null;

        // Make sure there's some data
        if (isset($data) && count($data) > 0) {
            // A link to start their own author search like this
            $list['lookfor']
                = $serverUrl."/Author/Search?lookfor=".urlencode($lookfor);

            // Total authors (currently there is no way to calculate this without
            // risking out-of-memory errors or slow results, so we set this to
            // false; if we are able to find this information out in the future,
            // we can fill it in here and the templates will display it).
            $list['count'] = false;
            // Build our array of values for this field
            $list['list'] = array();
            foreach ($data as $facet) {
                // Stop at ten entries
                if (count($list['list']) < 10) {
                    $list['list'][] = array(
                        'value' => $facet[0],
                        'count' => $facet[1],
                        'url' =>
                            $serverUrl."/Author/Home?author=" . urlencode($facet[0])
                    );
                }
            }
        }
        return $list;
    }

    /**
     * process
     *
     * Called after the SearchObject has performed its main search.  This may be
     * used to extract necessary information from the SearchObject or to perform
     * completely unrelated processing.
     *
     * @return void
     * @access public
     */
    public function process()
    {
        global $interface;
        $interface->assign('similarAuthors', $this->_processAuthors());
    }

    /**
     * getTemplate
     *
     * This method provides a template name so that recommendations can be displayed
     * to the end user.  It is the responsibility of the process() method to
     * populate all necessary template variables.
     *
     * @return string The template to use to display the recommendations.
     * @access public
     */
    public function getTemplate()
    {
        return 'Search/Recommend/AuthorFacets.tpl';
    }
}

?>