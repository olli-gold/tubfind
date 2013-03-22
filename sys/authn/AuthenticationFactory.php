<?php
/**
 * Factory class for constructing authentication modules.
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
require_once 'UnknownAuthenticationHandlerException.php';

/**
 * Factory class for constructing authentication modules.
 *
 * @category VuFind
 * @package  Authentication
 * @author   Franck Borel <franck.borel@gbv.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_an_authentication_handler Wiki
 */
class AuthenticationFactory
{
    /**
     * Initialize an authentication module.
     *
     * @param string $authNHandler          The name of the module to initialize.
     * @param string $configurationFilePath Optional path to alternate config file.
     *
     * @return object
     * @access public
     */
    static function initAuthentication($authNHandler, $configurationFilePath = '')
    {
        // Special handling for authentication classes that don't conform to the
        // standard pattern (for legacy support):
        if ($authNHandler == 'DB') {
            $authNHandler = 'Database';
        } else if ($authNHandler == 'SIP2') {
            $authNHandler = 'SIP';
        }

        // Build the class name and filename.  Use basename on the filename just
        // to ensure nothing weird can happen if bad values are passed through.
        $handler = $authNHandler . 'Authentication';
        $filename = basename($handler . '.php');

        // Load up the handler if a legal name has been supplied.
        if (!empty($authNHandler)
            && file_exists(dirname(__FILE__) . '/' . $filename)
        ) {
            include_once $filename;
            return new $handler($configurationFilePath);
        } else {
            throw new UnknownAuthenticationHandlerException(
                'Authentication handler ' . $authNHandler . ' does not exist!'
            );
        }
    }
}
?>