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
    protected $remarks;

    public function getSearchResult($view = 'list')
    {
        global $interface;
        parent::getSearchResult($view);

        $interface->assign('nlurls', false);
        if (in_array('NL', $this->getCollections())) {
            $interface->assign('nlurls', $this->getURLs());
        }

        //$interface->assign('summRemarks', $this->getRemark());

        if (in_array('Journal', $this->getFormats()) || in_array('eJournal', $this->getFormats())) {
            $interface->assign('summDateSpan', $this->getDateSpan());
        }

        $interface->assign('summInterlibraryLoan', $this->checkInterlibraryLoan());
        $interface->assign('summArticleHRef', $this->_normalize($this->getArticleHReference()));

        $interface->assign('summSeries', $this->_normalize($this->getSeriesShort()));
        $interface->assign('summFullTitle', $this->_normalize($this->getFullTitle()));
        $interface->assign('summAddTitle', $this->_normalize($this->getTitleAddition()));

        $interface->assign('volumename', $this->getVolumeName($this->marcRecord));

        return 'RecordDrivers/GBVCentral/result.tpl';
    }

    public function getCoreMetadata() {
        global $interface;
        parent::getCoreMetadata();

        if (in_array('NL', $this->getCollections())) {
            $interface->assign('nlurls', $this->getURLs());
        }
        $artHref = $this->getArticleHReference();
        $interface->assign('coreArticleHRef', $artHref);
        $interface->assign('artFieldedRef', $this->getArticleFieldedReference());
        $artFieldedRef = $this->getArticleFieldedReference();
        if ($artHref['hrefId']) {
            $articleVol = $this->searchArticleVolume($artHref['hrefId'], $artFieldedRef);
            $interface->assign('articleVol', $articleVol);
        }
        //$interface->assign('multipartParent', $this->getMultipartParent());
        $interface->assign('isMultipartChildren', $this->isMultipartChildren());
        $interface->assign('hasArticles', $this->hasArticles());
        $this->getSeriesLink();
        $linkNames = $this->getLinkNames();
        $interface->assign('linkNames', $linkNames);
        $interface->assign('thesis', $this->getThesisInformation());
        //$interface->assign('coreAddtitle', $this->getTitleAddition());
        $interface->assign('coreFullTitle', $this->_normalize($this->getFullTitle()));

        $interface->assign('volumename', $this->getVolumeName($this->marcRecord));

        /*
        $interface->assign('articleChildren', $this->getArticleChildren());
        $interface->assign('coreSubseries', $this->getSubseries());
        */

        return 'RecordDrivers/GBVCentral/core.tpl';
    }

    protected function getVolumeName($record) {
        $titleFields = $record->getFields('245');
        $vol = array();
        if ($titleFields) {
            foreach($titleFields as $titleField) {
                $volumeFields = $titleField->getSubfields('p');
                if (count($volumeFields) > 0) {
                    $vol[] = $volumeFields[0]->getData();
                }
            }
        }
        return $vol;
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
        /* ignore field format_se
        if (isset($this->fields['format_se'])) {
            if (is_array($this->fields['format_se']) === false) $this->fields['format_se'] = array($this->fields['format_se']);
            $result = array_merge($result, $this->fields['format_se']);
        }
        */
        if (isset($this->fields['format'])) {
            if (is_array($this->fields['format']) === false) $this->fields['format'] = array($this->fields['format']);
            $result = array_merge($result, $this->fields['format']);
        }
        return $result;
    }

    /**
     * Get an array of all the collections associated with the record.
     *
     * @access  protected
     * @return  array
     */
    protected function getCollections()
    {
        if (isset($this->fields['collection'])) {
            if (is_array($this->fields['collection']) === false) $this->fields['collection'] = array($this->fields['collection']);
        }
        return $this->fields['collection'];
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
        /*
        $subrecords = $this->getRealTimeJournalHoldings();
        $interface->assign('gbvsubrecords', $subrecords);
        */
        // Only display OpenURL link if the option is turned on and we have
        // an ISSN.  We may eventually want to make this rule more flexible,
        // but for now the ISSN restriction is designed to be consistent with
        // the way we display items on the search results list.
        $hasOpenURL = ($this->openURLActive('results'));
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
        if (in_array('eBook', $this->getFormats()) === true || in_array('eJournal', $this->getFormats()) === true || $this->isNLZ() === true) {
            return '0';
        }

        return '1';
    }

    /**
     * Get the reference of the article including its link.
     *
     * @access  protected
     * @return  array
     */
    protected function getArticleHReference()
    {
        $vs = null;
        $vs = $this->marcRecord->getFields('773');
        if (count($vs) > 0) {
            $refs = array();
            foreach($vs as $v) {
                $inRefField = $v->getSubfields('i');
                if (count($inRefField) > 0) {
                    $inRef = $inRefField[0]->getData();
                }
                else {
                    $inRef = "in:";
                }
                $journalRefField = $v->getSubfields('t');
                if (count($journalRefField) > 0) {
                    $journalRef = $journalRefField[0]->getData();
                }
                $articleRefField = $v->getSubfields('g');
                if (count($articleRefField) > 0) {
                    $articleRef = $articleRefField[0]->getData();
                }
                $a_names = $v->getSubfields('w');
                if (count($a_names) > 0) {
                    $idArr = explode(')', $a_names[0]->getData());
                    $hrefId = $this->addNLZ($idArr[1]);
                }
                if ($journalRef || $articleRef) {
                    $refs[] = array('inref' => $inRef, 'jref' => $journalRef, 'aref' => $articleRef, 'hrefId' => $hrefId);
                }
            }
            return $refs;
        }
        return null;
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
        if ($issn == false) {
            $issn = $this->_getFirstFieldValue('773', array('x'));
        }
        if ($issn == false) {
            $issn = $this->_getFirstFieldValue('029', array('a'));
        }
        if ($issn == false) {
            $issn = $this->_getFirstFieldValue('022', array('a'));
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
            'rfr_id' => "info:sid/{$coinsID}:tubfind",
            'rft.title' => $this->getShortTitle(),
            'rft.date' => $pubDate
        );

        $urls = $this->getUrls();
        if ($urls) {
            foreach ($urls as $url => $desc) {
                // check if we have a doi
                if (strstr($url, 'http://dx.doi.org/') !== false) {
                    $params['rft_id'] = 'info:doi/'.substr($url, 18);
                }
            }
        }

        // Add additional parameters based on the format of the record:
        $formats = $this->getFormats();

        // If we have multiple formats, Book and Journal are most important...
        if (in_array('Aufsätze', $formats) || in_array('Elektronische Aufsätze', $formats) || in_array('electronic Article', $formats)) {
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
                 */
                   $params['rft_val_fmt'] = 'info:ofi/fmt:kev:mtx:journal';
                   $params['rft.genre'] = 'journal';
                   $params['rft.jtitle'] = $params['rft.title'];
                   $params['rft.issn'] = $this->getCleanISSN();
                   $params['rft.au'] = $this->getPrimaryAuthor();
//                $params['rft.issn'] = $this->getCleanISSN();

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
                unset($params['rft.date']);
                $params['rft.atitle'] = $params['rft.title'];
                $articleFields = $this->getArticleFieldedReference();
                if ($articleFields['volume']) $params['rft.volume'] = $articleFields['volume'];
                if ($articleFields['issue']) $params['rft.issue'] = $articleFields['issue'];
                if ($articleFields['spage']) $params['rft.spage'] = $articleFields['spage'];
                if ($articleFields['epage']) $params['rft.epage'] = $articleFields['epage'];
                if ($articleFields['date']) $params['rft.date'] = $articleFields['date'];
                $journalTitle = $this->getArticleHReference();
                if ($journalTitle['jref']) $params['rft.jtitle'] = $journalTitle['jref'];
                unset($params['rft.title']);
                /*
                if (isset($configArray['OpenURL']['resolver']) &&
                    strtolower($configArray['OpenURL']['resolver']) == 'sfx') {
                    $params['sfx.ignore_date_threshold'] = 1;
                }*/
                break;
            case 'electronic Resource':
                $params['rft.genre'] = 'book';
                $params['rft.isbn'] = $this->getCleanISBN();
                // Don't stop, take the default parameters also
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

        $configPica = parse_ini_file('conf/GBVCentral.ini', true);
        $iln = $configPica['iln'];

        $vs = $this->marcRecord->getFields('954');
        if ($vs) {
            foreach($vs as $v) {
                // is this ours?
                $libArr = $v->getSubfields('a');
                $lib = $libArr[0]->getData();
                if ($lib === $iln) {
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

        $interface->assign('articleChildren', $this->getArticleChildren());

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
        if (isset($searchSettings['DefaultFilters'])) {
            foreach ($searchSettings['DefaultFilters'] as $defFilter) {
                $this->addHiddenFilter($defFilter);
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
            $result = $index->search($query, null, $this->hiddenFilters, 0, null, null, '', null, null, 'id',  HTTP_REQUEST_METHOD_POST , false, false, false);

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
            $query.= "id:".$this->addNLZ($mp);
        }

        // echo "<pre>".$query."</pre>";

        $index = $this->getIndexEngine();

        // Perform the search and return either results or an error:
        $this->setHiddenFilters();
        $result = $index->search($query, null, $this->hiddenFilters, 0, null, null, '', null, null, 'title, id',  HTTP_REQUEST_METHOD_POST , false, false, false);

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
            $retval[$cnt]['sort']=$doc['sort'];
            $retval[$cnt]['title']=$doc['title_full'][0];
            $retval[$cnt]['id']=$doc['id'];
            $retval[$cnt]['date'] = preg_replace("/[^0-9]/","", $doc['publishDate'][0]);
            $retval[$cnt]['part'] = $part;
            $retval[$cnt]['partNum'] = preg_replace("/[^0-9]/","", $part);
            $cnt++;
        }
        foreach ($retval as $key => $row) {
            $part0[$key] = (isset($row['sort'])) ? $row['sort'] : 0;
            $part1[$key] = (isset($row['partNum'])) ? $row['partNum'] : 0;
            $part2[$key] = (isset($row['date'])) ? $row['date'] : 0;
        }
        array_multisort($part0, SORT_DESC, $part1, SORT_DESC, $part2, SORT_DESC, $retval );
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
            $retval[$cnt]['sort'] = $doc['sort'];
            $cnt++;
        }
        foreach ($retval as $key => $row) {
            $part0[$key] = (isset($row['sort'])) ? $row['sort'] : 0;
            $part1[$key] = (isset($row['date'])) ? $row['date'] : 0;
            $part2[$key] = (isset($row['volume'])) ? $row['volume'] : 0;
            $part3[$key] = (isset($row['issue'])) ? $row['issue'] : 0;
            $part4[$key] = (isset($row['pages'])) ? $row['pages'] : 0;
            $part5[$key] = (isset($row['title'])) ? $row['title'] : 0;
        }
        array_multisort($part0, SORT_DESC, $part1, SORT_DESC, $part2, SORT_DESC, $part3, SORT_DESC, $part4, SORT_DESC, $part5, SORT_ASC, $retval );
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
        $query = '(ppnlink:'.$this->stripNLZ($rid).' AND NOT (format:Article OR format:"electronic Article"))';

        // Perform the search and return either results or an error:
        $this->setHiddenFilters();

        $result = $index->search($query, null, $this->hiddenFilters, 0, 1000, null, '', null, null, '',  HTTP_REQUEST_METHOD_POST , false, false, false);

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
        $query = '(ppnlink:'.$this->stripNLZ($rid).' AND (format:Article OR format:"electronic Article"))';

        // Perform the search and return either results or an error:
        $this->setHiddenFilters();

        $result = $index->search($query, null, $this->hiddenFilters, 0, 1000, null, '', null, null, '',  HTTP_REQUEST_METHOD_POST , false, false, false);

        // Check if the PPNs are from the same origin (either both should have an NLZ-prefix or both should not have it)
        $resultArray = array();
        $resultArray['response'] = array();
        $resultArray['response']['docs'] = array();
        foreach ($result['response']['docs'] as $resp) {
            if (($this->_isNLZ($resp['id']) && $this->_isNLZ($rid)) || (!$this->_isNLZ($resp['id']) && !$this->_isNLZ($rid))) {
                $resultArray['response']['docs'][] = $resp;
            }
        }

        //return ($result['response'] > 0) ? $result['response'] : false;
        return ($resultArray['response'] > 0) ? $resultArray['response'] : false;
    }

    /**
     * Check if at least one article for this item exists.
     * Method to keep performance lean in core.tpl.
     *
     * @return bool
     * @access protected
     */
    public function searchArticleVolume($rid, $fieldref)
    {
        $index = $this->getIndexEngine();

        $queryparts = array();
        $queryparts[] = 'ppnlink:'.$this->stripNLZ($rid);
        if ($fieldref['volume']) {
            $fieldsToSearch .= $fieldref['volume'].'.';
        }
        if ($fieldref['date']) {
            $fieldsToSearch .= $fieldref['date'];
        }
        if ($fieldsToSearch) {
            $queryparts[] = $fieldsToSearch;
        }
        $queryparts[] = '(format:Book OR format:"Serial Volume")';
        // Assemble the query parts and filter out current record:
        $query = implode(" AND ", $queryparts);
        $query = '('.$query.')';
        //$query = '(ppnlink:'.$rid.' AND '.$fieldref.')';

        // Perform the search and return either results or an error:
        $this->setHiddenFilters();

        $result = $index->search($query, null, $this->hiddenFilters, 0, 1000, null, '', null, null, '',  HTTP_REQUEST_METHOD_POST, false, false, false);

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
        $query = '(ppnlink:'.$this->stripNLZ($rid).' AND (format:Article OR format:"electronic Article")';
        //if ($this->isNLZ() === false) $query .= ' AND (NOT id:"NLZ*")';
        $query .= ')';

        // Perform the search and return either results or an error:
        $this->setHiddenFilters();

        $result = $index->search($query, null, $this->hiddenFilters, 0, 1000, null, '', null, null, 'id',  HTTP_REQUEST_METHOD_POST , false, false, false);

        $showRegister = false;
        foreach ($result['response']['docs'] as $resp) {
            // Walk through the results until there is a match, which is added to the result array
            if (($this->_isNLZ($resp['id']) && $this->_isNLZ($rid)) || (!$this->_isNLZ($resp['id']) && !$this->_isNLZ($rid))) {
                $showRegister = true;
                // After one hit is found, its clear that the register card needs to be shown, so leave the loop
                break;
            }
        }

        return $showRegister;
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
        $query = '(ppnlink:'.$this->stripNLZ($rid).' AND NOT (format:Article OR format:"electronic Article"';
        //if ($this->isNLZ() === false) $query .= ' OR id:"NLZ*"';
        $query .= '))';

        // Perform the search and return either results or an error:
        $this->setHiddenFilters();

        $result = $index->search($query, null, $this->hiddenFilters, 0, 1, null, '', null, null, 'id',  HTTP_REQUEST_METHOD_POST , false, false, false);

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
     * Get an array of all series names containing the record.  Array entries may
     * be either the name string, or an associative array with 'name' and 'number'
     * keys.
     *
     * @return array
     * @access protected
     */
    protected function getSeriesShort()
    {
        $matches = array();

        // First check the 440, 800 and 830 fields for series information:
        $primaryFields = array(
            '440' => array('a', 'p'),
            '800' => array('a', 'b', 'c', 'd', 'f', 'p', 'q', 't'),
            '830' => array('a', 'p'));
        $matches = $this->_getSeriesFromMARC($primaryFields);

        return $matches;
    }

    protected function getSeriesLink()
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
        $onlyTopLevel = 1;
        $parentIds = array();
        $volNumber = array();
        if ($onlyTopLevel === 1) {
            // only look for the parent of this record, all other associated publications can be ignored
            $vs = $this->marcRecord->getFields('773');
            if ($vs) {
                foreach($vs as $v) {
                    $a_names = $v->getSubfields('w');
                    if (count($a_names) > 0) {
                        $idArr = explode(')', $a_names[0]->getData());
                        $parentIds[] = $this->addNLZ($idArr[1]);
                    }
                    $v_names = $v->getSubfields('v');
                    if (count($v_names) > 0) {
                        $volNumber[$idArr[1]] = $v_names[0]->getData();
                    }
                }
            }
            if (count($parentIds) === 0) {
                $vs = $this->marcRecord->getFields('830');
                if ($vs) {
                    foreach($vs as $v) {
                        $a_names = $v->getSubfields('w');
                        if (count($a_names) > 0) {
                            $idArr = explode(')', $a_names[0]->getData());
                            if ($idArr[0] === '(DE-601') {
                                $parentIds[] = $idArr[1];
                            }
                        }
                        $v_names = $v->getSubfields('v');
                        if (count($v_names) > 0) {
                            $volNumber[$idArr[1]] = $v_names[0]->getData();
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
                                    $parentIds[] = $idArr[1];
                                }
                            }
                            $v_names = $v->getSubfields('v');
                            if (count($v_names) > 0) {
                                $volNumber[$idArr[1]] = $v_names[0]->getData();
                            }
                        }
                    }
                }
            }
            foreach ($parentIds as $parentId) {
                $subr = $this->db->getRecord($parentId);
                $subrecord = array('id' => $parentId);
                $subrecord['number'] = $volNumber[$parentId];
                $subrecord['title_full'] = array();
                if (!$subr) {
                    $subrecord['record_url'] = $record_url.$parentId;
                }
                $m = trim($subr['fullrecord']);
                // check if we are dealing with MARCXML
                $xmlHead = '<?xml version';
                if (strcasecmp(substr($m, 0, strlen($xmlHead)), $xmlHead) === 0) {
                    $m = new File_MARCXML($m, File_MARCXML::SOURCE_STRING);
                } else {
                    $m = preg_replace('/#31;/', "\x1F", $m);
                    $m = preg_replace('/#30;/', "\x1E", $m);
                    $m = new File_MARC($m, File_MARC::SOURCE_STRING);
                }
                $marcRecord = $m->next();
                if (is_a($marcRecord, 'File_MARC_Record') === true || is_a($marcRecord, 'File_MARCXML_Record') === true) {
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
            if (count($parentIds) === 0) {
                $interface->assign('showAssociated', '0');
            }
            //print_r($subrecord);
            $interface->assign('parentRecord', $subrecords);
            return $subrecords;
        }
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
            // check if we are dealing with MARCXML
            $xmlHead = '<?xml version';
            if (strcasecmp(substr($m, 0, strlen($xmlHead)), $xmlHead) === 0) {
                $m = new File_MARCXML($m, File_MARCXML::SOURCE_STRING);
            } else {
                $m = preg_replace('/#31;/', "\x1F", $m);
                $m = preg_replace('/#30;/', "\x1E", $m);
                $m = new File_MARC($m, File_MARC::SOURCE_STRING);
            }
            $marcRecord = $m->next();
            if (is_a($marcRecord, 'File_MARC_Record') === true || is_a($marcRecord, 'File_MARCXML_Record') === true) {
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
                // check if we are dealing with MARCXML
                $xmlHead = '<?xml version';
                if (strcasecmp(substr($m, 0, strlen($xmlHead)), $xmlHead) === 0) {
                    $m = new File_MARCXML($m, File_MARCXML::SOURCE_STRING);
                } else {
                    $m = preg_replace('/#31;/', "\x1F", $m);
                    $m = preg_replace('/#30;/', "\x1E", $m);
                    $m = new File_MARC($m, File_MARC::SOURCE_STRING);
                }
                $marcRecord = $m->next();
                if (is_a($marcRecord, 'File_MARC_Record') === true || is_a($marcRecord, 'File_MARCXML_Record') === true) {
                // 800$t$v -> 773$q -> 830$v -> 245$a$b -> "Title not found"
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
                                        $e_names = $v->getSubfields('9');
                                        if (count($e_names) > 0 && $parId === $id) {
                                            $subrecord['sort'] = $e_names[0]->getData();
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
                                        $e_names = $v->getSubfields('9');
                                        if (count($e_names) > 0 && $parId === $id) {
                                            $subrecord['sort'] = $e_names[0]->getData();
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
                                        $e_names = $v->getSubfields('9');
                                        if (count($e_names) > 0 && $parId === $id) {
                                            $subrecord['sort'] = $e_names[0]->getData();
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
                                    $e_names = $v->getSubfields('9');
                                    if (count($e_names) > 0 && $parId === $id) {
                                        $subrecord['sort'] = $e_names[0]->getData();
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
                if (count($subrecord['title_full']) === 0) {
                    $subrecord['title_full'][] = '';
                }

                $subrecords[] = $subrecord;
            }
            //print_r($subrecords);
            return $subrecords;
        #}
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
                        if (count($v_names) > 0 && $parentId === $id) {
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
                            if (count($v_names) > 0 && $parentId === $id) {
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
            // check if we are dealing with MARCXML
            $xmlHead = '<?xml version';
            if (strcasecmp(substr($m, 0, strlen($xmlHead)), $xmlHead) === 0) {
                $m = new File_MARCXML($m, File_MARCXML::SOURCE_STRING);
            } else {
                $m = preg_replace('/#31;/', "\x1F", $m);
                $m = preg_replace('/#30;/', "\x1E", $m);
                $m = new File_MARC($m, File_MARC::SOURCE_STRING);
            }
            $marcRecord = $m->next();
            if (is_a($marcRecord, 'File_MARC_Record') === true || is_a($marcRecord, 'File_MARCXML_Record') === true) {
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
                // check if we are dealing with MARCXML
                $xmlHead = '<?xml version';
                if (strcasecmp(substr($m, 0, strlen($xmlHead)), $xmlHead) === 0) {
                    $m = new File_MARCXML($m, File_MARCXML::SOURCE_STRING);
                } else {
                    $m = preg_replace('/#31;/', "\x1F", $m);
                    $m = preg_replace('/#30;/', "\x1E", $m);
                    $m = new File_MARC($m, File_MARC::SOURCE_STRING);
                }
                $marcRecord = $m->next();
                if (is_a($marcRecord, 'File_MARC_Record') === true || is_a($marcRecord, 'File_MARCXML_Record') === true) {
                // 800$t$v -> 773$q -> 830$v -> 245$a$b -> "Title not found"
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
                                            $parentId = $idArr[1];
                                        }
                                    }
                                    $v_names = $v->getSubfields('v');
                                    if (count($v_names) > 0 && $parentId === $id) {
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
                                                $parentId = $idArr[1];
                                            }
                                        }
                                        $v_names = $v->getSubfields('v');
                                        if (count($v_names) > 0 && $parentId === $id) {
                                            $subrecord['volume'] = $v_names[0]->getData();
                                        }
                                        $e_names = $v->getSubfields('9');
                                        if (count($e_names) > 0 && $parentId === $id) {
                                            $subrecord['sort'] = $e_names[0]->getData();
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
                                            $parentId = $idArr[1];
                                        }
                                    }
                                    $v_names = $v->getSubfields('v');
                                    if (count($v_names) > 0 && $parentId === $id) {
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
                                                $parentId = $idArr[1];
                                            }
                                        }
                                        $v_names = $v->getSubfields('v');
                                        if (count($v_names) > 0 && $parentId === $id) {
                                            $subrecord['volume'] = $v_names[0]->getData();
                                        }
                                        $e_names = $v->getSubfields('9');
                                        if (count($e_names) > 0 && $parentId === $id) {
                                            $subrecord['sort'] = $e_names[0]->getData();
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
                                    $a_names = $v->getSubfields('w');
                                    if (count($a_names) > 0) {
                                        $idArr = explode(')', $a_names[0]->getData());
                                        if ($idArr[0] === '(DE-601') {
                                            $parentId = $idArr[1];
                                        }
                                    }
                                    $v_names = $v->getSubfields('v');
                                    if (count($v_names) > 0 && $parentId === $id) {
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
                                                $parentId = $idArr[1];
                                            }
                                        }
                                        $v_names = $v->getSubfields('v');
                                        if (count($v_names) > 0 && $parentId === $id) {
                                            $subrecord['volume'] = $v_names[0]->getData();
                                        }
                                        $e_names = $v->getSubfields('9');
                                        if (count($e_names) > 0 && $parentId === $id) {
                                            $subrecord['sort'] = $e_names[0]->getData();
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
                                            $parentId = $idArr[1];
                                        }
                                    }
                                    $v_names = $v->getSubfields('v');
                                    if (count($v_names) > 0 && $parentId === $id) {
                                        $subrecord['volume'] = $v_names[0]->getData();
                                    }
                                    $e_names = $v->getSubfields('9');
                                    if (count($e_names) > 0 && $parentId === $id) {
                                        $subrecord['sort'] = $e_names[0]->getData();
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
                $afr = $marcRecord->getFields('952');
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
                        $j_names = $articlefieldedref->getSubfields('j');
                        if (count($j_names) > 0) {
                            $subrecord['publishDate'][] = $j_names[0]->getData();
                        }
                    }
                }
                if (count($subrecord['title_full']) === 0) {
                    $subrecord['title_full'][] = '';
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
        return $this->_getFirstFieldValue('952', array('d'));
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
        return $this->_getFirstFieldValue('952', array('e'));
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
        return $this->_getFirstFieldValue('952', array('h'));
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
        return $this->_getFirstFieldValue('952', array('j'));
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
            // check if we are dealing with MARCXML
            $xmlHead = '<?xml version';
            if (strcasecmp(substr($m, 0, strlen($xmlHead)), $xmlHead) === 0) {
                $m = new File_MARCXML($m, File_MARCXML::SOURCE_STRING);
            } else {
                $m = preg_replace('/#31;/', "\x1F", $m);
                $m = preg_replace('/#30;/', "\x1E", $m);
                $m = new File_MARC($m, File_MARC::SOURCE_STRING);
            }
            $marcRecord = $m->next();
            if (is_a($marcRecord, 'File_MARC_Record') === true || is_a($marcRecord, 'File_MARCXML_Record') === true) {
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
                // check if we are dealing with MARCXML
                $xmlHead = '<?xml version';
                if (strcasecmp(substr($m, 0, strlen($xmlHead)), $xmlHead) === 0) {
                    $m = new File_MARCXML($m, File_MARCXML::SOURCE_STRING);
                } else {
                    $m = preg_replace('/#31;/', "\x1F", $m);
                    $m = preg_replace('/#30;/', "\x1E", $m);
                    $m = new File_MARC($m, File_MARC::SOURCE_STRING);
                }
                $marcRecord = $m->next();
                if (is_a($marcRecord, 'File_MARC_Record') === true || is_a($marcRecord, 'File_MARCXML_Record') === true) {
                // 800$t$v -> 773$q -> 830$v -> 245$a$b -> "Title not found"
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

    private function _normalize($field) {
        $t_start = microtime(true);
        if (function_exists('normalizer_normalize')) {
            if (is_array($field)) {
                $return = array();
                foreach ($field as $f) {
                    if (normalizer_normalize($f)) {
                        $return[] = normalizer_normalize($f);
                    }
                    else {
                        $return[] = $f;
                    }
                }
            }
            else {
                if (normalizer_normalize($field)) {
                    $return = normalizer_normalize($field);
                }
                else {
                    $return = $field;
                }
            }
        }
        else {
            $return = $field;
        }
        $t_stop = microtime(true);
        //echo "Normalisierung dauerte ".(($t_stop-$t_start)*1000)." ms\n";
        return $return;
    }

    /**
     * Get an array of information about record holdings, obtained in real-time
     * from the ILS.
     *
     * @return array
     * @access protected
     */
    protected function getRealTimeHoldingsDepracated()
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
        $this->getRemarksFromMarc();
        $id = $this->getUniqueID();
        $catalog = ConnectionManager::connectToCatalog();

        if ($catalog && $catalog->status) {
            $result = $catalog->getHolding($id);
            if (PEAR::isError($result)) {
                PEAR::raiseError($result);
            }
            $holdings = array();
            if (count($result)) {
                // Jedes Exemplar aus dem DAIA-Output soll in die Exemplarliste
                foreach ($result as $copy) {
                    if ($linkname != false) $copy['linkname'] = $linkname;
                    $itemId = $copy['itemid'];
                    $epnArray = explode(':epn:', $itemId);
                    $epn = $epnArray[1];
                    // Eventuelle Anreicherungen ergänzen...
                    if ($this->remarks[$epn]) $copy = array_merge($copy, $this->remarks[$epn]);
                    $holdings[$copy['location']][] = $copy;
                }
            }
            return $holdings;
        }
        return array();
    }


    /**
     * Obtains an array of remarks and comments in MARC 980$k and $g. The array is aved to $this->remarks
     *
     * @return void
     * @access protected
     */
    protected function getRemarksFromMarc()
    {
        $configPica = parse_ini_file('conf/GBVCentral.ini', true);
        $iln = $configPica['iln'];
        $vs = $this->marcRecord->getFields('980');
        if ($vs) {
            // Durchlaufe die Felder 980 (Bestandsangaben aller GBV-Bibliotheken)
            // Dies ist notwendig, um die Kommentare und Bemerkungen aus dem MARC-Code abzufischen
            foreach($vs as $v) {
                // is this ours? In Feld $2 steht die ILN der Bibliothek, zu der diese Bestandsangabe gehoert
                // Wenn der Titel zur konfigurierten Bibliothek gehoert, werte die Zeile aus
                $libArr = $v->getSubfields('2');
                $lib = $libArr[0]->getData();
                if ($lib === $iln) {
                    $v_signature = null;
                    $epnArr = $v->getSubfields('b');
                    $epn = $epnArr[0]->getData();
                    $copy[$epn] = array();
                    $v_names = $v->getSubfields('k');
                    $v_remarks = $v->getSubfields('g');
                    if (count($v_names) > 0) {
                        $copy[$epn]['summary'] = array();
                        foreach($v_names as $v_name) {
                            $copy[$epn]['summary'][] = $v_name->getData();
                        }
                    }
                    if (count($v_remarks) > 0) {
                        $copy[$epn]['marc_notes'] = array();
                        foreach($v_remarks as $v_remark) {
                            $copy[$epn]['marc_notes'][] = $v_remark->getData();
                        }
                    }
                }
            }
        }
        $this->remarks = $copy;
    }

    /**
     * Get an array of link names from MARC
     *
     * @return array
     * @access protected
     */
    protected function getLinkNames() {
        $configPica = parse_ini_file('conf/GBVCentral.ini', true);
        $iln = $configPica['iln'];
        $returnArray = array();
        $vsl = $this->marcRecord->getFields('981');
        if ($vsl) {
            // Durchlaufe die Felder 981 (Online-Bestandsangaben aller GBV-Bibliotheken)
            // Dies ist notwendig, um die Linkbezeichnungen aus dem MARC-Code abzufischen
            foreach($vsl as $vsle) {
                // is this ours? In Feld $2 steht die ILN der Bibliothek, zu der diese Bestandsangabe gehoert
                $libField = $vsle->getSubfield('2');
                $lib = $libField->getData();
                if ($lib === $iln) {
                    $counter++;
                    // Wenn der Titel zur konfigurierten Bibliothek gehoert, werte die Zeile aus
                    $v_name = $vsle->getSubfield('y');
                    $v_link = $vsle->getSubfield('r');
                    if ($v_name) $returnArray[$v_link->getData()] = $v_name->getData();
                }
            }
        }
        return $returnArray;
    }

    /**
     * Get thesis information from MARC
     *
     * @return array
     * @access protected
     */
    protected function getThesisInformation() {
        $returnArray = array();
        $vsl = $this->marcRecord->getFields('502');
        if ($vsl) {
            // Durchlaufe die Felder 981 (Online-Bestandsangaben aller GBV-Bibliotheken)
            // Dies ist notwendig, um die Linkbezeichnungen aus dem MARC-Code abzufischen
            foreach($vsl as $vsle) {
                $v_name = $vsle->getSubfield('a');
                $returnArray[] = $v_name->getData();
            }
        }
        return $returnArray;
    }

    /**
     * Get an array of remarks from record holdings
     *
     * @return array
     * @access protected
     */
    protected function getRemark()
    {
        // Get Holdings Data
        $id = $this->getUniqueID();
        $catalog = ConnectionManager::connectToCatalog();
        $configPica = parse_ini_file('conf/GBVCentral.ini', true);
        $iln = $configPica['iln'];

        if ($catalog && $catalog->status) {
            $result = $catalog->getHolding($id);
            if (PEAR::isError($result)) {
                PEAR::raiseError($result);
            }
            $holdings = array();
            $done_array = array();
            $counter = 0;
            if (count($result)) {
                foreach ($result as $copy) {
                    $vs = $this->marcRecord->getFields('980');
                    if ($vs) {
                        // Durchlaufe die Felder 980 (Bestandsangaben aller GBV-Bibliotheken)
                        foreach($vs as $v) {
                            // is this ours? In Feld $2 steht die ILN der Bibliothek, zu der diese Bestandsangabe gehoert
                            $libArr = $v->getSubfields('2');
                            $lib = $libArr[0]->getData();
                            if ($lib === $iln) {
                                $counter++;
                                // Wenn der Titel zur konfigurierten Bibliothek gehoert, werte die Zeile aus
                                $v_names = $v->getSubfields('k');
                                $return = array();
                                if (count($v_names) > 0) {
                                    foreach($v_names as $v_name) {
                                        if (!in_array($v_name->getData(), $return)) {
                                            $return[] = $v_name->getData();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return $return;
        }
        return array();
    }

    /**
     * Get the text to represent this record in the body of an email.
     *
     * @return string Text for inclusion in email.
     * @access public
     */
    public function getTitleParent()
    {
        $title = parent::getTitle();
        return "  " . $title[0] . "\n";
    }

    /**
     * Get the text to represent this record in the body of an email.
     *
     * @return string Text for inclusion in email.
     * @access public
     */
    public function getEmail()
    {
        $title = $this->getTitle();
        return "  " . $title . "\n";
    }

    /**
     * Get the text to represent this record in the body of an email.
     *
     * @return string Text for inclusion in email.
     * @access public
     */
    public function getBreadcrumb()
    {
        $title = $this->getTitle();
        return "  " . $title . "\n";
    }


    /**
     * Get an array of search results for other editions of the title
     * represented by this record (empty if unavailable).  In most cases,
     * this will use the XISSN/XISBN logic to find matches.
     *
     * @return mixed Editions in index engine result format (or null if no
     * hits, or PEAR_Error object).
     * @access public
     */
    public function getEditions()
    {
        include_once 'sys/WorldCatUtils.php';
        $wc = new WorldCatUtils();

        // Try to build an array of OCLC Number, ISBN or ISSN-based sub-queries:
        $parts = array();
        $oclcNum = $this->getCleanOCLCNum();
        if (!empty($oclcNum)) {
            $oclcList = $wc->getXOCLCNUM($oclcNum);
            foreach ($oclcList as $current) {
                $parts[] = "oclc_num:" . $current;
            }
        }
        $isbns = $this->getISBNs();
        if (!empty($isbns)) {
            foreach ($isbns as $current) {
                $parts[] = 'isbn:' . $current;
            }
        }
        /*
        $isbn = $this->getCleanISBN();
        if (!empty($isbn)) {
            $isbnList = $wc->getXISBN($isbn);
            foreach ($isbnList as $current) {
                $parts[] = 'isbn:' . $current;
            }
        }*/
        $issns = $this->getISSNs();
        if (!empty($issns)) {
            foreach ($issns as $current) {
                $parts[] = 'issn:' . $current;
            }
        }
        /*
        $issn = $this->getCleanISSN();
        if (!empty($issn)) {
            $issnList = $wc->getXISSN($issn);
            foreach ($issnList as $current) {
                $parts[] = 'issn:' . $current;
            }
        }*/

        // If we have query parts, we should try to find related records:
        if (!empty($parts)) {

            // Limit the number of parts based on the boolean clause limit:
            $index = $this->getIndexEngine();
            $limit = $index->getBooleanClauseLimit();
            if (count($parts) > $limit) {
                $parts = array_slice($parts, 0, $limit);
            }
            // Assemble the query parts and filter out current record:
            // also exclude articles, which are found by ISSN search
            $query = ' NOT (format:Article OR format:"electronic Article") 
                AND (' . implode(' OR ', $parts) . ') 
                NOT ppnlink:' . $this->getUniqueID() . '
                NOT id:' .
                $this->getUniqueID();

            // Perform the search and return either results or an error:
            $index = $this->getIndexEngine();
            $this->setHiddenFilters();

            // Query with filters applied
            $result = $index->search($query, null, $this->hiddenFilters, 0, 5);
            // Query without filters
            #$result = $index->search($query, null, null, 0, 5);
            if (PEAR::isError($result)) {
                return $result;
            }

            $subrecs = $result['response']['docs'];
            $resultRecords = array();
            foreach ($subrecs as $subId) {
                $subr = $subId;
                $subrecord = array('id' => $subId['id']);
                $subrecord['title'] = array();
                if ($subr['title']) $subrecord['title'][] = $subr['title'][0];
                if ($subr['journal']) $subrecord['journal'][] = $subr['journal'][0];
                if ($subr['series2']) $subrecord['journal'][] = $subr['series2'][0];
                $subrecord['publishDate'] = array();
                if (!$subr) {
                    $subrecord['record_url'] = $record_url.$subId;
                }
                $m = trim($subr['fullrecord']);
                // check if we are dealing with MARCXML
                $xmlHead = '<?xml version';
                if (strcasecmp(substr($m, 0, strlen($xmlHead)), $xmlHead) === 0) {
                    $m = new File_MARCXML($m, File_MARCXML::SOURCE_STRING);
                } else {
                    $m = preg_replace('/#31;/', "\x1F", $m);
                    $m = preg_replace('/#30;/', "\x1E", $m);
                    $m = new File_MARC($m, File_MARC::SOURCE_STRING);
                }
                $marcRecord = $m->next();
                if (is_a($marcRecord, 'File_MARC_Record') === true || is_a($marcRecord, 'File_MARCXML_Record') === true) {
                // 800$t$v -> 773$q -> 830$v -> 245$a$b -> "Title not found"
                    $yearFields = $marcRecord->getFields('008');
                    $yearField = $yearFields[0];
                    $pos = strpos($yearField, 's');
                    $year = substr($yearField, $pos+1, 4);
                    $subrecord['publishDate'][] = $year;
                    $titleFields = $marcRecord->getFields('245');
                    $titleField = $titleFields[0];
                    $volField = $titleField->getSubfields('p');
                    if (count($volField) > 0) {
                        $vol = $volField[0]->getData();
                    }
                    $subrecord['volume'] = $vol;
                }
                $resultRecords[] = $subrecord;
            }

            if (isset($resultRecords)
                && !empty($resultRecords)) {
                return $resultRecords;
            }
            if (isset($result['response']['docs'])
                && !empty($result['response']['docs'])
            ) {
                return $result['response']['docs'];
            }
        }

        // If we got this far, we were unable to find any results:
        return null;
    }

    /**
     * Get the ID of a record without NLZ prefix
     *
     * @return string ID without NLZ-prefix (if this is an NLZ record)
     * @access protected
     */
    protected function stripNLZ($rid = false) {
        if ($rid === false) $rid = $this->fields['id'];
        // if this is a national licence record, strip NLZ prefix since this is not indexed as ppnlink
        if (substr($this->fields['id'], 0, 3) === 'NLZ' || substr($this->fields['id'], 0, 3) === 'NLM') {
            $rid = substr($rid, 3);
        }
        return $rid;
    }

    /**
     * Get the ID of a record with NLZ prefix, if this is appropriate
     *
     * @return string ID with NLZ-prefix (if this is an NLZ record)
     * @access protected
     */
    protected function addNLZ($rid = false) {
        if ($rid === false) $rid = $this->fields['id'];
        $prefix = '';
        if (substr($this->fields['id'], 0, 3) === 'NLZ') {
            $prefix = 'NLZ';
        }
        if (substr($this->fields['id'], 0, 3) === 'NLM') {
            $prefix = 'NLM';
        }
        return $prefix.$rid;
    }

    /**
     * Determine if we have a national license hit
     *
     * @return boolean is this a national license hit?
     * @access protected
     */
    protected function isNLZ() {
        return ($this->_isNLZ($this->fields['id']));
    }

    /**
     * Determine if we have a national license hit
     *
     * @return boolean is this a national license hit?
     * @access protected
     */
    private function _isNLZ($id) {
        if (substr($id, 0, 3) === 'NLZ' || substr($id, 0, 3) === 'NLM') {
            return true;
        }
        return false;
    }

    /**
     * Get the reference of the article including its link.
     *
     * @access  protected
     * @return  array
     */
    protected function getTitleAddition()
    {
        $return = "";
        #if ($this->_getFirstFieldValue('245', array('n'))) $return .= $this->_getFirstFieldValue('245', array('n'));
        #if ($this->_getFirstFieldValue('245', array('b'))) $return .= $this->_getFirstFieldValue('245', array('b'));
        if ($this->_getFirstFieldValue('110', array('a'))) $return .= ' ('.$this->_getFirstFieldValue('110', array('a')).')';
        return $return;
    }

    /**
     * Get the title of the item
     *
     * @access  protected
     * @return  array
     */
    protected function getTitle() {
        $return = '';
        if ($this->_getFirstFieldValue('245', array('a'))) $return = $this->_getFirstFieldValue('245', array('a'));
        if ($this->_getFirstFieldValue('245', array('b'))) $return .= " ".$this->_getFirstFieldValue('245', array('b'));
        return $return;
    }

    /**
     * Get the content of MARC field 246
     *
     * @return array
     * @access protected
     */
    protected function getSubseries() {
        $return = array();
        $vs = $this->marcRecord->getFields('246');
        if ($vs) {
            foreach($vs as $v) {
                $libArr = $v->getSubfields('i');
                if ($libArr) {
                    $lib = $libArr[0]->getData();
                    $v_names = $v->getSubfields('a');
                    if (count($v_names) > 0) $return[] = array($lib => $v_names[0]->getData());
                }
            }
        }
        return $return;
    }

    /**
     * Get the item's places of publication.
     *
     * @return array
     * @access protected
     */
    protected function getPublicationDetailsFromField260()
    {
        $field260 = $this->marcRecord->getFields('260');
        $pubArr = array();
        $index = 0;
        if ($field260) {
            foreach($field260 as $v) {
                $a_names = $v->getSubfields('a');
                $b_names = $v->getSubfields('b');
                $c_names = $v->getSubfields('c');
                if ($a_names[0]) $pubArr[$index]['place'] = $a_names[0]->getData();
                if ($b_names[0]) $pubArr[$index]['name'] = $b_names[0]->getData();
                if ($c_names[0]) $pubArr[$index]['date'] = $c_names[0]->getData();
                $index++;
            }
        }
        return $pubArr;
    }

    /**
     * Get an array of publication detail lines combining information from
     * getPublicationDates(), getPublishers() and getPlacesOfPublication().
     *
     * @return array
     * @access protected
     */
    protected function getPublicationDetailsFromMarc()
    {
        $details = $this->getPublicationDetailsFromField260();

        $i = 0;
        $retval = array();
        while (isset($details[$i]['place']) || isset($details[$i]['name']) || isset($details[$i]['date'])) {
            // Put all the pieces together, and do a little processing to clean up
            // unwanted whitespace.
            $retval[] = trim(
                str_replace(
                    '  ', ' ',
                    ((isset($details[$i]['place']) ? $details[$i]['place'] . ' ' : '') .
                    (isset($details[$i]['name']) ? $details[$i]['name'] . ' ' : '') .
                    (isset($details[$i]['date']) ? $details[$i]['date'] : ''))
                )
            );
            $i++;
        }

        return $retval;
    }

    /**
     * Get an array of publication detail lines combining information from
     * getPublicationDates(), getPublishers() and getPlacesOfPublication().
     *
     * @return array
     * @access protected
     */
    protected function getPublicationDetails()
    {
        $places = $this->getPlacesOfPublication();
        $names = $this->getPublishers();
        $dates = $this->getPublicationDates();

        // if the number of publishers is higher than one, get the publication details from MARC
        if (count($names) > 1) {
            $details = $this->getPublicationDetailsFromMarc();
            if ($details) return $details;
        }

        $i = 0;
        $retval = array();
        while (isset($places[$i]) || isset($names[$i]) || isset($dates[$i])) {
            // Put all the pieces together, and do a little processing to clean up
            // unwanted whitespace.
            $retval[] = trim(
                str_replace(
                    '  ', ' ',
                    ((isset($places[$i]) ? $places[$i] . ' ' : '') .
                    (isset($names[$i]) ? $names[$i] . ' ' : '') .
                    (isset($dates[$i]) ? $dates[$i] : ''))
                )
            );
            $i++;
        }

        return $retval;
    }

    protected function getDateSpan() {
        $spanArray = parent::getDateSpan();
        $span = implode(' ', $spanArray);
        if (substr($span, -1) === '-') {
            $span .= ' '.translate('heute');
        }
        return($span);
    }

    /**
     * Get the short (pre-subtitle) title of the record.
     *
     * @return string
     * @access protected
     */
    /*
    protected function getShortTitle()
    {
        $return = '';
        if ($this->_getFirstFieldValue('245', array('a'))) $return = $this->_getFirstFieldValue('245', array('a'));
        return $return;
    }
    */
}
?>