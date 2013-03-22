<?php
/**
 * Table Definition for resource
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
 * Table Definition for resource
 *
 * @category VuFind
 * @package  DB_DataObject
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://pear.php.net/package/DB_DataObject/ PEAR Documentation
 */
class Resource extends DB_DataObject
{
    // @codingStandardsIgnoreStart
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'resource';                        // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $record_id;                       // string(30)  not_null multiple_key
    public $title;                           // string(200)  not_null
    public $source = 'VuFind';               // string(50)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Resource',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    // @codingStandardsIgnoreEnd

    /**
     * Get tags associated with the current resource.
     *
     * @param int $limit Max. number of tags to return (0 = no limit)
     *
     * @return array
     * @access public
     */
    public function getTags($limit = 0)
    {
        $tagList = array();

        $query = 'SELECT MIN("tags"."id"), "tags"."tag", COUNT(*) as cnt ' .
            'FROM "tags", "resource_tags", "resource" ' .
            'WHERE "tags"."id" = "resource_tags"."tag_id" ' .
            'AND "resource"."id" = "resource_tags"."resource_id" ' .
            'AND "resource"."record_id" = ' .
            "'" . $this->escape($this->record_id) . "' " .
            'AND "resource"."source" = ' .
            "'" . $this->escape($this->source) . "' " .
            'GROUP BY "tags"."tag" ORDER BY cnt DESC, "tags"."tag"';
        $tag = new Tags();
        $tag->query($query);
        if ($tag->N) {
            while ($tag->fetch()) {
                $tagList[] = clone($tag);
                // Return prematurely if we hit the tag limit:
                if ($limit > 0 && count($tagList) >= $limit) {
                    return $tagList;
                }
            }
        }

        return $tagList;
    }

    /**
     * Add a tag to the current resource.
     *
     * @param string $tag  The tag to save.
     * @param object $user The user posting the tag.
     *
     * @return bool        True on success, false on failure.
     * @access public
     */
    public function addTag($tag, $user)
    {
        $tag = trim($tag);
        if (!empty($tag)) {
            include_once 'services/MyResearch/lib/Tags.php';
            include_once 'services/MyResearch/lib/Resource_tags.php';

            $tags = new Tags();
            $tags->tag = $tag;
            if (!$tags->find(true)) {
                $tags->insert();
            }

            $rTag = new Resource_tags();
            $rTag->resource_id = $this->id;
            $rTag->tag_id = $tags->id;
            if (!$rTag->find()) {
                $rTag->user_id = $user->id;
                $rTag->insert();
            }
        }

        return true;
    }

    /**
     * Add a comment to the current resource.
     *
     * @param string $body The comment to save.
     * @param object $user The user posting the comment.
     *
     * @return bool        True on success, false on failure.
     * @access public
     */
    public function addComment($body, $user)
    {
        include_once 'services/MyResearch/lib/Comments.php';

        // We can't save the comment without a logged-in user!
        if (!is_object($user)) {
            return false;
        }

        $comment = new Comments();
        $comment->user_id = $user->id;
        $comment->resource_id = $this->id;
        $comment->comment = $body;
        $comment->created = date('Y-m-d h:i:s');
        $comment->insert();

        return true;
    }

    /**
     * Get a list of all comments associated with this resource.
     *
     * @return array
     * @access public
     */
    public function getComments()
    {
        include_once 'services/MyResearch/lib/Comments.php';

        $sql = 'SELECT "comments".*, "user"."firstname" || ' .
            "' ' || " . '"user"."lastname" as fullname ' .
            'FROM "comments" RIGHT OUTER JOIN "user" ' .
            'ON "comments"."user_id" = "user"."id" ' .
            'WHERE "comments"."resource_id" = ' .
            "'" . $this->escape($this->id) . "' " .
            'ORDER BY "comments"."created"';

        $commentList = array();

        $comment = new Comments();
        $comment->query($sql);
        if ($comment->N) {
            while ($comment->fetch()) {
                $commentList[] = clone($comment);
            }
        }

        return $commentList;
    }

}
