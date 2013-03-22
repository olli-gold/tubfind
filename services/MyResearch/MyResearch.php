<?php
/**
 * Base class for most MyResearch module actions
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
 * @package  Controller_MyResearch
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'Action.php';

require_once 'services/MyResearch/lib/User.php';
require_once 'services/MyResearch/lib/Resource.php';

/**
 * Base class for most MyResearch module actions
 *
 * @category VuFind
 * @package  Controller_MyResearch
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class MyResearch extends Action
{
    protected $db;
    protected $catalog;
    protected $errorMsg;
    protected $infoMsg;
    protected $showExport;
    protected $followupUrl;
    protected $checkHolds;
    protected $checkRenew;
    protected $cancelHolds;

    /**
     * Constructor
     *
     * @param bool $skipLogin Set to true to bypass the default login requirement.
     *
     * @access public
     */
    public function __construct($skipLogin = false)
    {
        global $interface;
        global $configArray;
        global $user;

        if (!$skipLogin && !UserAccount::isLoggedIn()) {
            include_once 'Login.php';
            Login::launch();
            exit();
        }

        // Setup Search Engine Connection
        $this->db = ConnectionManager::connectToIndex();

        // Connect to Database
        $this->catalog = ConnectionManager::connectToCatalog();

        // Is Placing Holds allowed?
        $this->checkHolds = $this->catalog->checkFunction("Holds");

        // Is Cancelling Holds allowed?
        $this->cancelHolds = $this->catalog->checkFunction("cancelHolds");

        // Is Renewing Items allowed?
        $this->checkRenew = $this->catalog->checkFunction("Renewals");

        // Register Library Catalog Account
        if (isset($_POST['submit']) && !empty($_POST['submit']) && $this->catalog
            && isset($_POST['cat_username']) && isset($_POST['cat_password'])
        ) {
            if (UserAccount::processCatalogLogin(
                $_POST['cat_username'], $_POST['cat_password']
            )) {
                $interface->assign('user', $user);
            } else {
                $interface->assign('loginError', 'Invalid Patron Login');
            }
        }

        // Assign Exporter Options
        $exportOptions = array();
        if ($configArray['BulkExport']['enabled']) {
            $options = explode(':', $configArray['BulkExport']['options']);
            foreach ($options as $option) {
                if ($configArray['Export'][$option] == true) {
                    $exportOptions[] = $option;
                }
            }
            $interface->assign('exportOptions', $exportOptions);
        }
        // Get Messages
        $this->infoMsg = isset($_GET['infoMsg']) ? $_GET['infoMsg'] : false;
        $this->errorMsg = isset($_GET['errorMsg']) ? $_GET['errorMsg'] : false;
        $this->showExport = isset($_GET['showExport']) ? $_GET['showExport'] : false;
        $this->followupUrl = false;
    }
}

?>
