<?php
/**
 * Holds action for MyResearch module
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
require_once 'services/MyResearch/MyResearch.php';

/**
 * Holds action for MyResearch module
 *
 * @category VuFind
 * @package  Controller_MyResearch
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Holds extends MyResearch
{
    protected $holdResults;
    protected $cancelResults;

    /**
     * Process parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $interface;

        // Get My Holds
        if ($patron = UserAccount::catalogLogin()) {
            if (PEAR::isError($patron)) {
                PEAR::raiseError($patron);
            }
            // Is cancelling Holds Available
            if ($this->cancelHolds != false) {

                // Get Message from Hold.php
                if (isset($_GET['success']) && $_GET['success'] != "") {
                    $this->holdResults = array(
                        'success' => true, 'status' => "hold_place_success"
                    );
                }

                // Process Submitted Form
                if (isset($_POST['cancelSelected']) || isset($_POST['cancelAll'])) {
                    $cancelRequest = $this->_cancelHolds($patron);
                }
                $interface->assign('holdResults', $this->holdResults);
                $interface->assign('cancelResults', $this->cancelResults);
            }

            $result = $this->catalog->getMyHolds($patron);
            if (!PEAR::isError($result)) {
                if (count($result)) {
                    $recordList = array();
                    foreach ($result as $row) {
                        $record = $this->db->getRecord($row['id']);
                        $record['ils_details'] = $row;
                        $recordList[] = $record;
                    }

                    // Get List of PickUp Libraries based on patrons home library
                    $libs = $this->catalog->getPickUpLocations($patron);
                    $interface->assign('pickup', $libs);

                    if ($this->cancelHolds != false) {
                        $recordList = $this->_addCancelDetails($recordList);
                    }
                    $interface->assign('recordList', $recordList);
                } else {
                    $interface->assign('recordList', false);
                }
            } else {
                PEAR::raiseError($result);
            }
        }

        $interface->setTemplate('holds.tpl');
        $interface->setPageTitle('My Holds');
        $interface->display('layout.tpl');
    }

    /**
     * Private method for cancelling holds
     *
     * @param array $patron An array of patron information
     *
     * @return null
     * @access private
     */
    private function _cancelHolds($patron)
    {
        global $interface;

        $gatheredDetails['details'] = isset($_POST['cancelAll'])
                ? $_POST['cancelAllIDS'] : $_POST['cancelSelectedIDS'];

        if (is_array($gatheredDetails['details'])) {

            $session_details = $_SESSION['cancelValidData'];

            foreach ($gatheredDetails['details'] as $info) {
                // If the user input contains a value not found in the session
                // whitelist, something has been tampered with -- abort the process.
                if (!in_array($info, $session_details)) {
                    $interface->assign('errorMsg', 'error_inconsistent_parameters');
                    return false;
                }
            }

            // Add Patron Data to Submitted Data
            $gatheredDetails['patron'] = $patron;
            $this->cancelResults = $this->catalog->cancelHolds($gatheredDetails);
            if ($this->cancelResults == false) {
                $interface->assign('errorMsg', 'hold_cancel_fail');
            } else {
                return true;
            }
        } else {
             $interface->assign('errorMsg', 'hold_empty_selection');
        }
        return false;
    }

    /**
     * Adds a link or form details to existing hold details
     *
     * @param array $recordList An array of patron holds
     *
     * @return array An array of patron holds with links / form details
     * @access private
     */
    private function _addCancelDetails($recordList)
    {
        global $interface;
        $session_details = array();

        foreach ($recordList as $record) {
            // Generate Form Details for cancelling Holds if Cancelling Holds
            // is enabled
            if ($this->cancelHolds['function'] == "getCancelHoldLink") {
                // Build OPAC URL
                $record['ils_details']['cancel_link']
                    = $this->catalog->getCancelHoldLink($record['ils_details']);
            } else {
                // Form Details
                $interface->assign('cancelForm', true);
                $cancel_details
                    = $this->catalog->getCancelHoldDetails($record['ils_details']);
                $record['ils_details']['cancel_details']
                    = $session_details[] = $cancel_details;
            }
            $holdList[] = $record;
        }
        // Save all valid options in the session so user input can be validated later
        $_SESSION['cancelValidData'] = $session_details;
        return $holdList;
    }
}

?>