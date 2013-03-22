<?php
/**
 * Home action for MyResearch module
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

/**
 * MyResearch Home Page Controller
 *
 * @category VuFind
 * @package  Controller_MyResearch
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Home extends MyResearch
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

        // Do we need to send the user to a specific follow-up URL?
        if (isset($_REQUEST['followup'])) {
            $followupUrl
                =  $configArray['Site']['url'] . "/". $_REQUEST['followupModule'];
            if (!empty($_REQUEST['recordId'])) {
                $followupUrl .= "/" . $_REQUEST['recordId'];
            }
            $followupUrl .= "/" . $_REQUEST['followupAction'];
            if (isset($_REQUEST['comment'])) {
                $followupUrl .= "?comment=" . urlencode($_REQUEST['comment']);
            }
            if (isset($_REQUEST['extraParams'])
                && is_array($_REQUEST['extraParams'])
            ) {
                $parts = array();
                foreach ($_REQUEST['extraParams'] as $param) {
                    $pair = explode("|", $param, 2);
                    $parts[] = urlencode($pair[0]) . "=". urlencode($pair[1]);
                }
                $followupUrl .= "?" . implode('&', $parts);
            }
            header("Location: " . $followupUrl);
        } else {
            // No follow-up URL; choose the default:
            $page = isset($configArray['Site']['defaultAccountPage']) ?
                $configArray['Site']['defaultAccountPage'] : 'Favorites';
            $accountStart = $configArray['Site']['url'] . "/MyResearch/". $page;
            header("Location: " . $accountStart);
        }
    }
}

?>
