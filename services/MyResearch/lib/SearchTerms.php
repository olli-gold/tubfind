<?php
/**
 * Table Definition for tags
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
 * Table Definition for tags
 *
 * @category VuFind
 * @package  DB_DataObject
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://pear.php.net/package/DB_DataObject/ PEAR Documentation
 */
class SearchTerms extends DB_DataObject
{
    // @codingStandardsIgnoreStart
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'searchterms';         // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $query;                           // string(25)  not_null
    public $count;                           // int(11)

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('SearchTerms',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    // @codingStandardsIgnoreEnd

    /**
     * Get all resources associated with the current tag.
     *
     * @return array
     * @access public
     */
    public function getResources()
    {
        $resList = array();

        $sql = 'SELECT * FROM "searchterms" ' .
            'WHERE ' .
            '"query" = ' . 
            "'" . $this->escape($this->query) . "'" . 
            ' ORDER BY count DESC';
        $res = new Resource();
        $res->query($sql);
        if ($res->N) {
            while ($res->fetch()) {
                $resList[] = clone($res);
            }
        }

        return $resList;
    }
}
