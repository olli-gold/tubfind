<?php
/**
 * Common AJAX functions for the Recommender Visualisation module using JSON as
 * output format.
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
 * @package  Controller_AJAX
 * @author   Till Kinstler <kinstler@gbv.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */

require_once 'JSON.php';

/**
 * Common AJAX functions for the Recommender Visualisation module using JSON as
 * output format.
 *
 * @category VuFind
 * @package  Controller_AJAX
 * @author   Till Kinstler <kinstler@gbv.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class JSON_Vis extends JSON
{
    private $_searchObject;
    private $_dateFacets;

    /**
     * Constructor.
     *
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
        $this->_searchObject = SearchObjectFactory::initSearchObject();
        $this->_dateFacets = isset($_GET['facetFields'])
            ? explode(':', $_GET['facetFields']) : array();
    }

    /**
     * Get data and output in JSON
     *
     * @param array $fields Fields to process
     *
     * @return void
     * @access public
     */
    public function getVisData($fields = array('publishDate'))
    {
        global $interface;

        if (is_a($this->_searchObject, 'SearchObject_Solr')) {
            $this->_searchObject->init();
            $filters = $this->_searchObject->getFilters();
            $fields = $this->_processDateFacets($filters);
            $facets = $this->_processFacetValues($fields);
            foreach ($fields as $field => $val) {
                $facets[$field]['min'] = $val[0] > 0 ? $val[0] : 0;
                $facets[$field]['max'] = $val[1] > 0 ? $val[1] : 0;
                $facets[$field]['removalURL']
                    = $this->_searchObject->renderLinkWithoutFilter(
                        isset($filters[$field][0])
                        ? $field .':' . $filters[$field][0] : null
                    );
            }
            $this->output($facets, JSON::STATUS_OK);
        } else {
            $this->output("", JSON::STATUS_ERROR);
        }
    }

    /**
     * Support method for getVisData() -- filter bad values from facet lists.
     *
     * @param array $fields Processed date information from _processDateFacets()
     *
     * @return array
     * @access private
     */
    private function _processFacetValues($fields)
    {
        $facets = $this->_searchObject->getFullFieldFacets(array_keys($fields));
        $retVal = array();
        foreach ($facets as $field => $values) {
            $newValues = array('data' => array());
            foreach ($values['data'] as $current) {
                // Only retain numeric values!
                if (preg_match("/^[0-9]+$/", $current[0])) {
                    $newValues['data'][] = $current;
                }
            }
            $retVal[$field] = $newValues;
        }
        return $retVal;
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
        }
        return $result;
    }
}
?>
