<?php
/**
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
 */

require_once 'RecordDrivers/IndexRecord.php';
require_once 'RecordDrivers/MarcRecord.php';

/**
 * GBVCentral Record Driver
 *
 * This class is designed to handle records recieved from GBV Discovery.
 * Much of its functionality is inherited from the default index-based driver.
 * @author	Oliver Goldschmidt <o.goldschmidt@tu-harburg.de>
 *
 */
class GBVCentralRecord extends MarcRecord
{

    private $subrecord;

    public function getSearchResult($view = 'list')
    {
        global $interface;
        parent::getSearchResult($view);
        $interface->assign('summInterlibraryLoan', $this->checkInterlibraryLoan());
        // Assign data for displaying values by finc project 2012-01-20
        $interface->assign('multipartLink', $this->getMultipartLink());
        $interface->assign('multipartChildren', $this->getMultipartChildren());
        return 'RecordDrivers/GBVCentral/result.tpl';
    }

    public function getCoreMetadata() {
        global $interface;
        parent::getCoreMetadata();
        //$interface->assign('multipartParent', $this->getMultipartParent());
        $interface->assign('isMultipartChildren', $this->isMultipartChildren());
        $interface->assign('hasArticles', $this->hasArticles());
        $interface->assign('articleChildren', $this->getArticleChildren());
        $interface->assign('coreSubseries', $this->getSubseries());

        return 'RecordDrivers/GBVCentral/core.tpl';
    }

    /**
     * Get the item's place of publication.
     *
     * @access  protected
     * @return  array
     */
    protected function getPlacesOfPublication() {
        if (isset($this->fields['publishPlace'])) {
            if (is_array($this->fields['publishPlace'])) {
                //return $this->fields['publishPlace'];
                $return = implode(',', $this->fields['publishPlace']);
            }
            else {
                //return array($this->fields['publishPlace']);
                $return = $this->fields['publishPlace'];
            }
            return array($return);
        }
        return array();
    }

    /**
     * Get an array of all the formats associated with the record.
     *
     * @access  protected
     * @return  array
     */
    protected function getFormats()
    {
        $result = array();
        if (isset($this->fields['format_se'])) {
            if (is_array($this->fields['format_se']) === false) $this->fields['format_se'] = array($this->fields['format_se']);
            $result = array_merge($result, $this->fields['format_se']);
        }
        if (isset($this->fields['format'])) {
            if (is_array($this->fields['format']) === false) $this->fields['format'] = array($this->fields['format']);
            $result = array_merge($result, $this->fields['format']);
        }
        return $result;
    }

    /**
     * @access  public
     * @return  string              Name of Smarty template file to display.
     */
    public function getHoldings()
    {
        global $interface;
        global $configArray;

        $configPica = parse_ini_file('conf/PICA.ini', true);
        $record_url = $configPica['Catalog']['ppnUrl'];

        try {
            $catalog = new CatalogConnection($configArray['Catalog']['driver']);
        } catch (PDOException $e) {
            // What should we do with this error?
            if ($configArray['System']['debug']) {
                echo '<pre>';
                echo 'DEBUG: ' . $e->getMessage();
                echo '</pre>';
            }
        }
        $ppnTitleValue = $this->_getFirstFieldValue('773', array('w'));
        $ppnTitleArray = explode(')', $ppnTitleValue);
        $ppnTitle = $ppnTitleArray[(count($ppnTitleArray)-1)];
        if ($ppnTitle == null) {
            $ppnTitle = $this->fields['id'];
        }
        $volumes = $this->getVolumes();
        $interface->assign('volumes', $volumes);
        $interface->assign('gbvholdings', $this->getRealTimeHoldings());
        $subrecords = $this->getRealTimeJournalHoldings();
        $interface->assign('gbvsubrecords', $subrecords);

        // Only display OpenURL link if the option is turned on and we have
        // an ISSN.  We may eventually want to make this rule more flexible,
        // but for now the ISSN restriction is designed to be consistent with
        // the way we display items on the search results list.
        $hasOpenURL = ($this->openURLActive('holdings') && $this->getCleanISSN());
        if ($hasOpenURL) {
            $interface->assign('holdingsOpenURL', $this->getOpenURL());
        }

        // Display regular URLs unless OpenURL is present and configured to
        // replace them:
        if (!isset($configArray['OpenURL']['replace_other_urls']) || !$configArray['OpenURL']['replace_other_urls'] || !$hasOpenURL) {
            $interface->assign('holdingURLs', $this->getURLs());
        }

        $interface->assign('interlibraryLoan', $this->checkInterlibraryLoan());
        //$interface->display('layout.tpl');

        return 'RecordDrivers/GBVCentral/holdings.tpl';
    }

    /**
     * checks if this item is in the local stock
     *
     * @access  protected
     * @return  string
     */
    protected function checkInterlibraryLoan()
    {
        // Return null if we have no table of contents:
        $fields = $this->marcRecord->getFields('912');
        if (!$fields) {
            return null;
        }

        $configPica = parse_ini_file('conf/GBVCentral.ini', true);

        $mylib = $configPica['libfilter'];
        $iln = $configPica['iln'];
        if (!isset($mylib) || $mylib === '') {
            $mylib = "GBV_ILN_".$iln;
        }

        // If we got this far, we have libraries owning this item -- check if we have it locally
        foreach ($fields as $field) {
            $subfields = $field->getSubfields();
            foreach ($subfields as $subfield) {
                if ($subfield->getCode() === 'a') {
                    if ($subfield->getData() === $mylib) {
                        return '0';
                    }
                }
            }
        }

        // Is this item an e-ressource?
        if (in_array('eBook', $this->getFormats()) === true) {
            return '0';
        }

        return '1';
    }

    /**
     * Get the reference of the article.
     *
     * @access  protected
     * @return  string
     */
    protected function getArticleReference()
    {
        $inRef = $this->_getFirstFieldValue('773', array('i'));
        $journalRef = $this->_getFirstFieldValue('773', array('t'));
        $articleRef = $this->_getFirstFieldValue('773', array('g'));
        if ($articleRef !== null) {
            return $inRef." ".$journalRef." ".$articleRef;
        }
        return null;
    }

    /**
     * Get just the base portion of the first listed ISSN (or false if no ISSNs).
     *
     * @access  protected
     * @return  mixed
     */
    protected function getCleanISSN()
    {
        $issn = parent::getCleanISSN();
        if ($issn === false) {
            $issn = $this->_getFirstFieldValue('773', array('x'));
        }
        return $issn;
    }

    /**
     * Get the OpenURL parameters to represent this record (useful for the
     * title attribute of a COinS span tag).
     *
     * @return string OpenURL parameters.
     * @access public
     */
    public function getOpenURL()
    {
        // Get the COinS ID -- it should be in the OpenURL section of config.ini,
        // but we'll also check the COinS section for compatibility with legacy
        // configurations (this moved between the RC2 and 1.0 releases).
        global $configArray;
        $coinsID = isset($configArray['OpenURL']['rfr_id']) ?
            $configArray['OpenURL']['rfr_id'] :
            $configArray['COinS']['identifier'];
        if (empty($coinsID)) {
            $coinsID = 'vufind.svn.sourceforge.net';
        }

        // Get a representative publication date:
        $pubDate = $this->getPublicationDates();
        $pubDate = empty($pubDate) ? '' : $pubDate[0];

        // Start an array of OpenURL parameters:
        $params = array(
            'ctx_ver' => 'Z39.88-2004',
            'ctx_enc' => 'info:ofi/enc:UTF-8',
            'rfr_id' => "info:sid/{$coinsID}:generator",
            'rft.title' => $this->getTitle(),
            'rft.date' => $pubDate
        );

        // Add additional parameters based on the format of the record:
        $formats = $this->getFormats();

        // If we have multiple formats, Book and Journal are most important...
        if (in_array('Aufsätze', $formats) || in_array('Elektronische Aufsätze', $formats)) {
            $format = 'Article';
        }
        else if (in_array('Book', $formats) || in_array('eBook', $formats)) {
            $format = 'Book';
        } else if (in_array('Journal', $formats) || in_array('eJournal', $formats)) {
            $format = 'Journal';
        } else {
            $format = $formats[0];
        }
        switch($format) {
            case 'Book':
                $params['rft_val_fmt'] = 'info:ofi/fmt:kev:mtx:book';
                $params['rft.genre'] = 'book';
                $params['rft.btitle'] = $params['rft.title'];
                $series = $this->getSeries();
                if (count($series) > 0) {
                    // Handle both possible return formats of getSeries:
                    $params['rft.series'] = is_array($series[0]) ?
                        $series[0]['name'] : $series[0];
                }
                $params['rft.au'] = $this->getPrimaryAuthor();
                $publishers = $this->getPublishers();
                if (count($publishers) > 0) {
                    $params['rft.pub'] = $publishers[0];
                }
                $params['rft.edition'] = $this->getEdition();
                $params['rft.isbn'] = $this->getCleanISBN();
                break;
            case 'Journal':
                /* This is probably the most technically correct way to represent
                 * a journal run as an OpenURL; however, it doesn't work well with
                 * Zotero, so it is currently commented out -- instead, we just add
                 * some extra fields and then drop through to the default case.
                   $params['rft_val_fmt'] = 'info:ofi/fmt:kev:mtx:journal';
                   $params['rft.genre'] = 'journal';
                   $params['rft.jtitle'] = $params['rft.title'];
                   $params['rft.issn'] = $this->getCleanISSN();
                   $params['rft.au'] = $this->getPrimaryAuthor();
                   break;
                */
                $params['rft.issn'] = $this->getCleanISSN();

                // Including a date in a title-level Journal OpenURL may be too
                // limiting -- in some link resolvers, it may cause the exclusion
                // of databases if they do not cover the exact date provided!
                unset($params['rft.date']);

                // If we're working with the SFX resolver, we should add a
                // special parameter to ensure that electronic holdings links
                // are shown even though no specific date or issue is specified:
                if (isset($configArray['OpenURL']['resolver'])
                    && strtolower($configArray['OpenURL']['resolver']) == 'sfx'
                ) {
                    $params['sfx.ignore_date_threshold'] = 1;
                }
                break;
            case 'Article':
                $params['rft.issn'] = $this->getCleanISSN();
                $params['rft.genre'] = 'article';
                $params['rft.atitle'] = $params['rft.title'];
                //unset($params['rft.date']);
                $articleFields = $this->getArticleFieldedReference();
                if ($articleFields['volume']) $params['rft.volume'] = $articleFields['volume'];
                if ($articleFields['issue']) $params['rft.issue'] = $articleFields['issue'];
                if ($articleFields['spage']) $params['rft.spage'] = $articleFields['spage'];
                if ($articleFields['epage']) $params['rft.epage'] = $articleFields['epage'];
                if ($articleFields['date']) $params['rft.date'] = $articleFields['date'];
                unset($params['rft.title']);
                /*
                if (isset($configArray['OpenURL']['resolver']) &&
                    strtolower($configArray['OpenURL']['resolver']) == 'sfx') {
                    $params['sfx.ignore_date_threshold'] = 1;
                }*/
                break;
            default:
                $params['rft_val_fmt'] = 'info:ofi/fmt:kev:mtx:dc';
                $params['rft.creator'] = $this->getPrimaryAuthor();
                $publishers = $this->getPublishers();
                if (count($publishers) > 0) {
                    $params['rft.pub'] = $publishers[0];
                }
                $params['rft.format'] = $format;
                $langs = $this->getLanguages();
                if (count($langs) > 0) {
                    $params['rft.language'] = $langs[0];
                }
                break;
            }
            /**.
            http://sfx.gbv.de:9004/sfx_tuhh?.
            ctx_enc=info%3Aofi%2Fenc%3AUTF-8&.
            ctx_id=10_1&.
            ctx_tim=2011-03-28T15%3A10%3A47CEST&.
            ctx_ver=Z39.88-2004&.
            rfr_id=info%3Asid%2Fsfxit.com%3Acitation&.
            rft.atitle=When+Johnny+comes+marching+home&.
            rft.epage=136&.
            rft.genre=article&.
            rft.issn=0028-0836&.
            rft.issue=7200&.
            rft.jtitle=Nature&.
            rft.spage=136&.
            rft.volume=454&.
            rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Aarticle&.
            sfx.title_search=exact&.
            url_ctx_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Actx&.
            url_ver=Z39.88-2004.

            GBV Central:
            953 <--><------><------>|d 332  |j 2011  |e 6035  |b 10  |c 6  |h 1256-1259  |g 3..
            d = Volume
            j = Jahr
            e = issue
            h = Seitenangabe Von Bis
            */

            // Assemble the URL:
            $parts = array();
            foreach ($params as $key => $value) {
                if (is_array($value) === true) {
                    $parts[] = $key . '=' . urlencode($value[0]);
                }
                else {
                    $parts[] = $key . '=' . urlencode($value);
                }
            }
            return implode('&', $parts);
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

        $vs = $this->marcRecord->getFields('954');
        if ($vs) {
            foreach($vs as $v) {
                // is this ours?
                $libArr = $v->getSubfields('a');
                $lib = $libArr[0]->getData();
                if ($lib === '23') {
                    $v_signatures = $v->getSubfields('d');
                    $v_remarks = $v->getSubfields('k');
                    $v_names = $v->getSubfields('g');

                    // check if there is a first signature (Freihandsignatur)
                    if (count($v_signatures) > 1) {
                        $signature = $v_signatures[1]->getData();
                    } else if (is_object($v_signatures[0]) === true ){
                        $signature = $v_signatures[0]->getData();
                    }
                    else {
                        $signature = 1;
                    }
                    $retVal[$signature] = array();
                    $retVal[$signature]['volume'] = '0';
                    if (count($v_names) > 0) {
                        $retVal[$signature]['volume'] = $v_names[0]->getData();
                    }
                    if (count($v_remarks) > 0) {
                        $retVal[$signature]['remark'] = $v_remarks[0]->getData();
                    }
                }
            }
        }
        return $retVal;
    }

    /**
     * Assign necessary Smarty variables and return a template name to
     * load in order to display multipart metadata (more details beyond
     * what is found in getCoreMetadata() -- used as the contents of the
     * Description tab of the record view).
     *
     * @return string Name of Smarty template file to display.
     * @access public
     */
    public function getMultipartMetadata()
    {
        global $interface;

        // Assign various values for display by the template; we'll prefix
        // everything with "extended" to avoid clashes with values assigned
        // Assign data for displaying values by finc project 2012-01-20
        $interface->assign('multipartLink', $this->getMultipartLink());
        $interface->assign('multipartChildren', $this->getMultipartChildren());

        return 'RecordDrivers/Index/multipart.tpl';
    }

    /**
     * Assign necessary Smarty variables and return a template name to
     * load in order to display articles metadata (more details beyond
     * what is found in getCoreMetadata() -- used as the contents of the
     * Description tab of the record view).
     *
     * @return string Name of Smarty template file to display.
     * @access public
     */
    public function getArticlesMetadata()
    {
        global $interface;

        $interface->assign('articlesChildren', $this->getArticleChildren());

        return 'RecordDrivers/GBVCentral/articles.tpl';
    }

    // --- Start finc defintion for displaying dependent volumes --- //
    /**
     * Get multipart link.
     *
     * @return string
     * @access protected
     */

    protected $hiddenFilters = array();

    protected function setHiddenFilters()
    {
        $searchSettings = getExtraConfigArray('searches');

        if (isset($searchSettings['HiddenFilters'])) {
            foreach ($searchSettings['HiddenFilters'] as $field => $subfields) {
                $this->addHiddenFilter($field.':'.'"'.$subfields.'"');
            }
        }
        if (isset($searchSettings['RawHiddenFilters'])) {
            foreach ($searchSettings['RawHiddenFilters'] as $rawFilter) {
                $this->addHiddenFilter($rawFilter);
            }
        }
    }

    public function addHiddenFilter($fq)
    {
        $this->hiddenFilters[] = $fq;
    }

    protected function getMultipartLink()
    {
        $retval=array();

        if (isset($this->fields['multipart_link']) && !empty($this->fields['multipart_link'])) {
            $rid=$this->fields['multipart_link'];
            if(is_string($rid) && strlen($rid)<2) {
                return array();
            }
            $rid=str_replace(":","\:",$rid);
            $index = $this->getIndexEngine();

            // Assemble the query parts and filter out current record:
            $query = "(record_id:".$rid[0].")";

            // Perform the search and return either results or an error:
            $this->setHiddenFilters();
            $result = $index->search($query, null, $this->hiddenFilters, 0, null, null, '', null, null, 'id',  HTTP_REQUEST_METHOD_POST , false, false);

            if (PEAR::isError($result)) {
                return $result;
            }
            if (isset($result['response']['docs'])
                && !empty($result['response']['docs'])
                ) {
                // return $result['response']['docs'][0]['id'];
                foreach($result['response']['docs'] as $key => $doc) {
                    $retval[]=$doc['id'];
                }
            }
        }
        return $retval;
    }

    /**
     * Get multipart parent.
     *
     * @return array
     * @access protected
     */
    protected function getMultipartParent()
    {
        if (!(isset($this->fields['ppnlink'])) || $this->fields['ppnlink'] == null) {
            return array();
        }
        $mpid = $this->fields['ppnlink'];
        $query="";
        foreach($mpid as $mp) {
            if(strlen($mp)<2) continue;
            $mp=str_replace(":","\:",$mp);
            if(strlen($query)>0) $query.=" OR ";
            $query.= "id:".$mp;
        }

        // echo "<pre>".$query."</pre>";

        $index = $this->getIndexEngine();

        // Perform the search and return either results or an error:
        $this->setHiddenFilters();
        $result = $index->search($query, null, $this->hiddenFilters, 0, null, null, '', null, null, 'title, id',  HTTP_REQUEST_METHOD_POST , false, false);

        if (PEAR::isError($result)) {
            return $result;
        }

        if (isset($result['response']['docs'])
            && !empty($result['response']['docs'])
            ) {
            $cnt=0;
            foreach($result['response']['docs'] as $doc) {
                $retval[$cnt]['title']=$doc['title'];
                $retval[$cnt]['id']=$doc['id'];
                $cnt++;
            }
            // sort array for key 'part'
            return $retval = $this->_sortArray($retval,'title','asort');
        }
        return array();
    }

    /**
     * Get multipart children.
     *
     * @return array
     * @access protected
     */
    protected function getMultipartChildren()
    {
        $cnt=0;
        $retval = array();
        $sort = array();
        $result = $this->getTomes();
        foreach($result as $doc) {
            $part = $doc['volume'];
            $retval[$cnt]['title']=$doc['title_full'][0];
            $retval[$cnt]['id']=$doc['id'];
            $retval[$cnt]['date'] = preg_replace("/[^0-9]/","", $doc['publishDate'][0]);
            $retval[$cnt]['part'] = $part;
            $retval[$cnt]['partNum'] = preg_replace("/[^0-9]/","", $part);
            $cnt++;
        }
        foreach ($retval as $key => $row) {
            $part1[$key] = (isset($row['partNum'])) ? $row['partNum'] : 0;
            $part2[$key] = (isset($row['date'])) ? $row['date'] : 0;
        }
        array_multisort($part2, SORT_DESC, $part1, SORT_DESC, $retval );
        return $retval;
    }

    /**
     * Get article children.
     *
     * @return array
     * @access protected
     */
    protected function getArticleChildren()
    {
        $cnt=0;
        $retval = array();
        $sort = array();
        $result = $this->getArticles();
        foreach($result as $doc) {
            $retval[$cnt]['title']=$doc['title_full'][0];
            $retval[$cnt]['id']=$doc['id'];
            $retval[$cnt]['date']=$doc['publishDate'][0];
            $retval[$cnt]['volume'] = $doc['volume'];
            $retval[$cnt]['issue'] = $doc['issue'];
            $retval[$cnt]['pages'] = $doc['pages'];
            $cnt++;
        }
        foreach ($retval as $key => $row) {
            $part1[$key] = (isset($row['date'])) ? $row['date'] : 0;
            $part2[$key] = (isset($row['title'])) ? $row['title'] : 0;
        }
        array_multisort($part1, SORT_DESC, $part2, SORT_ASC, $retval );
        return $retval;
    }

    /**
     * Check if at least one multipart child exists.
     * Method to keep performance lean in core.tpl.
     *
     * @return bool
     * @access protected
     */
    public function searchMultipart()
    {
        $rid=$this->fields['id'];
        if(strlen($rid)<2) {
            return array();
        }
        $rid=str_replace(":","\:",$rid);
        $index = $this->getIndexEngine();

        // Assemble the query parts and filter out current record:
        $query = "(ppnlink:".$rid." AND NOT format:Article)";

        // Perform the search and return either results or an error:
        $this->setHiddenFilters();

        $result = $index->search($query, null, $this->hiddenFilters, 0, 1000, null, '', null, null, '',  HTTP_REQUEST_METHOD_POST , false, false);

        return ($result['response'] > 0) ? $result['response'] : false;
    }

    /**
     * Check if at least one article for this item exists.
     * Method to keep performance lean in core.tpl.
     *
     * @return bool
     * @access protected
     */
    public function searchArticles()
    {
        $rid=$this->fields['id'];
        if(strlen($rid)<2) {
            return array();
        }
        $rid=str_replace(":","\:",$rid);
        $index = $this->getIndexEngine();

        // Assemble the query parts and filter out current record:
        $query = "(ppnlink:".$rid." AND format:Article)";

        // Perform the search and return either results or an error:
        $this->setHiddenFilters();

        $result = $index->search($query, null, $this->hiddenFilters, 0, 1000, null, '', null, null, '',  HTTP_REQUEST_METHOD_POST , false, false);

        return ($result['response'] > 0) ? $result['response'] : false;
    }

    /**
     * Check if at least one article for this item exists.
     * Method to keep performance lean in core.tpl.
     *
     * @return bool
     * @access protected
     */
    public function hasArticles()
    {
        $rid=$this->fields['id'];
        if(strlen($rid)<2) {
            return array();
        }
        $rid=str_replace(":","\:",$rid);
        $index = $this->getIndexEngine();

        // Assemble the query parts and filter out current record:
        $query = "(ppnlink:".$rid." AND format:Article)";

        // Perform the search and return either results or an error:
        $this->setHiddenFilters();

        $result = $index->search($query, null, $this->hiddenFilters, 0, 1, null, '', null, null, 'id',  HTTP_REQUEST_METHOD_POST , false, false);

        return ($result['response']['numFound'] > 0) ? true : false;
    }

    public function isMultipartChildren()
    {
        $rid=$this->fields['id'];
        if(strlen($rid)<2) {
            return array();
        }
        $rid=str_replace(":","\:",$rid);
        $index = $this->getIndexEngine();

        // Assemble the query parts and filter out current record:
        $query = "(ppnlink:".$rid." AND NOT format:Article)";

        // Perform the search and return either results or an error:
        $this->setHiddenFilters();

        $result = $index->search($query, null, $this->hiddenFilters, 0, 1, null, '', null, null, 'id',  HTTP_REQUEST_METHOD_POST , false, false);

        return ($result['response']['numFound'] > 0) ? true : false;
    }

    public function searchMultipartChildren()
    {
        $result = $this->searchMultipart();

        return ($result['docs'] > 0) ? $result['docs'] : false;
    }

    public function searchArticleChildren()
    {
        $result = $this->searchArticles();

        return ($result['docs'] > 0) ? $result['docs'] : false;
    }

    /**
     * Get an array of information about record holdings, obtained in real-time
     * from the ILS.
     *
     * @return array
     * @access protected
     */
    protected function getTomes()
    {
        global $configArray, $interface;
        /*
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
                #$onlyTopLevel = 1;
                $interface->assign('showAssociated', '3');
                break;
        }
        }
        if ($onlyTopLevel === 1) {
            // only look for the parent of this record, all other associated publications can be ignored
            $vs = $this->marcRecord->getFields('773');
            if ($vs) {
                foreach($vs as $v) {
                    $a_names = $v->getSubfields('w');
                    if (count($a_names) > 0) {
                        $idArr = explode(')', $a_names[0]->getData());
                        $parentId = $idArr[1];
                    }
                    $v_names = $v->getSubfields('v');
                    if (count($v_names) > 0) {
                        $volNumber = $v_names[0]->getData();
                    }
                }
            }
            if (!$parentId) {
                $vs = $this->marcRecord->getFields('830');
                if ($vs) {
                    foreach($vs as $v) {
                        $a_names = $v->getSubfields('w');
                        if (count($a_names) > 0) {
                            $idArr = explode(')', $a_names[0]->getData());
                            if ($idArr[0] === '(DE-601') {
                                $parentId = $idArr[1];
                            }
                        }
                        $v_names = $v->getSubfields('v');
                        if (count($v_names) > 0) {
                            $volNumber = $v_names[0]->getData();
                        }
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
                                    $parentId = $idArr[1];
                                }
                            }
                            $v_names = $v->getSubfields('v');
                            if (count($v_names) > 0) {
                                $volNumber = $v_names[0]->getData();
                            }
                        }
                    }
                }
            }
            $subr = $this->db->getRecord($parentId);
            $subrecord = array('id' => $parentId);
            $subrecord['number'] = $volNumber;
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
            if (!$parentId) {
                $interface->assign('showAssociated', '0');
            }
            $subrecords[] = $subrecord;
            //print_r($subrecord);
            $interface->assign('parentRecord', $subrecord);
            return $subrecords;
        }
        */

        // Get Holdings Data
        $id = $this->getUniqueID();
        #$catalog = ConnectionManager::connectToCatalog();
        #if ($catalog && $catalog->status) {
            #$result = $this->db->getRecordsByPPNLink($id);
            $result = $this->searchMultipartChildren();
            #$result = $catalog->getJournalHoldings($id);
            if (PEAR::isError($result)) {
                PEAR::raiseError($result);
            }

            // Retrieve the record from the index
            foreach ($result as $subId) {
                $subrecord = array('id' => $subId['id']);
                $subrecord['title_full'] = array();
                $subrecord['publishDate'] = array();
                if (!$subr) {
                    $subrecord['record_url'] = $record_url.$subId;
                }
                $subrecord['title_full'][] = $subId['title_full'];
                $subrecord['publishDate'][0] = $subId['publishDate'][0];
                $subrecords[] = $subrecord;
            }
            return $subrecords;

                /*if (!($subrecord = $this->db->getRecord($subId))) {
                    $subrecord = array('id' => $subId, 'title_full' => array("Title not found"), 'record_url' => $record_url.$subId);
                }*/
/*
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
                                    $a_names = $v->getSubfields('w');
                                    if (count($a_names) > 0) {
                                        $idArr = explode(')', $a_names[0]->getData());
                                        if ($idArr[0] === '(DE-601') {
                                            $parId = $idArr[1];
                                        }
                                    }
                                    $v_names = $v->getSubfields('v');
                                    if (count($v_names) > 0 && $parId === $id) {
                                        $subrecord['volume'] = $v_names[0]->getData();
                                        $thisHasBeenSet = 1;
                                    }
                                }
                            }
                            if ($thisHasBeenSet === 0) {
                                $vs = $marcRecord->getFields('830');
                                if ($vs) {
                                    foreach($vs as $v) {
                                        $a_names = $v->getSubfields('w');
                                        if (count($a_names) > 0) {
                                            $idArr = explode(')', $a_names[0]->getData());
                                            if ($idArr[0] === '(DE-601') {
                                                $parId = $idArr[1];
                                            }
                                        }
                                        $v_names = $v->getSubfields('v');
                                        if (count($v_names) > 0 && $parId === $id) {
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
                                    $a_names = $v->getSubfields('w');
                                    if (count($a_names) > 0) {
                                        $idArr = explode(')', $a_names[0]->getData());
                                        if ($idArr[0] === '(DE-601') {
                                            $parId = $idArr[1];
                                        }
                                    }
                                    $v_names = $v->getSubfields('v');
                                    if (count($v_names) > 0 && $parId === $id) {
                                        $subrecord['volume'] = $v_names[0]->getData();
                                        $thisHasBeenSet = 1;
                                    }
                                }
                            }
                            if ($thisHasBeenSet === 0) {
                                $vs = $marcRecord->getFields('830');
                                if ($vs) {
                                    foreach($vs as $v) {
                                        $a_names = $v->getSubfields('w');
                                        if (count($a_names) > 0) {
                                            $idArr = explode(')', $a_names[0]->getData());
                                            if ($idArr[0] === '(DE-601') {
                                                $parId = $idArr[1];
                                            }
                                        }
                                        $v_names = $v->getSubfields('v');
                                        if (count($v_names) > 0 && $parId === $id) {
                                            $subrecord['volume'] = $v_names[0]->getData();
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
                                        $subrecord['publishDate'][0] = " ".$a_names[0]->getData();
                                    }
                                }
                            }
*/
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
/*
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
                                    $a_names = $v->getSubfields('w');
                                    if (count($a_names) > 0) {
                                        $idArr = explode(')', $a_names[0]->getData());
                                        if ($idArr[0] === '(DE-601') {
                                            $parId = $idArr[1];
                                        }
                                    }
                                    $v_names = $v->getSubfields('v');
                                    if (count($v_names) > 0 && $parId === $id) {
                                        $subrecord['volume'] = $v_names[0]->getData();
                                        $thisHasBeenSet = 1;
                                    }
                                }
                            }
                            if ($thisHasBeenSet === 0) {
                                $vs = $marcRecord->getFields('830');
                                if ($vs) {
                                    foreach($vs as $v) {
                                        $a_names = $v->getSubfields('w');
                                        if (count($a_names) > 0) {
                                            $idArr = explode(')', $a_names[0]->getData());
                                            if ($idArr[0] === '(DE-601') {
                                                $parId = $idArr[1];
                                            }
                                        }
                                        $v_names = $v->getSubfields('v');
                                        if (count($v_names) > 0 && $parId === $id) {
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
                                    $a_names = $v->getSubfields('w');
                                    if (count($a_names) > 0) {
                                        $idArr = explode(')', $a_names[0]->getData());
                                        if ($idArr[0] === '(DE-601') {
                                            $parId = $idArr[1];
                                        }
                                    }
                                    $v_names = $v->getSubfields('v');
                                    if (count($v_names) > 0 && $parId === $id) {
                                        $subrecord['volume'] = $v_names[0]->getData();
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
*/
                                /*
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
                                */
/*
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
                    $subrecord['title_full'][] = 'Title not found';
                }

                $subrecords[] = $subrecord;
            }
            #print_r($subrecords);
            return $subrecords;
        #}
*/
    }

    /**
     * Get an array of information about record holdings, obtained in real-time
     * from the ILS.
     *
     * @return array
     * @access protected
     */
    protected function getArticles()
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
                #$onlyTopLevel = 1;
                $interface->assign('showAssociated', '3');
                break;
        }
        }
        if ($onlyTopLevel === 1) {
            // only look for the parent of this record, all other associated publications can be ignored
            $vs = $this->marcRecord->getFields('773');
            if ($vs) {
                foreach($vs as $v) {
                    $a_names = $v->getSubfields('w');
                    if (count($a_names) > 0) {
                        $idArr = explode(')', $a_names[0]->getData());
                        $parentId = $idArr[1];
                    }
                    $v_names = $v->getSubfields('v');
                    if (count($v_names) > 0) {
                        $volNumber = $v_names[0]->getData();
                    }
                }
            }
            if (!$parentId) {
                $vs = $this->marcRecord->getFields('830');
                if ($vs) {
                    foreach($vs as $v) {
                        $a_names = $v->getSubfields('w');
                        if (count($a_names) > 0) {
                            $idArr = explode(')', $a_names[0]->getData());
                            if ($idArr[0] === '(DE-601') {
                                $parentId = $idArr[1];
                            }
                        }
                        $v_names = $v->getSubfields('v');
                        if (count($v_names) > 0) {
                            $volNumber = $v_names[0]->getData();
                        }
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
                                    $parentId = $idArr[1];
                                }
                            }
                            $v_names = $v->getSubfields('v');
                            if (count($v_names) > 0) {
                                $volNumber = $v_names[0]->getData();
                            }
                        }
                    }
                }
            }
            $subr = $this->db->getRecord($parentId);
            $subrecord = array('id' => $parentId);
            $subrecord['number'] = $volNumber;
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
            if (!$parentId) {
                $interface->assign('showAssociated', '0');
            }
            $subrecords[] = $subrecord;
            $interface->assign('parentRecord', $subrecord);
            return $subrecords;
        }
        // Get Holdings Data
        $id = $this->getUniqueID();
        #$catalog = ConnectionManager::connectToCatalog();
        #if ($catalog && $catalog->status) {
            #$result = $this->db->getRecordsByPPNLink($id);
            $result = $this->searchArticleChildren();
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
                            $vs = $marcRecord->getFields('260');
                            if ($vs) {
                                foreach($vs as $v) {
                                    $a_names = $v->getSubfields('c');
                                    if (count($a_names) > 0) {
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
                                /*
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
                                */
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
                $afr = $marcRecord->getFields('953');
                if ($afr) {
                    foreach($afr as $articlefieldedref) {
                        $a_names = $articlefieldedref->getSubfields('d');
                        if (count($a_names) > 0) {
                            $subrecord['volume'] = $a_names[0]->getData();
                        }
                        $e_names = $articlefieldedref->getSubfields('e');
                        if (count($e_names) > 0) {
                            $subrecord['issue'] = $e_names[0]->getData();
                        }
                        $h_names = $articlefieldedref->getSubfields('h');
                        if (count($h_names) > 0) {
                            $subrecord['pages'] = $h_names[0]->getData();
                        }
                    }
                }
                if (count($subrecord['title_full']) === 0) {
                    $subrecord['title_full'][] = 'Title not found';
                }

                $subrecords[] = $subrecord;
            }
            #print_r($subrecords);
            return $subrecords;
        #}
    }

    /**
     * TUBHH Enhancement for GBV Discovery
     * Return the reference of one article
     * An array will be returned with keys=volume, issue, startpage [spage], endpage [epage] and publication year [date].
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
        $fields = $this->subrecord->getFields($field);
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
            $vs = $this->marcRecord->getFields('773');
            if ($vs) {
                foreach($vs as $v) {
                    $a_names = $v->getSubfields('w');
                    if (count($a_names) > 0) {
                        $idArr = explode(')', $a_names[0]->getData());
                        $parentId = $idArr[1];
                    }
                    $v_names = $v->getSubfields('v');
                    if (count($v_names) > 0) {
                        $volNumber = $v_names[0]->getData();
                    }
                }
            }
            if (!$parentId) {
                $vs = $this->marcRecord->getFields('830');
                if ($vs) {
                    foreach($vs as $v) {
                        $a_names = $v->getSubfields('w');
                        if (count($a_names) > 0) {
                            $idArr = explode(')', $a_names[0]->getData());
                            if ($idArr[0] === '(DE-601') {
                                $parentId = $idArr[1];
                            }
                        }
                        $v_names = $v->getSubfields('v');
                        if (count($v_names) > 0) {
                            $volNumber = $v_names[0]->getData();
                        }
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
                                    $parentId = $idArr[1];
                                }
                            }
                            $v_names = $v->getSubfields('v');
                            if (count($v_names) > 0) {
                                $volNumber = $v_names[0]->getData();
                            }
                        }
                    }
                }
            }
            $subr = $this->db->getRecord($parentId);
            $subrecord = array('id' => $parentId);
            $subrecord['number'] = $volNumber;
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
            if (!$parentId) {
                $interface->assign('showAssociated', '0');
            }
            $subrecords[] = $subrecord;
            $interface->assign('parentRecord', $subrecords);
            return $subrecords;
        }
        // Get Holdings Data
        $id = $this->getUniqueID();
        #$catalog = ConnectionManager::connectToCatalog();
        #if ($catalog && $catalog->status) {
            #$result = $this->db->getRecordsByPPNLink($id);
            $result = $this->searchArticleChildren();
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
                    $subrecord['title_full'][] = 'Title not found';
                }

                $subrecords[] = $subrecord;
            }
            return $subrecords;
        #}
    }

    private function _normalize($field) {
        #if (is_array($field)) {
        #    array_walk($field, '_callback_normalize');
        #}
        if (function_exists('normalizer_normalize') && normalizer_normalize($field)) {
            $return = normalizer_normalize($field);
        }
        else {
            $return = $field;
        }
        return $return;
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
        $configPica = parse_ini_file('conf/GBVCentral.ini', true);
        $isil = $configPica['isil'];

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
                                if ($lib === $isil) {
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
}