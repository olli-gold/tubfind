<?php
/**
 * Table Definition for user_list
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
 * Table Definition for user_list
 *
 * @category VuFind
 * @package  DB_DataObject
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://pear.php.net/package/DB_DataObject/ PEAR Documentation
 */ // @codingStandardsIgnoreStart
class User_list extends DB_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user_list';                       // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $user_id;                         // int(11)  not_null multiple_key
    public $title;                           // string(200)  not_null
    public $description;                     // string(500)
    public $created;                         // datetime(19)  not_null binary
    public $public;                          // int(11)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('User_list',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    // @codingStandardsIgnoreEnd

    /**
     * Load the resources associated with the list.
     *
     * @param array $tags Tags to use as filters against retrieved results.
     *
     * @return array
     * @access public
     */
    public function getResources($tags = null)
    {
        $resourceList = array();

        $sql = 'SELECT DISTINCT "resource".* FROM "resource", "user_resource" ' .
            'WHERE "resource"."id" = "user_resource"."resource_id" ' .
            'AND "user_resource"."user_id" = ' .
            "'" . $this->escape($this->user_id) . "' " .
            'AND "user_resource"."list_id" = ' .
            "'" . $this->escape($this->id) . "'";

        if ($tags) {
            for ($i=0; $i<count($tags); $i++) {
                $sql .= ' AND "resource"."id" IN ' .
                    '(SELECT DISTINCT "resource_tags"."resource_id" ' .
                    'FROM "resource_tags", "tags" ' .
                    'WHERE "resource_tags"."tag_id"="tags"."id" ' .
                    'AND "tags"."tag" = ' . "'" . $this->escape($tags[$i]) . "' " .
                    'AND "resource_tags"."user_id" = ' .
                    "'" . $this->escape($this->user_id) . "' " .
                    'AND "resource_tags"."list_id" = ' .
                    "'" . $this->escape($this->id) . "')";
            }
        }

        $resource = new Resource();
        $resource->query($sql);
        if ($resource->N) {
            while ($resource->fetch()) {
                $resourceList[] = clone($resource);
            }
        }

        return $resourceList;
    }

    /**
     * Get all of the tags associated with the list.
     *
     * @return array
     * @access public
     */
    public function getTags()
    {
        $tagList = array();

        $sql = 'SELECT "tags"."tag", COUNT("resource_tags"."id") AS cnt ' .
            'FROM "resource_tags", "tags" WHERE "resource_tags"."user_id" = ' .
            "'" . $this->escape($this->user_id) . "' " .
            'AND "resource_tags"."list_id" = ' .
            "'" . $this->escape($this->id) . "' " .
            'AND "tags"."id" = "resource_tags"."tag_id" ' .
            'GROUP BY "tags"."tag" ORDER BY "tag"';

        $resource = new Resource();
        $resource->query($sql);
        if ($resource->N) {
            while ($resource->fetch()) {
                $tagList[] = clone($resource);
            }
        }

        return $tagList;
    }

    /**
     * Remove the specified resource from the list.
     *
     * @param Resource $resource Resource object to remove
     *
     * @return void
     * @access public
     * @todo: delete any unused tags
     */
    public function removeResource($resource)
    {
        // Remove the Saved Resource
        $join = new User_resource();
        $join->user_id = $this->user_id;
        $join->resource_id = $resource->id;
        $join->list_id = $this->id;
        $join->delete();

        // Remove the Tags from the resource
        $join = new Resource_tags();
        $join->user_id = $this->user_id;
        $join->resource_id = $resource->id;
        $join->list_id = $this->id;
        $join->delete();
    }

    /**
     * Remember that this list was used so that it can become the default in
     * dialog boxes.
     *
     * @return void
     * @access public
     */
    public function rememberLastUsed()
    {
        $_SESSION['lastListUsed'] = $this->id;
    }

    /**
     * Retrieve the ID of the last list that was accessed, if any.
     *
     * @return mixed User_list ID (if set) or null (if not available).
     * @access public
     */
    public static function getLastUsed()
    {
        return isset($_SESSION['lastListUsed']) ? $_SESSION['lastListUsed'] : null;
    }

    /**
     * Given an array of item ids, remove them from a list
     *
     * @param array  $ids    IDs to remove from the list
     * @param string $source Type of resource identified by IDs
     *
     * @return bool          True on success, false on error.
     * @access public
     */
    public function removeResourcesById($ids, $source = 'VuFind')
    {
        $sqlIDS = array();
        foreach ($ids as $id) {
            if (!empty($id)) {
                $sqlIDS[] = "'".$this->escape($id)."'";
            }
        }

        // No work is needed if we have no IDs to delete:
        if (empty($sqlIDS)) {
            return true;
        }

        // Get Resource Ids
        $sql = 'SELECT "id" FROM "resource" WHERE ("record_id" = ' .
            implode($sqlIDS, ' OR "record_id" = ') . ") " .
            'AND "source" = ' . "'" . $this->escape($source) . "'";

        $resources = new Resource();
        $resources->query($sql);

        if ($resources->N) {
            while ($resources->fetch()) {
                $resourceList[] = "'".$this->escape($resources->id)."'";
            }
        }

        // Remove Resource
        $sql = 'DELETE FROM "user_resource" ' .
            "WHERE \"user_id\" = '" . $this->escape($this->user_id) . "' " .
            "AND \"list_id\" = '" . $this->escape($this->id) . "' " .
            'AND ("resource_id" =' .
            implode($resourceList, ' OR "resource_id" =') . ")";

        $removeResource = new User_resource();
        $removeResource->query($sql);

        // Remove Resource Tags
        $sql = 'DELETE FROM "resource_tags" ' .
            "WHERE \"user_id\" = '" . $this->escape($this->user_id) ."' " .
            "AND \"list_id\" = '" . $this->escape($this->id) . "' " .
            'AND ("resource_id" =' .
            implode($resourceList, ' OR "resource_id" =') . ")";

        $removeTags = new Resource_tags();
        $removeTags->query($sql);

        // If we got this far, there were no fatal DB errors so report success
        return true;
    }

    /**
     * Remove all resources and tags and delete a list.
     *
     * @return bool True on success, false on error.
     * @access public
     */
    public function emptyList()
    {
        // Remove Resources
        $sql = 'DELETE FROM "user_resource" ' .
            "WHERE \"user_id\" = '" . $this->escape($this->user_id) . "' " .
            "AND \"list_id\" = '" . $this->escape($this->id) . "'";

        $removeResource = new User_resource();
        $removeResource->query($sql);

        // Remove Resource Tags
        $sql = 'DELETE FROM "resource_tags" ' .
            "WHERE \"user_id\" = '" . $this->escape($this->user_id) . "' " .
            "AND \"list_id\" = '" . $this->escape($this->id) . "'";

        $removeTags = new Resource_tags();
        $removeTags->query($sql);

        // Remove the List
        $this->delete();

        // If we got this far, there were no fatal DB errors so report success
        return true;
    }

    /**
     * Updates a list
     *
     * @param string $title  New title for list
     * @param string $desc   New description for list
     * @param int    $public Is list public? (1 = yes, 0 = no)
     *
     * @return bool          True on success, false on error
     * @access public
     */
    public function updateList($title, $desc, $public)
    {
        $this->title = $title;
        $this->description = $desc;
        $this->public = $public;
        $this->update();

        // If we got this far, there were no fatal DB errors so report success
        return true;
    }
}

?>
