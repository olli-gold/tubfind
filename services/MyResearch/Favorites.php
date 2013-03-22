<?php
/**
 * MyResearch Favorites Page Controller
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
require_once 'services/MyResearch/MyResearch.php';
require_once 'services/MyResearch/lib/FavoriteHandler.php';

/**
 * MyResearch Favorites Page Controller
 *
 * @category VuFind
 * @package  Controller_MyResearch
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Favorites extends MyResearch
{
    /**
     * Process incoming parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $configArray;
        global $interface;
        global $user;

        // Delete Resource
        if (isset($_GET['delete'])) {
            $resource = Resource::staticGet('record_id', $_GET['delete']);
            $user->removeResource($resource);
        }

        // Narrow by Tag
        if (isset($_GET['tag'])) {
            $interface->assign('tags', $_GET['tag']);
        }

        // Build Favorites List
        $favorites = $user->getResources(isset($_GET['tag']) ? $_GET['tag'] : null);
        $favList = new FavoriteHandler($favorites, $user);
        $favList->assign();
        if (!$this->infoMsg) {
            $this->infoMsg = $favList->getInfoMsg();
        }

        // Get My Lists
        $listList = $user->getLists();
        $interface->assign('listList', $listList);

        // Get My Tags
        $tagList = $user->getTags();
        $interface->assign('tagList', $tagList);

        // Assign Error & Info Messages
        $interface->assign('infoMsg', $this->infoMsg);
        $interface->assign('errorMsg', $this->errorMsg);
        $interface->assign('showExport', $this->showExport);

        $interface->setPageTitle('Favorites');
        $interface->setTemplate('favorites.tpl');
        $interface->display('layout.tpl');
    }
}

?>
