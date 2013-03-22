<?php
/**
 * Reserves action for Search module
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
 * @package  Controller_Search
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'Action.php';

require_once 'sys/Pager.php';

/**
 * Reserves action for Search module
 *
 * @category VuFind
 * @package  Controller_Search
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Reserves extends Action
{
    protected $catalog;
    protected $useReservesIndex;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
        global $configArray;

        $this->useReservesIndex
            = isset($configArray['Reserves']['search_enabled'])
            && $configArray['Reserves']['search_enabled'];

        // connect to the ILS if not using course reserves Solr index
        if (!$this->useReservesIndex) {
            $catalog = ConnectionManager::connectToCatalog();
            if (!$catalog || !$catalog->status) {
                PEAR::raiseError(new PEAR_Error('Cannot Load Catalog Driver'));
            }
            $this->catalog = $catalog;
        }
    }

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

        if (isset($_GET['inst']) || isset($_GET['course']) || isset($_GET['dept'])) {
            // Initialise from the current search globals
            $searchObject = SearchObjectFactory::initSearchObject();
            $searchObject->init();

            // Must have atleast Action and Module set to continue
            $interface->setPageTitle('Reserves Search Results');
            $interface->setTemplate('reserves-list.tpl');
            //Get view & load template
            $currentView  = $searchObject->getView();
            $interface->assign('subpage', 'Search/list-' . $currentView .'.tpl');
            $interface->assign('viewList',   $searchObject->getViewList());
            $interface->assign('sortList', $searchObject->getSortList());
            $interface->assign('limitList', $searchObject->getLimitList());
            $interface->assign('rssLink', $searchObject->getRSSUrl());

            // Get reserve info from the catalog or solr reserves index
            // and catch any fatal errors:
            $result = $this->findReserves();
            if (PEAR::isError($result)) {
                PEAR::raiseError($result);
            }

            // Perform a Solr query to get details on the reserve items, assuming
            // we found at least one.
            if (count($result) > 0) {
                if (isset($result[0]['instructor'])) {
                    $interface->assign('instructor', $result[0]['instructor']);
                }
                if (isset($result[0]['course'])) {
                    $interface->assign('course', $result[0]['course']);
                }
                $bibIDs = array();
                foreach ($result as $record) {
                    // Avoid duplicate IDs (necessary for Voyager ILS driver):
                    if (!in_array($record['BIB_ID'], $bibIDs)) {
                        $bibIDs[] = $record['BIB_ID'];
                    }
                }
                if (!$searchObject->setQueryIDs($bibIDs)) {
                    $interface->assign('infoMsg', 'too_many_reserves');
                }

                // Build RSS Feed for Results (if requested)
                if ($searchObject->getView() == 'rss') {
                    // Throw the XML to screen
                    echo $searchObject->buildRSS();
                    // And we're done
                    exit();
                }

                // Process Search
                $result = $searchObject->processSearch(false, true);
                if (PEAR::isError($result)) {
                    PEAR::raiseError($result->getMessage());
                }

                // Store recommendations (facets, etc.)
                $interface->assign(
                    'topRecommendations',
                    $searchObject->getRecommendationsTemplates('top')
                );
                $interface->assign(
                    'sideRecommendations',
                    $searchObject->getRecommendationsTemplates('side')
                );
            } else if ($searchObject->getView() == 'rss') {
                // Special case -- empty RSS feed...

                // Throw the XML to screen
                echo $searchObject->buildRSS(
                    array(
                        'response' => array('numFound' => 0),
                        'responseHeader' => array('params' => array('rows' => 0)),
                    )
                );
                // And we're done
                exit();
            }

            $interface->assign('recordSet', $searchObject->getResultRecordHTML());
            $summary = $searchObject->getResultSummary();
            $interface->assign('recordCount', $summary['resultTotal']);
            $interface->assign('recordStart', $summary['startRecord']);
            $interface->assign('recordEnd',   $summary['endRecord']);

            $link = $searchObject->renderLinkPageTemplate();
            $total = isset($result['response']['numFound']) ?
                $result['response']['numFound'] : 0;
            $options = array('totalItems' => $total,
                             'perPage' => $summary['perPage'],
                             'fileName' => $link);
            $pager = new VuFindPager($options);
            $interface->assign('pageLinks', $pager->getLinks());

            // Save the URL of this search to the session so we can return to it
            // easily:
            $_SESSION['lastSearchURL'] = $searchObject->renderSearchUrl();
        } else {
            $interface->setPageTitle('Reserves Search');
            if ($this->useReservesIndex) {
                // Keep filter control interferes with this screen:
                $interface->assign('disableKeepFilterControl', true);
                $this->searchReservesIndex();
            } else {
                if ($this->catalog->status) {
                    $interface->assign('deptList', $this->catalog->getDepartments());
                    $interface->assign('instList', $this->catalog->getInstructors());
                    $interface->assign('courseList', $this->catalog->getCourses());
                }
            }
            $interface->setTemplate('reserves.tpl');
        }
        $interface->assign('useReservesIndex', $this->useReservesIndex);
        $interface->display('layout.tpl');
    }

    /**
     * Get reserve info from the catalog or solr reserves index.
     *
     * @return array    array of bib record ids or PEAR error object if error
     * occurred.
     * @access protected
     */
    protected function findReserves()
    {
        if ($this->useReservesIndex) {
            // connect to reserves index
            $reservesIndex = ConnectionManager::connectToIndex('SolrReserves');
            // get the selected reserve record from reserves index
            // and extract the bib IDs from it
            $result = $reservesIndex->findReserves(
                $_GET['course'], $_GET['inst'], $_GET['dept']
            );
            if (!PEAR::isError($result)) {
                $bibs = array();
                $instructor = $result['instructor'];
                $course = $result['course'];
                foreach ($result['bib_id'] as $bib_id) {
                    $bibs[] = array(
                        'BIB_ID' => $bib_id,
                        'bib_id' => $bib_id,
                        'course' => $course,
                        'instructor' => $instructor
                    );
                }
                $result = $bibs;
            }
        } else {
            // Find reserves info from the catalog
            $result = $this->catalog->findReserves(
                $_GET['course'], $_GET['inst'], $_GET['dept']
            );
        }
        return $result;
    }

    /**
     * Search course reserves index and display a list of matches.
     *
     * @return void
     * @access protected
     */
    protected function searchReservesIndex()
    {
        global $interface;

        $searchObject = SearchObjectFactory::initSearchObject('SolrReserves');
        $searchObject->init();
        // Process Search
        $result = $searchObject->processSearch(true, true);
        if (PEAR::isError($result)) {
            PEAR::raiseError($result->getMessage());
        }
        $interface->assign('qtime', round($searchObject->getQuerySpeed(), 2));
        $interface->assign(
            'spellingSuggestions', $searchObject->getSpellingSuggestions()
        );
        $interface->assign(
            'sideRecommendations',
            $searchObject->getRecommendationsTemplates('side')
        );
        $interface->assign('reservesLookfor', $searchObject->displayQuery());
        $interface->assign('searchType', $searchObject->getSearchType());
        $interface->assign('sortList',   $searchObject->getSortList());

        if ($searchObject->getResultTotal() < 1) {
            // No record found
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
        } else {
            $interface->assign('recordSet', $result['response']['docs']);
            $summary = $searchObject->getResultSummary();
            $interface->assign('recordCount', $summary['resultTotal']);
            $interface->assign('recordStart', $summary['startRecord']);
            $interface->assign('recordEnd',   $summary['endRecord']);
            // Process Paging
            $link = $searchObject->renderLinkPageTemplate();
            $options = array('totalItems' => $summary['resultTotal'],
                             'fileName'   => $link,
                             'perPage'    => $summary['perPage']);
            $pager = new VuFindPager($options);
            $interface->assign('pageLinks', $pager->getLinks());
        }
    }
}
