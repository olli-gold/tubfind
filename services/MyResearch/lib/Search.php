<?php
/**
 * Table Definition for search
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
 * @package  DB_DataObject
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://pear.php.net/package/DB_DataObject/ PEAR Documentation
 */
require_once 'DB/DataObject.php';

/**
 * Table Definition for search
 *
 * @category VuFind
 * @package  DB_DataObject
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://pear.php.net/package/DB_DataObject/ PEAR Documentation
 */
class SearchEntry extends DB_DataObject
{
    // @codingStandardsIgnoreStart
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'search';                          // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $user_id;                         // int(11)  not_null multiple_key
    public $list_id;                         // int(11)  multiple_key
    public $created;                         // date(10)  not_null binary
    public $title;                           // string(20)
    public $saved;                           // int(1) not_null default 0
    public $search_object;                   // blob
    public $session_id;                      // varchar(128)

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Search',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    // @codingStandardsIgnoreEnd

    /**
     * Get an array of SearchEntry objects for the specified user.
     *
     * @param int $sid Session ID of current user.
     * @param int $uid User ID of current user (optional).
     *
     * @return array   Matching SearchEntry objects.
     * @access public
     */
    public function getSearches($sid, $uid = null)
    {
        $searches = array();

        $sql = 'SELECT * FROM "search" WHERE "session_id" = ' .
            "'" . $this->escape($sid) . "'";
        if ($uid != null) {
            $sql .= " OR \"user_id\" = '" . $this->escape($uid) . "'";
        }
        $sql .= ' ORDER BY "id"';

        $s = new SearchEntry();
        $s->query($sql);
        if ($s->N) {
            while ($s->fetch()) {
                $searches[] = clone($s);
            }
        }

        return $searches;
    }

    /**
     * Get an array of SearchEntry objects representing expired, unsaved searches.
     *
     * @param int $daysOld Age in days of an "expired" search.
     *
     * @return array       Matching SearchEntry objects.
     * @access public
     */
    public function getExpiredSearches($daysOld = 2)
    {
        // Determine the expiration date:
        $expireDate = date('Y-m-d', time() - $daysOld * 24 * 60 * 60);

        // Find expired, unsaved searches:
        $sql = 'SELECT * FROM "search" WHERE "saved"=0 AND "created"<' .
            "'{$expireDate}'";
        $s = new SearchEntry();
        $s->query($sql);
        $searches = array();
        if ($s->N) {
            while ($s->fetch()) {
                $searches[] = clone($s);
            }
        }
        return $searches;
    }
}
