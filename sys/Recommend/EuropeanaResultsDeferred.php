<?php
/**
 * EuropeanaResultsDeferred Recommendations Module
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
 * @author   Lutz Biedinger <lutz.biedigner@gmail.com>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_recommendations_module Wiki
 */
require_once 'sys/Recommend/EuropeanaResults.php';

/**
 * EuropeanaResultsDeferred Recommendations Module
 *
 * This class sets up an AJAX call to trigger a call to the EuropeanaResults
 * module.  
 *
 * @category VuFind
 * @package  Recommendations
 * @author   Lutz Biedinger <lutz.biedigner@gmail.com>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_recommendations_module Wiki
 */
class EuropeanaResultsDeferred
{
    private $_searchObject;
    private $_params;
    private $_lookfor;

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
        $this->_searchObject = $searchObject;

        // Parse out parameters:
        $paramsArray = explode(':', $params);
        // Make sure all elements of the paramsArray are filled in, even if just
        // with a blank string, so we can rebuild the parameters to pass through
        // AJAX later on!
        for ($i = 0; $i<4; $i++) {
            $paramsArray[$i] = isset($paramsArray[$i]) ? $paramsArray[$i] : '';
        }

        // Collect the best possible search term(s):
        $this->_lookfor = isset($_REQUEST['lookfor'])
            ? $_REQUEST['lookfor'] : '';
        if (empty($this->_lookfor) && is_object($searchObject)) {
            $this->_lookfor = $searchObject->extractAdvancedTerms();
        }
        $this->_lookfor = trim($this->_lookfor);

        $this->_params = implode(':', $paramsArray);
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

        $interface->assign('deferredEuropeanaResultsParams', $this->_params);
        $interface->assign('deferredEuropeanaResultsSearchString', $this->_lookfor);
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
        return 'Search/Recommend/EuropeanaResultsDeferred.tpl';
    }
}
?>
