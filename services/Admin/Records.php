<?php
/**
 * Records action for Admin module
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
 * @package  Controller_Admin
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'Admin.php';

/**
 * Records action for Admin module
 *
 * @category VuFind
 * @package  Controller_Admin
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Records extends Admin
{
    private $_db;

    /**
     * Process parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $interface;

        // Run the specified method if it exists...  but don't run the launch
        // method or we'll end up in an infinite loop!!
        if (isset($_GET['util']) && $_GET['util'] != 'launch'
            && method_exists($this, $_GET['util'])
        ) {
            // Setup Search Engine Connection
            $this->_db = ConnectionManager::connectToIndex();

            $this->$_GET['util']();
        } else {
            $interface->setTemplate('records.tpl');
            $interface->setPageTitle('Record Management');
            $interface->display('layout-admin.tpl');
        }
    }

    /**
     * Display a specified record.
     *
     * @return void
     * @access public
     */
    public function viewRecord()
    {
        global $interface;

        // Read in the original record:
        $record = $this->_db->getRecord($_GET['id']);

        $interface->assign('record', $record);
        $interface->assign('recordId', $_GET['id']);

        $interface->setTemplate('record-view.tpl');
        $interface->display('layout-admin.tpl');
    }

    /**
     * Delete a specified record.
     *
     * @return void
     * @access public
     */
    public function deleteRecord()
    {
        global $interface;

        if (!empty($_GET['id'])) {
            $this->_db->deleteRecord($_GET['id']);
            $this->_db->commit();
            //$this->_db->optimize();
            $interface->assign('status', 'Record ' . $_GET['id'] . ' deleted.');
        } else {
            $interface->assign('status', 'Please specify a record to delete.');
        }

        $interface->setTemplate('records.tpl');
        $interface->display('layout-admin.tpl');
    }

    /**
     * Delete suppressed records.
     *
     * @return void
     * @access public
     */
    public function deleteSuppressed()
    {
        global $interface;

        ini_set('memory_limit', '50M');
        ini_set('max_execution_time', '3600');

        // Make ILS Connection
        $catalog = ConnectionManager::connectToCatalog();

        /*
        // Display Progress Page
        $interface->display('loading.tpl');
        ob_flush();
        flush();
        */

        // Get Suppressed Records and Delete from index
        $deletes = array();
        if ($catalog && $catalog->status) {
            $result = $catalog->getSuppressedRecords();
            if (!PEAR::isError($result)) {
                $status = $this->_db->deleteRecords($result);
                foreach ($result as $current) {
                    $deletes[] = array('id' => $current);
                }

                $this->_db->commit();
                $this->_db->optimize();
            }
        } else {
            PEAR::raiseError(new PEAR_Error('Cannot connect to ILS'));
        }

        $interface->assign('resultList', $deletes);

        $interface->setTemplate('grid.tpl');
        $interface->setPageTitle('Delete Suppressed');
        $interface->display('layout-admin.tpl');
    }
}

?>