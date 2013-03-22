<?php
/**
 * SRU Search Interface
 *
 * PHP version 5
 *
 * Copyright (C) Andrew Nagy 2008.
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
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes#searching Wiki
 */

require_once 'XML/Unserializer.php';
require_once 'XML/Serializer.php';
require_once 'File/MARCXML.php';
require_once 'sys/Proxy_Request.php';

/**
 * SRU Search Interface
 *
 * @category VuFind
 * @package  Support_Classes
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes#searching Wiki
 */
class SRU
{
    /**
     * A boolean value detemrining whether to print debug information
     * @var bool
     */
    public $debug = false;

    /**
     * Whether to Serialize to a PHP Array or not.
     * @var bool
     */
    public $raw = false;

    /**
     * The HTTP_Request object used for REST transactions
     * @var object HTTP_Request
     */
    protected $client;

    /**
     * The host to connect to
     * @var string
     */
    protected $host;

    /**
     * The version to specify in the URL
     * @var string
     */
    protected $sruVersion = '1.1';

    /**
     * Constructor
     *
     * Sets up the SOAP Client
     *
     * @param string $host The URL of the eXist Server
     *
     * @access public
     */
    public function __construct($host)
    {
        global $configArray;

        $this->host = $host;
        $this->client = new Proxy_Request(null, array('useBrackets' => false));

        if ($configArray['System']['debug']) {
            $this->debug = true;
        }
    }

    /**
     * Retrieves a document specified by the ID and returns a MARC record.
     *
     * @param string $id The document to retrieve from Solr
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
        $options = array('operation' => 'searchRetrieve',
                         'query' => "rec.id=\"$id\"",
                         'maximumRecords' => 1,
                         'startRecord' => 1,
                         'recordSchema' => 'marcxml');

        $result = $this->call('GET', $options, false);
        if (PEAR::isError($result)) {
            PEAR::raiseError($result);
        }

        $style = new DOMDocument;
        $style->load('xsl/sru-marcxml.xsl');
        $xsl = new XSLTProcessor();
        $xsl->importStyleSheet($style);
        $xml = new DOMDocument;
        $xml->loadXML($result);
        $marcxml = $xsl->transformToXML($xml);

        $marc = new File_MARCXML($marcxml, File_MARC::SOURCE_STRING);
        return $marc->next();
    }

    /**
     * Build Query string from search parameters
     *
     * @param array $search An array of search parameters
     *
     * @throws object       PEAR Error
     * @return array        An array of query results
     * @access public
     */
    public function buildQuery($search)
    {
        foreach ($search as $params) {
            if ($params['lookfor'] != '') {
                $query = (isset($query)) ? $query . ' ' . $params['bool'] . ' ' : '';
                switch ($params['field']) {
                case 'title':
                    $query .= 'dc.title="' . $params['lookfor'] . '" OR ';
                    $query .= 'dc.title=' . $params['lookfor'];
                    break;
                case 'id':
                    $query .= 'rec.id=' . $params['lookfor'];
                    break;
                case 'author':
                    preg_match_all('/"[^"]*"|[^ ]+/', $params['lookfor'], $wordList);
                    $author = array();
                    foreach ($wordList[0] as $phrase) {
                        if (substr($phrase, 0, 1) == '"') {
                            $arr = explode(
                                ' ', substr($phrase, 1, strlen($phrase) - 2)
                            );
                            $author[] = implode(' AND ', $arr);
                        } else {
                            $author[] = $phrase;
                        }
                    }
                    $author = implode(' ', $author);
                    $query .= 'dc.creator any "' . $author . '" OR';
                    $query .= 'dc.creator any ' . $author;
                    break;
                case 'callnumber':
                    break;
                case 'publisher':
                    break;
                case 'year':
                    $query = 'dc.date=' . $params['lookfor'];
                    break;
                case 'series':
                    break;
                case 'language':
                    break;
                case 'toc':
                    break;
                case 'topic':
                    break;
                case 'geo':
                    break;
                case 'era':
                    break;
                case 'genre':
                    break;
                case 'subject':
                    break;
                case 'isn':
                    break;
                case 'all':
                default:
                    $query = 'dc.title="' . $params['lookfor'] . '" OR dc.title=' .
                        $params['lookfor'] . ' OR dc.creator="' .
                        $params['lookfor'] . '" OR dc.creator=' .
                        $params['lookfor'] . ' OR dc.subject="' .
                        $params['lookfor'] . '" OR dc.subject=' .
                        $params['lookfor'] . ' OR dc.description=' . 
                        $params['lookfor'] . ' OR dc.date=' . $params['lookfor'];
                    break;
                }
            }
        }

        return $query;
    }

    /**
     * Get records similiar to one record
     *
     * @param array  $record An associative array of the record data
     * @param string $id     The record id
     * @param int    $max    The maximum records to return; Default is 5
     *
     * @throws object        PEAR Error
     * @return array         An array of query results
     * @access public
     */
    public function getMoreLikeThis($record, $id, $max = 5)
    {
        global $configArray;

        // More Like This Query
        $query = 'title="' . $record['245']['a'] . '" ' .
                 "NOT rec.id=$id";

        // Query String Parameters
        $options = array('operation' => 'searchRetrieve',
                         'query' => $query,
                         'maximumRecords' => $max,
                         'startRecord' => 1,
                         'recordSchema' => 'marcxml');

        if ($this->debug) {
            echo '<pre>More Like This Query: ';
            print_r($query);
            echo "</pre>\n";
        }

        $result = $this->call('GET', $options);
        if (PEAR::isError($result)) {
            PEAR::raiseError($result);
        }

        return $result;
    }

    /**
     * Scan
     *
     * @param string $clause   The CQL clause specifying the start point
     * @param int    $pos      The position of the start point in the response
     * @param int    $maxTerms The maximum number of terms to return
     *
     * @return string          XML response
     * @access public
     */
    public function scan($clause, $pos = null, $maxTerms = null)
    {
        $options = array('operation' => 'scan',
                         'scanClause' => $clause);
        if (!is_null($pos)) {
            $options['responsePosition'] = $pos;
        }
        if (!is_null($maxTerms)) {
            $options['maximumTerms'] = $maxTerms;
        }

        $result = $this->call('GET', $options, false);
        if (PEAR::isError($result)) {
            PEAR::raiseError($result);
        }

        return $result;
    }

    /**
     * Search
     *
     * @param string $query   The search query
     * @param string $start   The record to start with
     * @param string $limit   The amount of records to return
     * @param string $sortBy  The value to be used by for sorting
     * @param string $schema  Record schema to use in results list
     * @param bool   $process Process into array (true) or return raw (false)
     *
     * @throws object         PEAR Error
     * @return array          An array of query results
     * @access public
     */
    public function search($query, $start = 1, $limit = null, $sortBy = null,
        $schema = 'marcxml', $process = true
    ) {
        if ($this->debug) {
            echo '<pre>Query: ';
            print_r($query);
            echo "</pre>\n";
        }

        // Query String Parameters
        $options = array('operation' => 'searchRetrieve',
                         'query' => $query,
                         'startRecord' => ($start) ? $start : 1,
                         'recordSchema' => $schema);
        if (!is_null($limit)) {
            $options['maximumRecords'] = $limit;
        }
        if (!is_null($sortBy)) {
            $options['sortKeys'] = $sortBy;
        }

        $result = $this->call('GET', $options, $process);
        if (PEAR::isError($result)) {
            PEAR::raiseError($result);
        }

        return $result;
    }

    /**
     * Submit REST Request
     *
     * @param string $method  HTTP Method to use: GET, POST,
     * @param array  $params  An array of parameters for the request
     * @param bool   $process Should we convert the MARCXML?
     *
     * @return string         The response from the XServer
     * @access protected
     */
    protected function call($method = HTTP_REQUEST_METHOD_GET, $params = null,
        $process = true
    ) {
        if ($params) {
            $query = array('version='.$this->sruVersion);
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
            $url = implode('&', $query);
        }

        if ($this->debug) {
            echo '<pre>Connect: ';
            print_r($this->host . '?' . $url);
            echo "</pre>\n";
        }

        $this->client->setMethod($method);
        $this->client->setURL($this->host);
        $this->client->addRawQueryString($url);
        $result = $this->client->sendRequest();

        if (!PEAR::isError($result)) {
            if ($process) {
                return $this->_process($this->client->getResponseBody());
            } else {
                return $this->client->getResponseBody();
            }
        } else {
            return $result;
        }
    }

    /**
     * Process an SRU response.
     *
     * @param string $result SRU response
     *
     * @return array         Unserialized version of XML.
     * @access private
     */
    private function _process($result)
    {
        global $configArray;

        if (substr($result, 0, 5) != '<?xml') {
            PEAR::raiseError(new PEAR_Error('Cannot Load Results'));
        }

        $xsl = new XSLTProcessor();

        $style = new DOMDocument;
        $style->load('xsl/sru-convert.xsl');
        $xsl->importStyleSheet($style);

        $xml = new DOMDocument;
        $xml->loadXML($result);

        $result = $xsl->transformToXML($xml);

        if ($this->raw) {
            return $result;
        } else {
            $unxml = new XML_Unserializer();
            $result = $unxml->unserialize($result);

            if (!PEAR::isError($result)) {
                $output = $unxml->getUnserializedData();
                // Make sure the result list is always an array, even if there is
                // only a single result!
                if (isset($output['record']['title'])) {
                    $output['record'] = array($output['record']);
                }
                return $output;
            } else {
                PEAR::raiseError($result);
            }
        }

        return null;
    }
}

?>