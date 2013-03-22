<?php
/**
 * SearchObject Factory Class
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
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
 * @package  SearchObject
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_search_object Wiki
 */

/**
 * SearchObjectFactory Class
 *
 * This is a factory class to build objects for managing searches.
 *
 * @category VuFind
 * @package  SearchObject
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_search_object Wiki
 */
class SearchObjectFactory
{
    /**
     * initSearchObject
     *
     * This constructs a search object for the specified engine.
     *
     * @param string $engine The type of SearchObject to build (Solr/Summon).
     *
     * @return mixed         The search object on success, false otherwise
     * @access public
     */
    static function initSearchObject($engine = 'Solr')
    {
        $path = dirname(__FILE__) . "/{$engine}.php";
        if (is_readable($path)) {
            include_once $path;
            $class = 'SearchObject_' . $engine;
            if (class_exists($class)) {
                $recommend = new $class();
                return $recommend;
            }
        }

        return false;
    }

    /**
     * deminify
     *
     * Construct an appropriate Search Object from a MinSO object.
     *
     * @param object $minSO The MinSO object to use as the base.
     *
     * @return mixed        The search object on success, false otherwise
     * @access public
     */
    static function deminify($minSO)
    {
        // To avoid excessive constructor calls, we'll keep a static cache of
        // objects to use for the deminification process:
        static $objectCache = array();

        // Figure out the engine type for the object we're about to construct:
        switch($minSO->ty) {
        case 'Summon':
        case 'SummonAdvanced':
            $type = 'Summon';
            break;
        case 'WorldCat':
        case 'WorldCatAdvanced':
            $type = 'WorldCat';
            break;
        case 'Authority':
        case 'AuthorityAdvanced':
            $type = 'SolrAuth';
            break;
        default:
            $type = 'Solr';
            break;
        }

        // Construct a new object if we don't already have one:
        if (!isset($objectCache[$type])) {
            $objectCache[$type] = self::initSearchObject($type);
        }

        // Populate and return the deminified object:
        $objectCache[$type]->deminify($minSO);
        return $objectCache[$type];
    }
}
?>