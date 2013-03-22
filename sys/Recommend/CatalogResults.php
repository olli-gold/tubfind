<?php
/**
 * CatalogResults Recommendations Module
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
 * CatalogResults Recommendations Module
 *
 * This class provides recommendations by doing a search of the catalog; useful
 * for displaying catalog recommendations in other modules (i.e. Summon, Web, etc.)
 *
 * @category VuFind
 * @package  Recommendations
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_recommendations_module Wiki
 */
class CatalogResults implements RecommendationInterface
{
    private $_searchObject;

    /**
     * Constructor
     *
     * Establishes base settings for making recommendations.
     *
     * @param object $searchObject The SearchObject requesting recommendations.
     * @param string $params       Colon-separated settings from config file.
     *
     * @access public
     */
    public function __construct($searchObject, $params)
    {
        // Parse out parameters:
        $params = explode(':', $params);
        $requestParam = empty($params[0]) ? 'lookfor' : $params[0];
        $limit = isset($params[1]) && is_numeric($params[1]) && $params[1] > 0 ?
            intval($params[1]) : 5;

        // We don't actually care about the passed-in search object; let's just
        // create our own!
        $this->_searchObject = SearchObjectFactory::initSearchObject();

        // Not really a browse, but browse searches are similar in that they
        //   have no facets (until added later) and no spellchecking
        $this->_searchObject->initBrowseScreen();
        $this->_searchObject->disableLogging();
        $this->_searchObject->setBasicQuery($_REQUEST[$requestParam]);
        $this->_searchObject->setLimit($limit);
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

        // Perform the search without throwing fatal errors -- if we get an error,
        // we'll treat it as an empty result and simply skip recommendations.  This
        // should only happen in the case of complex queries.
        $result = $this->_searchObject->processSearch(true);
        $resultDocs = isset($result['response']['docs']) ?
            $result['response']['docs'] : array();
        $this->_searchObject->close();
        $interface->assign('catalogResults', $resultDocs);
        $interface->assign(
            'catalogSearchUrl', $this->_searchObject->renderSearchUrl()
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
        return 'Search/Recommend/CatalogResults.tpl';
    }
}

?>