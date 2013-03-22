<?php
/**
 * Hold action for Record module
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
 * @package  Controller_Record
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */

require_once 'Record.php';
require_once 'Crypt/generateHMAC.php';

/**
 * Hold action for Record module
 *
 * @category VuFind
 * @package  Controller_Record
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Hold extends Record
{
    protected $gatheredDetails;
    protected $logonURL;

    /**
     * Process incoming parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $configArray;
        global $interface;
        global $user;

        // Are Holds Allowed?
        $this->checkHolds = $this->catalog->checkFunction("Holds");
        if ($this->checkHolds != false) {

            // Do we have valid information?
            // Sets $this->logonURL and $this->gatheredDetails
            $validate = $this->_validateHoldData($this->checkHolds['HMACKeys']);
            if (!$validate) {
                header(
                    'Location: ../../Record/' .
                    urlencode($this->recordDriver->getUniqueID())
                );
                return false;
            }

            // Assign FollowUp Details required for login and catalog login
            $interface->assign('followup', true);
            $interface->assign('recordId', $this->recordDriver->getUniqueID());
            $interface->assign('followupModule', 'Record');
            $interface->assign('followupAction', 'Hold'.$this->logonURL);

            // User Must be logged In to Place Holds
            if (UserAccount::isLoggedIn()) {
                if ($patron = UserAccount::catalogLogin()) {
                    // Block invalid requests:
                    if (!$this->catalog->checkRequestIsValid(
                        $this->recordDriver->getUniqueID(),
                        $this->gatheredDetails, $patron
                    )) {
                        header(
                            'Location: ../../Record/' .
                            urlencode($this->recordDriver->getUniqueID()) .
                            "?errorMsg=hold_error_blocked#top"
                        );
                        return false;
                    }

                    $interface->assign('formURL', $this->logonURL);

                    $interface->assign('gatheredDetails', $this->gatheredDetails);

                    // Get List of PickUp Libraries
                    $libs = $this->catalog->getPickUpLocations(
                        $patron, $this->gatheredDetails
                    );
                    $interface->assign('pickup', $libs);
                    $interface->assign('home_library', $user->home_library);

                    $interface->assign('defaultDuedate', $this->getDefaultDueDate());

                    $extraHoldFields = isset($this->checkHolds['extraHoldFields'])
                        ? explode(":", $this->checkHolds['extraHoldFields'])
                            : array();
                    $interface->assign('extraHoldFields', $extraHoldFields);

                    $defaultPickUpLoc = $this->catalog->getDefaultPickUpLocation(
                        $patron, $this->gatheredDetails
                    );
                    $interface->assign('defaultPickUpLocation', $defaultPickUpLoc);

                    if (isset($_POST['placeHold'])) {
                        // If the form contained a pickup location, make sure that
                        // the value has not been tampered with:
                        if (!$this->validatePickUpInput($extraHoldFields, $libs)) {
                            $this->assignError(
                                array('status' => 'error_inconsistent_parameters')
                            );
                        } else if ($this->_placeHold($patron)) {
                            // If we made it this far, we're ready to place the hold;
                            // if successful, we will redirect and can stop here.
                            return;
                        }
                    }
                }
                $interface->setPageTitle(
                    translate('request_place_text') . ': ' .
                    $this->recordDriver->getBreadcrumb()
                );
                // Display Hold Form
                $interface->assign('subTemplate', 'hold-submit.tpl');

                // Main Details
                $interface->setTemplate('view.tpl');
                // Display Page
                $interface->display('layout.tpl');
            } else {
                // User is not logged in
                // Display Login Form
                $interface->setTemplate('../MyResearch/login.tpl');
                // Display Page
                $interface->display('layout.tpl');
            }

        } else {
            // Shouldn't Be Here
            header(
                'Location: ../../Record/' .
                urlencode($this->recordDriver->getUniqueID())
            );
            return false;
        }
    }

    /**
     * Check if the user-provided pickup location is valid.
     *
     * @param array $extraHoldFields Hold form fields enabled by configuration/driver
     * @param array $pickUpLibs      Pickup library list from driver
     *
     * @return bool
     * @access protected
     */
    protected function validatePickUpInput($extraHoldFields, $pickUpLibs)
    {
        // Not having to care for pickUpLocation is equivalent to having a valid one.
        if (!in_array('pickUpLocation', $extraHoldFields)) {
            return true;
        }

        // Check the valid pickup locations for a match against user input:
        return $this->validatePickUpLocation(
            $this->gatheredDetails['pickUpLocation'], $pickUpLibs
        );
    }

    /**
     * Check if the provided pickup location is valid.
     *
     * @param string $location   Location to check
     * @param array  $pickUpLibs Pickup locations list from driver
     *
     * @return bool
     * @access protected
     */
    protected function validatePickUpLocation($location, $pickUpLibs)
    {
        foreach ($pickUpLibs as $lib) {
            if ($location == $lib['locationID']) {
                return true;
            }
        }

        // If we got this far, something is wrong!
         return false;
    }

    /**
     * Protected method for getting a default due date
     *
     * @return string A formatted default due date
     * @access protected
     */

    protected function getDefaultDueDate()
    {
        include_once 'sys/VuFindDate.php';
        $formatDate = new VuFindDate();

        $dateArray = isset($this->checkHolds['defaultRequiredDate'])
             ? explode(":", $this->checkHolds['defaultRequiredDate'])
             : array(0, 1, 0);
        list($d, $m, $y) = $dateArray;
        $nextMonth  = mktime(
            0, 0, 0, date("m")+$m,   date("d")+$d,   date("Y")+$y
        );

        return $formatDate->convertToDisplayDate("U", $nextMonth);
    }

    /**
     * Send an error response to the view.
     *
     * @param array $results Place hold response containing an error.
     *
     * @return void
     * @access protected
     */
    protected function assignError($results)
    {
        global $interface;

        $interface->assign('results', $results);

        // Fail: Display Form for Try Again
        // Get as much data back as possible
        $interface->assign('subTemplate', 'hold-submit.tpl');
    }

    /**
     * Private method for validating hold data
     *
     * @param array $linkData An array of keys to check
     *
     * @return boolean True on success
     * @access private
     */
    private function _validateHoldData($linkData)
    {
        foreach ($linkData as $details) {
            $keyValueArray[$details] = $_GET[$details];
        }
        $hashKey = generateHMAC($linkData, $keyValueArray);

        if ($_REQUEST['hashKey'] != $hashKey) {
            return false;
        } else {
            // Initialize gatheredDetails with any POST values we find; this will
            // allow us to repopulate the hold form with user-entered values if there
            // is an error.  However, it is important that we load the POST data
            // FIRST and then override it with GET values in order to ensure that
            // the user doesn't bypass the hashkey verification by manipulating POST
            // values.
            $this->gatheredDetails = isset($_POST['gatheredDetails'])
                ? $_POST['gatheredDetails'] : array();

            // Make sure the bib ID is included, even if it's not loaded as part of
            // the validation loop below.
            $this->gatheredDetails['id'] = $_GET['id'];

            // Get Values Passed from holdings.php
            $i=0;
            foreach ($linkData as $details) {
                $this->gatheredDetails[$details] = $_GET[$details];
                // Build Logon URL
                if ($i == 0) {
                    $this->logonURL = "?".$details."=".urlencode($_GET[$details]);
                } else {
                    $this->logonURL .= "&".$details."=".urlencode($_GET[$details]);
                }
                $i++;
            }
            $this->logonURL .= ($i == 0 ? '?' : '&') .
                "hashKey=".urlencode($hashKey);
        }
        return true;
    }

    /**
     * Private method for placing holds
     *
     * @param array $patron An array of patron information
     *
     * @return boolean true on success, false on failure
     * @access private
     */
    private function _placeHold($patron)
    {
        // Add Patron Data to Submitted Data
        $holdDetails = $this->gatheredDetails + array('patron' => $patron);

        // Attempt to place the hold:
        $function = (string)$this->checkHolds['function'];
        $results = $this->catalog->$function($holdDetails);
        if (PEAR::isError($results)) {
            PEAR::raiseError($results);
        }
        // Success: Go to Display Holds
        if ($results['success'] == true) {
            header('Location: ../../MyResearch/Holds?success=true');
            return true;
        } else {
            $this->assignError($results);
        }
        return false;
    }
}

?>