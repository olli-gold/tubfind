<?php
/**
 * Results action for AlphaBrowse module
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
 * @package  Controller_AlphaBrowse
 * @author   Mark Triggs <vufind-tech@lists.sourceforge.net>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/alphabetical_heading_browse Wiki
 */
require_once 'Home.php';

/**
 * Results action for AlphaBrowse module
 *
 * @category VuFind
 * @package  Controller_AlphaBrowse
 * @author   Mark Triggs <vufind-tech@lists.sourceforge.net>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/alphabetical_heading_browse Wiki
 */
class Results extends Home
{
    /**
     * Display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $interface;
        global $configArray;

        // Connect to Solr:
        $db = ConnectionManager::connectToIndex();

        // Process incoming parameters:
        $source = isset($_GET['source']) ? $_GET['source'] : false;
        $from = isset($_GET['from']) ? $_GET['from'] : false;
        $page = (isset($_GET['page']) && is_numeric($_GET['page']))
            ? $_GET['page'] : 0;
        $limit = isset($configArray['AlphaBrowse']['page_size'])
            ? $configArray['AlphaBrowse']['page_size'] : 20;

        // If required parameters are present, load results:
        if ($source && $from !== false) {
            // Load Solr data or die trying:
            $result = $db->alphabeticBrowse($source, $from, $page, $limit, true);
            $this->_checkError($result);

            // No results?  Try the previous page just in case we've gone past the
            // end of the list....
            if ($result['Browse']['totalCount'] == 0) {
                $page--;
                $result = $db->alphabeticBrowse($source, $from, $page, $limit, true);
                $this->_checkError($result);
            }

            // Only display next/previous page links when applicable:
            if ($result['Browse']['totalCount'] > $limit) {
                $interface->assign('nextpage', $page + 1);
            }
            if ($result['Browse']['offset'] + $result['Browse']['startRow'] > 1) {
                $interface->assign('prevpage', $page - 1);
            }

            // Send other relevant values to the template:
            $interface->assign('source', $source);
            $interface->assign('from', $from);
            $interface->assign('result', $result);
        }

        // We also need to load all the same details as the basic Home action:
        parent::launch();
    }
    
    /**
     * Given an alphabrowse response, die with an error if necessary.
     *
     * @param array $result Result to check.
     *
     * @return void
     * @access private
     */
    private function _checkError($result)
    {
        if (isset($result['error'])) {
            // Special case --  missing alphabrowse index probably means the
            // user could use a tip about how to build the index.
            if (strstr($result['error'], 'does not exist')
                || strstr($result['error'], 'no such table')
                || strstr($result['error'], 'couldn\'t find a browse index')
            ) {
                $result['error'] = "Alphabetic Browse index missing.  See " .
                    "http://vufind.org/wiki/alphabetical_heading_browse for " .
                    "details on generating the index.";
            }
            PEAR::raiseError(new PEAR_Error($result['error']));
        }
    }
}

?>