<?php
/**
 * getvalue Smarty plugin
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
 * Name:     getvalue
 * Purpose:  Prints the subfield data of a MARC_Field object
 * -------------------------------------------------------------
 *
 * @param MARC_Field $marcField MARC field object
 * @param string     $subfield  Subfield to extract
 *
 * @return string               Extracted subfield data
 */ // @codingStandardsIgnoreStart
function smarty_modifier_getvalue($marcField, $subfield)
{   // @codingStandardsIgnoreEnd
    if ($marcField) {
        $subfield = $marcField->getSubfield($subfield);
        if ($subfield) {
            return $subfield->getData();
        } else {
            return null;
        }
    } else {
        return null;
    }
}
?>