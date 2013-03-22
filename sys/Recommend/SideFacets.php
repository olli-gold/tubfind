<?php
/**
 * SideFacets Recommendations Module
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
 * @package  Recommendations
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_recommendations_module Wiki
 */
require_once 'sys/Recommend/Interface.php';

/**
 * SideFacets Recommendations Module
 *
 * This class provides recommendations displaying facets beside search results
 *
 * @category VuFind
 * @package  Recommendations
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_recommendations_module Wiki
 */
class SideFacets implements RecommendationInterface
{
    private $_searchObject;
    private $_dateFacets = array();
    private $_mainFacets;
    private $_checkboxFacets;

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
        // Save the passed-in SearchObject:
        $this->_searchObject = $searchObject;
        // Parse the additional settings:
        $params = explode(':', $params);
        $mainSection = empty($params[0]) ? 'Results' : $params[0];
        $checkboxSection = isset($params[1]) ? $params[1] : false;
        $iniName = isset($params[2]) ? $params[2] : 'facets';
        // Load the desired facet information...
        $this->config = getExtraConfigArray($iniName);
        // All standard facets to display:
        $this->_mainFacets = isset($this->config[$mainSection]) ?
            $this->config[$mainSection] : array();
        // Get a list of fields that should be displayed as date ranges rather than
        // standard facet lists.
        if (isset($this->config['SpecialFacets']['dateRange'])) {
            $this->_dateFacets = is_array($this->config['SpecialFacets']['dateRange'])
                ? $this->config['SpecialFacets']['dateRange']
                : array($this->config['SpecialFacets']['dateRange']);
        }

        // Checkbox facets:
        $this->_checkboxFacets
            = ($checkboxSection && isset($config[$checkboxSection]))
            ? $config[$checkboxSection] : array();

        if (isset($this->config['Raw'.$checkboxSection])) {
            $index = 0;
            for ($intCount = 0; $intCount < count($this->config['Raw'.$checkboxSection])/2; $intCount++) {
                $this->_checkboxFacets[$this->config['Raw'.$checkboxSection][$index]] = $this->config['Raw'.$checkboxSection][$index.'_label'];
                $index++;
            }
        }
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
        // Turn on side facets in the search results:
        foreach ($this->_mainFacets as $name => $desc) {
            $this->_searchObject->addFacet($name, $desc);
        }
        foreach ($this->_checkboxFacets as $name => $desc) {
            $this->_searchObject->addCheckboxFacet($name, $desc);
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
        $interface->assign(
            'checkboxFilters', $this->_searchObject->getCheckboxFacets()
        );
        $interface->assign('filterList', $this->_searchObject->getFilterList(true));
        $interface->assign(
            'dateFacets',
            $this->_processDateFacets($this->_searchObject->getFilters())
        );
        $interface->assign(
            'sideFacetSet', $this->_processFacetList($this->_searchObject->getFacetList($this->_mainFacets), $this->_searchObject->getMinimizedFilterList())
        );
        // echo "<pre>"; print_r($this->_searchObject->getFacetList($this->_mainFacets)); echo "</pre>";
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
        return 'Search/Recommend/SideFacets.tpl';
    }

    /**
     *
     * Support method to pre-populate date ranges using values found in filters.
     *
     * @param array $filters Filter information from search object.
     *
     * @return array         Array of from/to value arrays keyed by field.
     * @method _processDateFacets
     * @access private
     */
    private function _processDateFacets($filters)
    {
        $result = array();
        foreach ($this->_dateFacets as $current) {
            $from = $to = '';
            if (isset($filters[$current])) {
                foreach ($filters[$current] as $filter) {
                    if ($range = VuFindSolrUtils::parseRange($filter)) {
                        $from = $range['from'] == '*' ? '' : $range['from'];
                        $to = $range['to'] == '*' ? '' : $range['to'];
                        break;
                    }
                }
            }
            $result[$current] = array($from, $to);
        }
        return $result;
    }


    /**
     * Pre-populate sideFacet array with names of facets for hiding content of facets.
     *
     * @param array $facetList      List of all filters at the facets.
     * @param array $filterList     Minimized list of used filters on facets.
     *
     * @return array                Prepopulated array.
     * @method _processFacetList
     * @access private
     */
    private function _processFacetList($facetList, $filterList)
    {
            $hideFacets = (isset($this->config['HideFacets']['hide_facets'])) 
                ? explode(',' , $this->config['HideFacets']['hide_facets']) : array();
            $hideFacetsTopics = (isset($this->config['HideFacets']['hide_facets_topics'])) 
                ? explode(',' , $this->config['HideFacets']['hide_facets_topics']) : array();
        

            foreach( $facetList as $facet => &$value){
                // hiding facets completely from the beginning defined in 
                // the facets.ini
                $value['hide'] = (in_array($facet, $hideFacets) === true) ? 1 : 0;
                
                // sort list pt.i
                // initialize arrays for sorting parameters
                $arrayIsApplied = array();
                $arrayIsNotApplied = array();

                foreach ( $value['list'] as $key => &$parameter) {

                    // if there is a checkbox on a facet setted to true so get
                    // the removal url of the filter and overwrite the 
                    // url which adds filters on the facet
                    if (is_array($filterList) && count($filterList) >0) {
                        foreach ($filterList as $filterval) {
                            if (isset($filterval['facet']) 
                                && $filterval['facet'] == $parameter['untranslated']) {
                                $parameter['url'] = $filterval['removalUrl'];
                            } // end if
                        } // end foreach
                    } // end if

                    // add addtional parameters to perceive active changes on facets
                    // by user events. could be used to save facets into sessions
                    $addFacet = ($parameter['isApplied'] == 1) ? '&addFacet=false' : '&addFacet=true';
                    //$facetPart = '&facetPart=' . md5($parameter['untranslated']); 
                    // $parameter['url'] = $parameter['url'] . $facetPart . $addFacet;
                    $parameter['url'] = $parameter['url'] . $addFacet;

                    // if there is hide facets topics for a facet value so remove it
                    // set it facets.ini below HideFacets;
                    $isFacetToHide = false;
                    if (in_array($parameter['untranslated'], $hideFacetsTopics)) {
                        $isFacetToHide = true;
                        //unset($facetList[($facet)]['list'][($key)]);
                    }

                    // sort list pt. ii
                    // collecting parameter
                    if (false ===  $isFacetToHide) {
                        if ($parameter['isApplied'] == 1) {
                            $arrayIsApplied[] = $parameter;
                        } else {
                            $arrayIsNotApplied[] = $parameter;
                        }
                    }
                } // end foreach
                unset ($parameter);

                // sort list pt. iii 
                // multisort
                $value['list'] = array_merge($arrayIsApplied, $arrayIsNotApplied);

            }
            unset($value);

            // echo "FacetsList: <pre>"; print_r($facetList); echo "<pre>";
            return $facetList;
    }
}

?>