<?php
/**
 * Configuration File Loader
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
require_once 'IOException.php';
require_once 'FileParseException.php';

/**
 * Configuration File Loader Class
 *
 * @category VuFind
 * @package  Authentication
 * @author   Franck Borel <franck.borel@gbv.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_an_authentication_handler Wiki
 */
class ConfigurationReader
{
    private $_pathToConfigurationFile;
    private $_configurationFileContent;
    private $_sectionName;

    /**
     * Constructor
     *
     * @param string $pathToConfigurationFile Configuration file to load.
     *
     * @access public
     */
    public function __construct($pathToConfigurationFile = '')
    {
        $this->_setPathOfConfigurationFileIfParameterIsEmpty(
            $pathToConfigurationFile
        );
        $this->_checkIfConfigurationFileExists();
    }

    /**
     * Initialize the "path to configuration file" property.
     *
     * @param string $pathToConfigurationFile Configuration file to load.
     *
     * @return void
     * @access private
     */
    private function _setPathOfConfigurationFileIfParameterIsEmpty(
        $pathToConfigurationFile
    ) {
        if (empty($pathToConfigurationFile) || $pathToConfigurationFile == '') {
            $actualPath = dirname(__FILE__);
            // Handle forward and back slashes for Windows/Linux compatibility:
            $this->_pathToConfigurationFile = str_replace(
                array("/sys/authn", "\sys\authn"),
                array("/conf/config.ini", "\conf\config.ini"), $actualPath
            );
        } else {
            $this->_pathToConfigurationFile = $pathToConfigurationFile;
        }
    }

    /**
     * Throw an exception if the requested configuration file is missing.
     *
     * @return void
     * @access private
     */
    private function _checkIfConfigurationFileExists()
    {
        clearstatcache();
        if (!file_exists($this->_pathToConfigurationFile)) {
            throw new IOException(
                'Missing configuration file ' .
                $this->_pathToConfigurationFile . '.', 1
            );
        }
    }

    /**
     * Read a section from the configuration file.
     *
     * @param string $sectionName Section to read
     *
     * @return array
     * @access public
     */
    public function readConfiguration($sectionName)
    {
        $this->_sectionName = $sectionName;
        try {
            $this->_configurationFileContent
                = parse_ini_file($this->_pathToConfigurationFile, true);
        } catch (Exception $exception) {
            throw new FileParseException(
                "Error during parsing file '{$this->_pathToConfigurationFile}'", 2
            );
        }

        $this->_checkIfParsingWasSuccessful();
        $this->_checkIfSectionExists();
        return $this->_configurationFileContent[$this->_sectionName];
    }

    /**
     * Throw an exception if the configuration file was not successfully parsed.
     *
     * @return void
     * @access private
     */
    private function _checkIfParsingWasSuccessful()
    {
        if (!is_array($this->_configurationFileContent)) {
            throw new FileParseException(
                'Could not parse configuration file ' .
                $this->_pathToConfigurationFile . '.', 3
            );
        }
    }

    /**
     * Throw an exception if the requested section is missing.
     *
     * @return void
     * @access private
     */
    private function _checkIfSectionExists()
    {
        if (empty($this->_configurationFileContent[$this->_sectionName])) {
            throw new UnexpectedValueException(
                "Section {$this->_sectionName} does not exist! Could not proceed."
            );
        }
    }
}

?>