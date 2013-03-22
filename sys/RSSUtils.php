<?php
/**
 * RSS
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
 * @author   Eoghan Ó Carragáin <eoghan.ocarragain@gmail.com>
 * @author   Lutz Biedinger <lutz.biedinger@gmail.com>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes Wiki
 */

/**
 * RSS Utilities
 *
 * Class for accessing a rss feed of search results. The main entry point being
 * getResults, which takes the url to retrieve results from a limit for the amount
 * of items to read and an array of additional namespaces to be used/read.
 *
 * @category VuFind
 * @package  Support_Classes
 * @author   Eoghan Ó Carragáin <eoghan.ocarragain@gmail.com>
 * @author   Lutz Biedinger <lutz.biedinger@gmail.com>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes Wiki
 */
class RSSUtils
{
    /**
     * Returns an array of elements for each work matching the
     * parameters.
     *
     * @param string $targetUrl     The URL of the RSS document/feed
     * @param int    $limit         The number of works to return
     * @param array  $namespaceUris Namespace URIs to check for elements
     *
     * @return array
     * @access public
     */
    public function getResults($targetUrl, $limit = 5, $namespaceUris = array())
    {
        // empty array to hold the result
        $result = array();
        $result = $this->_process($targetUrl, $limit, $namespaceUris);

        return $result;
    }

    /**
     * Return the following array of values for each work:
     * title, cover_id, cover_id_type, key, ia, mainAuthor
     *
     * @param string $url           URL to request
     * @param int    $limit         The number of works to return
     * @param array  $namespaceUris Namespace URIs to check for elements
     *
     * @return array $result        parsed array of the rss data 
     * @access private
     */
    private function _process($url, $limit, $namespaceUris)
    {
        // empty array to hold the result
        $result = array();

        $client = new Proxy_Request();
        $client->setMethod(HTTP_REQUEST_METHOD_GET);
        $client->setURL($url);
        $response = $client->sendRequest();

        if (!PEAR::isError($response)) {
            $rssResult = $client->getResponseBody();

            $containsDiv = strpos($rssResult, "<div");

            if ($containsDiv === false || $containsDiv > 5) {
                //get the rss feed
                $rss = @simplexml_load_string($rssResult);

                // Was the request successful?
                if (isset($rss)) {
                    $results = isset($rss->channel->item)
                        ? $rss->channel->item : null;
                    $i = 0;
                    if (!empty($results)) {
                        foreach ($results as $item) {
                            if (!empty($item->title)) {
                                $result[$i]['title'] = (string)$item->title;
                                $result[$i]['link'] = (string)$item->link;
                                if (isset($item->description)) {
                                    $result[$i]['description']
                                        = (string)$item->description;
                                }
                                if (isset($item->enclosure)) {
                                    $atributes = $item->enclosure->attributes();
                                    $result[$i]['enclosure']
                                        = (string)$atributes['url'][0];
                                }

                                foreach ($namespaceUris as $ns_uri) {
                                    $ns_item = $item->children($ns_uri);

                                    if (isset($ns_item->date)) {
                                        $result[$i]['date'] = (string)$ns_item->date;
                                    }
                                    if (isset($ns_item->site)) {
                                        $result[$i]['site'] = (string)$ns_item->site;
                                    }
                                    if (isset($ns_item->dataProvider)) {
                                        $result[$i]['dataProvider']
                                            = (string)$ns_item->dataProvider;
                                    }
                                }

                                $i++;
                            }
                            //check if the result limit has been reached
                            if ($limit!= 0 && $i >= $limit) {
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }
}

?>
