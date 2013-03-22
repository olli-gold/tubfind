<?php
/**
 * Class for accessing OCLC WorldCat search API
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
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes Wiki
 */
require_once 'sys/SRU.php';

/**
 * WorldCat SRU Search Interface
 *
 * @category VuFind
 * @package  Support_Classes
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes Wiki
 */
class Worldcat extends SRU
{
    private $_wskey;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
        global $configArray;

        parent::__construct(
            'http://www.worldcat.org/webservices/catalog/search/sru'
        );
        $this->_wskey = isset($configArray['WorldCat']['apiKey']) ?
            $configArray['WorldCat']['apiKey'] : null;
    }

    /**
     * Get holdings information for the specified record.
     *
     * @param string $id Record to obtain holdings for.
     *
     * @return array
     * @access public
     */
    public function getHoldings($id)
    {
        $this->client->setMethod(HTTP_REQUEST_METHOD_POST);
        $this->client->setURL(
            'http://www.worldcat.org/webservices/catalog/content/libraries/' . $id
        );
        $this->client->addRawQueryString("wskey={$this->_wskey}&servicelevel=full");

        if ($this->debug) {
            echo '<pre>Connect: ';
            print_r($this->client->getUrl(true));
            echo "</pre>\n";
        }

        $result = $this->client->sendRequest();
        if (!PEAR::isError($result)) {
            $xml = $this->client->getResponseBody();
            $unxml = new XML_Unserializer();
            $result = $unxml->unserialize($xml);
            if (!PEAR::isError($result)) {
                return $unxml->getUnserializedData();
            } else {
                PEAR::raiseError($result);
            }
        } else {
            return $result;
        }
    }

    /**
     * Retrieve a specific record.
     *
     * @param string $id Record ID to retrieve
     *
     * @return mixed     PEAR_Error on error, MARC XML on success.
     * @access public
     */
    public function getRecord($id)
    {
        $this->client->setMethod(HTTP_REQUEST_METHOD_POST);
        $this->client->setURL(
            'http://www.worldcat.org/webservices/catalog/content/' . $id
        );
        $this->client->addRawQueryString("wskey={$this->_wskey}&servicelevel=full");
        $result = $this->client->sendRequest();

        if ($this->debug) {
            echo '<pre>Connect: ';
            print_r($this->client->getUrl(true));
            echo "</pre>\n";
        }

        if (!PEAR::isError($result)) {
            return $this->client->getResponseBody();
        } else {
            return $result;
        }
    }

    /**
     * Get records similiar to one record
     *
     * @param object $record The file_marc object for the current record
     * @param int    $max    The maximum records to return; Default is 5
     *
     * @throws object        PEAR Error
     * @return array         An array of query results
     * @access public
     */
    public function getMoreLikeThis($record, $max = 5)
    {
        // Create array of query parts:
        $parts = array();

        // Add Dewey class to query
        if ($deweyField = $record->getField('082')) {
            if ($deweyFieldData = $deweyField->getSubfield('a')) {
                $deweyClass = $deweyFieldData->getData();
                // Skip "English Fiction" Dewey class -- this won't give us useful
                // matches because there's too much of it and it's too broad.
                if (substr($deweyClass, 0, 3) != '823') {
                    $parts[] = 'srw.dd any "' . $deweyClass . '"';
                }
            }
        }

        // Add author to query
        if ($deweyField = $record->getField('100')) {
            if ($deweyFieldData = $deweyField->getSubfield('a')) {
                $parts[] = 'srw.au all "' . $deweyFieldData->getData() . '"';
            }
        }

        // Add subjects to query
        $subjTags = array('650', '651', '655');
        foreach ($subjTags as $currentTag) {
            if ($subjFieldList = $record->getFields($currentTag)) {
                foreach ($subjFieldList as $subjField) {
                    if ($subjField = $subjField->getSubfield('a')) {
                        $parts[]
                            = 'srw.su all "' . trim($subjField->getData()) . '"';
                    }
                }
            }
        }

        // Add title to query
        if ($titleField = $record->getField('245')) {
            if ($titleFieldData = $titleField->getSubfield('a')) {
                $title = trim($titleFieldData->getData());
                if ($titleFieldData = $titleField->getSubfield('b')) {
                    $title .= ' ' . trim($titleFieldData->getData());
                }
                $parts[] = 'srw.ti any "' . str_replace('"', '', $title) . '"';
            }
        }

        // Not current record ID
        $idField = $record->getField('001');
        $id = trim($idField->getData());
        $query = '(' . implode(' or ', $parts) . ") not srw.no all \"$id\"";

        // Query String Parameters
        $options = array('operation' => 'searchRetrieve',
                         'query' => $query,
                         'maximumRecords' => $max,
                         'startRecord' => 1,
                         'wskey' => $this->_wskey,
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
     * Search
     *
     * @param string $query    The search query
     * @param string $oclcCode An OCLC code to exclude from results
     * @param int    $page     The page of records to start with
     * @param int    $limit    The number of records to return per page
     * @param string $sort     The value to be used by for sorting
     *
     * @throws object         PEAR Error
     * @return array          An array of query results
     * @access public
     */
    public function search($query, $oclcCode = null, $page = 1, $limit = 10,
        $sort = null
    ) {
        // Exclude current library from results
        if ($oclcCode) {
            $query .= ' not srw.li all "' . $oclcCode . '"';
        }

        // Submit query
        $start = ($page-1) * $limit;
        $params = array('query' => $query,
                        'startRecord' => $start,
                        'maximumRecords' => $limit,
                        'sortKeys' => empty($sort) ? 'relevance' : $sort,
                        'servicelevel' => 'full',
                        'wskey' => $this->_wskey);

        // Establish a limitation on searching by OCLC Codes
        if (isset($configArray['WorldCat']['LimitCodes'])) {
            $params['oclcsymbol'] = $configArray['WorldCat']['LimitCodes'];
        }

        $result = $this->call(HTTP_REQUEST_METHOD_POST, $params);

        if (PEAR::isError($result)) {
            PEAR::raiseError($result);
        }

        return $result;
    }

    /**
     * Build Query string from search parameters
     *
     * @param array $search An array of search parameters
     *
     * @throws object       PEAR Error
     * @return string       The query
     * @access public
     */
    public function buildQuery($search)
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
                        $thisGroup[] = $this->buildQuery(array($group));
                    }
                    // Is this an exclusion (NOT) group or a normal group?
                    if ($params['group'][0]['bool'] == 'NOT') {
                        $excludes[] = join(" OR ", $thisGroup);
                    } else {
                        $groups[]
                            = join(" ".$params['group'][0]['bool']." ", $thisGroup);
                    }
                }

                // Basic Search
                if (isset($params['lookfor']) && $params['lookfor'] != '') {
                    // Clean and validate input -- note that index may be in a
                    // different field depending on whether this is a basic or
                    // advanced search.
                    $lookfor = str_replace('"', '', $params['lookfor']);
                    if (isset($params['field'])) {
                        $index = $params['field'];
                    } else if (isset($params['index'])) {
                        $index = $params['index'];
                    } else {
                        $index = 'srw.kw';
                    }

                    // The index may contain multiple parts -- we want to search all
                    // listed index fields:
                    $index = explode(':', $index);
                    $clauses = array();
                    foreach ($index as $currentIndex) {
                        $clauses[] = "{$currentIndex} all \"{$lookfor}\"";
                    }
                    $query .= '(' . implode(' OR ', $clauses) . ')';
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


}

?>