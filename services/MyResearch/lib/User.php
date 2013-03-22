<?php
/**
 * Table Definition for user
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
require_once 'DB/DataObject/Cast.php';

require_once 'User_resource.php';
require_once 'User_list.php';
require_once 'Resource_tags.php';
require_once 'Tags.php';

/**
 * Table Definition for user
 *
 * @category VuFind
 * @package  DB_DataObject
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://pear.php.net/package/DB_DataObject/ PEAR Documentation
 */
class User extends DB_DataObject
{
    // @codingStandardsIgnoreStart
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user';                // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $username;                        // string(30)  not_null unique_key
    public $password;                        // string(32)  not_null
    public $firstname;                       // string(50)  not_null
    public $lastname;                        // string(50)  not_null
    public $email;                           // string(250)  not_null
    public $cat_username;                    // string(50)
    public $cat_password;                    // string(50)
    public $college;                         // string(100)  not_null
    public $home_library;                         // string(100)  not_null
    public $major;                           // string(100)  not_null
    public $created;                         // datetime(19)  not_null binary

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('User',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    // @codingStandardsIgnoreEnd

    /**
     * Sleep method for serialization.
     *
     * @return array
     * @access public
     * @todo   Investigate if this is still necessary.
     */
    public function __sleep()
    {
        return array(
            'id', 'username', 'password', 'cat_username', 'cat_password',
            'firstname', 'lastname', 'email', 'college', 'home_library', 'major'
        );
    }

    /**
     * Wakeup method for serialization.
     *
     * @return void
     * @access public
     * @todo   Investigate if this is still necessary.
     */
    public function __wakeup()
    {
    }

    /**
     * Is the specified resource already in the user's account?
     *
     * @param object $resource Resource to check.
     *
     * @return bool
     * @access public
     */
    public function hasResource($resource)
    {
        $join = new User_resource();
        $join->user_id = $this->id;
        $join->resource_id = $resource->id;
        if ($join->find()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add a resource to the user's account.
     *
     * @param object $resource        The resource to add.
     * @param object $list            The list to store the resource in.
     * @param array  $tagArray        An array of tags to associate with the
     * resource.
     * @param string $notes           User notes about the resource.
     * @param bool   $replaceExisting Whether to replace all existing tags (true)
     * or append to the existing list (false).
     *
     * @return bool
     * @access public
     */
    public function addResource(
        $resource, $list, $tagArray, $notes, $replaceExisting = true
    ) {
        $join = new User_resource();
        $join->user_id = $this->id;
        $join->resource_id = $resource->id;
        $join->list_id = $list->id;
        if ($join->find(true)) {
            $join->notes = $notes;
            $join->update();
            // update() will return false if we save without making any changes,
            // but we always want to report success after this point.
            $result = true;
        } else {
            if ($notes) {
                $join->notes = $notes;
            }
            $result = $join->insert();
        }
        if ($result) {
            $join = new Resource_tags();
            $join->resource_id = $resource->id;
            $join->user_id = $this->id;
            $join->list_id = $list->id;

            if ($replaceExisting) {
                // Delete old tags -- note that we need to clone $join for this
                // operation or else it will be broken when we use it for searching
                // below.
                $killer = clone($join);
                $killer->delete();
            }

            // Add new tags, if any:
            if (is_array($tagArray) && count($tagArray)) {
                foreach ($tagArray as $value) {
                    $value = str_replace('"', '', $value);
                    $tag = new Tags();
                    $tag->tag = $value;
                    if (!$tag->find(true)) {
                        $tag->insert();
                    }
                    $join->tag_id = $tag->id;
                    // Don't save duplicate tags!
                    if (!$join->find(false)) {
                        $join->insert();
                    }
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Remove a resource from the user's account.
     *
     * @param object $resource The resource to remove.
     *
     * @return void
     * @access public
     * @todo: delete any unused tags
     */
    public function removeResource($resource)
    {
        // Remove the Saved Resource
        $join = new User_resource();
        $join->user_id = $this->id;
        $join->resource_id = $resource->id;
        $join->delete();

        // Remove the Tags from the resource
        $join = new Resource_tags();
        $join->user_id = $this->id;
        $join->resource_id = $resource->id;
        $join->delete();
    }

    /**
     * Given an array of item ids, remove them from all lists
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
        $sql = 'SELECT "id" from "resource" ' .
            'WHERE ("record_id" = '.implode($sqlIDS, ' OR "record_id" = ').") " .
            "AND \"source\"='" . $this->escape($source) . "'";

        $resources = new Resource();
        $resources->query($sql);

        if ($resources->N) {
            while ($resources->fetch()) {
                $resourceList[] = "'".$this->escape($resources->id)."'";
            }
        }

        // Remove Resource
        $sql = 'DELETE FROM "user_resource" ' .
            "WHERE \"user_id\" = '" . $this->escape($this->id) . "' " .
            'AND ("resource_id" =' .
            implode($resourceList, ' OR "resource_id" =') . ")";

        $removeResource = new User_resource();
        $removeResource->query($sql);

        // Remove Resource Tags
        $sql = 'DELETE FROM "resource_tags" ' .
            "WHERE \"user_id\" = '" . $this->escape($this->id) . "' " .
            'AND ("resource_id" =' .
            implode($resourceList, ' OR "resource_id" =') . ")";

        $removeTags = new Resource_tags();
        $removeTags->query($sql);

        // If we got this far, there were no fatal DB errors so report success
        return true;
    }

    /**
     * Load information from the resource table associated with this user.
     *
     * @param array $tags Array of tags to use as a filter (optional).
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
            "'" . $this->escape($this->id) . "'";

        if ($tags) {
            for ($i=0; $i<count($tags); $i++) {
                $sql .= ' AND "resource"."id" IN ' .
                    '(SELECT DISTINCT "resource_tags"."resource_id" ' .
                    'FROM "resource_tags", "tags" WHERE ' .
                    '"resource_tags"."tag_id"="tags"."id" AND "tags"."tag" = ' .
                    "'" . $this->escape($tags[$i]) . "'" .
                    ' AND "resource_tags"."user_id" = ' .
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
     * Get information saved in a user's favorites for a particular record.
     *
     * @param string $resourceId ID of record being checked.
     * @param int    $listId     Optional list ID (to limit results to a particular
     * list).
     *
     * @return array
     * @access public
     */
    public function getSavedData($resourceId, $listId = null)
    {
        $savedList = array();

        $sql = 'SELECT "user_resource".*, "user_list"."title" as list_title, ' .
            '"user_list"."id" as list_id ' .
            'FROM "user_resource", "resource", "user_list" ' .
            'WHERE "resource"."id" = "user_resource"."resource_id" ' .
            'AND "user_resource"."list_id" = "user_list"."id" ' .
            'AND "user_resource"."user_id" = ' .
            "'" . $this->escape($this->id) . "' " .
            'AND "resource"."record_id" = ' .
            "'" . $this->escape($resourceId) . "'";
        if (!is_null($listId)) {
            $sql .= ' AND "user_resource"."list_id"=' .
                "'" . $this->escape($listId) . "'";
        }
        $saved = new User_resource();
        $saved->query($sql);
        if ($saved->N) {
            while ($saved->fetch()) {
                $savedList[] = clone($saved);
            }
        }

        return $savedList;
    }


    /**
     * Get a list of all tags generated by the user in favorites lists.  Note that
     * the returned list WILL NOT include tags attached to records that are not
     * saved in favorites lists.
     *
     * @param int $resourceId Filter for tags tied to a specific resource (null
     * for no filter).
     * @param int $listId     Filter for tags tied to a specific list (null for no
     * filter).
     *
     * @return array
     * @access public
     */
    public function getTags($resourceId = null, $listId = null)
    {
        $tagList = array();

        $sql = 'SELECT MIN("tags"."id"), "tags"."tag", ' .
            'COUNT("resource_tags"."id") AS cnt ' .
            'FROM "tags", "resource_tags", "user_resource", "resource" ' .
            'WHERE "tags"."id" = "resource_tags"."tag_id" ' .
            'AND "user_resource"."user_id" = ' .
            "'" . $this->escape($this->id) . "' " .
            'AND "user_resource"."resource_id" = "resource"."id" ' .
            'AND "resource_tags"."user_id" = ' .
            "'" . $this->escape($this->id) . "' " .
            'AND "resource"."id" = "resource_tags"."resource_id" ' .
            'AND "user_resource"."list_id" = "resource_tags"."list_id" ';
        if (!is_null($resourceId)) {
            $sql .= 'AND "resource"."record_id" = ' .
                "'" . $this->escape($resourceId) . "' ";
        }
        if (!is_null($listId)) {
            $sql .= 'AND "resource_tags"."list_id" = ' .
                "'" . $this->escape($listId) . "' ";
        }
        $sql .= 'GROUP BY "tags"."tag" ORDER BY "tag"';
        $tag = new Tags();
        $tag->query($sql);
        if ($tag->N) {
            while ($tag->fetch()) {
                $tagList[] = clone($tag);
            }
        }

        return $tagList;
    }

    /**
     * Get all of the lists associated with this user.
     *
     * @return array
     * @access public
     */
    public function getLists()
    {
        $lists = array();

        $sql = 'SELECT "user_list".*, COUNT("user_resource"."id") AS cnt ' .
            'FROM "user_list" LEFT JOIN "user_resource" ' .
            'ON "user_list"."id" = "user_resource"."list_id" ' .
            'WHERE "user_list"."user_id" = ' .
            "'" . $this->escape($this->id) . "' " .
            'GROUP BY "user_list"."id", "user_list"."user_id", ' .
            '"user_list"."title", "user_list"."description", ' .
            '"user_list"."created", "user_list"."public" ' .
            'ORDER BY "user_list"."title"';
        $list = new User_list();
        $list->query($sql);
        if ($list->N) {
            while ($list->fetch()) {
                $lists[] = clone($list);
            }
        }

        return $lists;
    }

    /**
     * Changes the home library of a user
     *
     * @param string $home_library The new home library code
     *
     * @return boolean True on success
     * @access public
     */
    public function changeHomeLibrary($home_library)
    {
        $this->home_library = $home_library;
        $this->update();
        
        // Update Session

        if ($session_info = UserAccount::isLoggedIn()) {
            $session_info->home_library = $home_library;
            UserAccount::updateSession($session_info);
        }
        return true;
    }

}
