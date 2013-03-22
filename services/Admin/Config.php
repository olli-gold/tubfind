<?php
/**
 * Config action for Admin module
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
 * @package  Controller_Admin
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'Admin.php';
require_once 'sys/ConfigArray.php';

/**
 * Config action for Admin module
 *
 * @category VuFind
 * @package  Controller_Admin
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Config extends Admin
{
    /**
     * Process parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $configArray;
        global $interface;

        if (isset($_POST['submit'])) {
            switch ($_GET['file']) {
            case 'stopwords.txt':
                $this->_processStopWords();
                break;
            case 'synonyms.txt':
                $this->_processSynonyms();
                break;
            case 'protwords.txt':
                $this->_processProtWords();
                break;
            case 'elevate.xml':
                //$this->processElevate();
                $this->_processConfigFile(
                    $configArray['Index']['local'] . '/biblio/conf/elevate.xml',
                    $_POST['config_file']
                );
                break;
            case 'searchspecs.yaml':
            case 'searches.ini':
            case 'facets.ini':
                // Figure out the path to the file based on its extension -- the
                // YAML option is hard-coded, but the INI files may be renamed by
                // the user.
                list($filename, $ext) = explode('.', $_GET['file']);
                if ($ext == 'yaml') {
                    $path = 'conf/' . $_GET['file'];
                } else {
                    $path = getExtraConfigArrayFile($filename);
                }
                $this->_processConfigFile(
                    $configArray['Site']['local'] . '/' . $path,
                    $_POST['config_file']
                );
                break;
            case 'config.ini':
                //$this->processConfig();   // dangerous -- disabled until fixed
                $this->_processConfigFile(
                    $configArray['Site']['local'] . '/' . 'conf/config.ini',
                    $_POST['config_file']
                );
                break;
            }
        }

        if (isset($_GET['file'])) {
            switch ($_GET['file']) {
            case 'stopwords.txt':
                $this->_showStopWords();
                break;
            case 'synonyms.txt':
                $this->_showSynonyms();
                break;
            case 'protwords.txt':
                $this->_showProtWords();
                break;
            case 'elevate.xml':
                //$this->showElevate();
                $this->_showConfigFile(
                    'Elevated Terms Configuration',
                    $configArray['Index']['local'] . '/biblio/conf/elevate.xml'
                );
                break;
            case 'searchspecs.yaml':
                $this->_showConfigFile(
                    'Search Specifications',
                    $configArray['Site']['local'] . '/' . 'conf/searchspecs.yaml'
                );
                break;
            case 'searches.ini':
                $this->_showConfigFile(
                    'Search Settings', $configArray['Site']['local'] . '/' .
                    getExtraConfigArrayFile('searches')
                );
                break;
            case 'facets.ini':
                $this->_showConfigFile(
                    'Facet Settings', $configArray['Site']['local'] . '/' .
                    getExtraConfigArrayFile('facets')
                );
                break;
            case 'config.ini':
            default:
                //$this->showConfig();   // dangerous -- disabled until fixed
                $this->_showConfigFile(
                    'General Settings',
                    $configArray['Site']['local'] . '/' . 'conf/config.ini'
                );
                break;
            }
        } else {
            $interface->setPageTitle('Configuration');
            $interface->setTemplate('config.tpl');
        }
        $interface->display('layout-admin.tpl');
    }

    /**
     * Show the contents of the Solr stopwords.txt file.
     *
     * @return void
     * @access private
     */
    private function _showStopWords()
    {
        global $interface;
        global $configArray;

        $stopwords = file_get_contents(
            $configArray['Index']['local'] . '/biblio/conf/stopwords.txt'
        );
        $interface->assign('stopwords', $stopwords);
        $interface->setPageTitle("Stop Words Configuration");
        $interface->setTemplate('config-stopwords.tpl');
    }

    /**
     * Update the contents of the Solr stopwords.txt file.
     *
     * @return void
     * @access private
     */
    private function _processStopWords()
    {
        global $configArray;

        $this->_processConfigFile(
            $configArray['Index']['local'] . '/biblio/conf/stopwords.txt',
            $_POST['stopwords']
        );
    }

    /**
     * Show the contents of the Solr synonyms.txt file.
     *
     * @return void
     * @access private
     */
    private function _showSynonyms()
    {
        global $interface;
        global $configArray;

        $synonyms = file_get_contents(
            $configArray['Index']['local'] . '/biblio/conf/synonyms.txt'
        );
        $interface->assign('synonyms', $synonyms);
        $interface->setPageTitle("Synonyms Configuration");
        $interface->setTemplate('config-synonyms.tpl');
    }

    /**
     * Update the contents of the Solr synonyms.txt file.
     *
     * @return void
     * @access private
     */
    private function _processSynonyms()
    {
        global $configArray;

        $this->_processConfigFile(
            $configArray['Index']['local'] . '/biblio/conf/synonyms.txt',
            $_POST['synonyms']
        );
    }

    /**
     * Show the contents of the Solr protwords.txt file.
     *
     * @return void
     * @access private
     */
    private function _showProtWords()
    {
        global $interface;
        global $configArray;

        $protwords = file_get_contents(
            $configArray['Index']['local'] . '/biblio/conf/protwords.txt'
        );
        $interface->assign('protwords', $protwords);
        $interface->setPageTitle("Protected Words Configuration");
        $interface->setTemplate('config-protwords.tpl');
    }

    /**
     * Update the contents of the Solr protwords.txt file.
     *
     * @return void
     * @access private
     */
    private function _processProtWords()
    {
        global $configArray;

        $this->_processConfigFile(
            $configArray['Index']['local'] . '/biblio/conf/protwords.txt',
            $_POST['protwords']
        );
    }

    /* Not fully implemented -- commented out until this can be finished;
     * in the meantime, we'll use the generic _showConfigFile /
     * _processConfigFile methods.
    function showElevate()
    {
        global $interface;
        global $configArray;

        $elevate = file_get_contents(
            $configArray['Index']['local'] . '/biblio/conf/elevate.xml'
        );
        $interface->assign('elevate', $elevate);
        $interface->setPageTitle("Elevated Terms Configuration");
        $interface->setTemplate('config-elevate.tpl');
    }

    function processElevate()
    {
        global $configArray;

        $doc = new DOM_Document();
        $xml = $doc->saveXML();

        $this->_processConfigFile(
            $configArray['Index']['local'] . '/biblio/conf/elevate.xml', $xml
        );
    }
     */

    /**
     * Display the contents of a configuration file.
     *
     * @param string $title The title of the file.
     * @param string $path  The path to the file.
     *
     * @return void
     * @access private
     */
    private function _showConfigFile($title, $path)
    {
        global $interface;

        $file = @file_get_contents($path);
        $interface->assign('configPath', $path);
        $interface->assign('configFile', $file);
        $interface->setPageTitle($title);
        $interface->setTemplate('config-file.tpl');
    }

    /**
     * Save the contents of a configuration file.
     *
     * @param string $filename The path of the file to write.
     * @param string $contents The contents to write to the file.
     *
     * @return void
     * @access private
     */
    private function _processConfigFile($filename, $contents)
    {
        global $interface;

        $interface->assign('saved', true);
        $interface->assign(
            'bytesWritten', @file_put_contents($filename, $contents)
        );
    }

    /* This code is incomplete and dangerous since it can corrupt config.ini --
     * it's disabled for now until we can finish it.

    function showConfig()
    {
        global $interface;
        global $configArray;

        $list = array();
        $themesDir = $configArray['Site']['local'] . '/interface/themes';
        //echo $themesDir;
        if (is_dir($themesDir)) {
            if ($dh = opendir($themesDir)) {
                while (($file = readdir($dh)) !== false) {
                    if (substr($file, 0, 1) != '.') {
                        $list[] = $file;
                    }
                }
            }
            closedir($dh);
        }
        $interface->assign('themeList', $list);

        $interface->assign(
            'dsn', $this->_parseDSN($configArray['Database']['database'])
        );

        $interface->setTemplate('config-config.tpl');
        $interface->assign('config', $configArray);
    }

    function processConfig()
    {
        global $configArray;

        $configArray['Site']['path']     = $_POST['webpath'];
        $configArray['Site']['url']      = $_POST['weburl'];
        $configArray['Site']['local']    = $_POST['localpath'];
        $configArray['Site']['title']    = $_POST['title'];
        $configArray['Site']['email']    = $_POST['email'];
        $configArray['Site']['language'] = $_POST['language'];
        $configArray['Site']['locale']   = $_POST['locale'];
        $configArray['Site']['theme']    = $_POST['theme'];

        $configArray['Index']['engine'] = $_POST['engine'];
        $configArray['Index']['url']    = $_POST['engineurl'];

        $configArray['Catalog']['catalog'] = $_POST['ils'];

        $configArray['Database']['database'] = 'mysql://' . $_POST['dbusername'] .
            ':' . $_POST['dbpassword'] . '@' . $_POST['dbhost'] . '/' .
            $_POST['dbname'];

        $configArray['Mail']['host'] = $_POST['mailhost'];
        $configArray['Mail']['port'] = $_POST['mailport'];

        $configArray['BookCovers']['provider']  = $_POST['bookcover_provider'];
        $configArray['BookCovers']['id']        = $_POST['bookcover_id'];

        $configArray['BookReviews']['provider'] = $_POST['bookreview_provider'];
        $configArray['BookReviews']['id']       = $_POST['bookreview_id'];

        $configArray['LDAP']['host']    = $_POST['ldaphost'];
        $configArray['LDAP']['port']    = $_POST['ldapport'];
        $configArray['LDAP']['basedn']  = $_POST['ldapbasedn'];
        $configArray['LDAP']['uid']     = $_POST['ldapuid'];

        $configArray['COinS']['identifier'] = $_POST['coinsID'];

        $configArray['OAI']['identifier']   = $_POST['oaiID'];

        $configArray['OpenURL']['url']      = $_POST['openurl'];

        $configArray['EZproxy']['host']     = $_POST['ezproxyhost'];

        $fileData = '';
        foreach ($configArray as $name => $section) {
            $fileData .= "[$name]\n";
            foreach ($section as $field => $value) {
                $fileData .= "$field = \"$value\"\n";
            }
        }
        $this->_processConfigFile('conf/config.ini', $fileData);
    }
     */

    /**
     * This is lifted, err, adopted from the MDB2::parseDSN function
     *
     * Parse a data source name
     *
     * Additional keys can be added by appending a URI query string to the
     * end of the DSN
     *
     * The format of the supplied DSN is in its fullest form:
     * <code>
     * phptype(dbsyntax)://username:password@protocol+hostspec/database?option=8
     * &another=true
     * </code>
     *
     * Most variations are allowed:
     * <code>
     *   phptype://username:password@protocol+hostspec:110//usr/db_file.db?mode=0644
     *   phptype://username:password@hostspec/database_name
     *   phptype://username:password@hostspec
     *   phptype://username@hostspec
     *   phptype://hostspec/database
     *   phptype://hostspec
     *   phptype(dbsyntax)
     *   phptype
     * </code>
     *
     * @param string $dsn Data Source Name to be parsed
     *
     * @return array      An associative array with the following keys:
     *   + phptype: Database backend used (mysql, odbc, etc.)
     *   + dbsyntax: Database used with regards to SQL syntax
     *   + protocol: Communication protocol to use (tcp, unix, etc.)
     *   + hostspec: Host specification (hostname[:port]
     *   + database: Database to use on the DBMS server
     *   + username: User name for login
     *   + password: Password for login
     * @access private
     * @author Tomas V. V. Cox <cox@idecnet.com>
     * @author Modified for Vufind by Wayne Graham <wsgrah@wm.edu>
     */
    private function _parseDSN($dsn)
    {
        $parsed = array(
            'phptype'  => false,
            'dbsyntax' => false,
            'protocol' => false,
            'hostspec' => false,
            'database' => false,
            'username' => false,
            'password' => false
        );

        // Find phptype and dbsyntax
        if (($pos = strpos($dsn, '://')) !== false) {
            $str = substr($dsn, 0, $pos);
            $dsn = substr($dsn, $pos + 3);
        } else {
            $str = $dsn;
            $dsn = null;
        }

        // Get phptype and dbsyntax
        // $str => phptype(dbsyntax)
        if (preg_match('|^(.+?)\((.*?)\)$|', $str, $arr)) {
            $parsed['phptype']  = $arr[1];
            $parsed['dbsyntax'] = !$arr[2] ? $arr[1] : $arr[2];
        } else {
            $parsed['phptype']  = $str;
            $parsed['dbsyntax'] = $str;
        }

        if (!count($dsn)) {
            return $parsed;
        }

        // Get (if found): username and password
        // $dsn => username:password@protocol+hostspec/database
        if (($at = strrpos($dsn, '@')) !== false) {
            $str = substr($dsn, 0, $at);
            $dsn = substr($dsn, $at + 1);
            if (($pos = strpos($str, ':')) !== false) {
                $parsed['username'] = rawurldecode(substr($str, 0, $pos));
                $parsed['password'] = rawurldecode(substr($str, $pos + 1));
            } else {
                $parsed['username'] = rawurldecode($str);
            }
        }

        // Find protocol and hostspec

        // $dsn => proto(proto_opts)/database
        if (preg_match('|^([^(]+)\((.*?)\)/?(.*?)$|', $dsn, $match)) {
            $proto       = $match[1];
            $proto_opts  = $match[2] ? $match[2] : false;
            $dsn         = $match[3];

            // $dsn => protocol+hostspec/database (old format)
        } else {
            if (strpos($dsn, '+') !== false) {
                list($proto, $dsn) = explode('+', $dsn, 2);
            }
            if (strpos($dsn, '//') === 0
                && strpos($dsn, '/', 2) !== false
                && $parsed['phptype'] == 'oci8'
            ) {
                //oracle's "Easy Connect" syntax:
                //"username/password@[//]host[:port][/service_name]"
                //e.g. "scott/tiger@//mymachine:1521/oracle"
                $proto_opts = $dsn;
                $dsn = substr($proto_opts, strrpos($proto_opts, '/') + 1);
            } elseif (strpos($dsn, '/') !== false) {
                list($proto_opts, $dsn) = explode('/', $dsn, 2);
            } else {
                $proto_opts = $dsn;
                $dsn = null;
            }
        }

        // process the different protocol options
        $parsed['protocol'] = (!empty($proto)) ? $proto : 'tcp';
        $proto_opts = rawurldecode($proto_opts);
        if (strpos($proto_opts, ':') !== false) {
            list($proto_opts, $parsed['port']) = explode(':', $proto_opts);
        }
        if ($parsed['protocol'] == 'tcp') {
            $parsed['hostspec'] = $proto_opts;
        } elseif ($parsed['protocol'] == 'unix') {
            $parsed['socket'] = $proto_opts;
        }

        // Get dabase if any
        // $dsn => database
        if ($dsn) {
            // /database
            if (($pos = strpos($dsn, '?')) === false) {
                $parsed['database'] = $dsn;
                // /database?param1=value1&param2=value2
            } else {
                $parsed['database'] = substr($dsn, 0, $pos);
                $dsn = substr($dsn, $pos + 1);
                if (strpos($dsn, '&') !== false) {
                    $opts = explode('&', $dsn);
                } else { // database?param1=value1
                    $opts = array($dsn);
                }
                foreach ($opts as $opt) {
                    list($key, $value) = explode('=', $opt);
                    if (!isset($parsed[$key])) {
                        // don't allow params overwrite
                        $parsed[$key] = rawurldecode($value);
                    }
                }
            }
        }

        return $parsed;
    }
}

?>