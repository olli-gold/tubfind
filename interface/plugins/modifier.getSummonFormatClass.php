<?php
/**
 * getSummonFormatClass Smarty plugin
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
 * Name:     getSummonFormatClass
 * Purpose:  Get a class to display an icon for a Summon format
 * -------------------------------------------------------------
 *
 * @param string $format Format string provided by Summon API
 *
 * @return string        Format string suitable for VuFind display
 */ // @codingStandardsIgnoreStart
function smarty_modifier_getSummonFormatClass($format)
{   // @codingStandardsIgnoreEnd
    switch ($format) {
    case 'Audio Recording':
        return 'audio';
    case 'Book':
    case 'Book Chapter':
        return 'book';
    case 'Computer File':
    case 'Web Resource':
        return 'electronic';
    case 'Dissertation':
    case 'Manuscript':
    case 'Paper':
    case 'Patent':
        return 'manuscript';
    case 'eBook':
        return 'ebook';
    case 'Kit':
        return 'kit';
    case 'Image':
    case 'Photograph':
        return 'photo';
    case 'Music Score':
        return 'musicalscore';
    case 'Newspaper Article':
        return 'newspaper';
    case 'Video Recording':
        return 'video';
    default:
        return 'journal';
    }
}
?>