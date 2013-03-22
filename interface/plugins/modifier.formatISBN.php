<?php
/**
 * formatISBN Smarty plugin
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
require_once 'sys/ISBN.php';

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     formatISBN
 * Purpose:  Formats an ISBN number
 * -------------------------------------------------------------
 *
 * @param string $isbn Raw ISBN number
 *
 * @return string      Normalized ISBN, 10-digit if possible, 13-digit if necessary.
 */ // @codingStandardsIgnoreStart
function smarty_modifier_formatISBN($isbn)
{   // @codingStandardsIgnoreEnd
    // Normalize ISBN to an array if it is not already.
    $isbns = is_array($isbn) ? $isbn : array($isbn);

    // Loop through the ISBNs, trying to find an ISBN-10 if possible, and returning
    // the first ISBN-13 encountered as a last resort:
    $isbn13 = false;
    foreach ($isbns as $isbn) {
        // Strip off any unwanted notes:
        if ($pos = strpos($isbn, ' ')) {
            $isbn = substr($isbn, 0, $pos);
        }

        // If we find an ISBN-10, return it immediately; otherwise, if we find
        // an ISBN-13, save it if it is the first one encountered.
        $isbnObj = new ISBN($isbn);
        if ($isbn10 = $isbnObj->get10()) {
            return $isbn10;
        }
        if (!$isbn13) {
            $isbn13 = $isbnObj->get13();
        }
    }
    return $isbn13;
}
?>