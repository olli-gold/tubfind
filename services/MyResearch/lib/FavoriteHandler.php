<?php
/**
 * FavoriteHandler Class
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
 * @package  Controller_MyResearch
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'services/MyResearch/lib/Resource.php';
require_once 'sys/Pager.php';

/**
 * FavoriteHandler Class
 *
 * This class contains shared logic for displaying lists of favorites (based on
 * earlier logic duplicated between the MyResearch/Home and MyResearch/MyList
 * actions).
 *
 * @category VuFind
 * @package  Controller_MyResearch
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class FavoriteHandler
{
    private $_favorites;
    private $_user;
    private $_listId;
    private $_allowEdit;
    private $_ids = array();
    protected $infoMsg = false;

    /**
     * Constructor.
     *
     * @param array  $favorites Array of Resource objects.
     * @param object $user      User object owning tag/note metadata.
     * @param int    $listId    ID of list containing desired tags/notes (or null
     * to show tags/notes from all user's lists).
     * @param bool   $allowEdit Should we display edit controls?
     *
     * @access public
     */
    public function __construct($favorites, $user, $listId = null, $allowEdit = true)
    {
        $this->_favorites = $favorites;
        $this->_user = $user;
        $this->_listId = $listId;
        $this->_allowEdit = $allowEdit;

        // Process the IDs found in the favorites (sort by source):
        if (is_array($favorites)) {
            foreach ($favorites as $current) {
                $id = $current->record_id;
                if (!empty($id)) {
                    $source = $current->source;
                    if (!isset($this->_ids[$source])) {
                        $this->_ids[$source] = array();
                    }
                    $this->_ids[$source][] = $id;
                }
            }
        }
    }

    /**
     * Assign all necessary values to the interface.
     *
     * @return void
     * @access public
     */
    public function assign()
    {
        global $interface;

        // Initialise from the current search globals
        $searchObject = SearchObjectFactory::initSearchObject();
        $searchObject->init();
        $interface->assign('sortList', $searchObject->getSortList());

        // Retrieve records from index (currently, only Solr IDs supported):
        if (array_key_exists('VuFind', $this->_ids)
            && count($this->_ids['VuFind']) > 0
        ) {
            if (!$searchObject->setQueryIDs($this->_ids['VuFind'])) {
                $this->infoMsg = 'too_many_favorites';
            }
            $result = $searchObject->processSearch();
            $resourceList = $searchObject->getResultListHTML(
                $this->_user, $this->_listId, $this->_allowEdit
            );
            $interface->assign('resourceList', $resourceList);
        } else {
            // If no records are displayed, $allowListEdit will be missing;
            // make sure it gets assigned so the list can be edited:
            $interface->assign('listEditAllowed', $this->_allowEdit);
        }

        // Set up paging of list contents:
        $summary = $searchObject->getResultSummary();
        $interface->assign('recordCount', $summary['resultTotal']);
        $interface->assign('recordStart', $summary['startRecord']);
        $interface->assign('recordEnd',   $summary['endRecord']);

        $link = $searchObject->renderLinkPageTemplate();
        $options = array(
            'totalItems' => $summary['resultTotal'],
            'perPage' => $summary['perPage'],
            'fileName' => $link
        );
        $pager = new VuFindPager($options);
        $interface->assign('pageLinks', $pager->getLinks());
    }

    /**
     * Get info message, if any (boolean false if no message).
     *
     * @return string|bool
     * @access public
     */
    public function getInfoMsg()
    {
        return $this->infoMsg;
    }
}

?>