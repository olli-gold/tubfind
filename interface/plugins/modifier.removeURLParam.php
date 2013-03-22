<?php
/**
 * removeURLParam Smarty plugin
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
 * Name:     removeURLParam
 * Purpose:  Remove a parameter from a URL with GET parameters.
 * -------------------------------------------------------------
 *
 * @param string $url             URL to modify
 * @param string $param_to_remove Parameter name to remove
 *
 * @return string                 URL with parameter removed
 */ // @codingStandardsIgnoreStart
function smarty_modifier_removeURLParam($url, $param_to_remove)
{   // @codingStandardsIgnoreEnd
    // Break the base URL from the parameters:
    list($base, $params) = explode('?', $url);
    
    // Loop through the parameters and filter out the unwanted one:
    $params = explode('&', $params);
    $filtered_params = array();
    foreach ($params as $current_param) {
        list($name, $value) = explode('=', $current_param);
        if ($name != $param_to_remove) {
            $filtered_params[] = $current_param;
        }
    }

    // Reassemble the URL minus the unwanted parameter:
    return $base . '?' . implode('&', $filtered_params);
}
?>