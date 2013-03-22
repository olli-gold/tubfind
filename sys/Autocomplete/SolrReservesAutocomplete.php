<?php
/**
 * Solr Reserves Autocomplete Module
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
 * @package  Autocomplete
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/autocomplete Wiki
 */
require_once 'sys/Autocomplete/SolrAutocomplete.php';

/**
 * Solr Reserves Autocomplete Module
 *
 * This class provides suggestions by using the local Solr reserves index.
 *
 * @category VuFind
 * @package  Autocomplete
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/autocomplete Wiki
 */
class SolrReservesAutocomplete extends SolrAutocomplete
{
    /**
     * Constructor
     *
     * Establishes base settings for making autocomplete suggestions.
     *
     * @param string $params Additional settings from searches.ini.
     *
     * @access public
     */
    public function __construct($params)
    {
        // Use a different default field; otherwise, behave the same as the parent:
        $this->defaultDisplayField = 'course';
        parent::__construct($params);
    }

    /**
     * getSuggestions
     *
     * This method returns an array of strings matching the user's query for
     * display in the autocomplete box.
     *
     * @param string $query The user query
     *
     * @return array        The suggestions for the provided query
     * @access public
     */
    public function getSuggestions($query)
    {
        $this->searchObject->disableLogging();
        $this->searchObject->setBasicQuery(
            $this->mungeQuery($query), $this->handler
        );
        $this->searchObject->setSort($this->sortField);
        foreach ($this->filters as $current) {
            $this->searchObject->addFilter($current);
        }

        // Perform the search:
        $result = $this->searchObject->processSearch(true);
        $resultDocs = isset($result['response']['docs'])
            ? $result['response']['docs'] : array();
        $this->searchObject->close();

        // Build the recommendation list:
        $results = array();
        foreach ($resultDocs as $current) {
            foreach ($this->displayField as $field) {
                if (isset($current[$field])
                    && $this->matchQueryTerms($current[$field], $query)
                ) {
                    $results[] = is_array($current[$field]) ?
                    $current[$field][0] : $current[$field];
                    break;
                }
            }
        }

        return array_unique($results);
    }

    /**
     * initSearchObject
     *
     * Initialize the search object used for finding recommendations.
     *
     * @return void
     * @access protected
     */
    protected function initSearchObject()
    {
        // Build a new search object:
        $this->searchObject = SearchObjectFactory::initSearchObject('SolrReserves');
    }

    /**
     * Return true if all terms in the query occurs in the field data string.
     *
     * @param string $data  The data field returned from solr
     * @param string $query The query string entered by the user
     *
     * @return bool
     * @access protected
     */
    protected function matchQueryTerms($data, $query)
    {
        $terms = preg_split("/\s+/", $query);
        foreach ($terms as $term) {
            if (stripos($data, $term) === false) {
                return false;
            }
        }
        return true;
    }
}

?>
