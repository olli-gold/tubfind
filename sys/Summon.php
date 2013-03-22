<?php
/**
 * Summon Search API Interface for VuFind
 *
 * PHP version 5
 *
 * Copyright (C) Andrew Nagy 2009.
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
 * @link     http://api.summon.serialssolutions.com/help/api/ API Documentation
 */

require_once 'HTTP/Request.php';
require_once 'sys/ConfigArray.php';
require_once 'sys/SolrUtils.php';

/**
 * Summon REST API Interface
 *
 * @category VuFind
 * @package  Support_Classes
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://api.summon.serialssolutions.com/help/api/ API Documentation
 */
class Summon
{
    /**
     * A boolean value determining whether to print debug information
     * @var bool
     */
    public $debug = false;

    /**
     * The HTTP_Request object used for API transactions
     * @var object HTTP_Request
     */
    public $client;

    /**
     * The URL of the Summon API server
     * @var string
     */
    public $host;

    /**
     * The base path for API requests on the Summon API server
     *
     * @var string
     */
    public $apiPath;
    
    /**
     * The secret Key used for authentication
     * @var string
     */
    public $apiKey;

    /**
     * The Client ID used for authentication
     * @var string
     */
    public $apiId;

    /**
     * The session for the current transaction
     * @var string
     */
    public $sessionId;

    /**
     *
     * Configuration settings from web/conf/Summon.ini
     * @var array
     */
    private $_config;

    /**
     * Should boolean operators in the search string be treated as
     * case-insensitive (false), or must they be ALL UPPERCASE (true)?
     */
    private $_caseSensitiveBooleans = true;

    /**
     * Will we highlight text in responses?
     * @var bool
     */
    private $_highlight = false;

    /**
     * Will we include snippets in responses?
     * @var bool
     */
    private $_snippets = false;

    /**
     * Constructor
     *
     * Sets up the Summon API Client
     *
     * @param string $apiId  Summon API ID
     * @param string $apiKey Summon API Key
     *
     * @access public
     */
    public function __construct($apiId, $apiKey)
    {
        global $configArray;

        if ($configArray['System']['debug']) {
            $this->debug = true;
        }

        // Set hostname and base API path separately; this simplifies authentication:
        $this->host = 'http://api.summon.serialssolutions.com';
        $this->apiPath = '/2.0.0';
        $this->apiId = $apiId;
        $this->apiKey = $apiKey;
        $this->client = new HTTP_Request(null, array('useBrackets' => false));
        $this->_config = getExtraConfigArray('Summon');

        // Store preferred boolean behavior:
        if (isset($this->_config['General']['case_sensitive_bools'])) {
            $this->_caseSensitiveBooleans
                = $this->_config['General']['case_sensitive_bools'];
        }

        // Store highlighting/snippet behavior:
        if (isset($this->_config['General']['highlighting'])) {
            $this->_highlight = $this->_config['General']['highlighting'];
        }
        if (isset($this->_config['General']['snippets'])) {
            $this->_snippets = $this->_config['General']['snippets'];
        }
    }

    /**
     * Retrieves a document specified by the ID.
     *
     * @param string $id The document to retrieve from the Summon API
     *
     * @throws object    PEAR Error
     * @return string    The requested resource
     * @access public
     */
    public function getRecord($id)
    {
        if ($this->debug) {
            echo "<pre>Get Record: $id</pre>\n";
        }

        // Query String Parameters
        $options = array('s.q' => sprintf('ID:"%s"', $id));
        $result = $this->_call($options);
        if (PEAR::isError($result)) {
            PEAR::raiseError($result);
        }

        return $result;
    }

    /**
     * Escape a string for inclusion as part of a Summon parameter.
     *
     * @param string $input The string to escape.
     *
     * @return string       The escaped string.
     * @access private
     */
    private function _escapeParam($input)
    {
        // List of characters to escape taken from:
        //      http://api.summon.serialssolutions.com/help/api/search/parameters
        return addcslashes($input, ",:\\()\${}");
    }

    /**
     * Build Query string from search parameters
     *
     * @param array $search An array of search parameters
     *
     * @return string       The query
     * @access private
     */
    private function _buildQuery($search)
    {
        $groups   = array();
        $excludes = array();
        if (is_array($search)) {
            $query = '';

            foreach ($search as $params) {
                // Advanced Search
                if (isset($params['group'])) {
                    $thisGroup = array();
                    // Process each search group
                    foreach ($params['group'] as $group) {
                        // Build this group individually as a basic search
                        $thisGroup[] = $this->_buildQuery(array($group));
                    }
                    // Is this an exclusion (NOT) group or a normal group?
                    if ($params['group'][0]['bool'] == 'NOT') {
                        $excludes[] = join(" OR ", $thisGroup);
                    } else {
                        $groups[] = join(
                            " " . $params['group'][0]['bool'] . " ", $thisGroup
                        );
                    }
                }

                // Basic Search
                if (isset($params['lookfor']) && $params['lookfor'] != '') {
                    // Clean and validate input -- note that index may be in a
                    // different field depending on whether this is a basic or
                    // advanced search.
                    $lookfor = $params['lookfor'];
                    if (isset($params['field'])) {
                        $index = $params['field'];
                    } else if (isset($params['index'])) {
                        $index = $params['index'];
                    } else {
                        $index = 'AllFields';
                    }

                    // Force boolean operators to uppercase if we are in a
                    // case-insensitive mode:
                    if (!$this->_caseSensitiveBooleans) {
                        $lookfor = VuFindSolrUtils::capitalizeBooleans($lookfor);
                    }

                    // Prepend the index name, unless it's the special "AllFields"
                    // index:
                    if ($index != 'AllFields') {
                        $query .= "{$index}:($lookfor)";
                    } else {
                        $query .= "$lookfor";
                    }
                }
            }
        }

        // Put our advanced search together
        if (count($groups) > 0) {
            $query = "(" . join(") " . $search[0]['join'] . " (", $groups) . ")";
        }
        // and concatenate exclusion after that
        if (count($excludes) > 0) {
            $query .= " NOT ((" . join(") OR (", $excludes) . "))";
        }

        // Ensure we have a valid query to this point
        return isset($query) ? $query : '';
    }

    /**
     * Execute a search.
     *
     * @param array  $query      The search terms from the Search Object
     * @param array  $filterList The fields and values to filter results on
     * @param string $start      The record to start with
     * @param string $limit      The amount of records to return
     * @param string $sortBy     The value to be used by for sorting
     * @param array  $facets     The facets to include (null for defaults)
     * @param bool   $returnErr  On fatal error, should we fail outright (false) or
     * treat it as an empty result set with an error key set (true)?
     *
     * @throws object            PEAR Error
     * @return array             An array of query results
     * @access public
     */
    public function query($query, $filterList = null, $start = 1, $limit = 20,
        $sortBy = null, $facets = null, $returnErr = false
    ) {
        // Query String Parameters
        $options = array('s.q' => $this->_buildQuery($query));

        // TODO: add configurable authentication mechanisms to identify authorized
        // users and switch this to "authenticated" when appropriate (VUFIND-475):
        $options['s.role'] = 'none';

        // Which facets should we include in results?  Set defaults if not provided.
        if (!$facets) {
            $facets = array_keys($this->_config['Facets']);
        }

        // Default to "holdings only" unless a different setting is found in the
        // filters:
        $options['s.ho'] = 'true';

        // Which filters should be applied to our query?
        $options['s.fvf'] = array();
        $options['s.rf'] = array();
        if (!empty($filterList)) {
            // Loop through all filters and add appropriate values to request:
            foreach ($filterList as $filterArray) {
                foreach ($filterArray as $filter) {
                    $safeValue = $this->_escapeParam($filter['value']);
                    // Special case -- "holdings only" is a separate parameter from
                    // other facets.
                    if ($filter['field'] == 'holdingsOnly') {
                        $options['s.ho'] = $safeValue;
                    } else if ($filter['field'] == 'excludeNewspapers') {
                        // Special case -- support a checkbox for excluding
                        // newspapers:
                        $options['s.fvf'][] = "ContentType,Newspaper Article,true";
                    } else if ($range = VuFindSolrUtils::parseRange($filter['value'])) {
                        // Special case -- range query (translate [x TO y] syntax):
                        $from = $this->_escapeParam($range['from']);
                        $to = $this->_escapeParam($range['to']);
                        $options['s.rf'][] = "{$filter['field']},{$from}:{$to}";
                    } else {
                        // Standard case:
                        $options['s.fvf'][] = "{$filter['field']},{$safeValue}";
                    }
                }
            }
        }

        // Special case -- if user filtered down to newspapers AND excluded them,
        // we can't possibly have any results:
        if (in_array('ContentType,Newspaper Article,true', $options['s.fvf'])
            && in_array('ContentType,Newspaper Article', $options['s.fvf'])
        ) {
            return array(
                'recordCount' => 0,
                'documents' => array()
            );
        }

        if (is_array($facets)) {
            $options['s.ff'] = array();
            foreach ($facets as $facet) {
                // See if parameters are included as part of the facet name;
                // if not, override them with defaults.
                $parts = explode(',', $facet);
                $facetName = $parts[0];
                $facetMode = isset($parts[1]) ? $parts[1] : 'and';
                $facetPage = isset($parts[2]) ? $parts[2] : 1;
                if (isset($parts[3])) {
                    $facetLimit = $parts[3];
                } else {
                    $facetLimit
                        = isset($this->_config['Facet_Settings']['facet_limit'])
                            ? $this->_config['Facet_Settings']['facet_limit'] : 30;
                }
                $facetParams = "{$facetMode},{$facetPage},{$facetLimit}";

                // Special case -- we can't actually facet on PublicationDate,
                // but we need it in the results to display range controls.  If
                // we encounter this field, set a flag indicating that we need
                // to inject it into the results for proper display later:
                if ($facetName == 'PublicationDate') {
                    $injectPubDate = true;
                } else {
                    $options['s.ff'][] = "{$facetName},{$facetParams}";
                }
            }
        }

        if (isset($sortBy)) {
            $options['s.sort'] = $sortBy;
        }

        $options['s.ps'] = $limit;
        $options['s.pn'] = $start;

        // Define Highlighting
        if ($this->_highlight) {
            $options['s.hl'] = 'true';
            $options['s.hs'] = '{{{{START_HILITE}}}}';
            $options['s.he'] = '{{{{END_HILITE}}}}';
        } else {
            $options['s.hl'] = 'false';
            $options['s.hs'] = $options['s.he'] = '';
        }

        if ($this->debug) {
            echo '<pre>Query: ';
            print_r($options);
            echo "</pre>\n";
        }

        $result = $this->_call($options);
        if (PEAR::isError($result)) {
            if ($returnErr) {
                return array(
                    'recordCount' => 0,
                    'documents' => array(),
                    'errors' => $result->getMessage()
                );
            } else {
                PEAR::raiseError($result);
            }
        }

        // Add a fake "PublicationDate" facet if flagged earlier; this is necessary
        // in order to display the date range facet control in the interface.
        if (isset($injectPubDate) && $injectPubDate) {
            $result['facetFields'][] = array(
                'fieldName' => 'PublicationDate',
                'displayName' => 'PublicationDate',
                'counts' => array()
            );
        }

        return $result;
    }

    /**
     * Submit REST Request
     *
     * @param array  $params  An array of parameters for the request
     * @param string $service The API Service to call
     * @param string $method  The HTTP Method to use
     * @param bool   $raw     Whether to return raw XML or processed
     *
     * @return object         The Summon API response (or a PEAR_Error object).
     * @access private
     */
    private function _call($params = array(), $service = 'search', $method = 'POST',
        $raw = false
    ) {
        $this->client->setURL($this->host . $this->apiPath . '/' . $service);
        //$this->client->setMethod($method);
        $this->client->setMethod('GET');

        // Build Query String
        $query = array();
        foreach ($params as $function => $value) {
            if (is_array($value)) {
                foreach ($value as $additional) {
                    $additional = urlencode($additional);
                    $query[] = "$function=$additional";
                }
            } else {
                $value = urlencode($value);
                $query[] = "$function=$value";
            }
        }
        asort($query);
        $queryString = implode('&', $query);
        $this->client->addRawQueryString($queryString);

        if ($this->debug) {
            echo "<pre>$method: ";
            print_r($this->host . $this->apiPath . "/$service?" . $queryString);
            echo "</pre>\n";
        }

        // Build Authorization Headers
        $headers = array('Accept' => 'application/json',
                         'x-summon-date' => date('D, d M Y H:i:s T'),
                         'Host' => 'api.summon.serialssolutions.com');
        $data = implode($headers, "\n") . "\n$this->apiPath/$service\n" .
            urldecode($queryString) . "\n";
        $hmacHash = $this->_hmacsha1($this->apiKey, $data);
        foreach ($headers as $key => $value) {
            $this->client->addHeader($key, $value);
        }
        $this->client->addHeader('Authorization', "Summon $this->apiId;$hmacHash");
        if ($this->sessionId) {
            $this->client->addHeader('x-summon-session-id', $this->sessionId);
        }

        // Send Request
        $result = $this->client->sendRequest();
        if (!PEAR::isError($result)) {
            return $this->_process($this->client->getResponseBody());
        } else {
            return $result;
        }
    }

    /**
     * Perform normalization and analysis of Summon return value.
     *
     * @param array $input The raw response from Summon
     *
     * @return array       The processed response from Summon
     * @access private
     */
    private function _process($input)
    {
        // Unpack JSON Data
        $result = json_decode($input, true);

        // Catch decoding errors -- turn a bad JSON input into an empty result set
        // containing an appropriate error code.
        if (!$result) {
            $result = array(
                'recordCount' => 0,
                'documents' => array(),
                'errors' => array(
                    array(
                        'code' => 'VuFind-Internal',
                        'message' => 'Cannot decode JSON response: ' . $input
                    )
                )
            );
        }

        // Detect errors
        if (isset($result['errors']) && is_array($result['errors'])) {
            foreach ($result['errors'] as $current) {
                $errors[] = "{$current['code']}: {$current['message']}";
            }
            $msg = 'Unable to process query<br />Summon returned: ' .
                implode('<br />', $errors);
            return new PEAR_Error($msg);
        }

        // Process highlighting/snippets:
        foreach ($result['documents'] as $i => $current) {
            // Remove snippets if not desired:
            if (!$this->_snippets) {
                unset($result['documents'][$i]['Snippet']);
            }
        }

        return $result;
    }

    /**
     * Generate an HMAC hash
     *
     * @param string $key  Hash key
     * @param string $data Data to hash
     *
     * @return string      Generated hash
     */
    private function _hmacsha1($key, $data)
    {
        $blocksize=64;
        $hashfunc='sha1';
        if (strlen($key)>$blocksize) {
            $key=pack('H*', $hashfunc($key));
        }
        $key=str_pad($key, $blocksize, chr(0x00));
        $ipad=str_repeat(chr(0x36), $blocksize);
        $opad=str_repeat(chr(0x5c), $blocksize);
        $hmac = pack(
            'H*', $hashfunc(
                ($key^$opad).pack(
                    'H*', $hashfunc(
                        ($key^$ipad).$data
                    )
                )
            )
        );
        return base64_encode($hmac);
    }
}

?>
