<?php
/**
 * Home action for Summon module
 *
 * PHP version 5
 *
 * Copyright (C) Andrew Nagy 2009.
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
 * @package  Controller_Summon
 * @author   Andrew Nagy <vufind-tech@lists.sourceforge.net>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'Base.php';

/**
 * Home action for Summon module
 *
 * @category VuFind
 * @package  Controller_Summon
 * @author   Andrew Nagy <vufind-tech@lists.sourceforge.net>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Home extends Base
{
    /**
     * Display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $interface;
        global $configArray;

        // Cache homepage
        $interface->caching = 1;
        $cacheId = 'summon-homepage|' . $interface->lang . '|' .
            (UserAccount::isLoggedIn() ? '1' : '0') . '|' .
            (isset($_SESSION['lastUserLimit']) ? $_SESSION['lastUserLimit'] : '') .
            '|' .
            (isset($_SESSION['lastUserSort']) ? $_SESSION['lastUserSort'] : '');
        if (!$interface->is_cached('layout.tpl', $cacheId)) {
            $interface->setPageTitle('Search Home');
            $interface->setTemplate('home.tpl');

            // Search Summon
            $summon = new Summon(
                $configArray['Summon']['apiId'], $configArray['Summon']['apiKey']
            );
            $results = $summon->query(
                '', null, null, 0, null,
                array('ContentType,or,1,20', 'Language,or,1,20')
            );

            // Summon may not return facet values in a predictable order -- process
            // them to ensure we display the right thing in the right place:
            $facets = array();
            foreach ($results['facetFields'] as $current) {
                $facets[$current['displayName']] = $current;
            }

            $interface->assign('formatList', $facets['ContentType']);
            $interface->assign('languageList', $facets['Language']);
        }
        $interface->display('layout.tpl', $cacheId);
    }

}

?>
