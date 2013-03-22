<?php
/**
 * Shibboleth authentication module.
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
require_once 'Authentication.php';
require_once 'ShibbolethConfigurationParameter.php';
require_once 'services/MyResearch/lib/User.php';

/**
 * Shibboleth authentication module.
 *
 * @category VuFind
 * @package  Authentication
 * @author   Franck Borel <franck.borel@gbv.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_an_authentication_handler Wiki
 */
class ShibbolethAuthentication implements Authentication
{
    private $_userAttributes;

    /**
     * Constructor
     *
     * @param string $configurationFilePath Optional configuration file path.
     *
     * @access public
     */
    public function __construct($configurationFilePath = '')
    {
        $shibbolethConfigurationParameter
            = new ShibbolethConfigurationParameter($configurationFilePath);
        $this->_userAttributes
            = $shibbolethConfigurationParameter->getUserAttributes();
    }

    /**
     * Attempt to authenticate the current user.
     *
     * @return object User object if successful, PEAR_Error otherwise.
     * @access public
     */
    public function authenticate()
    {
        if (!$this->_isUsernamePartOfAssertions()) {
            return new PEAR_ERROR('authentication_error_admin');
        }
        foreach ($this->_userAttributes as $key => $value) {
            if ($key != 'username') {
                if (!preg_match('/'. $value .'/', $_SERVER[$key])) {
                    return new PEAR_ERROR('authentication_error_denied');
                }
            }
        }

        $user = new User();
        $user->username = $_SERVER[$this->_userAttributes['username']];
        $userIsInVufindDatabase = $this->_isUserInVufindDatabase($user);
        $this->_synchronizeVufindDatabase($userIsInVufindDatabase, $user);

        return $user;
    }

    /**
     * Check if username attribute is properly configured.
     *
     * @return bool
     * @access private
     */
    private function _isUsernamePartOfAssertions()
    {
        if (isset($_SERVER[$this->_userAttributes['username']])) {
            return true;
        }
        return false;
    }

    /**
     * Check if user is already found in database.
     *
     * @param object $user User to check.
     *
     * @return bool
     * @access private
     */
    private function _isUserInVufindDatabase($user)
    {
        return $user->find(true);
    }

    /**
     * Update the user information in the database.
     *
     * @param bool   $userIsInVufindDatabase Is the user already in the database?
     * @param object $user                   User to store/update.
     *
     * @return void
     * @access private
     */
    private function _synchronizeVufindDatabase($userIsInVufindDatabase, $user)
    {
        if ($userIsInVufindDatabase) {
            $user->update();
        } else {
            $user->created = date('Y-m-d');
            $user->insert();
        }
    }
}
?>
