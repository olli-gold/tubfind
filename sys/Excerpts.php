<?php
/**
 * Code for fetching external excerpts.
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
 * @package  Support_Classes
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes#external_content Wiki
 */
require_once 'sys/Proxy_Request.php';
require_once 'sys/ISBN.php';

/**
 * ExternalExcerpts Class
 *
 * This class fetches excerpts from various services for presentation to
 * the user.
 *
 * @category VuFind
 * @package  Support_Classes
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes#external_content Wiki
 */
class ExternalExcerpts
{
    private $_isbn;
    private $_results;

    /**
     * Constructor
     *
     * Do the actual work of loading the excerpts.
     *
     * @param string $isbn ISBN of book to find excerpts for
     *
     * @access public
     */
    public function __construct($isbn)
    {
        global $configArray;

        $this->_isbn = new ISBN($isbn);
        $this->_results = array();

        // We can't proceed without an ISBN:
        if (empty($this->_isbn)) {
            return;
        }

        // Fetch from provider
        if (isset($configArray['Content']['excerpts'])) {
            $providers = explode(',', $configArray['Content']['excerpts']);
            foreach ($providers as $provider) {
                $provider = explode(':', trim($provider));
                $func = '_' . strtolower($provider[0]);
                $key = $provider[1];
                $this->_results[$func] = method_exists($this, $func) ?
                    $this->$func($key) : false;

                // If the current provider had no valid excerpts, store nothing:
                if (empty($this->_results[$func])
                    || PEAR::isError($this->_results[$func])
                ) {
                    unset($this->_results[$func]);
                }
            }
        }
    }

    /**
     * Get the excerpt information.
     *
     * @return array Associative array of excerpts.
     * @access public
     */
    public function fetch()
    {
        return $this->_results;
    }

    /**
     * Attempt to get an ISBN-10; revert to ISBN-13 only when ISBN-10 representation
     * is impossible.
     *
     * @return string
     * @access private
     */
    private function _getIsbn10()
    {
        $isbn = $this->_isbn->get10();
        if (!$isbn) {
            $isbn = $this->_isbn->get13();
        }
        return $isbn;
    }

    /**
     * syndetics
     *
     * This method is responsible for connecting to Syndetics and abstracting
     * excerpts.
     *
     * It first queries the master url for the ISBN entry seeking an excerpt URL.
     * If an excerpt URL is found, the script will then use HTTP request to
     * retrieve the script. The script will then parse the excerpt according to
     * US MARC (I believe). It will provide a link to the URL master HTML page
     * for more information.
     * Configuration:  Sources are processed in order - refer to $sourceList.
     *
     * @param string $id     Client access key
     * @param bool   $s_plus Are we operating in Syndetics Plus mode?
     *
     * @return array     Returns array with excerpt data, otherwise a PEAR_Error.
     * @access private
     * @author Joel Timothy Norman <joel.t.norman@wmich.edu>
     * @author Andrew Nagy <vufind-tech@lists.sourceforge.net>
     */
    private function _syndetics($id, $s_plus=false)
    {
        global $configArray;

        //list of syndetic excerpts
        $sourceList = array(
            'DBCHAPTER' => array(
                'title' => 'First Chapter or Excerpt',
                'file' => 'DBCHAPTER.XML',
                'div' => '<div id="syn_dbchapter"></div>'
            )
        );

        //first request url
        $baseUrl = isset($configArray['Syndetics']['url']) ?
            $configArray['Syndetics']['url'] : 'http://syndetics.com';
        $url = $baseUrl . '/index.aspx?isbn=' . $this->_getIsbn10() .
               '/index.xml&client=' . $id . '&type=rw12,hw7';

        //find out if there are any excerpts
        $client = new Proxy_Request();
        $client->setMethod(HTTP_REQUEST_METHOD_GET);
        $client->setURL($url);
        if (PEAR::isError($http = $client->sendRequest())) {
            return $http;
        }

        // Test XML Response
        if (!($xmldoc = @DOMDocument::loadXML($client->getResponseBody()))) {
            return new PEAR_Error('Invalid XML');
        }

        $review = array();
        $i = 0;
        foreach ($sourceList as $source => $sourceInfo) {
            $nodes = $xmldoc->getElementsByTagName($source);
            if ($nodes->length) {
                // Load excerpts
                $url = $baseUrl . '/index.aspx?isbn=' . $this->_getIsbn10() . '/' .
                       $sourceInfo['file'] . '&client=' . $id . '&type=rw12,hw7';
                $client->setURL($url);
                if (PEAR::isError($http = $client->sendRequest())) {
                    return $http;
                }

                // Test XML Response
                $xmldoc2 = @DOMDocument::loadXML($client->getResponseBody());
                if (!$xmldoc2) {
                    return new PEAR_Error('Invalid XML');
                }

                // If we have syndetics plus, we don't actually want the content
                // we'll just stick in the relevant div
                if ($s_plus) {
                    $review[$i]['Content'] = $sourceInfo['div'];
                } else {
                    // Get the marc field for excerpts (520)
                    $nodes = $xmldoc2->GetElementsbyTagName("Fld520");
                    if (!$nodes->length) {
                        // Skip excerpts with missing text
                        continue;
                    }
                    $review[$i]['Content']
                        = html_entity_decode($xmldoc2->saveXML($nodes->item(0)));

                    // Get the marc field for copyright (997)
                    $nodes = $xmldoc->GetElementsbyTagName("Fld997");
                    if ($nodes->length) {
                        $review[$i]['Copyright'] = html_entity_decode(
                            $xmldoc2->saveXML($nodes->item(0))
                        );
                    } else {
                        $review[$i]['Copyright'] = null;
                    }

                    if ($review[$i]['Copyright']) {  //stop duplicate copyrights
                        $location = strripos(
                            $review[0]['Content'], $review[0]['Copyright']
                        );
                        if ($location > 0) {
                            $review[$i]['Content']
                                = substr($review[0]['Content'], 0, $location);
                        }
                    }
                }

                // change the xml to actual title:
                $review[$i]['Source'] = $sourceInfo['title'];

                $review[$i]['ISBN'] = $this->_getIsbn10(); //show more link
                $review[$i]['username'] = $id;

                $i++;
            }
        }

        return $review;
    }

    /**
     * Wrapper around _syndetics to provide Syndetics Plus functionality.
     *
     * @param string $id Client access key
     *
     * @return array     Returns array with auth notes data, otherwise a PEAR_Error.
     */
    private function _syndeticsplus($id) 
    {
        return $this->_syndetics($id, true);
    }
}
?>