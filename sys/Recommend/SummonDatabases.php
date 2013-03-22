<?php
/**
 * SummonDatabases Recommendations Module
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

require_once 'sys/Recommend/Interface.php';

/**
 * SummonDatabases Recommendations Module
 *
 * This class provides recommendations by doing a search of Summon.
 *
 * @category VuFind
 * @package  Recommendations
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_recommendations_module Wiki
 */
class SummonDatabases implements RecommendationInterface
{
    private $_searchObject;
    
    /**
     * Constructor
     *
     * Establishes base settings for making recommendations.
     *
     * @param object $searchObject The SearchObject requesting recommendations.
     * @param string $requestParam $_REQUEST field containing search terms (ignored
     * if $searchObject is Summon type).
     *
     * @access public
     */
    public function __construct($searchObject, $requestParam)
    {
        // If we received a Summon search object, we'll use that.  If not, we need
        // to create a new Summon search object using the specified REQUEST 
        // parameter for search terms.
        if (strtolower(get_class($searchObject)) == 'searchobject_summon') {
            $this->_searchObject = $searchObject;
        } else {
            $this->_searchObject = SearchObjectFactory::initSearchObject('Summon');
            $this->_searchObject->disableLogging();
            $this->_searchObject->setBasicQuery($_REQUEST[$requestParam]);
            $this->_searchObject->processSearch(true);
        }
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
        
        $interface->assign(
            'summonDatabases', $this->_searchObject->getDatabaseRecommendations()
        );
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
        return 'Search/Recommend/SummonDatabases.tpl';
    }
}

?>