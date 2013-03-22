<?php
/**
 * Advanced search form action for Summon module
 *
 * PHP version 5
 *
 * Copyright (C) Andrew Nagy 2009.
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
 * @package  Controller_Summon
 * @author   Andrew Nagy <vufind-tech@lists.sourceforge.net>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'Base.php';

/**
 * Advanced search form action for Summon module
 *
 * @category VuFind
 * @package  Controller_Summon
 * @author   Andrew Nagy <vufind-tech@lists.sourceforge.net>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Advanced extends Base
{
    /**
     * Process parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $interface;
        global $configArray;
        global $user;

        // Load a saved search, if any:
        $savedSearch = $this->_loadSavedSearch();

        // Send search type settings to the template
        $interface->assign(
            'advSearchTypes', $this->searchObject->getAdvancedTypes()
        );

        // Get checkbox filters to present narrow options; this may also strip some
        // filters out of the $savedSearch object to prevent redundant information
        // being retrieved via the getFilterList() method.
        $interface->assign(
            'checkboxFilters', $this->_getCheckboxFilters($savedSearch)
        );

        // Process settings to control special-purpose facets not supported by the
        //     more generic configuration options.
        $config = getExtraConfigArray('Summon');
        $special = isset($config['Advanced_Facet_Settings']['special_facets']) ?
            $config['Advanced_Facet_Settings']['special_facets'] : '';
        if (stristr($special, 'daterange')) {
            $interface->assign(
                'dateRangeLimit', $this->_getDateRangeSettings($savedSearch)
            );
        }

        // If we found a saved search, let's assign some details to the interface:
        if ($savedSearch) {
            $interface->assign('searchDetails', $savedSearch->getSearchTerms());
            $interface->assign('searchFilters', $savedSearch->getFilterList());
        }

        $interface->setPageTitle('Advanced Search');
        $interface->setTemplate('advanced.tpl');
        $interface->display('layout.tpl');
    }

    /**
     * Get the current settings for the date range facet, if it is set:
     *
     * @param object $savedSearch Saved search object (false if none)
     *
     * @return array              Date range: Key 0 = from, Key 1 = to.
     * @access private
     */
    private function _getDateRangeSettings($savedSearch = false)
    {
        // Default to blank strings:
        $from = $to = '';

        // Check to see if there is an existing range in the search object:
        if ($savedSearch) {
            $filters = $savedSearch->getFilters();
            if (isset($filters['PublicationDate'])) {
                foreach ($filters['PublicationDate'] as $current) {
                    if ($range = VuFindSolrUtils::parseRange($current)) {
                        $from = $range['from'] == '*' ? '' : $range['from'];
                        $to = $range['to'] == '*' ? '' : $range['to'];
                        $savedSearch->removeFilter('PublicationDate:' . $current);
                        break;
                    }
                }
            }
        }

        // Send back the settings:
        return array($from, $to);
    }

    /**
     * Load a saved search, if appropriate and legal; assign an error to the
     * interface if necessary.
     *
     * @return mixed Search Object on successful load, false otherwise
     * @access private
     */
    private function _loadSavedSearch()
    {
        global $interface;

        // Are we editing an existing search?
        if (isset($_REQUEST['edit'])) {
            // Go find it
            $search = new SearchEntry();
            $search->id = $_REQUEST['edit'];
            if ($search->find(true)) {
                // Check permissions
                if ($search->session_id == session_id()
                    || $search->user_id == $user->id
                ) {
                    // Retrieve the search details
                    $minSO = unserialize($search->search_object);
                    $savedSearch = SearchObjectFactory::deminify($minSO);
                    // Make sure it's an advanced search
                    if ($savedSearch->getSearchType() == 'SummonAdvanced') {
                        // Activate facets so we get appropriate descriptions
                        // in the filter list:
                        $savedSearch->activateAllFacets();
                        return $savedSearch;
                    } else {
                        $interface->assign('editErr', 'notAdvanced');
                    }
                } else {
                    // No permissions
                    $interface->assign('editErr', 'noRights');
                }
            } else {
                // Not found
                $interface->assign('editErr', 'notFound');
            }
        }

        return false;
    }

    /**
     * Get information on checkbox filters.  If any checkbox filters are turned on
     * in $savedSearch, remove them to prevent redundant information from being
     * displayed in the filter list retrieved later via getFilterList().
     *
     * @param object $savedSearch Saved search object (false if none)
     *
     * @return array              Legal options, with selected value flagged.
     * @access private
     */
    private function _getCheckboxFilters($savedSearch = false)
    {
        // No saved search?  Just retrieve the default list from our fresh search
        // object:
        if ($savedSearch == false) {
            $this->searchObject->activateAllFacets();
            return $this->searchObject->getCheckboxFacets();
        }

        // If we got this far, we have a saved search.  Let's get the checkbox
        // facet information and use it to remove any selected filters!
        $filters = $savedSearch->getCheckboxFacets();
        foreach ($filters as $current) {
            if ($current['selected']) {
                $savedSearch->removeFilter($current['filter']);
            }
        }
        return $filters;
    }
}
?>