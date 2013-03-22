<?php
/**
 * Configuration File Loader for LDAP module
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
require_once 'ConfigurationReader.php';

/**
 * Configuration File Loader Class for LDAP module
 *
 * @category VuFind
 * @package  Authentication
 * @author   Franck Borel <franck.borel@gbv.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_an_authentication_handler Wiki
 */
class LDAPConfigurationParameter
{
    private $_configurationFilePath;
    private $_ldapParameter;

    /**
     * Constructor
     *
     * @param string $configurationFilePath Configuration file to load (defaults to
     * standard config.ini).
     *
     * @access public
     */
    public function __construct($configurationFilePath = '')
    {
        $this->_configurationFilePath = $configurationFilePath;
    }

    /**
     * Load normalized LDAP configuration settings.
     *
     * @return array
     * @access public
     */
    public function getParameter()
    {
        $this->_getFullSectionParameters();
        $this->_checkIfMandatoryParametersAreSet();
        $this->_convertParameterValuesToLowercase();
        return $this->_ldapParameter;
    }

    /**
     * Load LDAP parameters from configuration file.
     *
     * @return void
     * @access private
     */
    private function _getFullSectionParameters()
    {
        $configurationReader
            = new ConfigurationReader($this->_configurationFilePath);
        $this->_ldapParameter = $configurationReader->readConfiguration("LDAP");
    }

    /**
     * Throw exception if required parameters are missing.
     *
     * @return void
     * @access private
     */
    private function _checkIfMandatoryParametersAreSet()
    {
        if (empty($this->_ldapParameter['host'])
            || empty($this->_ldapParameter['port'])
            || empty($this->_ldapParameter['basedn'])
            || empty($this->_ldapParameter['username'])
        ) {
            throw new InvalidArgumentException(
                "One or more LDAP parameter are missing. Check your config.ini!"
            );
        }
    }

    /**
     * Normalize parameter values to lowercase.
     *
     * @return void
     * @access private
     */
    private function _convertParameterValuesToLowercase()
    {
        foreach ($this->_ldapParameter as $index => $value) {
            // Don't lowercase the bind credentials -- they may be case sensitive!
            if ($index != 'bind_username' && $index != 'bind_password') {
                $this->_ldapParameter[$index] = strtolower($value);
            }
        }
    }


}
?>
