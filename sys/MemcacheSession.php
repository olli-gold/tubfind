<?php
/**
 * MemCache session handler
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
 * @package  Session_Handlers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/creating_a_session_handler Wiki
 */
require_once 'SessionInterface.php';

/**
 * Memcache session handler
 *
 * @category VuFind
 * @package  Session_Handlers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/creating_a_session_handler Wiki
 */
class MemcacheSession extends SessionInterface
{
    static private $_connection;

    /**
     * Initialize the session handler.
     *
     * @param int $lt Session lifetime (in seconds)
     *
     * @return void
     * @access public
     */
    public function init($lt)
    {
        global $configArray;

        // Set defaults if nothing set in config file.
        $host = isset($configArray['Session']['memcache_host']) ?
            $configArray['Session']['memcache_host'] : 'localhost';
        $port = isset($configArray['Session']['memcache_port']) ?
            $configArray['Session']['memcache_port'] : 11211;
        $timeout = isset($configArray['Session']['memcache_connection_timeout']) ?
            $configArray['Session']['memcache_connection_timeout'] : 1;

        // Connect to Memcache:
        self::$_connection = new Memcache();
        if (!@self::$_connection->connect($host, $port, $timeout)) {
            PEAR::raiseError(
                new PEAR_Error(
                    "Could not connect to Memcache (host = {$host}, port = {$port})."
                )
            );
        }

        // Call standard session initialization from this point.
        parent::init($lt);
    }

    /**
     * Read function must return string value always to make save handler work as
     * expected. Return empty string if there is no data to read.
     *
     * @param string $sess_id The session ID to read
     *
     * @return string
     * @access public
     */
    static public function read($sess_id)
    {
        return self::$_connection->get("vufind_sessions/{$sess_id}");
    }

    /**
     * Write function that is called when session data is to be saved.
     *
     * @param string $sess_id The current session ID
     * @param string $data    The session data to write
     *
     * @return void
     * @access public
     */
    static public function write($sess_id, $data)
    {
        return self::$_connection->set(
            "vufind_sessions/{$sess_id}", $data, 0, self::$lifetime
        );
    }

    /**
     * The destroy handler, this is executed when a session is destroyed with
     * session_destroy() and takes the session id as its only parameter.
     *
     * @param string $sess_id The session ID to destroy
     *
     * @return void
     * @access public
     */
    static public function destroy($sess_id)
    {
        // Perform standard actions required by all session methods:
        parent::destroy($sess_id);

        // Perform Memcache-specific cleanup:
        return self::$_connection->delete("vufind_sessions/{$sess_id}");
    }
}


?>
