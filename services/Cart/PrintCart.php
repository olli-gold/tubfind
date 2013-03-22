<?php
/**
 * Print action for Bulk module
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

/**
 * Print action for Bulk module
 *
 * @category VuFind
 * @package  Controller_Cart
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @author   Luke O'Sullivan <l.osullivan@swansea.ac.uk>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class PrintCart extends Bulk
{
    /**
     * Process parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $interface;
        global $configArray;

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

        $this->followupUrl = $configArray['Site']['url'] .
            "/Search/Results?lookfor=" . urlencode(implode(" ", $ids)) .
            "&type=ids&print=true";
        header("Location: " . $this->followupUrl);
        exit();
    }
}
?>
