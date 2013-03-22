<?php
/**
 * MyList action for MyResearch module (used to display individual user lists)
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2007.
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
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'Action.php';
require_once 'services/MyResearch/lib/FavoriteHandler.php';

/**
 * MyList action for MyResearch module (used to display individual user lists)
 *
 * This class does not use MyResearch base class (we don't need to connect to
 * the catalog, and we need to bypass the "redirect if not logged in" logic to
 * allow public lists to work properly).
 *
 * @category VuFind
 * @package  Controller_MyResearch
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class MyList extends Action
{
    protected $errorMsg;
    protected $infoMsg;
    protected $showExport;
    protected $followupUrl;

    /**
     * Process parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $configArray;
        global $interface;
        global $user;

        // Delete List and All Resources (but only if list owner is logged in!)
        if (isset($_POST['deleteList'])) {
            $listID = $_POST['listID'];

            $list = User_list::staticGet($listID);

            if ($user->id == $list->user_id) {
                // Remove the List
                $result = $list->emptyList();
                if ($result) {
                    $followupUrl =  $configArray['Site']['url'] .
                        "/MyResearch/Favorites";
                    header("Location: " . $followupUrl . "?infoMsg=fav_list_delete");
                    exit();
                }
            }
            // If we get this far, there's an error
            $this->errorMsg = "fav_list_delete_fail";
        }

        // Fetch List object
        $list = User_list::staticGet($_GET['id']);

        // Ensure user have privs to view the list
        if (!$list->public && !UserAccount::isLoggedIn()) {
            include_once 'Login.php';
            Login::launch();
            exit();
        }
        if (!$list->public && $list->user_id != $user->id) {
            PEAR::raiseError(new PEAR_Error(translate('list_access_denied')));
        }

        $this->infoMsg = isset($_GET['infoMsg']) ? $_GET['infoMsg'] : false;
        $this->errorMsg = isset($_GET['errorMsg']) ? $_GET['errorMsg'] : false;
        $this->showExport = isset($_GET['showExport']) ? $_GET['showExport'] : false;

        // Delete Resource (but only if list owner is logged in!)
        if (isset($_GET['delete']) && $user->id == $list->user_id) {
            $resource = Resource::staticGet('record_id', $_GET['delete']);
            $list->removeResource($resource);
        }

        // Send list to template so title/description can be displayed:
        $interface->assign('list', $list);

        // Build Favorites List
        $favorites = $list->getResources(isset($_GET['tag']) ? $_GET['tag'] : null);

        // Load the User object for the owner of the list (if necessary):
        if ($user && $user->id == $list->user_id) {
            $listUser = $user;
        } else {
            $listUser = User::staticGet($list->user_id);
        }

        // Create a handler for displaying favorites and use it to assign
        // appropriate template variables:
        $allowEdit = ($user && ($user->id == $list->user_id));
        $favList = new FavoriteHandler(
            $favorites, $listUser, $list->id, $allowEdit
        );
        $favList->assign();
        if (!$this->infoMsg) {
            $this->infoMsg = $favList->getInfoMsg();
        }

        // Narrow by Tag
        if (isset($_GET['tag'])) {
            $interface->assign('tags', $_GET['tag']);
        }

        // Get My Lists
        $listList = $user ? $user->getLists() : array();
        $interface->assign('listList', $listList);

        // Get My Tags
        $tagList = $list->getTags();
        $interface->assign('tagList', $tagList);

        // Assign Error & Info Messages
        $interface->assign('infoMsg', $this->infoMsg);
        $interface->assign('errorMsg', $this->errorMsg);
        $interface->assign('showExport', $this->showExport);

        // Assign Exporter Options
        $exportOptions = array();
        if ($configArray['BulkExport']['enabled']) {
            $options = explode(':', $configArray['BulkExport']['options']);
            foreach ($options as $option) {
                if ($configArray['Export'][$option] == true) {
                    $exportOptions[] = $option;
                }
            }
            $interface->assign('exportOptions', $exportOptions);
        }

        $interface->setTemplate('list.tpl');
        $interface->setPageTitle($list->title);
        $interface->display('layout.tpl');
    }
}

?>
