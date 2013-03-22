<?php
/**
 * LDAP authentication module.
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
require_once 'PEAR.php';
require_once 'services/MyResearch/lib/User.php';
require_once 'Authentication.php';
require_once 'LDAPConfigurationParameter.php';

/**
 * LDAP authentication module.
 *
 * @category VuFind
 * @package  Authentication
 * @author   Franck Borel <franck.borel@gbv.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_an_authentication_handler Wiki
 */
class LDAPAuthentication implements Authentication
{
    private $_username;
    private $_password;
    private $_ldapConfigurationParameter;

    /**
     * Constructor
     *
     * @param string $configurationFilePath Optional configuration file path.
     *
     * @access public
     */
    public function __construct($configurationFilePath = '')
    {
        $this->_ldapConfigurationParameter
            = new LDAPConfigurationParameter($configurationFilePath);
    }

    /**
     * Attempt to authenticate the current user.
     *
     * @return object User object if successful, PEAR_Error otherwise.
     * @access public
     */
    public function authenticate()
    {
        $this->_username = $_POST['username'];
        $this->_password = $_POST['password'];
        if ($this->_username == '' || $this->_password == '') {
            return new PEAR_Error('authentication_error_blank');
        }
        $this->_trimCredentials();
        return $this->_bindUser();
    }

    /**
     * Trim the credentials stored in the object.
     *
     * @return void
     * @access private
     */
    private function _trimCredentials()
    {
        $this->_username = trim($this->_username);
        $this->_password = trim($this->_password);
    }

    /**
     * Communicate with LDAP and obtain user details.
     *
     * @return object User object if successful, PEAR_Error otherwise.
     * @access private
     */
    private function _bindUser()
    {
        $ldapConnectionParameter
            = $this->_ldapConfigurationParameter->getParameter();

        // Try to connect to LDAP and die if we can't; note that some LDAP setups
        // will successfully return a resource from ldap_connect even if the server
        // is unavailable -- we need to check for bad return values again at search
        // time!
        $ldapConnection = @ldap_connect(
            $ldapConnectionParameter['host'], $ldapConnectionParameter['port']
        );
        if (!$ldapConnection) {
            return new PEAR_ERROR('authentication_error_technical');
        }

        // Set LDAP options -- use protocol version 3 and then initiate TLS so we
        // can have a secure connection over the standard LDAP port.
        @ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);
        if (!@ldap_start_tls($ldapConnection)) {
            return new PEAR_ERROR('authentication_error_technical');
        }

        // If bind_username and bind_password were supplied in the config file, use
        // them to access LDAP before proceeding.  In some LDAP setups, these
        // settings can be excluded in order to skip this step.
        if (isset($ldapConnectionParameter['bind_username'])
            && isset($ldapConnectionParameter['bind_password'])
        ) {
            $ldapBind = @ldap_bind(
                $ldapConnection, $ldapConnectionParameter['bind_username'],
                $ldapConnectionParameter['bind_password']
            );
            if (!$ldapBind) {
                return new PEAR_ERROR('authentication_error_technical');
            }
        }

        // Search for username
        $ldapFilter = $ldapConnectionParameter['username'] . '=' . $this->_username;
        $ldapSearch = @ldap_search(
            $ldapConnection, $ldapConnectionParameter['basedn'], $ldapFilter
        );
        if (!$ldapSearch) {
            return new PEAR_ERROR('authentication_error_technical');
        }

        $info = ldap_get_entries($ldapConnection, $ldapSearch);
        if ($info['count']) {
            // Validate the user credentials by attempting to bind to LDAP:
            $ldapBind = @ldap_bind(
                $ldapConnection, $info[0]['dn'], $this->_password
            );
            if ($ldapBind) {
                // If the bind was successful, we can look up the full user info:
                $ldapSearch = ldap_search(
                    $ldapConnection, $ldapConnectionParameter['basedn'], $ldapFilter
                );
                $data = ldap_get_entries($ldapConnection, $ldapSearch);
                return $this->_processLDAPUser($data, $ldapConnectionParameter);
            }
        }

        return new PEAR_ERROR('authentication_error_invalid');
    }

    /**
     * Build a User object from details obtained via LDAP.
     *
     * @param array $data                    Details from ldap_get_entries call.
     * @param array $ldapConnectionParameter LDAP settings from config.ini.
     *
     * @return User
     * @access private
     */
    private function _processLDAPUser($data, $ldapConnectionParameter)
    {
        $user = new User();
        $user->username = $this->_username;
        $userIsInVufindDatabase = $this->_isUserInVufindDatabase($user);
        for ($i=0; $i<$data["count"];$i++) {
            for ($j=0;$j<$data[$i]["count"];$j++) {
                if (($data[$i][$j] == $ldapConnectionParameter['firstname'])
                    && ($ldapConnectionParameter['firstname'] != "")
                ) {
                    $user->firstname = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['lastname']
                    && ($ldapConnectionParameter['lastname'] != "")
                ) {
                    $user->lastname = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['email']
                    && ($ldapConnectionParameter['email'] != "")
                ) {
                     $user->email = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['cat_username']
                    && ($ldapConnectionParameter['cat_username'] != "")
                ) {
                     $user->cat_username = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['cat_password']
                    && ($ldapConnectionParameter['cat_password'] != "")
                ) {
                     $user->cat_password = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['college']
                    && ($ldapConnectionParameter['college'] != "")
                ) {
                     $user->college = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['major']
                    && ($ldapConnectionParameter['major'] != "")
                ) {
                     $user->major = $data[$i][$data[$i][$j]][0];
                }
            }
        }
        $this->_synchronizeVufindDatabaseWithLDAPEntries(
            $userIsInVufindDatabase, $user
        );
        return $user;
    }

    /**
     * Is the specified user already in VuFind's local database?
     *
     * @param User $user User to check
     *
     * @return bool
     * @access private
     */
    private function _isUserInVufindDatabase($user)
    {
        return $user->find(true);
    }

    /**
     * Update VuFind's local database with details obtained via LDAP.
     *
     * @param bool $userIsInVufindDatabase Is this a new user (false) or an existing
     * one (true)?
     * @param User $user                   User object to store.
     *
     * @return void
     * @access private
     */
    private function _synchronizeVufindDatabaseWithLDAPEntries(
        $userIsInVufindDatabase, $user
    ) {
        if ($userIsInVufindDatabase) {
            $user->update();
        } else {
            $user->created = date('Y-m-d');
            $user->insert();
        }
    }
}

?>