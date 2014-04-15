<?php
/**
 * Results action for Search module
 *
 * PHP version 5
 *
 * Copyright (C) Andrew Nagy 2009
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
 * Results action for Search module
 *
 * @category VuFind
 * @package  Controller_Search
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Editions extends Action
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

        $url = null;
        $core = null;
        if (substr($_REQUEST['id'], 0, 2) == 'PC') {
            $url = isset($configArray['IndexShards']['Primo Central']) ? 'http://'.$configArray['IndexShards']['Primo Central'] : null;
            $url = str_replace('/biblio', '', $url);
            $core = 'biblio';
        }

        // Setup Search Engine Connection
        $db = ConnectionManager::connectToIndex(null, $core, $url);

        // Retrieve the record from the index
        if (!($record = $db->getRecord($_REQUEST['id']))) {
            PEAR::raiseError(new PEAR_Error('Record Does Not Exist'));
        }
        $recordDriver = RecordDriverFactory::initRecordDriver($record);

        // Find Other Editions
        $editions = $recordDriver->getEditions();
        if (!PEAR::isError($editions)) {
            $interface->assign('editions', $editions);
        }

        // Done, display the page
        $interface->display('Search/editions.tpl');
    } // End launch()

}

?>
