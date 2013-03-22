<?php
/**
 * Wrapper class for handling logged-in user in session.
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
 * @package  Support_Classes
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes Wiki
 */
require_once 'XML/Unserializer.php';
require_once 'XML/Serializer.php';

require_once 'sys/authn/AuthenticationFactory.php';

// This is necessary for unserialize
require_once 'services/MyResearch/lib/User.php';

/**
 * Wrapper class for handling logged-in user in session.
 *
 * @category VuFind
 * @package  Support_Classes
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes Wiki
 */
class UserAccount
{
    /**
     * Checks whether the user is logged in.
     *
     * @return bool Is the user logged in?
     * @access public
     */
    public static function isLoggedIn()
    {
        if (isset($_SESSION['userinfo'])) {
            return unserialize($_SESSION['userinfo']);
        }
        return false;
    }

    /**
     * Updates the user information in the session.
     *
     * @param object $user User object to store in the session
     *
     * @return void
     * @access public
     */
    public static function updateSession($user)
    {
        $_SESSION['userinfo'] = serialize($user);
    }

    /**
     * Try to log in the user using current query parameters; return User object
     * on success, PEAR error on failure.
     *
     * @return object
     * @access public
     */
    public static function login()
    {
        global $configArray;

        // Perform authentication:
        try {
            $authN = AuthenticationFactory::initAuthentication(
                $configArray['Authentication']['method']
            );
            $user = $authN->authenticate();
        } catch (Exception $e) {
            if ($configArray['System']['debug']) {
                echo "Exception: " . $e->getMessage();
            }
            $user = new PEAR_Error('authentication_error_technical');
        }

        // If we authenticated, store the user in the session:
        if (!PEAR::isError($user)) {
            self::updateSession($user);
        }

        // Send back the user object (which may be a PEAR error):
        return $user;
    }

    /**
     * Log the current user into the catalog using stored credentials; if this
     * fails, clear the user's stored credentials so they can enter new, corrected
     * ones.
     *
     * @return mixed                     $user object (on success) or false (on
     * failure)
     * @access protected
     */
    public static function catalogLogin()
    {
        global $user;

        $catalog = ConnectionManager::connectToCatalog();
        if ($catalog && $catalog->status && $user && $user->cat_username) {
            $patron = $catalog->patronLogin(
                $user->cat_username, $user->cat_password
            );
            if (empty($patron) || PEAR::isError($patron)) {
                // Problem logging in -- clear user credentials so they can be
                // prompted again; perhaps their password has changed in the
                // system!
                unset($user->cat_username);
                unset($user->cat_password);
            } else {
                return $patron;
            }
        }

        return false;
    }

    /**
     * Attempt to log in the user to the ILS, and save credentials if it works.
     *
     * @param string $username Catalog username
     * @param string $password Catalog password
     *
     * @return bool            True on successful login, false on error.
     */
    public static function processCatalogLogin($username, $password)
    {
        global $user;

        $catalog = ConnectionManager::connectToCatalog();
        $result = $catalog->patronLogin($username, $password);
        if ($result && !PEAR::isError($result)) {
            $user->cat_username = $username;
            $user->cat_password = $password;
            $user->update();
            self::updateSession($user);
            return true;
        }
        return false;
    }
}

?>
