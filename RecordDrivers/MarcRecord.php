<?php
/**
 * MARC Record Driver
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
require_once 'RecordDrivers/IndexRecord.php';

/**
 * MARC Record Driver
 *
 * This class is designed to handle MARC records.  Much of its functionality
 * is inherited from the default index-based driver.
 *
 * @category VuFind
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/other_than_marc Wiki
 */
class MarcRecord extends IndexRecord
{
    protected $marcRecord;

    /**
     * Constructor.  We build the object using all the data retrieved 
     * from the (Solr) index (which also happens to include the 
     * 'fullrecord' field containing raw metadata).  Since we have to 
     * make a search call to find out which record driver to construct, 
     * we will already have this data available, so we might as well 
     * just pass it into the constructor.
     *
     * @param array $record All fields retrieved from the index.
     *
     * @access public
     */
    public function __construct($record)
    {
        // Call the parent's constructor...
        parent::__construct($record);

        // Also process the MARC record:
        $marc = trim($record['fullrecord']);

        // check if we are dealing with MARCXML
        $xmlHead = '<?xml version';
        if (strcasecmp(substr($marc, 0, strlen($xmlHead)), $xmlHead) === 0) {
            $marc = new File_MARCXML($marc, File_MARCXML::SOURCE_STRING);
        } else {
            $marc = preg_replace('/#31;/', "\x1F", $marc);
            $marc = preg_replace('/#30;/', "\x1E", $marc);
            $marc = new File_MARC($marc, File_MARC::SOURCE_STRING);
        }

        $this->marcRecord = $marc->next();
        if (!$this->marcRecord) {
            PEAR::raiseError(new PEAR_Error('Cannot Process MARC Record'));
        }
    }

    public function getCoreMetadata() {
        global $interface;
        $template = parent::getCoreMetadata();
        $interface->assign('coreSubseries', $this->getSubseries());

        $secondaryCorps = $this->getSecondaryCorporates();
        if (!empty($corpAuthor)) {
            $duplicates[] = $corpAuthor;
        }
        if (!empty($duplicates)) {
            $secondaryCorps = array_diff($secondaryCorps, $duplicates);
        }
        $interface->assign('coreCorpContributors', $secondaryCorps);

        return $template;
    }

    protected function getSubseries() {
        return $this->_getFieldArray('246', array('a'));
    }

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
    public function getExport($format)
    {
        global $interface;

        switch(strtolower($format)) {
        case 'endnote':
            // This makes use of core metadata fields in addition to the
            // assignment below:
            header('Content-type: application/x-endnote-refer');
            $interface->assign('marc', $this->marcRecord);
            return 'RecordDrivers/Marc/export-endnote.tpl';
        case 'marc':
            $interface->assign('rawMarc', $this->marcRecord->toRaw());
            return 'RecordDrivers/Marc/export-marc.tpl';
        case 'marcxml':
            header("Content-type: application/rdf+xml");
            $interface->assign('rawMarc', $this->marcRecord->toXml());
            return 'RecordDrivers/Marc/export-marc.tpl';
        case 'rdf':
            header("Content-type: application/rdf+xml");
            $interface->assign('rdf', $this->getRDFXML());
            return 'RecordDrivers/Marc/export-rdf.tpl';
        case 'refworks':
            // To export to RefWorks, we actually have to redirect to
            // another page.  We'll do that here when the user requests a
            // RefWorks export, then we'll call back to this module from
            // inside RefWorks using the "refworks_data" special export format
            // to get the actual data.
            $this->redirectToRefWorks();
            break;
        case 'refworks_data':
            // This makes use of core metadata fields in addition to the
            // assignment below:
            header('Content-type: text/plain; charset=utf-8');
            $interface->assign('marc', $this->marcRecord);
            return 'RecordDrivers/Marc/export-refworks.tpl';
            break;
        case 'bibtex':
            // This makes use of core metadata fields in addition to the
            // assignment below:
            header('Content-type: text/plain; charset=utf-8');
            $interface->assign('marc', $this->marcRecord);
            return 'RecordDrivers/Marc/export-bibtex.tpl';
            break;
        default:
            return null;
        }
    }

    /**
     * Get an array of strings representing formats in which this record's 
     * data may be exported (empty if none).  Legal values: "RefWorks", 
     * "EndNote", "MARC", "RDF".
     *
     * @return array Strings representing export formats.
     * @access public
     */
    public function getExportFormats()
    {
        // Get an array of legal export formats (from config array, or use defaults
        // if nothing in config array).
        global $configArray;
        $active = isset($configArray['Export']) ?
            $configArray['Export'] : array('RefWorks' => true, 'EndNote' => true);

        // These are the formats we can possibly support if they are turned on in
        // config.ini:
        $possible = array('RefWorks', 'EndNote', 'MARC', 'RDF', 'MARCXML', 'BibTeX');

        // Check which formats are currently active:
        $formats = array();
        foreach ($possible as $current) {
            if ($active[$current]) {
                $formats[] = $current;
            }
        }

        // Send back the results:
        return $formats;
    }

    /**
     * Get an XML RDF representation of the data in this record.
     *
     * @return mixed XML RDF data (false if unsupported or error).
     * @access public
     */
    public function getRDFXML()
    {
        // Get Record as MARCXML
        $xml = trim($this->marcRecord->toXML());

        // Load Stylesheet
        $style = new DOMDocument;
        //$style->load('services/Record/xsl/MARC21slim2RDFDC.xsl');
        $style->load('services/Record/xsl/record-rdf-mods.xsl');

        // Setup XSLT
        $xsl = new XSLTProcessor();
        $xsl->importStyleSheet($style);

        // Transform MARCXML
        $doc = new DOMDocument;
        if ($doc->loadXML($xml)) {
            return $xsl->transformToXML($doc);
        }

        // If we got this far, something went wrong.
        return false;
    }

    /**
     * Assign necessary Smarty variables and return a template name to
     * load in order to display a summary of the item suitable for use in
     * search results.
     *
     * @return string Name of Smarty template file to display.
     * @access public
     */
    public function getSearchResult($view = 'list')
    {
        global $interface;

        // MARC results work just like index results, except that we want to
        // enable the AJAX status display since we assume that MARC records
        // come from the ILS:
        $template = parent::getSearchResult();
        $interface->assign('summAjaxStatus', true);
        return $template;
    }

    /**
     * Assign necessary Smarty variables and return a template name to 
     * load in order to display the full record information on the Staff 
     * View tab of the record view page.
     *
     * @return string Name of Smarty template file to display.
     * @access public
     */
    public function getStaffView()
    {
        global $interface;

        // Get Record as MARCXML
        $xml = trim($this->marcRecord->toXML());

        // Prevent unprintable characters from interfering with the XSL transform:
        $xml = str_replace(array(chr(29), chr(30), chr(31)), ' ', $xml);

        // Transform MARCXML
        $style = new DOMDocument;
        $style->load('services/Record/xsl/record-marc.xsl');
        $xsl = new XSLTProcessor();
        $xsl->importStyleSheet($style);
        $doc = new DOMDocument;
        if ($doc->loadXML($xml)) {
            $html = $xsl->transformToXML($doc);
            $interface->assign('details', $html);
        }

        return 'RecordDrivers/Marc/staff.tpl';
    }

    /**
     * Assign necessary Smarty variables and return a template name to 
     * load in order to display the Table of Contents extracted from the 
     * record.  Returns null if no Table of Contents is available.
     *
     * @return string Name of Smarty template file to display.
     * @access public
     */
    public function getTOC()
    {
        global $interface;

        // Return null if we have no table of contents:
        $fields = $this->marcRecord->getFields('505');
        if (!$fields) {
            return null;
        }

        // If we got this far, we have a table -- collect it as a string:
        $toc = '';
        foreach ($fields as $field) {
            $subfields = $field->getSubfields();
            foreach ($subfields as $subfield) {
                $toc .= $subfield->getData();
            }
        }

        // Assign the appropriate variable and return the template name:
        $interface->assign('toc', $toc);
        return 'RecordDrivers/Marc/toc.tpl';
    }

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
    public function getXML($format)
    {
        // Special case for MARC:
        if ($format == 'marc21') {
            $xml = $this->marcRecord->toXML();
            $xml = trim(str_replace(array(chr(29), chr(30), chr(31)), ' ', $xml));
            $xml = simplexml_load_string($xml);
            if (!$xml || !isset($xml->record)) {
                return false;
            }

            // Set up proper namespacing and extract just the <record> tag:
            $xml->record->addAttribute('xmlns', "http://www.loc.gov/MARC21/slim");
            $xml->record->addAttribute(
                'xsi:schemaLocation',
                'http://www.loc.gov/MARC21/slim ' .
                'http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd',
                'http://www.w3.org/2001/XMLSchema-instance'
            );
            $xml->record->addAttribute('type', 'Bibliographic');
            return $xml->record->asXML();
        }

        // Try the parent method:
        return parent::getXML($format);
    }

    /**
     * Does this record have a Table of Contents available?
     *
     * @return bool
     * @access public
     */
    public function hasTOC()
    {
        // Is there a table of contents in the MARC record?
        if ($this->marcRecord->getFields('505')) {
            return true;
        }
        return false;
    }

    /**
     * Does this record support an RDF representation?
     *
     * @return bool
     * @access public
     */
    public function hasRDF()
    {
        return true;
    }

    /**
     * Get access restriction notes for the record.
     *
     * @return array
     * @access protected
     */
    protected function getAccessRestrictions()
    {
        return $this->_getFieldArray('506');
    }

    /**
     * Get all subject headings associated with this record.  Each heading is
     * returned as an array of chunks, increasing from least specific to most
     * specific.
     *
     * @return array
     * @access protected
     */
    protected function getAllSubjectHeadings()
    {
        // These are the fields that may contain subject headings:
        $fields = array('600', '610', '630', '650', '651', '655');

        // This is all the collected data:
        $retval = array();

        // Try each MARC field one at a time:
        foreach ($fields as $field) {
            // Do we have any results for the current field?  If not, try the next.
            $results = $this->marcRecord->getFields($field);
            if (!$results) {
                continue;
            }

            // If we got here, we found results -- let's loop through them.
            foreach ($results as $result) {
                // Start an array for holding the chunks of the current heading:
                $current = array();

                // Get all the chunks and collect them together:
                $subfields = $result->getSubfields();
                if ($subfields) {
                    foreach ($subfields as $subfield) {
                        // Numeric subfields are for control purposes and should not
                        // be displayed:
                        if (!is_numeric($subfield->getCode())) {
                            $current[] = $subfield->getData();
                        }
                    }
                    // If we found at least one chunk, add a heading to our result:
                    if (!empty($current)) {
                        $retval[] = $current;
                    }
                }
            }
        }

        // Send back everything we collected:
        return $retval;
    }

    /**
     * Get award notes for the record.
     *
     * @return array
     * @access protected
     */
    protected function getAwards()
    {
        return $this->_getFieldArray('586');
    }

    /**
     * Get notes on bibliography content.
     *
     * @return array
     * @access protected
     */
    protected function getBibliographyNotes()
    {
        return $this->_getFieldArray('504');
    }

    /**
     * Get the main corporate author (if any) for the record.
     *
     * @return string
     * @access protected
     */
    protected function getCorporateAuthor()
    {
        return $this->_getFirstFieldValue('110', array('a', 'b'));
    }

    protected function getSecondaryAuthors() {
        $returnarray = array();
        $names = $this->_getFieldArray('700', array('a'));
        $functions = $this->_getFieldArray('700', array('e'));
        for ($i = 0; $i < count($names); $i++) {
            array_push($returnarray, array('name' => $names[$i], 'function' => $functions[$i]));
        }

        return $returnarray;
    }

    protected function getSecondaryCorporates() {
        return $this->_getFieldArray('710', array('a'));
    }

    /**
     * Return an array of all values extracted from the specified field/subfield
     * combination.  If multiple subfields are specified and $concat is true, they
     * will be concatenated together in the order listed -- each entry in the array
     * will correspond with a single MARC field.  If $concat is false, the return
     * array will contain separate entries for separate subfields.
     *
     * @param string $field     The MARC field number to read
     * @param array  $subfields The MARC subfield codes to read
     * @param bool   $concat    Should we concatenate subfields?
     *
     * @return array
     * @access protected
     */
    private function _getFieldArray($field, $subfields = null, $concat = true)
    {
        // Default to subfield a if nothing is specified.
        if (!is_array($subfields)) {
            $subfields = array('a');
        }

        // Initialize return array
        $matches = array();

        // Try to look up the specified field, return empty array if it doesn't
        // exist.
        $fields = $this->marcRecord->getFields($field);
        if (!is_array($fields)) {
            return $matches;
        }

        // Extract all the requested subfields, if applicable.
        foreach ($fields as $currentField) {
            $next = $this->_getSubfieldArray($currentField, $subfields, $concat);
            $matches = array_merge($matches, $next);
        }

        return $matches;
    }

    /**
     * Get notes on finding aids related to the record.
     *
     * @return array
     * @access protected
     */
    protected function getFindingAids()
    {
        return $this->_getFieldArray('555');
    }

    /**
     * Get the first value matching the specified MARC field and subfields.
     * If multiple subfields are specified, they will be concatenated together.
     *
     * @param string $field     The MARC field to read
     * @param array  $subfields The MARC subfield codes to read
     *
     * @return string
     * @access private
     */
    protected function _getFirstFieldValue($field, $subfields = null)
    {
        $matches = $this->_getFieldArray($field, $subfields);
        return (is_array($matches) && count($matches) > 0) ?
            $matches[0] : null;
    }

    /**
     * Get general notes on the record.
     *
     * @return array
     * @access protected
     */
    protected function getGeneralNotes()
    {
        return $this->_getFieldArray('500');
    }

    /**
     * Get the item's places of publication.
     *
     * @return array
     * @access protected
     */
    protected function getPlacesOfPublication()
    {
        return $this->_getFieldArray('260');
    }

    /**
     * Get an array of playing times for the record (if applicable).
     *
     * @return array
     * @access protected
     */
    protected function getPlayingTimes()
    {
        $times = $this->_getFieldArray('306', array('a'), false);

        // Format the times to include colons ("HH:MM:SS" format).
        for ($x = 0; $x < count($times); $x++) {
            $times[$x] = substr($times[$x], 0, 2) . ':' .
                substr($times[$x], 2, 2) . ':' .
                substr($times[$x], 4, 2);
        }

        return $times;
    }

    /**
     * Get credits of people involved in production of the item.
     *
     * @return array
     * @access protected
     */
    protected function getProductionCredits()
    {
        return $this->_getFieldArray('508');
    }

    /**
     * Get an array of publication frequency information.
     *
     * @return array
     * @access protected
     */
    protected function getPublicationFrequency()
    {
        return $this->_getFieldArray('310', array('a', 'b'));
    }

    /**
     * Get an array of information about record history, obtained in real-time
     * from the ILS.
     *
     * @return array
     * @access protected
     */
    protected function getRealTimeHistory()
    {
        // Get Acquisitions Data
        $id = $this->getUniqueID();
        $catalog = ConnectionManager::connectToCatalog();
        if ($catalog && $catalog->status) {
            $result = $catalog->getPurchaseHistory($id);
            if (PEAR::isError($result)) {
                PEAR::raiseError($result);
            }
            return $result;
        }
        return array();
    }

    /**
     * Get an array of information about record holdings, obtained in real-time
     * from the ILS.
     *
     * @return array
     * @access protected
     */
    protected function getRealTimeHoldings()
    {
        // Get Holdings Data
        $id = $this->getUniqueID();
        $catalog = ConnectionManager::connectToCatalog();
        if ($catalog && $catalog->status) {
            $result = $catalog->getHolding($id);
            if (PEAR::isError($result)) {
                PEAR::raiseError($result);
            }
            $holdings = array();
            if (count($result)) {
                foreach ($result as $copy) {
                    if ($copy['location'] != 'World Wide Web') {
                        $vs = $this->marcRecord->getFields('900');
                        if ($vs) {
                            foreach($vs as $v) {
                                // is this ours?
                                $libArr = $v->getSubfields('b');
                                $lib = $libArr[0]->getData();
                                if ($lib === 'TUB Hamburg <830>') {
                                    // Is there an address in the current field?
                                    $v_names = $v->getSubfields('c');
                                    $v_remarks = $v->getSubfields('f');
                                    $copy['summary'] = array();
                                    $copy['marc_notes'] = array();
                                    if (count($v_names) > 0) {
                                        foreach($v_names as $v_name) {
                                            $copy['summary'][] = $v_name->getData();
                                        }
                                    }
                                    if (count($v_remarks) > 0) {
                                        foreach ($v_remarks as $v_remark) {
                                            $copy['marc_notes'][] = $v_remark->getData();
                                        }
                                    }
                                }
                            }
                        }
                        $holdings[$copy['location']][] = $copy;
                    }
                }
            }
            return $holdings;
        }
        return array();
    }


    /**
     * Get an array of information about record holdings, obtained in real-time
     * from the ILS.
     *
     * @return array
     * @access protected
     */
    protected function getRealTimeJournalHoldings()
    {
        global $configArray, $interface;
        // only get associted volumes if this is a top level journal
        $class = $configArray['Index']['engine'];
        $url = $configArray['Index']['url'];
        $this->db = new $class($url);
        $picaConfigArray = parse_ini_file('conf/PICA.ini', true);
        $record_url = $picaConfigArray['Catalog']['ppnUrl'];

        $onlyTopLevel = 0;
        $leader = $this->marcRecord->getLeader();
        $indicator = substr($leader, 19, 1);
        switch ($indicator) {
            case 'a':
                $checkMore = 0;
                $interface->assign('showAssociated', '1');
                break;
            case 'c':
                $onlyTopLevel = 1;
                $interface->assign('showAssociated', '2');
                break;
            case 'b':
            case ' ':
            default:
                //$checkMore = 0;
                $interface->assign('showAssociated', '0');
                break;
        }
        if ($checkMore !== 0) {
            $journalIndicator = substr($leader, 7, 1);
            switch ($journalIndicator) {
                case 's':
                    $interface->assign('showAssociated', '1');
                    break;
                case 'b':
                case 'm':
                    $onlyTopLevel = 1;
                    $interface->assign('showAssociated', '3');
                    break;
            }
        }
        if ($onlyTopLevel === 1) {
            // only look for the parent of this record, all other associated publications can be ignored
            $parentIds = array();
            $parentName = array();
            $parentId = false;
            $subrecords = array();
            $index = 0;
            $vs = $this->marcRecord->getFields('773');
            if ($vs) {
                foreach($vs as $v) {
                    $a_names = $v->getSubfields('w');
                    if (count($a_names) > 0) {
                        $idArr = explode(')', $a_names[0]->getData());
                        if ($idArr[0] === '(DE-601') {
                            $parentId = true;
                            $parentIds[$index] = $idArr[1];
                        }
                    }
                    $v_names = $v->getSubfields('v');
                    if (count($v_names) > 0) {
                        $parentName[$index] = $v_names[0]->getData();
                    }
                    $index++;
                }
            }
            if ($parentId === false) {
                $vs = $this->marcRecord->getFields('830');
                if ($vs) {
                    foreach($vs as $v) {
                        $a_names = $v->getSubfields('w');
                        if (count($a_names) > 0) {
                            $idArr = explode(')', $a_names[0]->getData());
                            if ($idArr[0] === '(DE-601') {
                                $parentId = true;
                                $parentIds[$index] = $idArr[1];
                            }
                        }
                        $v_names = $v->getSubfields('v');
                        if (count($v_names) > 0) {
                            $parentName[$index] = $v_names[0]->getData();
                        }
                        $index++;
                    }
                }
                else {
                    $vs = $this->marcRecord->getFields('800');
                    if ($vs) {
                        foreach($vs as $v) {
                            $a_names = $v->getSubfields('w');
                            if (count($a_names) > 0) {
                                $idArr = explode(')', $a_names[0]->getData());
                                if ($idArr[0] === '(DE-601') {
                                    $parentId = true;
                                    $parentIds[$index] = $idArr[1];
                                }
                            }
                            $v_names = $v->getSubfields('v');
                            if (count($v_names) > 0) {
                                $parentName[$index] = $v_names[0]->getData();
                            }
                            $index++;
                        }
                    }
                }
            }
            foreach ($parentIds as $idxkey => $parId) {
                $subr = $this->db->getRecord($parId);
                $subrecord = array('id' => $parId);
                $subrecord['number'] = $parentName[$idxkey];
                $subrecord['title_full'] = array();
                if (!$subr) {
                    $subrecord['record_url'] = $record_url.$parentId;
                }
                $m = trim($subr['fullrecord']);
                $m = preg_replace('/#31;/', "\x1F", $m);
                $m = preg_replace('/#30;/', "\x1E", $m);
                $m = new File_MARC($m, File_MARC::SOURCE_STRING);
                $marcRecord = $m->next();
                if (is_a($marcRecord, 'File_MARC_Record') === true) {
                    $vs = $marcRecord->getFields('245');
                    if ($vs) {
                        foreach($vs as $v) {
                            $a_names = $v->getSubfields('a');
                            if (count($a_names) > 0) {
                                $subrecord['title_full'][] = " ".$a_names[0]->getData();
                            }
                        }
                    }
                }
                $subrecords[] = $subrecord;
            }
            if ($parentId === false) {
                $interface->assign('showAssociated', '0');
            }
            $interface->assign('parentRecord', $subrecords);
            return $subrecords;
        }
        // Get Holdings Data
        $id = $this->getUniqueID();
        #$catalog = ConnectionManager::connectToCatalog();
        #if ($catalog && $catalog->status) {
            #$result = $this->db->getRecordsByPPNLink($id);
            #$result = $catalog->getJournalHoldings($id);
            if (PEAR::isError($result)) {
                PEAR::raiseError($result);
            }

            // Retrieve the record from the index
            foreach ($result as $subId) {
                /*if (!($subrecord = $this->db->getRecord($subId))) {
                    $subrecord = array('id' => $subId, 'title_full' => array("Title not found"), 'record_url' => $record_url.$subId);
                }*/

                $subr = $subId;
                $subrecord = array('id' => $subId['id']);
                $subrecord['title_full'] = array();
                $subrecord['publishDate'] = array();
                if (!$subr) {
                    $subrecord['record_url'] = $record_url.$subId;
                }
                $m = trim($subr['fullrecord']);
                $m = preg_replace('/#31;/', "\x1F", $m);
                $m = preg_replace('/#30;/', "\x1E", $m);
                $m = new File_MARC($m, File_MARC::SOURCE_STRING);
                $marcRecord = $m->next();
                // 800$t$v -> 773$q -> 830$v -> 245$a$b -> "Title not found"
                if (is_a($marcRecord, 'File_MARC_Record') === true) {
                    $leader = $marcRecord->getLeader();
                    $indicator = substr($leader, 19, 1);
                    $journalIndicator = substr($leader, 7, 1);
                    switch ($indicator) {
                        case 'a':
                            $vs = $marcRecord->getFields('245');
                            if ($vs) {
                                foreach($vs as $v) {
                                    $a_names = $v->getSubfields('a');
                                    if (count($a_names) > 0) {
                                        $subrecord['title_full'][] = " ".$a_names[0]->getData();
                                    }
                                }
                            }
                            unset($vs);
                            $vs = $marcRecord->getFields('260');
                            if ($vs) {
                                foreach($vs as $v) {
                                    $a_names = $v->getSubfields('c');
                                    if (count($a_names) > 0) {
                                        $subrecord['publishDate'][0] = " ".$a_names[0]->getData();
                                    }
                                }
                            }
                            unset($vs);
                            $vs = $marcRecord->getFields('800');
                            $thisHasBeenSet = 0;
                            if ($vs) {
                                foreach($vs as $v) {
                                    $v_names = $v->getSubfields('v');
                                    if (count($v_names) > 0) {
                                        $subrecord['volume'] = $v_names[0]->getData();
                                        $thisHasBeenSet = 1;
                                    }
                                }
                            }
                            if ($thisHasBeenSet === 0) {
                                $vs = $marcRecord->getFields('830');
                                if ($vs) {
                                    foreach($vs as $v) {
                                        $v_names = $v->getSubfields('v');
                                        if (count($v_names) > 0) {
                                            $subrecord['volume'] = $v_names[0]->getData();
                                        }
                                    }
                                }
                            }
                            break;
                        case 'b':
                            $vs = $marcRecord->getFields('800');
                            $thisHasBeenSet = 0;
                            if ($vs) {
                                foreach($vs as $v) {
                                    $v_names = $v->getSubfields('v');
                                    if (count($v_names) > 0) {
                                        $subrecord['title_full'][] = $v_names[0]->getData();
                                        $thisHasBeenSet = 1;
                                    }
                                }
                            }
                            if ($thisHasBeenSet === 0) {
                                $vs = $marcRecord->getFields('830');
                                if ($vs) {
                                    foreach($vs as $v) {
                                        $v_names = $v->getSubfields('v');
                                        if (count($v_names) > 0) {
                                            $subrecord['volume'] = $v_names[0]->getData();
                                            $subrecord['title_full'][] = $v_names[0]->getData();
                                        }
                                    }
                                }
                            }
                            unset($vs);
                            $vs = $marcRecord->getFields('245');
                            if ($vs) {
                                foreach($vs as $v) {
                                    $a_names = $v->getSubfields('a');
                                    if (count($a_names) > 0) {
                                        $subrecord['volume'] = $v_names[0]->getData();
                                        $subrecord['title_full'][0] .= " ".$a_names[0]->getData();
                                    }
                                }
                            }
                            unset($vs);
                            $vs = $marcRecord->getFields('250');
                            if ($vs) {
                                foreach($vs as $v) {
                                    $a_names = $v->getSubfields('a');
                                    if (count($a_names) > 0) {
                                        $subrecord['title_full'][0] .= " ".$a_names[0]->getData();
                                    }
                                }
                            }
                            unset($vs);
                            $vs = $marcRecord->getFields('260');
                            if ($vs) {
                                foreach($vs as $v) {
                                    $a_names = $v->getSubfields('c');
                                    if (count($a_names) > 0) {
                                        $subrecord['title_full'][0] .= " ".$a_names[0]->getData();
                                        $subrecord['publishDate'][0] = " ".$a_names[0]->getData();
                                    }
                                }
                            }
                            /*
                            $ves = $marcRecord->getFields('900');
                            if ($ves) {
                                foreach($ves as $ve) {
                                    $libArr = $ve->getSubfields('b');
                                    $lib = $libArr[0]->getData();
                                    if ($lib === 'TUB Hamburg <830>') {
                                        // Is there an address in the current field?
                                        $ve_names = $ve->getSubfields('c');
                                        if (count($ve_names) > 0) {
                                            foreach($ve_names as $ve_name) {
                                                $subrecord['title_full'][] = $ve_name->getData();
                                            }
                                        }
                                    }
                                }
                            }
                            */
                            break;
                        case 'c':
                            $vs = $marcRecord->getFields('773');
                            if ($vs) {
                                foreach($vs as $v) {
                                    $q_names = $v->getSubfields('q');
                                    if ($q_names[0]) {
                                        $subrecord['title_full'][] = $q_names[0]->getData();
                                    }
                                }
                            }
                            unset($vs);
                            $vs = $marcRecord->getFields('260');
                            if ($vs) {
                                foreach($vs as $v) {
                                    $a_names = $v->getSubfields('c');
                                    if (count($a_names) > 0) {
                                        $subrecord['publishDate'][0] = " ".$a_names[0]->getData();
                                    }
                                }
                            }
                            unset($vs);
                            $vs = $marcRecord->getFields('800');
                            $thisHasBeenSet = 0;
                            if ($vs) {
                                foreach($vs as $v) {
                                    $v_names = $v->getSubfields('v');
                                    if (count($v_names) > 0) {
                                        $subrecord['volume'] = $v_names[0]->getData();
                                        $thisHasBeenSet = 1;
                                    }
                                }
                            }
                            if ($thisHasBeenSet === 0) {
                                $vs = $marcRecord->getFields('830');
                                if ($vs) {
                                    foreach($vs as $v) {
                                        $v_names = $v->getSubfields('v');
                                        if (count($v_names) > 0) {
                                            $subrecord['volume'] = $v_names[0]->getData();
                                        }
                                    }
                                }
                            }
                            break;
                        case ' ':
                        default:
                            $vs = $marcRecord->getFields('830');
                            if ($vs) {
                                foreach($vs as $v) {
                                    $v_names = $v->getSubfields('v');
                                    if (count($v_names) > 0) {
                                        $subrecord['volume'] = $v_names[0]->getData();
                                        $subrecord['title_full'][] = $v_names[0]->getData();
                                    }
                                }
                            }
                            if (count($subrecord['title_full']) === 0 || $journalIndicator === 'm' || $journalIndicator === 's') {
                                unset($vs);
                                $vs = $marcRecord->getFields('245');
                                if ($vs) {
                                    foreach($vs as $v) {
                                        $a_names = $v->getSubfields('a');
                                        if (count($a_names) > 0) {
                                            $subrecord['title_full'][0] .= " ".$a_names[0]->getData();
                                        }
                                    }
                                }
                                unset($vs);
                                $vs = $marcRecord->getFields('250');
                                if ($vs) {
                                    foreach($vs as $v) {
                                        $a_names = $v->getSubfields('a');
                                        if (count($a_names) > 0) {
                                            $subrecord['title_full'][0] .= " ".$a_names[0]->getData();
                                        }
                                    }
                                }
                                unset($vs);
                                if ($journalIndicator === 's') {
                                    $vs = $marcRecord->getFields('362');
                                    if ($vs) {
                                        foreach($vs as $v) {
                                            $a_names = $v->getSubfields('a');
                                            if (count($a_names) > 0) {
                                                $subrecord['title_full'][0] .= " ".$a_names[0]->getData();
                                            }
                                        }
                                    }
                                }
                                else {
                                    $vs = $marcRecord->getFields('260');
                                    if ($vs) {
                                        foreach($vs as $v) {
                                            $a_names = $v->getSubfields('c');
                                            if (count($a_names) > 0) {
                                                $subrecord['title_full'][0] .= " ".$a_names[0]->getData();
                                            }
                                        }
                                    }
                                }
                            }
                            unset($vs);
                            $vs = $marcRecord->getFields('260');
                            if ($vs) {
                                foreach($vs as $v) {
                                    $a_names = $v->getSubfields('c');
                                    if (count($a_names) > 0) {
                                        $subrecord['publishDate'][0] = " ".$a_names[0]->getData();
                                    }
                                }
                            }
                            break;
                    }
                }
                if (count($subrecord['title_full']) === 0) {
                    $subrecord['title_full'][] = '';
                }

                $subrecords[] = $subrecord;
            }
            return $subrecords;
        #}
    }

    /**
     * Get an array of strings describing relationships to other items.
     *
     * @return array
     * @access protected
     */
    protected function getRelationshipNotes()
    {
        return $this->_getFieldArray('580');
    }

    /**
     * Get an array of all series names containing the record.  Array entries may
     * be either the name string, or an associative array with 'name' and 'number'
     * keys.
     *
     * @return array
     * @access protected
     */
    protected function getSeries()
    {
        $matches = array();

        // First check the 440, 800 and 830 fields for series information:
        $primaryFields = array(
            '440' => array('a', 'p'),
            '800' => array('a', 'b', 'c', 'd', 'f', 'p', 'q', 't'),
            '830' => array('a', 'p'));
        $matches = $this->_getSeriesFromMARC($primaryFields);
        if (!empty($matches)) {
            return $matches;
        }

        // Now check 490 and display it only if 440/800/830 were empty:
        $secondaryFields = array('490' => array('a'));
        $matches = $this->_getSeriesFromMARC($secondaryFields);
        if (!empty($matches)) {
            return $matches;
        }

        // Still no results found?  Resort to the Solr-based method just in case!
        return parent::getSeries();
    }

    /**
     * Support method for getSeries() -- given a field specification, look for
     * series information in the MARC record.
     *
     * @param array $fieldInfo Associative array of field => subfield information
     * (used to find series name)
     *
     * @return array
     * @access private
     */
    protected function _getSeriesFromMARC($fieldInfo)
    {
        $matches = array();
        // Loop through the field specification....
        foreach ($fieldInfo as $field => $subfields) {
            // Did we find any matching fields?
            $series = $this->marcRecord->getFields($field);
            if (is_array($series)) {
                foreach ($series as $currentField) {
                    // Can we find a name using the specified subfield list?
                    $name = $this->_getSubfieldArray($currentField, $subfields);
                    if (isset($name[0])) {
                        $currentArray = array('name' => $name[0]);

                        // Can we find a number in subfield v?  (Note that number is
                        // always in subfield v regardless of whether we are dealing
                        // with 440, 490, 800 or 830 -- hence the hard-coded array
                        // rather than another parameter in $fieldInfo).
                        $number
                            = $this->_getSubfieldArray($currentField, array('v'));
                        if (isset($number[0])) {
                            $currentArray['number'] = $number[0];
                        }

                        // Save the current match:
                        $matches[] = $currentArray;
                    }
                }
            }
        }

        return $matches;
    }

    /**
     * Return an array of non-empty subfield values found in the provided MARC
     * field.  If $concat is true, the array will contain either zero or one
     * entries (empty array if no subfields found, subfield values concatenated
     * together in specified order if found).  If concat is false, the array
     * will contain a separate entry for each subfield value found.
     *
     * @param object $currentField Result from File_MARC::getFields.
     * @param array  $subfields    The MARC subfield codes to read
     * @param bool   $concat       Should we concatenate subfields?
     *
     * @return array
     * @access private
     */
    private function _getSubfieldArray($currentField, $subfields, $concat = true)
    {
        // Start building a line of text for the current field
        $matches = array();
        $currentLine = '';

        // Loop through all specified subfields, collecting results:
        foreach ($subfields as $subfield) {
            $subfieldsResult = $currentField->getSubfields($subfield);
            if (is_array($subfieldsResult)) {
                foreach ($subfieldsResult as $currentSubfield) {
                    // Grab the current subfield value and act on it if it is
                    // non-empty:
                    $data = trim($currentSubfield->getData());
                    if (!empty($data)) {
                        // Are we concatenating fields or storing them separately?
                        if ($concat) {
                            $currentLine .= $data . ' ';
                        } else {
                            $matches[] = $data;
                        }
                    }
                }
            }
        }

        // If we're in concat mode and found data, it will be in $currentLine and
        // must be moved into the matches array.  If we're not in concat mode,
        // $currentLine will always be empty and this code will be ignored.
        if (!empty($currentLine)) {
            $matches[] = trim($currentLine);
        }

        // Send back our result array:
        return $matches;
    }

    /**
     * Get an array of summary strings for the record.
     *
     * @return array
     * @access protected
     */
    protected function getSummary()
    {
        return $this->_getFieldArray('520');
    }

    /**
     * Get an array of technical details on the item represented by the record.
     *
     * @return array
     * @access protected
     */
    protected function getSystemDetails()
    {
        return $this->_getFieldArray('538');
    }

    /**
     * Get an array of note about the record's target audience.
     *
     * @return array
     * @access protected
     */
    protected function getTargetAudienceNotes()
    {
        return $this->_getFieldArray('521');
    }

    /**
     * Get the text of the part/section portion of the title.
     *
     * @return string
     * @access protected
     */
    protected function getTitleSection()
    {
        return $this->_getFirstFieldValue('245', array('n', 'p'));
    }

    /**
     * Get the statement of responsibility that goes with the title (i.e. "by John
     * Smith").
     *
     * @return string
     * @access protected
     */
    protected function getTitleStatement()
    {
        return $this->_getFirstFieldValue('245', array('c'));
    }

    /**
     * Return an associative array of URLs associated with this record (key = URL,
     * value = description).
     *
     * @return array
     * @access protected
     */
    protected function getURLs()
    {
        $retVal = array();

        $urls = $this->marcRecord->getFields('856');
        if ($urls) {
            foreach ($urls as $url) {
                // Is there an address in the current field?
                $address = $url->getSubfield('u');
                if ($address) {
                    $address = $address->getData();

                    // Is there a description?  If not, just use the URL itself.
                    $desc = $url->getSubfield('y');
                    //$desc2 = $url->getSubfield('z');
                    //if ($desc || $desc2) {                                                                                                                                                                                                                                                                                    $desc = $url->getSubfield('z');
                        if ($desc) {
                            $desc = $desc->getData();
                    //    }
                    //    if ($desc2) {
                    //        $desc .= ' ' . $desc2->getData();
                    //    }
                    } else {
                        $desc = $address;
                    }

                    if ($desc !== 'C') {
                        $retVal[$address] = $desc;
                    }
                }
            }
        }

        return $retVal;
    }

    /**
     * Redirect to the RefWorks site and then die -- support method for getExport().
     *
     * @return void
     * @access protected
     */
    protected function redirectToRefWorks()
    {
        global $configArray;

        // Build the URL to pass data to RefWorks:
        $exportUrl = $configArray['Site']['url'] . '/Record/' .
            urlencode($this->getUniqueID()) . '/Export?style=refworks_data';

        // Build up the RefWorks URL:
        $url = $configArray['RefWorks']['url'] . '/express/expressimport.asp';
        $url .= '?vendor=' . urlencode($configArray['RefWorks']['vendor']);
        $url .= '&filter=RefWorks%20Tagged%20Format&url=' . urlencode($exportUrl);

        header("Location: {$url}");
        die();
    }

    /**
     * TUBHH Enhancement
     * Return the title (period) and the signature of a volume
     * An array will be returned with key=signature, value=title.
     *
     * @access  public
     * @return  array
     */
    public function getVolumes()
    {
        $retVal = array();

        $vs = $this->marcRecord->getFields('900');
        if ($vs) {
            foreach($vs as $v) {
                // is this ours?
                $libArr = $v->getSubfields('b');
                $lib = $libArr[0]->getData();
                if ($lib === 'TUB Hamburg <830>') {
                    // Is there an address in the current field?
                    $v_names = $v->getSubfields('c');
                    $v_signatures = $v->getSubfields('d');
                    $int_counter = 0;
                    if (count($v_signatures) > count($v_names)) {
                        $int_counter = 1;
                    }
                    foreach ($v_names as $v_name) {
                        $name = $v_name->getData();
                        if ($v_signatures[$int_counter]) {
                            $signature = $v_signatures[$int_counter]->getData();
                            $signature_keys = explode('!', $signature);
                            $signature_key = trim($signature_keys[count($signature_keys)-1]);
                            if (count($v_signatures) > count($v_names)) {
                                $int_counter++;
                            }
                            $int_counter++;
                            $retVal[$signature_key] = $name;
                            #if (count($signature_keys) > 1) {
                            #    $retVal[$signature_key] = "Lesesaal";
                            #}
                        }
                    }
                }
            }
        }
        return $retVal;
    }

    /**.
     * Get the title of this medium even if its contained in another medium.
     * overrides the function in IndexRecord.
     * if no title found in 505t, use the parental record.
     *.
     * @access  protected.
     * @return  string.
     */
/*    protected function getShortTitle()
    {
        return $this->getTitle();
        $isSubTitle = $this->_getFirstFieldValue('505', array('t'));
        if ($isSubTitle !== null) {
            return $isSubTitle;
        }
        return parent::getShortTitle();
    }
*/
    /**
     * Get the full title of the record.
     *
     * @access  protected
     * @return  string
     */
/*    protected function getTitle()
    {
        global $configArray;
        $title = parent::getTitle();
        if (empty($title) !== true) {
            return $title;
        }
        $ppnTitleValue = $this->_getFirstFieldValue('830', array('w'));
        $ppnTitleArray = explode(')', $ppnTitleValue);
        $ppnTitle = $ppnTitleArray[(count($ppnTitleArray)-1)];
        if ($ppnTitle != null) {
            $class = $configArray['Index']['engine'];
            $url = $configArray['Index']['url'];
            $this->db = new $class($url);
            // Retrieve the record from the index
            $record = $this->db->getRecord($ppnTitle);
            if ($record != null) {
                $parentalRecord = new MarcRecord($record);
                $parentalTitle = $parentalRecord->_getFirstFieldValue('245', array('a'));
                if ($parentalTitle != null) {
                    return $parentalTitle;
                }
            }
        }
        $issnTitle = $this->getFirstFieldValue('773', array('x'));
        if ($issnTitle !== null) {
            $rec = file_get_contents("http://cl1.b.tu-harburg.de/issnfetch.php?issn=$issnTitle");
            if ($rec !== "") {
                return $rec;
            }
        }
    }
*/
    /**
     * TUBHH Enhancement
     * Return the title (period) and the signature of a volume
     * An array will be returned with key=signature, value=title.
     *
     * @access  public
     * @return  array
     */
    public function getArticleFieldedReference()
    {
        $retVal = array();
        $retVal['volume'] = $this->getVolume();
        $retVal['issue'] = $this->getIssue();
        $pages = $this->getPages();
        $pagesArr = explode('-', $pages);
        $retVal['spage'] = $pagesArr[0];
        $retVal['epage'] = $pagesArr[1];
        $retVal['date'] = $this->getRefYear();
        return $retVal;
    }

    /**
     * TUBHH Enhancement
     * Return the title (period) and the signature of a volume
     * An array will be returned with key=signature, value=title.
     *
     * @access  public
     * @return  array
     */
    public function getVolume()
    {
        return $this->_getFirstFieldValue('953', array('d'));
    }

    /**
     * TUBHH Enhancement
     * Return the title (period) and the signature of a volume
     * An array will be returned with key=signature, value=title.
     *
     * @access  public
     * @return  array
     */
    public function getIssue()
    {
        return $this->_getFirstFieldValue('953', array('e'));
    }

    /**
     * TUBHH Enhancement
     * Return the title (period) and the signature of a volume
     * An array will be returned with key=signature, value=title.
     *
     * @access  public
     * @return  array
     */
    public function getPages()
    {
        return $this->_getFirstFieldValue('953', array('h'));
    }

    /**
     * TUBHH Enhancement
     * Return the title (period) and the signature of a volume
     * An array will be returned with key=signature, value=title.
     *
     * @access  public
     * @return  array
     */
    public function getRefYear()
    {
        return $this->_getFirstFieldValue('953', array('j'));
    }
}

?>
