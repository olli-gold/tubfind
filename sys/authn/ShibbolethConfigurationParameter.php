<?php
/**
 * Configuration File Loader for Shibboleth module
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
 * Configuration File Loader Class for Shibboleth module
 *
 * @category VuFind
 * @package  Authentication
 * @author   Franck Borel <franck.borel@gbv.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_an_authentication_handler Wiki
 */
class ShibbolethConfigurationParameter
{
    private $_configurationFilePath;
    private $_userAttributes;

    /**
     * Constructor
     *
     * @param string $configurationFilePath Optional path to configuration file
     *
     * @access public
     */
    public function __construct($configurationFilePath = '')
    {
        $this->_configurationFilePath = $configurationFilePath;
    }

    /**
     * Obtain user attributes from the configuration file.
     *
     * @return array
     * @access public
     */
    public function getUserAttributes()
    {
        $this->_getFullSectionParameters();
        $this->_checkIfUsernameExists();
        $this->_filterFullSectionParameter();
        $this->_sortUserAttributes();
        $this->_checkIfAnyAttributeValueIsEmpty();
        $this->_checkIfAtLeastOneUserAttributeIsSet();
        return $this->_userAttributes;
    }

    /**
     * Load user attribute settings from config.ini.
     *
     * @return void
     * @access private
     */
    private function _getFullSectionParameters()
    {
        $configurationReader
            = new ConfigurationReader($this->_configurationFilePath);
        $this->_userAttributes
            = $configurationReader->readConfiguration("Shibboleth");
    }

    /**
     * Throw an exception of the required username setting is missing.
     *
     * @return void
     * @access private
     */
    private function _checkIfUsernameExists()
    {
        if (empty($this->_userAttributes['username'])) {
            throw new UnexpectedValueException(
                "Username is missing in your configuration file : '" .
                $this->_configurationFilePath . "'"
            );
        }
    }

    /**
     * Eliminate unwanted values from the userAttributes property.
     *
     * @return void
     * @access private
     */
    private function _filterFullSectionParameter()
    {
        $filterPatternAttribute = "/userattribute_[0-9]{1,}/";
        $filterPatternAttributeValue = "/userattribute_value_[0-9]{1,}/";
        foreach ($this->_userAttributes as $key => $value) {
            if (!preg_match($filterPatternAttribute, $key)
                && !preg_match($filterPatternAttributeValue, $key)
                && $key != "username"
            ) {
                unset($this->_userAttributes[$key]);
            }
        }
    }

    /**
     * Build an associative array of attribute name => attribute value by parsing
     * the userattribute_* settings from config.ini.  Store the result in the object.
     *
     * @return void
     * @access private
     */
    private function _sortUserAttributes()
    {
        $filterPatternAttributes = "/userattribute_[0-9]{1,}/";
        $sortedUserAttributes['username'] = $this->_userAttributes['username'];
        foreach ($this->_userAttributes as $key => $value) {
            if (preg_match($filterPatternAttributes, $key)) {
                $sortedUserAttributes[$value]
                    = $this->_getUserAttributeValue(substr($key, 14));
            }
        }
        $this->_userAttributes = $sortedUserAttributes;
    }

    /**
     * Find the configuration value for a specific attribute.
     *
     * @param int $userAttributeNumber The configuration index to look up
     *
     * @return string
     * @access private
     */
    private function _getUserAttributeValue($userAttributeNumber)
    {
        $filterPatternAttributeValues = "/userattribute_value_[" .
            $userAttributeNumber . "]{1,}/";
        foreach ($this->_userAttributes as $key => $value) {
            if (preg_match($filterPatternAttributeValues, $key)) {
                return $value;
            }
        }
    }

    /**
     * Throw an exception if attributes are missing/empty.
     *
     * @return void
     * @access private
     */
    private function _checkIfAnyAttributeValueIsEmpty()
    {
        foreach ($this->_userAttributes as $key => $value) {
            if (empty($value)) {
                throw new UnexpectedValueException(
                    "User attribute value of " . $key. " is missing!"
                );
            }
        }
    }

    /**
     * Throw an exception if attribute configuration is empty.
     *
     * @return void
     * @access private
     */
    private function _checkIfAtLeastOneUserAttributeIsSet()
    {
        if (count($this->_userAttributes) == 1) {
            throw new UnexpectedValueException(
                "You must at least set one user attribute in your configuration " .
                "file '" . $this->_configurationFilePath  . "'.", 3
            );
        }
    }
}


?>