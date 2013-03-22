<?php
/**
 * css function Smarty plugin
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
 * File:     function.css.php
 * Type:     function
 * Name:     css
 * Purpose:  Loads a CSS file from the appropriate theme 
 *           directory.  Supports two parameters: 
 *              filename (required) - file to load from
 *                  interface/themes/[theme]/css/ folder.
 *              media (optional) - media attribute to
 *                  pass into <link> tag.
 * -------------------------------------------------------------
 *
 * @param array  $params  Incoming parameter array
 * @param object &$smarty Smarty object
 *
 * @return string        <link> tag for including CSS
 */ // @codingStandardsIgnoreStart
function smarty_function_css($params, &$smarty)
{   // @codingStandardsIgnoreEnd
    // Extract details from the config file, Smarty interface and parameters
    // so we can find CSS files:
    global $configArray;

    $path = $configArray['Site']['path'];
    $local = $configArray['Site']['local'];
    $themes = explode(',', $smarty->getVuFindTheme());
    $filename = $params['filename'];

    // Loop through the available themes looking for the requested CSS file:
    $css = false;
    foreach ($themes as $theme) {
        $theme = trim($theme);
        
        // If the file exists on the local file system, set $css to the relative
        // path needed to link to it from the web interface.
        if (file_exists("{$local}/interface/themes/{$theme}/css/{$filename}")) {
            $css = "{$path}/interface/themes/{$theme}/css/{$filename}";
            break;
        }
    }

    // If we couldn't find the file, we shouldn't try to link to it:
    if (!$css) {
        return '';
    }

    // We found the file -- build the link tag:        
    $media = isset($params['media']) ? " media=\"{$params['media']}\"" : '';
    return "<link rel=\"stylesheet\" type=\"text/css\"{$media} href=\"{$css}\" />";
}
?>