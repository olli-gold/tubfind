<?php
/**
 * Record Driver Interface
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
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/other_than_marc Wiki
 */

/**
 * Record Driver Interface
 *
 * This interface class is the definition of the required methods for
 * interacting with a particular metadata record format.
 *
 * @category VuFind
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/other_than_marc Wiki
 */
interface RecordInterface
{
    /**
     * Constructor.  We build the object using all the data retrieved
     * from the (Solr) index (which also happens to include the
     * 'fullrecord' field containing raw metadata).  Since we have to
     * make a search call to find out which record driver to construct,
     * we will already have this data available, so we might as well
     * just pass it into the constructor.
     *
     * @param array $indexFields All fields retrieved from the index.
     *
     * @access public
     */
    public function __construct($indexFields);

    /**
     * Get text that can be displayed to represent this record in
     * breadcrumbs.
     *
     * @return string Breadcrumb text to represent this record.
     * @access public
     */
    public function getBreadcrumb();

    /**
     * Assign necessary Smarty variables and return a template name
     * to load in order to display the requested citation format.
     * For legal values, see getCitationFormats().  Returns null if
     * format is not supported.
     *
     * @param string $format Citation format to display.
     *
     * @return string        Name of Smarty template file to display.
     * @access public
     */
    public function getCitation($format);

    /**
     * Get an array of strings representing citation formats supported
     * by this record's data (empty if none).  Legal values: "APA", "MLA".
     *
     * @return array Strings representing citation formats.
     * @access public
     */
    public function getCitationFormats();

    /**
     * Assign necessary Smarty variables and return a template name to
     * load in order to display core metadata (the details shown in the
     * top portion of the record view pages, above the tabs).
     *
     * @return string Name of Smarty template file to display.
     * @access public
     */
    public function getCoreMetadata();

    /**
     * Get an array of search results for other editions of the title
     * represented by this record (empty if unavailable).  In most cases,
     * this will use the XISSN/XISBN logic to find matches.
     *
     * @return mixed Editions in index engine result format (or null if no hits,
     * or PEAR_Error object).
     * @access public
     */
    public function getEditions();

    /**
     * Get the text to represent this record in the body of an email.
     *
     * @return string Text for inclusion in email.
     * @access public
     */
    public function getEmail();

    /**
     * Get any excerpts associated with this record.  For details of
     * the return format, see sys/Excerpts.php.
     *
     * @return array Excerpt information.
     * @access public
     */
    public function getExcerpts();

    /**
     * Assign necessary Smarty variables and return a template name to
     * load in order to export the record in the requested format.  For
     * legal values, see getExportFormats().  Returns null if format is
     * not supported.
     *
     * @param string $format Export format to display.
     *
     * @return string        Name of Smarty template file to display.
     * @access public
     */
    public function getExport($format);

    /**
     * Get an array of strings representing formats in which this record's
     * data may be exported (empty if none).  Legal values: "RefWorks",
     * "EndNote", "MARC", "RDF".
     *
     * @return array Strings representing export formats.
     * @access public
     */
    public function getExportFormats();

    /**
     * Assign necessary Smarty variables and return a template name to
     * load in order to display extended metadata (more details beyond
     * what is found in getCoreMetadata() -- used as the contents of the
     * Description tab of the record view).
     *
     * @return string Name of Smarty template file to display.
     * @access public
     */
    public function getExtendedMetadata();

    /**
     * Assign necessary Smarty variables and return a template name to
     * load in order to display holdings extracted from the base record
     * (i.e. URLs in MARC 856 fields) and, if necessary, the ILS driver.
     * Returns null if no data is available.
     *
     * @return string Name of Smarty template file to display.
     * @access public
     */
    public function getHoldings();

    /**
     * Assign necessary Smarty variables and return a template name to
     * load in order to display a summary of the item suitable for use in
     * user's favorites list.
     *
     * @param object $user      User object owning tag/note metadata.
     * @param int    $listId    ID of list containing desired tags/notes (or null
     * to show tags/notes from all user's lists).
     * @param bool   $allowEdit Should we display edit controls?
     *
     * @return string           Name of Smarty template file to display.
     * @access public
     */
    public function getListEntry($user, $listId = null, $allowEdit = true);

    /**
     * getMapView - gets the map view template.
     *
     * @return string template name
     * @access public
     */
    public function getMapView();

    /**
     * Get the OpenURL parameters to represent this record (useful for the
     * title attribute of a COinS span tag).
     *
     * @return string OpenURL parameters.
     * @access public
     */
    public function getOpenURL();

    /**
     * Get an XML RDF representation of the data in this record.
     *
     * @return mixed XML RDF data (false if unsupported or error).
     * @access public
     */
    public function getRDFXML();

    /**
     * Get any reviews associated with this record.  For details of
     * the return format, see sys/Reviews.php.
     *
     * @return array Review information.
     * @access public
     */
    public function getReviews();

    /**
     * Assign necessary Smarty variables and return a template name for the current
     * view to load in order to display a summary of the item suitable for use in
     * search results.
     *
     * @param string $view The current view.
     *
     * @return string      Name of Smarty template file to display.
     * @access public
     */
    public function getSearchResult($view = 'list');

    /**
     * Assign necessary Smarty variables and return a template name to
     * load in order to display the full record information on the Staff
     * View tab of the record view page.
     *
     * @return string Name of Smarty template file to display.
     * @access public
     */
    public function getStaffView();

    /**
     * Assign necessary Smarty variables and return a template name to
     * load in order to display the Table of Contents extracted from the
     * record.  Returns null if no Table of Contents is available.
     *
     * @return string Name of Smarty template file to display.
     * @access public
     */
    public function getTOC();

    /**
     * Return the unique identifier of this record within the Solr index;
     * useful for retrieving additional information (like tags and user
     * comments) from the external MySQL database.
     *
     * @return string Unique identifier.
     * @access public
     */
    public function getUniqueID();

    /**
     * Return an XML representation of the record using the specified format.
     * Return false if the format is unsupported.
     *
     * @param string $format Name of format to use (corresponds with OAI-PMH
     * metadataPrefix parameter).
     *
     * @return mixed         XML, or false if format unsupported.
     * @access public
     */
    public function getXML($format);

    /**
     * Does this record have audio content available?
     *
     * @return bool
     * @access public
     */
    public function hasAudio();

    /**
     * Does this record have an excerpt available?
     *
     * @return bool
     * @access public
     */
    public function hasExcerpt();

    /**
     * Does this record have searchable full text in the index?
     *
     * Note: As of this writing, searchable full text is not a VuFind feature,
     *       but this method will be useful if/when it is eventually added.
     *
     * @return bool
     * @access public
     */
    public function hasFullText();

    /**
     * Does this record have image content available?
     *
     * @return bool
     * @access public
     */
    public function hasImages();

    /**
     * Can this record be rendered on a map?  (If so, getMapView must return a
     * template for the map tab).
     *
     * @return bool
     * @access public
     */
    public function hasMap();

    /**
     * Does this record support an RDF representation?
     *
     * @return bool
     * @access public
     */
    public function hasRDF();

    /**
     * Does this record have reviews available?
     *
     * @return bool
     * @access public
     */
    public function hasReviews();

    /**
     * Does this record have a Table of Contents available?
     *
     * @return bool
     * @access public
     */
    public function hasTOC();

    /**
     * Does this record have video content available?
     *
     * @return bool
     * @access public
     */
    public function hasVideo();
}

?>