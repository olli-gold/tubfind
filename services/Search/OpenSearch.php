<?php
/**
 * OpenSearch action for Search module
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
 * OpenSearch action for Search module
 *
 * @category VuFind
 * @package  Controller_Search
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class OpenSearch extends Action
{
    /**
     * Process incoming parameters and display the XML response.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        header('Content-type: text/xml');

        if (isset($_GET['method'])) {
            $method = '_' . $_GET['method'];
            if (is_callable(array($this, $method))) {
                $this->$method();
            } else {
                //echo '<Error>Invalid Method. Use either ' .
                //    '"describe" or "search"</Error>';
                echo '<Error>Invalid Method. Only "describe" is supported</Error>';
            }
        } else {
            $this->_describe();
        }
    }

    /**
     * Provide a response to the OpenSearch describe request.
     *
     * @return void
     * @access private
     */
    private function _describe()
    {
        global $interface;
        global $configArray;

        $interface->assign('site', $configArray['Site']);

        $interface->display('Search/opensearch-describe.tpl');
    }

    /* Unused, incomplete method -- commented out 10/9/09 to prevent confusion:
    private function _search()
    {
        // Setup Search Engine Connection
        $db = ConnectionManager::connectToIndex();

        $search = array();
        $search[] = array('lookfor' => $_GET['lookfor'],
                          'type' => $_GET['type']);
        $query = $db->buildQuery($search);
        $results = $db->search($query['query']);
        $interface->assign('results', $results);

        $interface->display('Search/opensearch-search.tpl');
    }
     */
}
?>