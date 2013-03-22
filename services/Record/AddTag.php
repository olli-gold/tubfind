<?php
/**
 * AddTag action for Record module
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
 * @package  Controller_Record
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'Action.php';

require_once 'services/MyResearch/lib/User.php';
require_once 'services/MyResearch/lib/Tags.php';
require_once 'services/MyResearch/lib/Resource.php';

/**
 * AddTag action for Record module
 *
 * @category VuFind
 * @package  Controller_Record
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class AddTag extends Action
{
    private $_user;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
        $this->_user = UserAccount::isLoggedIn();
    }

    /**
     * Process incoming parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $interface;
        global $configArray;

        $interface->assign('id', $_GET['id']);

        // Check if user is logged in
        if (!$this->_user) {
            $interface->assign('recordId', $_GET['id']);
            $interface->assign('followupModule', 'Record');
            $interface->assign('followupAction', 'AddTag');
            if (isset($_GET['lightbox'])) {
                $interface->assign('title', $_GET['message']);
                $interface->assign('message', 'You must be logged in first');
                return $interface->fetch('AJAX/login.tpl');
            } else {
                $interface->assign('followup', true);
                $interface->setPageTitle('You must be logged in first');
                $interface->assign('subTemplate', '../MyResearch/login.tpl');
                $interface->setTemplate('view-alt.tpl');
                $interface->display('layout.tpl', 'AddTag' . $_GET['id']);
            }
            exit();
        }

        if (isset($_POST['submit'])) {
            $result = $this->save($this->_user);
            header(
                "Location: " . $configArray['Site']['url'] . '/Record/' .
                urlencode($_GET['id']) . '/Home'
            );
        } else {
            return $this->_displayForm();
        }
    }

    /**
     * Support method to display the tag entry form.
     *
     * @return void
     * @access private
     */
    private function _displayForm()
    {
        global $interface;

        // Display Page
        if (isset($_GET['lightbox'])) {
            $interface->assign('title', $_GET['message']);
            return $interface->fetch('Record/addtag.tpl');
        } else {
            $interface->setPageTitle('Add Tag');
            $interface->assign('subTemplate', 'addtag.tpl');
            $interface->setTemplate('view-alt.tpl');
            $interface->display('layout.tpl', 'AddTag' . $_GET['id']);
        }
    }

    /**
     * Save the tag information based on GET parameters.
     *
     * @param object $user User that is adding the tag.
     *
     * @return bool        True on success, false on failure.
     * @access public
     */
    public static function save($user)
    {
        // Fail if we don't know what record we're working with:
        if (!isset($_GET['id'])) {
            return false;
        }

        // Create a resource entry for the current ID if necessary (or find the
        // existing one):
        $resource = new Resource();
        $resource->record_id = $_GET['id'];
        if (!$resource->find(true)) {
            $resource->insert();
        }

        // Parse apart the tags and save them in association with the resource:
        preg_match_all('/"[^"]*"|[^ ]+/', $_REQUEST['tag'], $words);
        foreach ($words[0] as $tag) {
            $tag = str_replace('"', '', $tag);
            $resource->addTag($tag, $user);
        }

        // Done -- report success:
        return true;
    }
}

?>
