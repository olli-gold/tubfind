<?php
/**
 * MySQL session handler
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
require_once 'services/MyResearch/lib/Session.php';

/**
 * MySQL session handler
 *
 * @category VuFind
 * @package  Session_Handlers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/creating_a_session_handler Wiki
 */
class MySQLSession extends SessionInterface
{
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
        $s = new Session();
        $s->session_id = $sess_id;

        if ($s->find(true)) {
            // enforce lifetime of this session data
            if ($s->last_used + self::$lifetime > time()) {
                $s->last_used = time();
                $s->update();
                return $s->data;
            } else {
                $s->delete();
                return '';
            }
        } else {
            // in seconds - easier for calcuating duration
            $s->last_used = time();
            // in date format - easier to read
            $s->created = date('Y-m-d h:i:s');
            $s->insert();
            return '';
        }
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
        $s = new Session();
        $s->session_id = $sess_id;
        if ($s->find(true)) {
            $s->data = $data;
            return $s->update();
        } else {
            return false;
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

        // Now do database-specific destruction:
        $s = new Session();
        $s->session_id = $sess_id;
        return $s->delete();
    }

    /**
     * The garbage collector, this is executed when the session garbage collector
     * is executed and takes the max session lifetime as its only parameter.
     *
     * @param int $sess_maxlifetime Maximum session lifetime.
     *
     * @return void
     * @access public
     */
    static public function gc($sess_maxlifetime)
    {
        $s = new Session();
        $s->whereAdd(
            '"last_used" + ' . $s->escape($sess_maxlifetime) . ' < ' . time()
        );
        $s->delete(true);
    }
}

?>
