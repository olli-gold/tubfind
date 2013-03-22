<?php
/**
 * Save action for Bulk module
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
 * @package  Controller_Cart
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @author   Luke O'Sullivan <l.osullivan@swansea.ac.uk>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */

require_once 'Bulk.php';
require_once 'services/MyResearch/lib/Resource.php';
require_once 'services/MyResearch/lib/User.php';

/**
 * Save action for Bulk module
 *
 * @category VuFind
 * @package  Controller_Cart
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @author   Luke O'Sullivan <l.osullivan@swansea.ac.uk>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Save extends Bulk
{

    /**
     * Process parameters and display the page.
     *
     * @return void
     * @access public
     */
    function launch()
    {
        global $interface;
        global $configArray;

        // If we have a submission, lets try and save
        if (isset($_REQUEST['submit'])) {
            $result = $this->saveRecord();
            if ($result) {
                $this->followupUrl = $configArray['Site']['url'] .
                    "/MyResearch/MyList/" . urlencode($result['list']);
                header(
                    "Location: " . $this->followupUrl . "?infoMsg=bulk_save_success"
                );
                exit();
            } else {
                $this->errorMsg = translate("bulk_save_failure");
            }
        }

        if (isset($_GET['lightbox'])) {
            if (!$this->user) {
                return $this->_userLightBox();
            } else {
                return $this->_processLightBox();
            }

        } else {
            if (!$this->user) {
                return $this->_userNonLightBox();
            } else {
                return $this->_processNonLightBox();
            }
        }
    }

    /**
     * Process Light Box Request
     * Display error message on terminal error or save details page on success
     *
     * @return void
     * @access public
     */
    private function _processLightBox()
    {
        global $interface;

        // Without IDs, we can't continue
        if (empty($_REQUEST['ids'])) {
            $interface->assign('title', translate('bulk_fail'));
            $interface->assign('errorMsg', $_GET['message']);
            return $interface->fetch('Cart/bulkError.tpl');
        }

          // Create a list of all lists
        $lists = $this->user->getLists();

        $interface->assign('itemIDS', $_POST['ids']);
        $interface->assign('lastListUsed', User_list::getLastUsed());
        $interface->assign('itemList', $this->getRecordDetails($_REQUEST['ids']));
        $interface->assign('lists', $lists);
        $interface->assign('title', $_GET['message']);
        return $interface->fetch('Cart/save.tpl');
    }

    /**
     * Process Non-LightBox Request
     * Display error message on terminal error or save details page on success
     *
     * @return void
     * @access public
     */
    private function _processNonLightBox()
    {
        global $interface;

        // Assign IDs
        if (isset($_REQUEST['selectAll']) && is_array($_REQUEST['idsAll'])) {
            $ids = $_REQUEST['idsAll'];
        } else if (isset($_REQUEST['ids'])) {
            $ids = $_REQUEST['ids'];
        }

        // Without IDs, we can't continue
        if (empty($ids)) {
            header(
                "Location: " . $this->followupUrl . "?errorMsg=bulk_noitems_advice"
            );
            exit();
        }

        // Create a list of all lists
        $lists = $this->user->getLists();

        $parts = array();
        foreach ($ids as $id) {
            $parts[] = urlencode('ids[]') . '=' . urlencode($id);
        }
        $url = implode('&', $parts);

        $interface->assign('idURL', $url);
        $interface->assign('lastListUsed', User_list::getLastUsed());
        $interface->assign('itemIDS', $ids);
        $interface->assign('itemList', $this->getRecordDetails($ids));
        $interface->assign('lists', $lists);
        $interface->setPageTitle('bookbag_save_selected');
        $interface->assign('subTemplate', 'save.tpl');
        $interface->setTemplate('view.tpl');
        $interface->display('layout.tpl');
    }

    /**
     * Present User Login for Light Box
     * Display LightBox Login
     *
     * @return void
     * @access public
     */
    private function _userLightBox()
    {
        global $interface;

        // Without IDs, we can't continue
        if (empty($_REQUEST['ids'])) {
            $interface->assign('title', translate('bulk_fail'));
            $interface->assign('errorMsg', $_GET['message']);
            return $interface->fetch('Cart/bulkError.tpl');
        }
        foreach ($_REQUEST['ids'] as $id) {
            $extraParams[] = array('name' => "ids[]", 'value' => $id);
        }
        $extraParams[] = array('name' => "saveCart", 'value' => 1);
        $interface->assign('extraParams', $extraParams);
        $interface->assign('title', $_GET['message']);
        $interface->assign('message', 'You must be logged in first');
        $interface->assign('followup', true);
        $interface->assign('followupAction', 'Home');
        return $interface->fetch('AJAX/login.tpl');
    }

    /**
     * Present User Login
     * Display Login
     *
     * @return void
     * @access public
     */
    private function _userNonLightBox()
    {
        global $interface;

        // Assign IDs
        if (isset($_REQUEST['selectAll']) && is_array($_REQUEST['idsAll'])) {
            $ids = $_REQUEST['idsAll'];
        } else if (isset($_REQUEST['ids'])) {
            $ids = $_REQUEST['ids'];
        }

        // Without IDs, we can't continue
        if (empty($ids)) {
            header(
                "Location: " . $this->followupUrl . "?errorMsg=bulk_noitems_advice"
            );
            exit();
        }
        foreach ($ids as $id) {
            $extraParams[] = array('name' => "ids[]", 'value' => $id);
        }
        $extraParams[] = array('name' => "saveCart", 'value' => 1);
        $interface->assign('extraParams', $extraParams);
        $interface->assign('followup', true);
        $interface->assign('followupModule', 'Cart');
        $interface->assign('followupAction', 'Home');
        $interface->assign('infoMsg', 'You must be logged in first');
        $interface->assign('subTemplate', '../MyResearch/login.tpl');
        $interface->setTemplate('view.tpl');
        $interface->display('layout.tpl');
    }

    /**
     * Save the records
     *
     * @return array A list of successfully saved record ids and the list id
     * @access public
     */
    public function saveRecord()
    {
        // Fail if the user is not logged in:
        if (!$this->user || !isset($_POST['ids'])) {
            return false;
        }

        $list = new User_list();
        if ($_REQUEST['list'] != '') {
            $list->id = $_REQUEST['list'];
        } else {
            $list->user_id = $this->user->id;
            $list->title = "My Favorites";
            $list->insert();
        }

        // Remember that the list was used so it can be the default in future
        // dialog boxes:
        $list->rememberLastUsed();

        foreach ($_POST['ids'] as $id) {

            $resource = new Resource();
            $resource->record_id = $id;
            $resource->source= $_REQUEST['service'];
            if (!$resource->find(true)) {
                $resource->insert();
            }

            preg_match_all('/"[^"]*"|[^ ]+/', $_REQUEST['mytags'], $tagArray);
            $result['details'][$id] = $this->user->addResource(
                $resource, $list, $tagArray[0], false, false
            );

        }
        $result['list'] = $list->id;
        return $result;
    }

}
?>
