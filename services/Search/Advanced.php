<?php
/**
 * Advanced search action for Search module
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2007.
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
 * @package  Controller_Search
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'Action.php';

/**
 * Advanced search action for Search module
 *
 * @category VuFind
 * @package  Controller_Search
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Advanced extends Action
{
    /**
     * Process incoming parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $interface;
        global $configArray;
        global $user;

        // Create our search object
        $searchObject = SearchObjectFactory::initSearchObject();
        $searchObject->initAdvancedFacets();
        // We don't want this search in the search history
        $searchObject->disableLogging();
        // Go get the facets
        $searchObject->processSearch();
        $facetList = $searchObject->getFacetList();
        //Assign page limit options & last limit from session
        $interface->assign('limitList',  $searchObject->getLimitList());
        // Shutdown the search object
        $searchObject->close();

        // Load a saved search, if any:
        $savedSearch = $this->_loadSavedSearch();

        // Process the facets for appropriate display on the Advanced Search screen:
        $facets = $this->_processFacets($facetList, $savedSearch);
        $interface->assign('facetList', $facets);

        // Integer for % width of each column (be careful to avoid divide by zero!)
        $columnWidth = (count($facets) > 1) ? round(100 / count($facets), 0) : 0;
        $interface->assign('columnWidth', $columnWidth);

        // Process settings to control special-purpose facets not supported by the
        //     more generic configuration options.
        $specialFacets
            = $searchObject->getFacetSetting('Advanced_Settings', 'special_facets');
        if (stristr($specialFacets, 'illustrated')) {
            $interface->assign(
                'illustratedLimit', $this->_getIllustrationSettings($savedSearch)
            );
        }
        if (stristr($specialFacets, 'daterange')) {
            $interface->assign(
                'dateRangeLimit', $this->_getDateRangeSettings($savedSearch)
            );
        }

        // Send search type settings to the template
        $interface->assign('advSearchTypes', $searchObject->getAdvancedTypes());

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
     * Get the possible legal values for the illustration limit radio buttons.
     *
     * @param object $savedSearch Saved search object (false if none)
     *
     * @return array              Legal options, with selected value flagged.
     * @access private
     */
    private function _getIllustrationSettings($savedSearch = false)
    {
        $illYes= array(
            'text' => 'Has Illustrations', 'value' => 1, 'selected' => false
        );
        $illNo = array(
            'text' => 'Not Illustrated', 'value' => 0, 'selected' => false
        );
        $illAny = array(
            'text' => 'No Preference', 'value' => -1, 'selected' => false
        );

        // Find the selected value by analyzing facets -- if we find match, remove
        // the offending facet to avoid inappropriate items appearing in the
        // "applied filters" sidebar!
        if ($savedSearch && $savedSearch->hasFilter('illustrated:Illustrated')) {
            $illYes['selected'] = true;
            $savedSearch->removeFilter('illustrated:Illustrated');
        } else if ($savedSearch
            && $savedSearch->hasFilter('illustrated:"Not Illustrated"')
        ) {
            $illNo['selected'] = true;
            $savedSearch->removeFilter('illustrated:"Not Illustrated"');
        } else {
            $illAny['selected'] = true;
        }
        return array($illYes, $illNo, $illAny);
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
            if (isset($filters['publishDate'])) {
                foreach ($filters['publishDate'] as $current) {
                    if ($range = VuFindSolrUtils::parseRange($current)) {
                        $from = $range['from'] == '*' ? '' : $range['from'];
                        $to = $range['to'] == '*' ? '' : $range['to'];
                        $savedSearch->removeFilter('publishDate:' . $current);
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
                    if ($savedSearch->getSearchType() == 'advanced') {
                        // Activate facets so we get appropriate descriptions
                        // in the filter list:
                        $savedSearch->activateAllFacets('Advanced');
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
     * Process the facets to be used as limits on the Advanced Search screen.
     *
     * @param array  $facetList    The advanced facet values
     * @param object $searchObject Saved search object (false if none)
     *
     * @return array               Sorted facets, with selected values flagged.
     * @access private
     */
    private function _processFacets($facetList, $searchObject = false)
    {
        // Process the facets, assuming they came back
        $facets = array();
        foreach ($facetList as $facet => $list) {
            $currentList = array();
            foreach ($list['list'] as $value) {
                // Build the filter string for the URL:
                $fullFilter = $facet.':"'.$value['untranslated'].'"';

                // If we haven't already found a selected facet and the current
                // facet has been applied to the search, we should store it as
                // the selected facet for the current control.
                if ($searchObject && $searchObject->hasFilter($fullFilter)) {
                    $selected = true;
                    // Remove the filter from the search object -- we don't want
                    // it to show up in the "applied filters" sidebar since it
                    // will already be accounted for by being selected in the
                    // filter select list!
                    $searchObject->removeFilter($fullFilter);
                } else {
                    $selected = false;
                }
                $currentList[$value['value']]
                    = array('filter' => $fullFilter, 'selected' => $selected);
            }

            // Perform a natural case sort on the array of facet values:
            $keys = array_keys($currentList);
            natcasesort($keys);
            foreach ($keys as $key) {
                $facets[$list['label']][$key] = $currentList[$key];
            }
        }
        return $facets;
    }
}
?>