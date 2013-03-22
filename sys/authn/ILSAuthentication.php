<?php
/**
 * ILS authentication module.
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
require_once 'Authentication.php';

/**
 * ILS authentication module.
 *
 * @category VuFind
 * @package  Authentication
 * @author   Franck Borel <franck.borel@gbv.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_an_authentication_handler Wiki
 */
class ILSAuthentication implements Authentication
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
        
        $username = $_POST['username'];
        $password = $_POST['password'];

        if ($username == '' || $password == '') {
            $user = new PEAR_Error('authentication_error_blank');
        } else {
            // Connect to catalog:
            $catalog = ConnectionManager::connectToCatalog();

            if ($catalog && $catalog->status) {
                $patron = $catalog->patronLogin($username, $password);
                if ($patron && !PEAR::isError($patron)) {
                    $user = $this->_processILSUser($patron);
                } else {
                    $user = new PEAR_Error('authentication_error_invalid');
                }
            } else {
                $user = new PEAR_Error('authentication_error_technical');
            }
        } 
        return $user;
    }

    /**
     * Update the database using details from the ILS, then return the User object.
     *
     * @param array $info User details returned by ILS driver.
     *
     * @return object     Processed User object.
     * @access private
     */
    private function _processILSUser($info)
    {
        include_once "services/MyResearch/lib/User.php";

        // Check to see if we already have an account for this user:
        $user = new User();
        $user->username = $info['cat_username'];
        if ($user->find(true)) {
            $insert = false;
        } else {
            $insert = true;
        }

        // No need to store the ILS password in VuFind's main password field:
        $user->password = "";

        // Update user information based on ILS data:
        $user->firstname = $info['firstname'] == null ? " " : $info['firstname'];
        $user->lastname = $info['lastname'] == null ? " " : $info['lastname'];
        $user->cat_username = $info['cat_username'] == null
            ? " " : $info['cat_username'];
        $user->cat_password = $info['cat_password'] == null
            ? " " : $info['cat_password'];
        $user->email = $info['email']        == null ? " " : $info['email'];
        $user->major = $info['major']        == null ? " " : $info['major'];
        $user->college = $info['college']      == null ? " " : $info['college'];

        // Either insert or update the database entry depending on whether or not
        // it already existed:
        if ($insert) {
            $user->created = date('Y-m-d');
            $user->insert();
        } else {
            $user->update();
        }

        // Send back the updated user object:
        return $user;
    }
}
?>
