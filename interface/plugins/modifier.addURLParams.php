<?php
/**
 * addURLParams Smarty plugin
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
 * @package  Smarty_Plugins
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_plugin Wiki
 */

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     addURLParams
 * Purpose:  Add parameters to a URL with GET parameters.
 * -------------------------------------------------------------
 *
 * @param string $url           Base URL, which may or may not already have params
 * @param string $params_to_add URL-formatted string of parameters to add
 *
 * @return string               URL with new parameters appended
 */ // @codingStandardsIgnoreStart
function smarty_modifier_addURLParams($url, $params_to_add)
{   // @codingStandardsIgnoreEnd
    // Break the base URL from the parameters:
    list($base, $params) = explode('?', $url);
    
    // Loop through the parameters and filter out the unwanted one:
    $parts = explode('&', $params);
    $params = array();
    foreach ($parts as $param) {
        if (!empty($param)) {
            $params[] = $param;
        }
    }
    $extra_params = explode('&', $params_to_add);
    foreach ($extra_params as $current_param) {
        if (!empty($current_param)) {
            $params[] = $current_param;
        }
    }

    // Reassemble the URL with the added parameter(s):
    return $base . '?' . implode('&', $params);
}
?>