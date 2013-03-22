<?php
/**
 * File-based session handler
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
 * File-based session handler
 *
 * @category VuFind
 * @package  Session_Handlers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/creating_a_session_handler Wiki
 */
class FileSession extends SessionInterface
{
    static private $_path;

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
        self::$_path= isset($configArray['Session']['file_save_path']) ?
            $configArray['Session']['file_save_path'] : '/tmp/vufind_sessions';

        // Die if the session directory does not exist and cannot be created.
        if (!file_exists(self::$_path) || !is_dir(self::$_path)) {
            if (!@mkdir(self::$_path)) {
                PEAR::raiseError(
                    new PEAR_Error(
                        "Cannot access session save path: " . self::$_path
                    )
                );
            }
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
        $sess_file = self::$_path . '/sess_' . $sess_id;
        return (string) @file_get_contents($sess_file);
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
        $sess_file = self::$_path . '/sess_' . $sess_id;
        if ($fp = @fopen($sess_file, "w")) {
            $return = fwrite($fp, $data);
            fclose($fp);
            return $return;
        } else {
            return(false);
        }
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

        // Perform file-specific cleanup:
        $sess_file = self::$_path . '/sess_' . $sess_id;
        return(@unlink($sess_file));
    }

    /**
     * The garbage collector, this is executed when the session garbage collector
     * is executed and takes the max session lifetime as its only parameter.
     *
     * @param int $maxlifetime Maximum session lifetime.
     *
     * @return void
     * @access public
     */
    static public function gc($maxlifetime)
    {
        foreach (glob(self::$_path . "/sess_*") as $filename) {
            if (filemtime($filename) + $maxlifetime < time()) {
                @unlink($filename);
            }
        }
        return true;
    }
}

?>
