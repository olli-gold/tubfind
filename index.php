<?php
/**
 * CORE APPLICATION CONTROLLER
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
 * @package  Controller
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/developer_manual Wiki
 */

// Retrieve values from configuration file
require_once 'sys/ConfigArray.php';
$configArray = readConfig();

// Try to set the locale to UTF-8, but fail back to the exact string from the config
// file if this doesn't work -- different systems may vary in their behavior here.
setlocale(
    LC_MONETARY, array($configArray['Site']['locale'] . ".UTF-8",
    $configArray['Site']['locale'])
);
date_default_timezone_set($configArray['Site']['timezone']);

// Require System Libraries
require_once 'PEAR.php';
require_once 'sys/Interface.php';
require_once 'sys/Logger.php';
require_once 'sys/User.php';
require_once 'sys/Translator.php';
require_once 'sys/SearchObject/Factory.php';
require_once 'sys/ConnectionManager.php';
require_once 'sys/Autoloader.php';
spl_autoload_register('vuFindAutoloader');

// Load local overrides file (if it exists) to pick up local class overrides.
// This can be used to override autoloaded classes, allowing local customizations
// of some features without the need to modify core VuFind code.
if (file_exists(dirname(__FILE__).'/local_overrides.php')) {
    include_once dirname(__FILE__).'/local_overrides.php';
}

// Sets global error handler for PEAR errors
PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'handlePEARError');

// Sets global error handler for PHP errors
//set_error_handler('handlePHPError');

if ($configArray['System']['debug']) {
    ini_set('display_errors', true);
    error_reporting(E_ALL);
}

// Start Interface
$interface = new UInterface();

// Check system availability
$mode = checkAvailabilityMode();
if ($mode['online'] === false) {
    // Why are we offline?
    switch ($mode['level']) {
    // Forced Downtime
    case "unavailable":
        // TODO : Variable reasons, and translated
        //$interface->assign('message', $mode['message']);
        $interface->display($mode['template']);
        break;
    // Should never execute. checkAvailabilityMode() would
    // need to know we are offline, but not why.
    default:
        // TODO : Variable reasons, and translated
        //$interface->assign('message', $mode['message']);
        $interface->display($mode['template']);
        break;
    }
    exit();
}

// Proxy server settings
if (isset($configArray['Proxy']['host'])) {
    if (isset($configArray['Proxy']['port'])) {
        $proxy_server
            = $configArray['Proxy']['host'] . ":" . $configArray['Proxy']['port'];
    } else {
        $proxy_server = $configArray['Proxy']['host'];
    }
    $proxy = array(
        'http' => array('proxy' => "tcp://$proxy_server", 'request_fulluri' => true)
    );
    stream_context_get_default($proxy);
}

// Setup Translator
if (isset($_REQUEST['mylang'])) {
    $language = $_REQUEST['mylang'];
    setcookie('language', $language, null, '/');
} else {
    $language = (isset($_COOKIE['language'])) ? $_COOKIE['language'] :
                    $configArray['Site']['language'];
}
// Make sure language code is valid, reset to default if bad:
$validLanguages = array_keys($configArray['Languages']);
if (!in_array($language, $validLanguages)) {
    $language = $configArray['Site']['language'];
}
$translator = new I18N_Translator(
    'lang', $language, $configArray['System']['debug']
);
$interface->setLanguage($language);
$interface->assign('language', $language);

// Setup Local Database Connection
ConnectionManager::connectToDatabase();

// Initiate Session State
$session_type = $configArray['Session']['type'];
$session_lifetime = $configArray['Session']['lifetime'];
require_once 'sys/' . $session_type . '.php';
if (class_exists($session_type)) {
    $session = new $session_type();
    $session->init($session_lifetime);
}

/*
print_r($_SESSION);
echo "Request:";
print_r($_REQUEST);
*/
$searchesConfig = getExtraConfigArray('searches');

// Determine Module and Action
$loggedInModule = isset($configArray['Site']['defaultLoggedInModule'])
    ? $configArray['Site']['defaultLoggedInModule'] : 'MyResearch';
$loggedOutModule = isset($configArray['Site']['defaultModule'])
    ? $configArray['Site']['defaultModule'] : 'Search';
$module = ($user = UserAccount::isLoggedIn()) ? $loggedInModule : $loggedOutModule;
$module = (isset($_GET['module'])) ? $_GET['module'] : $module;
$module = preg_replace('/[^\w]/', '', $module);
$action = (isset($_GET['action'])) ? $_GET['action'] : 'Home';
$action = preg_replace('/[^\w]/', '', $action);

// Process Authentication
if (!$user) {
    // Special case for Shibboleth:
    $shibLoginNeeded = ($configArray['Authentication']['method'] == 'Shibboleth'
        && $module == 'MyResearch');
    // Default case for all other authentication methods:
    $standardLoginNeeded = (isset($_POST['username']) && isset($_POST['password'])
        && $action != 'Account');

    // Perform a login if necessary:
    if ($shibLoginNeeded || $standardLoginNeeded) {
        $user = UserAccount::login();
        if (PEAR::isError($user)) {
            $interface->initGlobals();
            include_once 'services/MyResearch/Login.php';
            Login::launch($user->getMessage());
            exit();
        }
    }
}

// Assign global interface values now that the environment is all set up:
$interface->initGlobals();

if (in_array('Primo Central', $_REQUEST['shard']) === true) {
    unset($_SESSION['shards']);
    unset($_REQUEST['shard']);
    $_REQUEST['shard'] = array();
    $_SESSION['shards'] = array();
    $_REQUEST['shard'][] = 'Primo Central';
    $_REQUEST['tab'] = 'primo';
    $_SESSION['shards'][] = 'Primo Central';
}
else if (in_array('GBV Primo Bridged', $_REQUEST['shard']) === true) {
    unset($_SESSION['shards']);
    unset($_REQUEST['shard']);
    $_REQUEST['shard'] = array();
    $_SESSION['shards'] = array();
    $_REQUEST['shard'][] = 'GBV Primo Bridged';
    $_REQUEST['tab'] = 'primo';
    $_SESSION['shards'][] = 'GBV Primo Bridged';
}
else if (in_array('GBV Central', $_REQUEST['shard']) === true || in_array('TUBdok', $_REQUEST['shard']) === true || in_array('wwwtub', $_REQUEST['shard']) === true) {
    unset($_SESSION['shards']);
    unset($_REQUEST['shard']);
    $_REQUEST['shard'] = array();
    $_SESSION['shards'] = array();
    $_REQUEST['shard'][] = 'GBV Central';
    $_REQUEST['shard'][] = 'TUBdok';
    $_REQUEST['shard'][] = 'wwwtub';
    $_REQUEST['shard'][] = 'localbiblio';
    $_REQUEST['tab'] = 'all';
    $_SESSION['shards'][] = 'GBV Central';
    $_SESSION['shards'][] = 'TUBdok';
    $_SESSION['shards'][] = 'wwwtub';
    $_SESSION['shards'][] = 'localbiblio';
}

if (in_array('Primo Central', $_SESSION['shards']) === true || in_array('GBV Primo Bridged', $_SESSION['shards']) === true) {
    $_REQUEST['tab'] = 'primo';
}
else {
    $_REQUEST['tab'] = 'all';
}

if ($configArray['IndexShards']['GBV Central'] == $configArray['IndexShards']['localbiblio']) {
    $interface->assign('gbvmessage', 'GBV-Index ist zu langsam, verwende lokalen Fallback-Index');
}

// Process Login Followup
if (isset($_REQUEST['followup'])) {
    processFollowup();
}

// Process Solr shard settings
processShards();

// Call Action
if (is_readable("services/$module/$action.php")) {
    include_once "services/$module/$action.php";
    if (class_exists($action)) {
        $service = new $action();
        $service->launch();
    } else {
        PEAR::raiseError(new PEAR_Error('Unknown Action'));
    }
} else {
    PEAR::RaiseError(new PEAR_Error('Cannot Load Action'));
}

/**
 * Handle processing and/or redirection for user followup actions.
 *
 * @return void
 */
function processFollowup()
{
    global $configArray;

    // The MyResearch/Login action may assign a value to followup.  In the case of
    // a SaveSearch action, we need to redirect after a successful login.  This
    // behavior is rather confusing -- we should consider achieving the same effect
    // in a more straightforward way.
    switch ($_REQUEST['followup']) {
    case 'SaveSearch':
        header(
            "Location: {$configArray['Site']['url']}/" .
            $_REQUEST['followupModule'] . "/" . $_REQUEST['followupAction'] .
            "?" . $_REQUEST['recordId']
        );
        die();
        break;
    }
}

/**
 * Process Solr-shard-related parameters and settings.
 *
 * @return void
 */
function processShards()
{
    global $configArray;
    global $interface;

    // If shards are not configured, give up now:
    if (!isset($configArray['IndexShards']) || empty($configArray['IndexShards'])) {
        return;
    }

    // If a shard selection list is found as an incoming parameter, we should save
    // it in the session for future reference:
    if (array_key_exists('shard', $_REQUEST)) {
        $_SESSION['shards'] = $_REQUEST['shard'];
    } else if (!array_key_exists('shards', $_SESSION)) {
        // If no selection list was passed in, use the default...

        // If we have a default from the configuration, use that...
        if (isset($configArray['ShardPreferences']['defaultChecked'])
            && !empty($configArray['ShardPreferences']['defaultChecked'])
        ) {
            $checkedShards = $configArray['ShardPreferences']['defaultChecked'];
            $_SESSION['shards'] = is_array($checkedShards) ?
                $checkedShards : array($checkedShards);
        } else {
            // If no default is configured, use all shards...
            $_SESSION['shards'] = array_keys($configArray['IndexShards']);
        }
    }

    // If we are configured to display shard checkboxes, send a list of shards
    // to the interface, with keys being shard names and values being a boolean
    // value indicating whether or not the shard is currently selected.
    if (isset($configArray['ShardPreferences']['showCheckboxes'])
        && $configArray['ShardPreferences']['showCheckboxes'] == true
    ) {
        $shards = array();
        foreach ($configArray['IndexShards'] as $shardName => $shardAddress) {
            $shards[$shardName] = in_array($shardName, $_SESSION['shards']);
        }
        $interface->assign('shards', $shards);
    }
}

/**
 * Callback function to handle any PEAR errors that are thrown.
 *
 * @param PEAR_Error $error The error object.
 *
 * @return void
 */
function handlePEARError($error)
{
    global $configArray;

    // It would be really bad if an error got raised from within the error handler;
    // we would go into an infinite loop and run out of memory.  To avoid this,
    // we'll set a static value to indicate that we're inside the error handler.
    // If the error handler gets called again from within itself, it will just
    // return without doing anything to avoid problems.  We know that the top-level
    // call will terminate execution anyway.
    static $errorAlreadyOccurred = false;
    if ($errorAlreadyOccurred) {
        return;
    } else {
        $errorAlreadyOccurred = true;
    }

    // Display an error screen to the user:
    $interface = new UInterface();

    $interface->assign('error', $error);
    $interface->assign('debug', $configArray['System']['debug']);

    $interface->display('error.tpl');

    // Exceptions we don't want to log
    $doLog = true;
    // Microsoft Web Discussions Toolbar polls the server for these two files
    //    it's not script kiddie hacking, just annoying in logs, ignore them.
    if (strpos($_SERVER['REQUEST_URI'], "cltreq.asp") !== false) {
        $doLog = false;
    }
    if (strpos($_SERVER['REQUEST_URI'], "owssvr.dll") !== false) {
        $doLog = false;
    }
    // If we found any exceptions, finish here
    if (!$doLog) {
        exit();
    }

    // Log the error for administrative purposes -- we need to build a variety
    // of pieces so we can supply information at five different verbosity levels:
    $baseError = $error->toString();
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'none';
    $basicServer = " (Server: IP = {$_SERVER['REMOTE_ADDR']}, " .
        "Referer = {$referer}, " .
        "User Agent = {$_SERVER['HTTP_USER_AGENT']}, " .
        "Request URI = {$_SERVER['REQUEST_URI']})";
    $detailedServer = "\nServer Context:\n" . print_r($_SERVER, true);
    $basicBacktrace = "\nBacktrace:\n";
    if (is_array($error->backtrace)) {
        foreach ($error->backtrace as $line) {
            $basicBacktrace .= "{$line['file']} line {$line['line']} - " .
                "class = {$line['class']}, function = {$line['function']}\n";
        }
    }
    $detailedBacktrace = "\nBacktrace:\n" . print_r($error->backtrace, true);
    $errorDetails = array(
        1 => $baseError,
        2 => $baseError . $basicServer,
        3 => $baseError . $basicServer . $basicBacktrace,
        4 => $baseError . $detailedServer . $basicBacktrace,
        5 => $baseError . $detailedServer . $detailedBacktrace
        );

    $logger = new Logger();
    $logger->log($errorDetails, PEAR_LOG_ERR);

    exit();
}

/**
 * Check for the various stages of functionality
 *
 * @return void
 */
function checkAvailabilityMode()
{
    global $configArray;
    $mode = array();

    // If the config file 'available' flag is
    //    set we are forcing downtime.
    if (!$configArray['System']['available']) {
        $mode['online']   = false;
        $mode['level']    = 'unavailable';
        // TODO : Variable reasons passed to template... and translated
        //$mode['message']  = $configArray['System']['available_reason'];
        $mode['template'] = 'unavailable.tpl';
        return $mode;
    }
    // TODO : Check if solr index is online
    // TODO : Check if ILMS database is online
    // TODO : More?

    // No problems? We are online then
    $mode['online'] = true;
    return $mode;
}
?>
