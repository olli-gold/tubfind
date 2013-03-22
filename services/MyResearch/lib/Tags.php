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
class Tags extends DB_DataObject
{
    // @codingStandardsIgnoreStart
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'tags';                            // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $tag;                             // string(25)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Tags',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    // @codingStandardsIgnoreEnd

    /**
     * Get all resources associated with the current tag.
     *
     * @return array
     * @access public
     */
    public function getAllResources()
    {
        $resList = array();

        $sql = 'SELECT "resource".* FROM "resource_tags", "resource" ' .
            'WHERE "resource"."id" = "resource_tags"."resource_id" ' .
            'AND "resource_tags"."tag_id" = ' . 
            "'" . $this->escape($this->id) . "'";
        $res = new Resource();
        $res->query($sql);
        if ($res->N) {
            while ($res->fetch()) {
                $resList[] = clone($res);
            }
        }

        return $resList;
    }

    /**
     * Get limited resources associated with the current tag.
     *
     * @return array
     * @access public
     */
    public function getResources($limitStart = 0, $limitCount = 20)
    {
        $resList = array();

        $sql = 'SELECT resource.* FROM resource_tags, resource ' .
            'WHERE resource.id = resource_tags.resource_id ' .
            'AND resource_tags.tag_id = ' . 
            "'" . $this->escape($this->id) . "' LIMIT $limitStart, $limitCount";
        $res = new Resource();
        $res->query($sql);
        if ($res->N) {
            while ($res->fetch()) {
                $resList[] = clone($res);
            }
        }

        return $resList;
    }

    /**
     * Get all resources associated with the current tag.
     *
     * @return array
     * @access public
     */
    public function getTotalResources()
    {
        $sql = 'SELECT COUNT(*) FROM "resource_tags", "resource" ' .
            'WHERE "resource"."id" = "resource_tags"."resource_id" ' .
            'AND "resource_tags"."tag_id" = ' . 
            "'" . $this->escape($this->id) . "'";
        $res = new Resource();
        $res->query($sql);
        if ($res->N) {
            while ($res->fetch()) {
                $r2 = $res->toArray();
                $resCount = $r2['COUNT(*)'];
            }
        }

        return $resCount;
    }
}
