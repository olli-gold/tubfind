<?php
/**
 * RecommendationsFactory Class
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2009.
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
 * @package  Recommendations
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_recommendations_module Wiki
 */

/**
 * RecommendationFactory Class
 *
 * This is a factory class to build recommendation modules for use in searches.
 *
 * @category VuFind
 * @package  Recommendations
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_recommendations_module Wiki
 */
class RecommendationFactory
{
    /**
     * initRecommendation
     *
     * This constructs a recommendation module object.
     *
     * @param string $module    The name of the recommendation module to build
     * @param object $searchObj The SearchObject using the recommendations.
     * @param string $params    Configuration string to send to the constructor
     *
     * @return mixed            The $module object on success, false otherwise
     * @access public
     */
    static function initRecommendation($module, $searchObj, $params)
    {
        global $configArray;
        $path = "{$configArray['Site']['local']}/sys/Recommend/{$module}.php";
        if (is_readable($path)) {
            include_once $path;
            if (class_exists($module)) {
                $recommend = new $module($searchObj, $params);
                return $recommend;
            }
        }
        
        return false;
    }
}
?>