<?php
/**
 * Index Engine Interface
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
 * @link     http://vufind.org/wiki/system_classes#index_interface Wiki
 */

/**
 * Index Engine Interface
 *
 * @category VuFind
 * @package  Support_Classes
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes#index_interface Wiki
 */
Interface IndexEngine
{
    /**
     * Retrieves a document specified by the ID.
     *
     * @param string $id The document to retrieve from the index
     *
     * @throws object    PEAR Error
     * @return string    The requested resource (or null if bad ID)
     * @access public
     */
    function getRecord($id); 
    
    /**
     * Get records similiar to one record
     *
     * @param string $id A document ID.
     *
     * @throws object    PEAR Error
     * @return array     An array of query results similar to the specified record
     * @access public
     */
    function getMoreLikeThis($id);
    
    /**
     * Get record data based on the provided field and phrase.
     * Used for AJAX suggestions.
     *
     * @param string $phrase The input phrase
     * @param string $field  The field to search on
     * @param int    $limit  The number of results to return
     *
     * @return array         An array of query results
     * @access public
     */
    function getSuggestion($phrase, $field, $limit);
    
    /**
     * Get spelling suggestions based on input phrase.
     *
     * @param string $phrase The input phrase
     *
     * @return array         An array of spelling suggestions
     * @access public
     */
    function checkSpelling($phrase);

    /**
     * Build Query string from search parameters
     *
     * @param array $search An array of search parameters
     *
     * @throws object       PEAR Error
     * @return string       The query
     * @access public
     */
    function buildQuery($search);

    /**
     * Execute a search.
     *
     * @param string $query   The search query
     * @param string $handler The Query Handler to use (null for default)
     * @param array  $filter  The fields and values to filter results on
     * @param string $start   The record to start with
     * @param string $limit   The amount of records to return
     * @param array  $facet   An array of faceting options
     * @param string $spell   Phrase to spell check
     * @param string $sort    Field name to use for sorting
     * @param string $fields  A list of fields to be returned
     * @param string $method  Method to use for sending request (GET/POST)
     *
     * @throws object         PEAR Error
     * @return array          An array of query results
     * @access public
     */
    function search($query, $handler = null, $filter = null, $start = 0,
        $limit = null, $facet = null, $spell = null, $sort = null, 
        $fields = null, $method = HTTP_REQUEST_METHOD_POST
    );


}
?>