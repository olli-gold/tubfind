<?php
/**
 * Maintenance action for Admin module.
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
 * @package  Controller_Admin
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'Admin.php';

/**
 * Maintenance action for Admin module.
 *
 * @category VuFind
 * @package  Controller_Admin
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Maintenance extends Admin
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

        // Run the specified method if it exists...  but don't run the launch
        // method or we'll end up in an infinite loop!!
        if (isset($_GET['util']) && $_GET['util'] != 'launch'
            && method_exists($this, $_GET['util'])
        ) {
            $this->$_GET['util']();
        } else {
            $interface->setTemplate('maintenance.tpl');
            $interface->setPageTitle('System Maintenance');
            $interface->display('layout-admin.tpl');
        }
    }

    /**
     * Call the expiration code to clean up old saved searches in the database.
     *
     * @return void
     * @access public
     */
    public function deleteExpiredSearches()
    {
        global $interface;
        include_once 'services/MyResearch/lib/Search.php';

        // Use passed-in value as expiration age, or default to 2.
        $daysOld = isset($_REQUEST['daysOld']) ? intval($_REQUEST['daysOld']) : 2;

        // Fail if we have an invalid expiration age.
        if ($daysOld < 2) {
            $interface->assign(
                'status', "Expiration age must be at least two days."
            );
        } else {
            // Delete the expired searches -- this cleans up any junk left in the
            // database from old search histories that were not caught by the
            // session garbage collector.
            $search = new SearchEntry();
            $expired = $search->getExpiredSearches($daysOld);
            if (empty($expired)) {
                $interface->assign('status', "No expired searches to delete.");
            } else {
                $count = count($expired);
                foreach ($expired as $oldSearch) {
                    $oldSearch->delete();
                }
                $interface->assign('status', "{$count} expired searches deleted.");
            }
        }
        $interface->setTemplate('maintenance.tpl');
        $interface->setPageTitle('System Maintenance');
        $interface->display('layout-admin.tpl');
    }
}

?>