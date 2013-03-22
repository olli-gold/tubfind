<?php
/**
 * WorldCatTerms Recommendations Module
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
 * @package  Recommendations
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_recommendations_module Wiki
 */
require_once 'sys/WorldCatUtils.php';
require_once 'sys/Recommend/Interface.php';

/**
 * WorldCatTerms Recommendations Module
 *
 * This class provides recommendations by using the WorldCat Terminologies API.
 *
 * @category VuFind
 * @package  Recommendations
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_recommendations_module Wiki
 */
class WorldCatTerms implements RecommendationInterface
{
    private $_searchObject;
    private $_vocab;

    /**
     * Constructor
     *
     * Establishes base settings for making recommendations.
     *
     * @param object $searchObject The SearchObject requesting recommendations.
     * @param string $params       Additional settings from searches.ini.
     *
     * @access public
     */
    public function __construct($searchObject, $params)
    {
        // Save the search object:
        $this->_searchObject = $searchObject;

        // Pick a vocabulary (either user-specified, or LCSH by default):
        $params = trim($params);
        $this->_vocab = empty($params) ? 'lcsh' : $params;
    }

    /**
     * init
     *
     * Called before the SearchObject performs its main search.  This may be used
     * to set SearchObject parameters in order to generate recommendations as part
     * of the search.
     *
     * @return void
     * @access public
     */
    public function init()
    {
        // No action needed here.
    }

    /**
     * process
     *
     * Called after the SearchObject has performed its main search.  This may be
     * used to extract necessary information from the SearchObject or to perform
     * completely unrelated processing.
     *
     * @return void
     * @access public
     */
    public function process()
    {
        global $interface;

        // Extract the first search term from the search object:
        $search = $this->_searchObject->getSearchTerms();
        $lookfor = isset($search[0]['lookfor']) ? $search[0]['lookfor'] : '';

        // Get terminology information:
        $wc = new WorldCatUtils();
        $terms = $wc->getRelatedTerms($lookfor, $this->_vocab);

        // Wipe out any empty or unexpected sections of the related terms array;
        // this will make it easier to only display content in the template if
        // we have something worth displaying.
        if (is_array($terms)) {
            $desiredKeys = array('exact', 'broader', 'narrower');
            foreach ($terms as $key => $value) {
                if (empty($value) || !in_array($key, $desiredKeys)) {
                    unset($terms[$key]);
                }
            }
        }
        $interface->assign('WorldCatTerms', $terms);
    }

    /**
     * getTemplate
     *
     * This method provides a template name so that recommendations can be displayed
     * to the end user.  It is the responsibility of the process() method to
     * populate all necessary template variables.
     *
     * @return string The template to use to display the recommendations.
     * @access public
     */
    public function getTemplate()
    {
        return 'Search/Recommend/WorldCatTerms.tpl';
    }
}

?>