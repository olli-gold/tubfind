<?php
/**
 * Code for fetching external reviews.
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
require_once 'sys/Amazon.php';
require_once 'sys/Proxy_Request.php';
require_once 'sys/ISBN.php';

/**
 * ExternalReviews Class
 *
 * This class fetches reviews from various services for presentation to
 * the user.
 *
 * @category VuFind
 * @package  Support_Classes
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes#external_content Wiki
 */
class ExternalReviews
{
    private $_isbn;
    private $_results;

    /**
     * Constructor
     *
     * Do the actual work of loading the reviews.
     *
     * @param string $isbn ISBN of book to find reviews for
     *
     * @access public
     */
    public function __construct($isbn)
    {
        global $configArray;

        $this->_isbn = new ISBN($isbn);
        $this->_results = array();

        // We can't proceed without a valid ISBN:
        if (!$this->_isbn->isValid()) {
            return;
        }

        // Fetch from provider
        if (isset($configArray['Content']['reviews'])) {
            $providers = explode(',', $configArray['Content']['reviews']);
            foreach ($providers as $provider) {
                $provider = explode(':', trim($provider));
                $func = '_' . strtolower($provider[0]);
                $key = $provider[1];
                $this->_results[$func] = method_exists($this, $func) ?
                    $this->$func($key) : false;

                // If the current provider had no valid reviews, store nothing:
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
     * Amazon Reviews
     *
     * This method is responsible for connecting to Amazon AWS and abstracting
     * customer reviews for the specific ISBN
     *
     * @param string $id Amazon access key
     *
     * @return array     Returns array with review data, otherwise a PEAR_Error.
     * @access private
     * @author Andrew Nagy <vufind-tech@lists.sourceforge.net>
     */
    private function _amazon($id)
    {
        $params = array(
            'ResponseGroup' => 'Reviews', 'ItemId' => $this->_getIsbn10(),
            'Version' => '2010-10-10'
        );
        $request = new AWS_Request($id, 'ItemLookup', $params);
        $response = $request->sendRequest();

        if (!PEAR::isError($response)) {
            $data = @simplexml_load_string($response);
            if ($data) {
                $i = 0;
                $result = array();
                $reviews = isset($data->Items->Item->CustomerReviews->Review)
                    ? $data->Items->Item->CustomerReviews->Review : null;
                if (!empty($reviews)) {
                    foreach ($reviews as $review) {
                        $customer = $this->_getAmazonCustomer(
                            $id, (string)$review->CustomerId
                        );
                        if (!PEAR::isError($customer)) {
                            $result[$i]['Source'] = $customer;
                        }
                        $result[$i]['Rating'] = (string)$review->Rating;
                        $result[$i]['Summary'] = (string)$review->Summary;
                        $result[$i]['Content'] = (string)$review->Content;
                        $i++;
                    }
                }

                // If we weren't able to extract any individual reviews, we'll have
                // to resort to displaying results in an iframe.
                if (empty($result)) {
                    $iframe = isset($data->Items->Item->CustomerReviews->IFrameURL)
                        ? (string)$data->Items->Item->CustomerReviews->IFrameURL
                        : null;
                    if (!empty($iframe)) {
                        // CSS for iframe (explicit dimensions needed for IE
                        // compatibility -- using 100% has bad results there):
                        $css = "width: 700px; height: 500px;";
                        $result[$i] = array(
                            'Rating' => '',
                            'Summary' => '',
                            'Content' =>
                                "<iframe style=\"{$css}\" src=\"{$iframe}\" />"
                        );
                    }
                }

                return $result;
            } else {
                return new PEAR_Error('Could not parse Amazon response.');
            }
        } else {
            return $response;
        }
    }

    /**
     * Amazon Editorial
     *
     * This method is responsible for connecting to Amazon AWS and abstracting
     * editorial reviews for the specific ISBN
     *
     * @param string $id Amazon access key
     *
     * @return array     Returns array with review data, otherwise a PEAR_Error.
     * @access private
     * @author Andrew Nagy <vufind-tech@lists.sourceforge.net>
     */
    private function _amazoneditorial($id)
    {
        $params = array(
            'ResponseGroup' => 'EditorialReview', 'ItemId' => $this->_getIsbn10()
        );
        $request = new AWS_Request($id, 'ItemLookup', $params);
        $response = $request->sendRequest();

        if (!PEAR::isError($response)) {
            $data = @simplexml_load_string($response);
            if ($data) {
                $i = 0;
                $result = array();
                $reviews
                    = isset($data->Items->Item->EditorialReviews->EditorialReview)
                        ? $data->Items->Item->EditorialReviews->EditorialReview
                        : null;
                if (!empty($reviews)) {
                    foreach ($reviews as $review) {
                        // Filter out product description
                        if ((string)$review->Source != 'Product Description') {
                            foreach ($review as $key => $value) {
                                $result[$i][$key] = (string)$value;
                            }
                            $i++;
                        }
                    }
                }
                return $result;
            } else {
                return new PEAR_Error('Could not parse Amazon Editorial response.');
            }
        } else {
            return $response;
        }
    }

    /**
     * syndetics
     *
     * This method is responsible for connecting to Syndetics and abstracting
     * reviews from multiple providers.
     *
     * It first queries the master url for the ISBN entry seeking a review URL.
     * If a review URL is found, the script will then use HTTP request to
     * retrieve the script. The script will then parse the review according to
     * US MARC (I believe). It will provide a link to the URL master HTML page
     * for more information.
     * Configuration:  Sources are processed in order - refer to $sourceList.
     * If your library prefers one reviewer over another change the order.
     * If your library does not like a reviewer, remove it.  If there are more
     * syndetics reviewers add another entry.
     *
     * @param string $id     Client access key
     * @param bool   $s_plus Are we operating in Syndetics Plus mode?
     *
     * @return array     Returns array with review data, otherwise a PEAR_Error.
     * @access private
     * @author Joel Timothy Norman <joel.t.norman@wmich.edu>
     * @author Andrew Nagy <vufind-tech@lists.sourceforge.net>
     */
    private function _syndetics($id, $s_plus=false)
    {
        global $configArray;

        //list of syndetic reviews
        $sourceList = array(
            'CHREVIEW' => array('title' => 'Choice Review',
                                'file' => 'CHREVIEW.XML',
                                'div' => '<div id="syn_chreview"></div>'),
            'NYREVIEW' => array('title' => 'New York Times Review',
                                'file' => 'NYREVIEW.XML',
                                'div' => '<div id="syn_nyreview"></div>'),
            'BLREVIEW' => array('title' => 'Booklist Review',
                                'file' => 'BLREVIEW.XML',
                                'div' => '<div id="syn_blreview"></div>'),
            'PWREVIEW' => array('title' => "Publisher's Weekly Review",
                                'file' => 'PWREVIEW.XML',
                                'div' => '<div id="syn_pwreview"></div>'),
            'LJREVIEW' => array('title' => 'Library Journal Review',
                                'file' => 'LJREVIEW.XML',
                                'div' => '<div id="syn_ljreview"></div>'),
            'SLJREVIEW' => array('title' => 'School Library Journal Review',
                                'file' => 'SLJREVIEW.XML',
                                'div' => '<div id="syn_sljreview"></div>'),
            'HBREVIEW' => array('title' => 'Horn Book Review',
                                'file' => 'HBREVIEW.XML',
                                'div' => '<div id="syn_hbreview"></div>'),
            'KIRKREVIEW' => array('title' => 'Kirkus Book Review',
                                'file' => 'KIRKREVIEW.XML',
                                'div' => '<div id="syn_kireview"></div>'),
            'CRITICASREVIEW' => array('title' => 'Criticas Review',
                                'file' => 'CRITICASREVIEW.XML',
                                'div' => '<div id="syn_criticasreview"></div>'),
            // These last two entries are probably typos -- retained for legacy
            // compatibility just in case they're actually used for something!
            'KIREVIEW' => array('title' => 'Kirkus Book Review',
                                'file' => 'KIREVIEW.XML'),
            'CRITICASEREVIEW' => array('title' => 'Criti Case Review',
                                'file' => 'CRITICASEREVIEW.XML')
        );

        //first request url
        $baseUrl = isset($configArray['Syndetics']['url']) ?
            $configArray['Syndetics']['url'] : 'http://syndetics.com';
        $url = $baseUrl . '/index.aspx?isbn=' . $this->_getIsbn10() . '/' .
               'index.xml&client=' . $id . '&type=rw12,hw7';

        //find out if there are any reviews
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
                // Load reviews
                $url = $baseUrl . '/index.aspx?isbn=' . $this->_getIsbn10() . '/' .
                       $sourceInfo['file'] . '&client=' . $id . '&type=rw12,hw7';
                $client->setURL($url);
                if (PEAR::isError($http = $client->sendRequest())) {
                    return $http;
                }

                // Test XML Response
                $responseBody = $client->getResponseBody();
                if (!($xmldoc2 = @DOMDocument::loadXML($responseBody))) {
                    return new PEAR_Error('Invalid XML');
                }

                // If we have syndetics plus, we don't actually want the content
                // we'll just stick in the relevant div
                if ($s_plus) {
                    $review[$i]['Content'] = $sourceInfo['div'];
                } else {

                    // Get the marc field for reviews (520)
                    $nodes = $xmldoc2->GetElementsbyTagName("Fld520");
                    if (!$nodes->length) {
                        // Skip reviews with missing text
                        continue;
                    }
                    // Decode the content and strip unwanted <a> tags:
                    $review[$i]['Content'] = preg_replace(
                        '/<a>|<a [^>]*>|<\/a>/', '',
                        html_entity_decode($xmldoc2->saveXML($nodes->item(0)))
                    );

                    // Get the marc field for copyright (997)
                    $nodes = $xmldoc2->GetElementsbyTagName("Fld997");
                    if ($nodes->length) {
                        $review[$i]['Copyright']
                            = html_entity_decode($xmldoc2->saveXML($nodes->item(0)));
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

                //change the xml to actual title:
                $review[$i]['Source'] = $sourceInfo['title'];

                $review[$i]['ISBN'] = $this->_getIsbn10(); //show more link
                $review[$i]['username'] = $configArray['BookReviews']['id'];

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

    /**
     * Guardian Reviews
     *
     * This method is responsible for connecting to the Guardian and abstracting
     * reviews for the specific ISBN.
     *
     * @param string $id Guardian API key
     *
     * @return array     Returns array with review data, otherwise a PEAR_Error.
     * @access private
     * @author Eoghan Ó Carragáin <eoghan.ocarragain@gmail.com>
     */
    private function _guardian($id)
    {
        global $configArray;

        //first request url
        $url
            = "http://content.guardianapis.com/search?order-by=newest&format=json" .
                "&show-fields=all&reference=isbn%2F" . $this->_isbn->get13();

        // Only add api-key if one has been provided in config.ini. If no key is
        // provided, a link to the Guardian can still be shown.
        if (strlen($id) > 0) {
            $url = $url . "&api-key=" . $id;
        }

        //find out if there are any reviews
        $client = new Proxy_Request();
        $client->setMethod(HTTP_REQUEST_METHOD_GET);
        $client->setURL($url);
        $response = $client->sendRequest();

        // Was the request successful?
        if (!PEAR::isError($response)) {
            // grab the response:
            $json = $client->getResponseBody();
            // parse json
            $data = json_decode($json, true);
            if ($data) {
                $result = array();
                $i = 0;
                foreach ($data['response']['results'] as $review) {
                    $result[$i]['Date'] = $review['webPublicationDate'];
                    $result[$i]['Summary'] = $review['fields']['headline'] . ". " .
                        preg_replace(
                            '/<p>|<p [^>]*>|<\/p>/', '',
                            html_entity_decode($review['fields']['trailText'])
                        );
                    $result[$i]['ReviewURL'] = $review['fields']['shortUrl'];

                    // TODO: Make this configurable (or store it locally), so users
                    //       running VuFind behind SSL don't get warnings due to
                    //       inclusion of this non-SSL image URL:
                    $poweredImage
                        = 'http://image.guardian.co.uk/sys-images/Guardian/' .
                        'Pix/pictures/2010/03/01/poweredbyguardianBLACK.png';

                    $result[$i]['Copyright'] = "<a href=\"" .
                        $review['fields']['shortUrl'] . "\" target=\"new\">" .
                        "<img src=\"{$poweredImage}\" " .
                        "alt=\"Powered by the Guardian\" /></a>";

                    $result[$i]['Source'] = $review['fields']['byline'];
                    // Only return Content if the body tag contains a usable review
                    $redist = "Redistribution rights for this field are unavailable";
                    if ((strlen($review['fields']['body']) > 0)
                        && (!strstr($review['fields']['body'], $redist))
                    ) {
                        $result[$i]['Content'] = $review['fields']['body'];
                    }
                    $i++;
                }
                return $result;
            } else {
                return new PEAR_Error('Could not parse Guardian response.');
            }
        } else {
            return $response;
        }
    }

    /**
     * Get the name of an Amazon customer.
     *
     * @param string $id         Amazon access key
     * @param string $customerId Amazon customer to look up
     *
     * @return  string           Customer name, if available.
     * @access  private
     */
    private function _getAmazonCustomer($id, $customerId)
    {
        // Spare ourselves an API call if no ID is available:
        if (empty($customerId)) {
            return 'Anonymous';
        }

        // Send request:
        $params = array(
            'ResponseGroup' => 'CustomerInfo',
            'CustomerId' => $customerId
        );
        $request = new AWS_Request($id, 'CustomerContentLookup', $params);
        $response = $request->sendRequest();

        // Parse response:
        if (!PEAR::isError($response)) {
            $data = @simplexml_load_string($response);
            if ($data) {
                if (isset($data->Customers->Customer->Name)) {
                    return (string)$data->Customers->Customer->Name;
                } elseif (isset($data->Customers->Customer->Nickname)) {
                    return (string)$data->Customers->Customer->Nickname;
                } else {
                    return 'Anonymous';
                }
            } else {
                return new PEAR_Error('Could not parse Amazon response.');
            }
        } else {
            return $response;
        }
    }
}
?>