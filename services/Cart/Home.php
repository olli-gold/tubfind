<?php
/**
 * Home action for Bulk module
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
 * @package  Controller_Cart
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @author   Luke O'Sullivan <l.osullivan@swansea.ac.uk>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */

require_once 'Action.php';
require_once 'Bulk.php';

/**
 * Home action for Bulk module
 *
 * @category VuFind
 * @package  Controller_Cart
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @author   Luke O'Sullivan <l.osullivan@swansea.ac.uk>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Home extends Bulk
{
    /**
     * Constructor.
     *
     * @access public
     */
    function __construct()
    {
        global $interface;
        global $configArray;
        global $user;

        parent::__construct();
    }

    /**
     * Process parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $configArray;
        global $interface;
        global $user;

        // Generic Bulk Functions
        if (isset($_REQUEST['export']) || isset($_REQUEST['exportInit'])) {
            // Export
            if (isset($_REQUEST['exportToRefworks'])) {
                $_SESSION['exportIDS'] = $_REQUEST['ids'];
                $_SESSION['exportFormat'] = 'refworks_data';
            }
            $action = array('module' => 'Cart', 'action' => 'Export');
        } else if (isset($_REQUEST['email'])) {
            // Email
            $action = array('module' => 'Cart', 'action' => 'Email');
        } else if (isset($_REQUEST['print'])) {
            // Print
            $action = array('module' => 'Cart', 'action' => 'PrintCart');
        } else if ($this->origin == "Favorites") {
            // Favorites Functions
            if (isset($_REQUEST['delete'])) {
                // Delete
                $action = array('module' => 'MyResearch', 'action' => 'Delete');
            } else if (isset($_REQUEST['deleteList'])) {
                // Delete List
                $action = array('module' => 'Cart', 'action' => 'Confirm');
            } else if (isset($_REQUEST['editList']) && isset($_POST['listID'])) {
                // Edit List
                $this->followupUrl = $configArray['Site']['url'] .
                    "/MyResearch/EditList/" . $_POST['listID'];
                header("Location: " . $this->followupUrl);
                exit();
            } else if (isset($_REQUEST['add'])) {
                //Update Cart
                $action = array('module' => 'Cart', 'action' => 'Cart');
            } else {
                //Error
                $action = array('module' => 'Cart', 'action' => 'BulkError');
            }
        } else {
            // Cart Functions (Default)
            if (isset($_REQUEST['empty'])
                || isset($_REQUEST['delete']) || isset($_REQUEST['update'])
            ) {
                // Empty / Delete Cart
                $action = array('module' => 'Cart', 'action' => 'Cart');
            } else if (isset($_REQUEST['saveCart'])) {
                // Save Cart
                $action = array('module' => 'Cart', 'action' => 'Save');
            } else {
                // View Cart
                $action = array('module' => 'Cart', 'action' => 'Cart');
            }
        }
        $className = $action['action'];
        include_once "services/{$action['module']}/{$action['action']}.php";
        $service = new $className();
        return $service->launch();
    }
}
