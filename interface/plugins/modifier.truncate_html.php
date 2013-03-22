<?php
/**
 * truncate_html Smarty plugin
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
 * Name:     truncate_html
 * Purpose:  Tag-aware variant of standard truncate modifier
 * Note:     Adapted from substrws() function found at:
 *                http://php.net/manual/en/function.substr.php
 * -------------------------------------------------------------
 *
 * @param string $text   Base text to modify.
 * @param int    $len    Target length for result string.
 * @param string $suffix Text to append to final string.
 *
 * @return string        Truncated valid HTML.
 */ // @codingStandardsIgnoreStart
function smarty_modifier_truncate_html($text, $len, $suffix = '')
{   // @codingStandardsIgnoreEnd
    if ((strlen($text) > $len)) {
        $whitespaceposition = strpos($text, " ", $len) - 1;

        if ($whitespaceposition > 0) {
            $text = substr($text, 0, ($whitespaceposition + 1));
        }

        // strip trailing partial tags
        $text = preg_replace('/<[^>]*$/Um', '', $text);

        // close unclosed html tags
        if (preg_match_all("|<([a-zA-Z]+)[^>]*>|", $text, $aBuffer)) {
            $openers = array();
            if (!empty($aBuffer[1])) {
                $selfClosing = array('br', 'img');
                foreach ($aBuffer[1] as $current) {
                    if (!in_array(strtolower($current), $selfClosing)) {
                        $openers[] = $current;
                    }
                }
            }
            if (!empty($openers)) {
                preg_match_all("|</([a-zA-Z]+)>|", $text, $aBuffer2);
                if (count($openers) != count($aBuffer2[1])) {
                    foreach ($openers as $index => $tag) {
                        if (empty($aBuffer2[1][$index])
                            || $aBuffer2[1][$index] != $tag
                        ) {
                            $text .= '</'.$tag.'>';
                        }
                    }
                }
            }
        }
        $text .= $suffix;
    }

    return $text;
}
?>