<?php
/**
 * SIP2 authentication module.
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
 * @package  Authentication
 * @author   Franck Borel <franck.borel@gbv.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_an_authentication_handler Wiki
 */
require_once 'sys/SIP2.php';
require_once 'Authentication.php';

/**
 * SIP2 authentication module.
 *
 * @category VuFind
 * @package  Authentication
 * @author   Franck Borel <franck.borel@gbv.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_an_authentication_handler Wiki
 */
class SIPAuthentication implements Authentication
{
    /**
     * Attempt to authenticate the current user.
     *
     * @return object User object if successful, PEAR_Error otherwise.
     * @access public
     */
    public function authenticate()
    {
        global $configArray;
        
        if (isset($_POST['username']) && isset($_POST['password'])) {
            $username = $_POST['username'];
            $password = $_POST['password'];
            if ($username != '' && $password != '') {
                // Attempt SIP2 Authentication

                $mysip = new sip2;
                $mysip->hostname = $configArray['SIP2']['host'];
                $mysip->port = $configArray['SIP2']['port'];

                if ($mysip->connect()) {
                    //send selfcheck status message
                    $in = $mysip->msgSCStatus();
                    $msg_result = $mysip->get_message($in);

                    // Make sure the response is 98 as expected
                    if (preg_match("/^98/", $msg_result)) {
                        $result = $mysip->parseACSStatusResponse($msg_result);

                        //  Use result to populate SIP2 setings
                        $mysip->AO = $result['variable']['AO'][0];
                        $mysip->AN = $result['variable']['AN'][0];

                        $mysip->patron = $username;
                        $mysip->patronpwd = $password;

                        $in = $mysip->msgPatronStatusRequest();
                        $msg_result = $mysip->get_message($in);

                        // Make sure the response is 24 as expected
                        if (preg_match("/^24/", $msg_result)) {
                            $result = $mysip->parsePatronStatusResponse($msg_result);

                            if (($result['variable']['BL'][0] == 'Y')
                                and ($result['variable']['CQ'][0] == 'Y')
                            ) {
                                // Success!!!
                                $user = $this->_processSIP2User(
                                    $result, $username, $password
                                );

                                // Set login cookie for 1 hour
                                $user->password = $password; // Need this for Metalib
                            } else {
                                $user = new PEAR_Error(
                                    'authentication_error_invalid'
                                );
                            }
                        } else {
                            $user = new PEAR_Error(
                                'authentication_error_technical'
                            );
                        }
                    } else {
                        $user = new PEAR_Error('authentication_error_technical');
                    }
                    $mysip->disconnect();

                } else {
                    $user = new PEAR_Error('authentication_error_technical');
                }
            } else {
                $user = new PEAR_Error('authentication_error_blank');
            }
        } else {
            $user = new PEAR_Error('authentication_error_blank');
        }

        return $user;
    }

    /**
     * Process SIP2 User Account
     *
     * Based on code by Bob Wicksall <bwicksall@pls-net.org>.
     *
     * @param array $info     An array of user information
     * @param array $username The user's ILS username
     * @param array $password The user's ILS password
     *
     * @return object         Populated User object.
     * @access private
     */
    private function _processSIP2User($info, $username, $password)
    {
        include_once "services/MyResearch/lib/User.php";

        $user = new User();
        $user->username = $info['variable']['AA'][0];
        if ($user->find(true)) {
            $insert = false;
        } else {
            $insert = true;
        }

        // This could potentially be different depending on the ILS.  Name could be
        // Bob Wicksall or Wicksall, Bob. This is currently assuming Wicksall, Bob
        $ae = $info['variable']['AE'][0];
        $user->firstname = trim(substr($ae, 1 + strripos($ae, ',')));
        $user->lastname = trim(substr($ae, 0, strripos($ae, ',')));
        // I'm inserting the sip username and password since the ILS is the source.
        // Should revisit this.
        $user->cat_username = $username;
        $user->cat_password = $password;
        $user->email = 'email';
        $user->major = 'null';
        $user->college = 'null';

        if ($insert) {
            $user->created = date('Y-m-d');
            $user->insert();
        } else {
            $user->update();
        }

        return $user;
    }
}
?>
