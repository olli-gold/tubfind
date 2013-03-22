<?php
/**
 * publishDateVis
 *
 * PHP version 5
 *
 * Copyright (C) Till Kinstler 2011.
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
 * @author   Till Kinstler <kinstler@gbv.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_recommendations_module Wiki
 */

require_once 'sys/Recommend/Interface.php';

/**
 * PubDateVisAjax Recommendations Module
 *
 * This class displays a visualisation of facet values in a recommendation module
 *
 * @category VuFind
 * @package  Recommendations
 * @author   Till Kinstler <kinstler@gbv.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_recommendations_module Wiki
 */
class PubDateVisAjax implements RecommendationInterface
{
    private $_searchObject;
    private $_baseSettings;
    private $_zooming;
    private $_dateFacets = array();

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
        $this->_searchObject = $searchObject;
        $params = explode(':', $params);
        if ($params[0] == "true" || $params[0] == "false") {
            $this->_zooming = $params[0];
            $this->_dateFacets = array_slice($params, 1);
        } else {
            $this->_zooming = "false";
            $this->_dateFacets = $params;
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
        // nothing to do
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

        // currently only works with Solr SearchObject
        if (is_a($this->_searchObject, 'SearchObject_Solr')) {
            $visFacets = $this->_processDateFacets(
                $this->_searchObject->getFilters()
            );
            $interface->assign(
                'visFacets',
                $visFacets
            );
            $interface->assign('zooming', $this->_zooming);
            $interface->assign('facetFields', implode(':', $this->_dateFacets));
            $interface->assign(
                'searchParams', $this->_searchObject->renderSearchUrlParams()
            );
        }
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
        return 'Search/Recommend/TopPubDateVisAjax.tpl';
    }

    /**
     * Support method for getVisData() -- extract details from applied filters.
     *
     * @param array $filters Current filter list
     *
     * @return array
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
            $result[$current]['label']
                = $this->_searchObject->getFacetLabel($current);
        }
        return $result;
    }
}

?>
