<?php
/**
 * Results action for Search module
 *
 * PHP version 5
 *
 * Copyright (C) Andrew Nagy 2009
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
 * @package  Controller_Search
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'Action.php';
require_once 'services/MyResearch/lib/User.php';
require_once 'services/MyResearch/lib/Search.php';

require_once 'sys/Pager.php';
require_once 'sys/ResultScroller.php';

require_once 'sys/Recommend/DBRecommender.php';

/**
 * Results action for Search module
 *
 * @category VuFind
 * @package  Controller_Search
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Results extends Action
{
    private $_solrStats = false;

    /**
     * Process incoming parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $interface;
        global $configArray;

        // Set Proxy URL
        if (isset($configArray['EZproxy']['host'])) {
            $interface->assign('proxy', $configArray['EZproxy']['host']);
        }

        $old_shards = $_SESSION['shards'];
        $old_shard = $_REQUEST['shard'];
        unset($_SESSION['shards']);
        unset($_REQUEST['shard']);
        $_REQUEST['shard'][] = 'GBV Central';
        $_REQUEST['shard'][] = 'TUBdok';
        $_REQUEST['shard'][] = 'wwwtub';
        $_REQUEST['shard'][] = 'localbiblio';
        $_SESSION['shards'][] = 'GBV Central';
        $_SESSION['shards'][] = 'TUBdok';
        $_SESSION['shards'][] = 'wwwtub';
        $_SESSION['shards'][] = 'localbiblio';

        // Initialise from the current search globals
        $spellingObject = SearchObjectFactory::initSearchObject();
        $spellingObject->init();
        $spellingObject->processSearch(true, true);

        $interface->assign(
            'spellingSuggestions', $spellingObject->getSpellingSuggestions()
        );

        unset($_SESSION['shards']);
        unset($_REQUEST['shard']);
        $_SESSION['shards'] = $old_shards;
        $_REQUEST['shard'] = $old_shard;

        // Initialise from the current search globals
        $searchObject = SearchObjectFactory::initSearchObject();
        $searchObject->init();

        // Build RSS Feed for Results (if requested)
        if ($searchObject->getView() == 'rss') {
            // Throw the XML to screen
            echo $searchObject->buildRSS();
            // And we're done
            exit();
        }

        // Determine whether to display book previews
        if (isset($configArray['Content']['previews'])) {
            $providers = explode(',', $configArray['Content']['previews']);
            $interface->assign('showPreviews', true);
            foreach ($providers as $provider) {
                switch ($provider) {
                case 'Google':
                    $interface->assign('showGBSPreviews', true);
                    break;
                case 'OpenLibrary':
                    $interface->assign('showOLPreviews', true);
                    break;
                case 'HathiTrust':
                    $interface->assign('showHTPreviews', true);
                    break;
                }
            }
        }

        // Assign active tab
        if (array_key_exists('tab', $_REQUEST)) {
            $interface->assign('tab', $_REQUEST['tab']);
        }

        if (isset($configArray['Tabs']['showTabs']) && $configArray['Tabs']['showTabs'] != '0') {
            $interface->assign('showtabs', true);
        }
        if (isset($configArray['Tabs']['autoreload']) && $configArray['Tabs']['autoreload'] != '0') {
            $rec = file_get_contents("http://www.tub.tu-harburg.de/ext/gbvindex/gbvslow");
            // file_exists('/srv/www/gbvproblem')
            if (substr($rec, 0, 4) == 'slow' && ($_REQUEST['tab'] == 'gbv' || $_REQUEST['tab'] == 'all' || $_REQUEST['tab'] == '' || array_key_exists('tab', $_REQUEST) === false)) {
                $interface->assign('tab', 'nogbvall');
                $interface->assign('gbvmessage', 'GBV Discovery ist derzeit zu langsam. Sie werden daher auf den lokalen Katalog umgeleitet. Aufs&auml;tze und Artikel k&ouml;nnen daher momentan nicht gefunden werden!');
            }
        }

        // TODO : Stats, move inside the search object
        // Setup Statistics Index Connection
        if ($configArray['Statistics']['enabled']) {
            $this->_solrStats = ConnectionManager::connectToIndex('SolrStats');
        }

        // Set Interface Variables
        //   Those we can construct BEFORE the search is executed
        $displayQuery = $searchObject->displayQuery();
        $interface->setPageTitle(
            translate('Search Results') .
            (empty($displayQuery) ? '' : ' - ' . htmlspecialchars($displayQuery))
        );
        $interface->assign('sortList',   $searchObject->getSortList());
        $interface->assign('viewList',   $searchObject->getViewList());
        $interface->assign('rssLink',    $searchObject->getRSSUrl());
        $interface->assign('limitList',  $searchObject->getLimitList());
        // Process Search
        $result = $searchObject->processSearch(true, true);
        if (PEAR::isError($result)) {
            PEAR::raiseError($result->getMessage());
        }

        // Some more variables
        //   Those we can construct AFTER the search is executed, but we need
        //   no matter whether there were any results
        $interface->assign('qtime', round($searchObject->getQuerySpeed(), 2));

        $interface->assign('lookfor', $displayQuery);
        $interface->assign('searchType', $searchObject->getSearchType());
        // Will assign null for an advanced search
        $interface->assign('searchIndex', $searchObject->getSearchIndex());

        // We'll need recommendations no matter how many results we found:
        $interface->assign(
            'topRecommendations', $searchObject->getRecommendationsTemplates('top')
        );
        $interface->assign(
            'sideRecommendations', $searchObject->getRecommendationsTemplates('side')
        );

        if (isset($configArray['DBR']['enabled']) && $configArray['DBR']['enabled'] == true) {
            $interface->assign('dbrenabled', 1);
        }

        if ($searchObject->getResultTotal() < 1) {
            // No record found
            $interface->setTemplate('list-none.tpl');
            $interface->assign('recordCount', 0);

            // Was the empty result set due to an error?
            $error = $searchObject->getIndexError();
            if ($error !== false) {
                // If it's a parse error or the user specified an invalid field, we
                // should display an appropriate message:
                if (stristr($error, 'org.apache.lucene.queryParser.ParseException')
                    || preg_match('/^undefined field/', $error)
                ) {
                    $interface->assign('parseError', true);
                } else {
                    // Unexpected error -- let's treat this as a fatal condition.
                    PEAR::raiseError(
                        new PEAR_Error(
                            'Unable to process query<br />Solr Returned: ' . $error
                        )
                    );
                }
            }

            // TODO : Stats, move inside the search object
            // Save no records found stat
            if ($this->_solrStats) {
                $this->_solrStats->saveNoHits($_GET['lookfor'], $_GET['type']);
            }
        } else {
            // TODO : Stats, move inside the search object
            // Save search stat
            if ($this->_solrStats) {
                $this->_solrStats->saveSearch($_GET['lookfor'], $_GET['type']);
            }

            // If the "jumpto" parameter is set, jump to the specified result index:
            $this->_processJumpto($result);

            // Assign interface variables
            $summary = $searchObject->getResultSummary();
            $interface->assign('recordCount', $summary['resultTotal']);
            $interface->assign('recordStart', $summary['startRecord']);
            $interface->assign('recordEnd',   $summary['endRecord']);

            // Big one - our results
            $interface->assign('recordSet', $searchObject->getResultRecordHTML());

            // Setup Display

            //Get view & load template
            $currentView  = $searchObject->getView();
            $interface->assign('subpage', 'Search/list-' . $currentView .'.tpl');
            $interface->setTemplate('list.tpl');

            // Process Paging
            $link = $searchObject->renderLinkPageTemplate();
            if ($summary['resultTotal'] > 200 && in_array('Primo Central', $_SESSION['shards']) === true) $summary['resultTotal'] = 200;
            $options = array('totalItems' => $summary['resultTotal'],
                             'fileName'   => $link,
                             'perPage'    => $summary['perPage']);
            $pager = new VuFindPager($options);
            $interface->assign('pageLinks', $pager->getLinks());
        }

        // 'Finish' the search... complete timers and log search history.
        $searchObject->close();
        $interface->assign('time', round($searchObject->getTotalSpeed(), 2));
        // Show the save/unsave code on screen
        // The ID won't exist until after the search has been put in the search
        //    history so this needs to occur after the close() on the searchObject
        $interface->assign('showSaved',   true);
        $interface->assign('savedSearch', $searchObject->isSavedSearch());
        $interface->assign('searchId',    $searchObject->getSearchId());

        // Save the URL of this search to the session so we can return to it easily:
        $_SESSION['lastSearchURL'] = $searchObject->renderSearchUrl();

        // initialize the search result scroller for this search
        $scroller = new ResultScroller();
        $scroller->init($searchObject, $result);

        // Done, display the page
        $interface->display('layout.tpl');
    } // End launch()

    /**
     * Process the "jumpto" parameter.
     *
     * @param array $result Solr result returned by SearchObject
     *
     * @return void
     * @access private
     */
    private function _processJumpto($result)
    {
        if (isset($_REQUEST['jumpto']) && is_numeric($_REQUEST['jumpto'])) {
            $i = intval($_REQUEST['jumpto'] - 1);
            if (isset($result['response']['docs'][$i])) {
                $record = RecordDriverFactory::initRecordDriver(
                    $result['response']['docs'][$i]
                );
                $jumpUrl = '../Record/' . urlencode($record->getUniqueID());
                header('Location: ' . $jumpUrl);
                die();
            }
        }
    }

}

?>
