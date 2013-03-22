<?php
/**
 * Home action for Admin module.
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
 * @package  Controller_Admin
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'Admin.php';

require_once 'XML/Unserializer.php';

/**
 * Home action for Admin module.
 *
 * @category VuFind
 * @package  Controller_Admin
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Home extends Admin
{
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

        // Load SOLR Statistics
        if ($configArray['Index']['engine'] == 'Solr') {
            $xml = @file_get_contents(
                $configArray['Index']['url'] . '/admin/multicore'
            );

            if ($xml) {
                $options = array('parseAttributes' => 'true',
                                 'keyAttribute' => 'name');
                $unxml = new XML_Unserializer($options);
                $unxml->unserialize($xml);
                $data = $unxml->getUnserializedData();
                $interface->assign('data', $data['status']);
            }
        }

        $interface->setTemplate('home.tpl');
        $interface->setPageTitle('Home');
        $interface->display('layout-admin.tpl');
    }
}

?>