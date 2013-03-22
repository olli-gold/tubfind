<?php
/**
 * ExpandFacets Recommendations Module
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
 * ExpandFacets Recommendations Module
 *
 * This class provides recommendations displaying facets beside search results;
 * unlike the standard SideFacets control, clicking these links displays ALL
 * results matching a facet rather than limiting the current search.
 *
 * @category VuFind
 * @package  Recommendations
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_recommendations_module Wiki
 */
class ExpandFacets implements RecommendationInterface
{
    private $_searchObject;
    private $_facets;

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

        // Parse the additional parameters:
        $params = explode(':', $params);
        $section = empty($params[0]) ? 'ResultsTop' : $params[0];
        $iniFile = isset($params[1]) ? $params[1] : 'facets';

        // Load the desired facet information:
        $config = getExtraConfigArray($iniFile);
        $this->_facets = isset($config[$section]) ? $config[$section] : array();
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
        // Turn on top facets in the search results:
        foreach ($this->_facets as $name => $desc) {
            $this->_searchObject->addFacet($name, $desc);
        }
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

        // Grab the facet set -- note that we need to take advantage of the second
        // parameter to getFacetList in order to get expanding links.
        $interface->assign(
            'expandFacetSet',
            $this->_searchObject->getFacetList($this->_facets, true)
        );
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
        return 'Search/Recommend/ExpandFacets.tpl';
    }
}

?>