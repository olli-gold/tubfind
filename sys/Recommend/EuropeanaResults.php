<?php
/**
 * Search Recommendations From RSS (Europeana)
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2011.
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
 * @author   Lutz Biedinger <lutz.biedigner@gmail.com>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_recommendations_module Wiki
 */

require_once 'sys/RSSUtils.php';
require_once 'sys/Recommend/Interface.php';

/**
 * Search Recommendations From RSS (Europeana)
 *
 * This class provides recommendations by doing a search of another VuFind
 * instance catalog; useful for displaying catalog recommendations in other
 * modules (i.e. Summon, Web, etc.)
 *
 * @category VuFind
 * @package  Recommendations
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Lutz Biedinger <lutz.biedigner@gmail.com>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_recommendations_module Wiki
 */

class EuropeanaResults implements RecommendationInterface
{
    private $_requestParam;
    private $_limit;
    private $_targetUrl;
    private $_excludeProviders;
    private $_searchSite;
    private $_sitePath;
    private $_key;
    private $_lookfor;

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
        global $configArray;

        // Collect the best possible search term(s):
        $this->_lookfor = isset($_REQUEST['lookfor'])
            ? $_REQUEST['lookfor'] : '';
        if (empty($this->_lookfor) && is_object($searchObject)) {
            $this->_lookfor = $searchObject->extractAdvancedTerms();
        }
        $this->_lookfor = urlencode(trim($this->_lookfor));

        // Parse out parameters:
        $params = explode(':', $params);
        $baseUrl = (isset($params[0]) && !empty($params[0]))
            ? $params[0] : 'api.europeana.eu/api/opensearch.rss';
        $this->_requestParam = (isset($params[1]) && !empty($params[1]))
            ? $params[1] : 'searchTerms';
        $this->_limit = isset($params[2]) && is_numeric($params[2])
                        && $params[2] > 0 ? intval($params[2]) : 5;
        $this->_excludeProviders = (isset($params[3]) && !empty($params[3]))
            ? $params[3] : '';
        //make array
        $this->_excludeProviders = explode(',', $this->_excludeProviders);

        //get the key from config.ini
        $this->_key = $configArray['Content']['europeanaAPI'];
        $this->_searchSite = "Europeana.eu";
        
        $this->_targetUrl = $this->_getURL(
            'http://' . $baseUrl, $this->_requestParam, $this->_excludeProviders
        );
        $this->_sitePath = 'http://www.europeana.eu/portal/search.html?query=' .
            $this->_lookfor;
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

        //empty array to hold the results
        $results = array();
        $resultsProccessed = array();

        //declare namespace uri
        $ns_array = array(0 => 'http://www.europeana.eu');

        $rss = new RSSUtils();

        if (!empty($this->_lookfor)) {
            $results = $rss->getResults($this->_targetUrl, $this->_limit, $ns_array);
        }
        $i = 0;
        //loop through the results, break if the limit has been reached
        foreach ($results as $item) {
            if ($item['title'] != 'Error') {
                $resultsProccessed[$i] = $item;
                $resultsProccessed[$i]['link']
                    = substr(
                        $resultsProccessed[$i]['link'], 0,
                        strpos($resultsProccessed[$i]['link'], '.srw')
                    ) . '.html' ;
                $i++;
                if ($i > $this->_limit) {
                    break;
                }
            }
        }
        if (!empty($resultsProccessed)) {
            // got some valid results, set smarty variables for output
            $interface->assign('validData', true);
            $interface->assign('worksArray', $resultsProccessed);
            $interface->assign('feedTitle', $this->_searchSite);
            $interface->assign('sourceLink', $this->_sitePath);
        }
    }

    /**
     * getURL
     *
     * This method builds the url which will be send to retrieve the RSS results
     *
     * @param string $targetUrl        Base URL
     * @param string $requestParam     Parameter name to add
     * @param array  $excludeProviders An array of providers to exclude when
     * getting results.
     *
     * @return string The url to be sent
     * @access private
     */
    private function _getURL($targetUrl, $requestParam, $excludeProviders)
    {
        // build url
        $url = $targetUrl . "?" . $requestParam . "=" . $this->_lookfor;
        //add providers to ignore
        foreach ($excludeProviders as $provider) {
            $provider = trim($provider);
            if (!empty($provider)) {
                $url .= urlencode(' NOT europeana_dataProvider:"' . $provider . '"');
            }
        }
        $url .= '&wskey=' . urlencode($this->_key);

        //return complete url
        return $url;
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
        return 'Search/Recommend/EuropeanaResults.tpl';
    }
}

?>
