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

/**
 * PrimoCentral Record Driver
 *
 * This class is designed to handle PrimoCentral records.  Much of its functionality
 * is inherited from the default index-based driver.
 */
class PCRecord extends IndexRecord
{
    public function getSearchResult($view = 'list')
    {
        global $interface;
        parent::getSearchResult($view);
        $interface->assign('summInterlibraryLoan', $this->checkInterlibraryLoan());
        $interface->assign('score', $this->getScore());
        $interface->assign('doi', $this->getDoi());
        $interface->assign('sfxmenu', $this->getSfxMenu());
        $interface->assign('sfxbutton', $this->getSfxMenuButton());
        $interface->assign('pcURLs', $this->getURLs());
        $interface->assign('multiaut', $this->getAuthorsCount());
        $interface->assign('showPrinted', $this->showPrinted());
        $interface->assign('titleSeries', $this->getTitleSeries());
        if ($this->isGbvRecord() === true) {
            $interface->assign('gbvppn', $this->getGbvPpn());
            $gbvRecord = $this->searchGBVPPN($this->getGbvPpn());
            $local = false;
            if (in_array('GBV_ILN_23', $gbvRecord['docs'][0]['collection_details'])) $local = true;
            $interface->assign('locally', $local);
        }
        $interface->assign('gbvppn', $this->getGbvPpn());

        return 'RecordDrivers/PC/result.tpl';
    }

    public function getCoreMetadata() {
        global $interface;
        parent::getCoreMetadata();
        $interface->assign('primoRecord', true);
        /*
        $interface->assign('articleChildren', $this->getArticleChildren());
        $interface->assign('coreSubseries', $this->getSubseries());
        */

        return 'RecordDrivers/PC/core.tpl';
    }

    public function getHoldings()
    {
        global $interface;
        global $configArray;

        parent::getHoldings();

        $interface->assign('doi', $this->getDoi());
        $interface->assign('sfxmenu', $this->getSfxMenu());
        $interface->assign('sfxbutton', $this->getSfxMenuButton());
        $interface->assign('pcURLs', $this->getURLs());

        if ($this->isGbvRecord() === true) {
            $interface->assign('gbvppn', $this->getGbvPpn());
            $gbvRecord = $this->searchGBVPPN($this->getGbvPpn());
            $local = false;
            if (in_array('GBV_ILN_23', $gbvRecord['docs'][0]['collection_details'])) $local = true;
            $interface->assign('locally', $local);
        }

        $interface->assign('printed', $this->getPrintedSample());

        $artFieldedRef = $this->getArticleFieldedReference();
        $articleVol = $this->searchArticleVolume($artFieldedRef);
        $interface->assign('articleVol', $articleVol);

        $bookFieldedRef = $this->getEbookFieldedReference();
        $printedEbook = $this->searchPrintedEbook($bookFieldedRef);
        $interface->assign('printedEbook', $printedEbook);

        return 'RecordDrivers/PC/holdings.tpl';
    }

    public function isPrimoRecord() {
        return true;
    }

    public function getGbvPpn() {
        $ppn = null;
        if (substr($this->fields['id'], 0, 5) == 'PCgbv') {
            $ppn = substr($this->fields['id'], 5);
        }
        return $ppn;
    }

    public function isGbvRecord() {
        if ($this->getGbvPpn() !== null) return true;
        return false;
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
     * Get the item's DOI address (if available).
     *
     * @access  protected
     * @return  array
     */
    public function getDoi() {
        if (isset($this->fields['doi'])) {
            if (is_array($this->fields['doi'])) {
                $return = implode(',', $this->fields['doi']);
            }
            else {
                $return = $this->fields['doi'];
            }
            return $return;
        }
        return null;
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
     * checks if this item is in the local stock
     *
     * @access  protected
     * @return  string
     */
    public function checkInterlibraryLoan()
    {
        // Return null if we have no table of contents:
/*        $fields = $this->marcRecord->getFields('912');
        if (!$fields) {
            return null;
        }

        // If we got this far, we have libraries owning this item -- check if we have it locally
        foreach ($fields as $field) {
            $subfields = $field->getSubfields();
            foreach ($subfields as $subfield) {
                if ($subfield->getCode() === 'a') {
                    if ($subfield->getData() === 'GBV_ILN_23') {
                        return '0';
                    }
                }
            }
        }

        // Is this item an e-ressource?
        if (in_array('eBook', $this->getFormats()) === true) {
            return '0';
        }*/

        return '0';
    }

    /**
     * Get the reference of the article.
     *
     * @access  protected
     * @return  string
     */
    protected function getArticleReference()
    {
/*        $inRef = $this->_getFirstFieldValue('773', array('i'));
        $journalRef = $this->_getFirstFieldValue('773', array('t'));
        $articleRef = $this->_getFirstFieldValue('773', array('g'));
        if ($articleRef !== null) {
            return $inRef." ".$journalRef." ".$articleRef;
        }*/
        return null;
    }

    /**
     * Get information on how to get a printed version of this record
     *
     * @access  public
     * @return  array
     */
    public function getPrintedSample() {
        $printed = $this->_getPrintedInformationFromEZB();
        return $printed;
    }

    private function _getPrintedInformationFromEZB() {
        $isil = 'DE-830';
        $zdbFullUrl = 'http://services.d-nb.de/fize-service/gvr/full.xml?';
        $item = null;
        /* we need a new query per ISSN */
        foreach ($this->fields['issn'] as $issn) {
            $openurl = null;
            $params = null;
            $params = array('pid' => 'isil='.$isil.'&print=1');
            $parts = null;
            $parts = array();
            if (strlen($issn) == 9 && strpos($issn, '-') == 4) {
                $params['issn'] = $issn;

                $formats = $this->fields['format'];
                // If we have multiple formats, Article and Journal are most important...
                if (in_array('Article', $formats)) {
                    $format = 'Article';
                }
                else if (in_array('Journal', $formats)) {
                    $format = 'Journal';
                } else {
                    $format = $formats[0];
                }
                switch($format) {
                    case 'Journal':
                        $params['genre'] = 'journal';
                        break;
                    case 'Article':
                        $params['genre'] = 'article';
                        break;
                    default:
                        return null;
                }
                $params['date'] = $this->fields['publishDate'][0];
                $params['title'] = $this->fields['series'][0];
                $params['atitle'] = $this->fields['title'];
                $params['volume'] = $this->fields['jvol'][0];
                $params['issue'] = $this->fields['jissue'][0];
                $params['spage'] = $this->fields['jspage'][0];
                $params['epage'] = $this->fields['jepage'][0];

                foreach ($params as $key => $value) {
                    if (is_array($value) === true) {
                        $parts[] = $key . '=' . urlencode($value[0]);
                    }
                    else {
                        $parts[] = $key . '=' . urlencode($value);
                    }
                }
                $openurl = implode('&', $parts);

                $ezb = new DomDocument();
                $ezb->load($zdbFullUrl.$openurl);
                $documentlist = $ezb->getElementsByTagName('ResultList');
                if ($documentlist->item(0)) {
                    $docs = $documentlist->item(0)->getElementsByTagName('Result');

                    for ($b = 0; $docs->item($b) !== null; $b++) {
                        $status = $docs->item($b)->getAttribute('state');
                        // if this state is not usable, try the next one
                        if ($status != '2' && $status != '3') continue;
                        // if the state is usable, collect the data
                        $item = array();
                        $item['jtitle'] = $docs->item($b)->getElementsByTagName('Title')->item(0)->nodeValue;
                        $item['location'] = $docs->item($b)->getElementsByTagName('Location')->item(0)->nodeValue;
                        $item['signature'] = $docs->item($b)->getElementsByTagName('Signature')->item(0)->nodeValue;
                        $item['period'] = $docs->item($b)->getElementsByTagName('Period')->item(0)->nodeValue;
                        $item['status'] = $status;
                    }
                }
            }
            // if we have got a usable result, return now
            if ($item !== null) return $item;
        }
        // Unfortunately we did not find a usable record
        return null;
    }

    /**
     * Check if at least one article for this item exists.
     * Method to keep performance lean in core.tpl.
     *
     * @return bool
     * @access protected
     */
    public function searchArticleVolume($fieldref)
    {
        if (in_array('Article', $this->getFormats()) === true) {
            unset($_SESSION['shards']);
            $_SESSION['shards'] = array();
            $_SESSION['shards'][] = 'GBV Central';
            $_SESSION['shards'][] = 'TUBdok';
            $_SESSION['shards'][] = 'wwwtub';

            $f1info = false;
            $f2info = false;
            $index = $this->getIndexEngine();

            $queryparts = array();

            if (count($fieldref['issn']) > 0) {
                $queryparts[] = 'issn:('.implode(' OR ', $fieldref['issn']).')';
            }
            else {
                $queryparts[] = $fieldref['title'];
            }
            if ($fieldref['volume']) {
                $f1info = true;
                $fieldsToSearch .= $fieldref['volume'].'.';
            }
            if ($fieldref['date']) {
                $f2info = true;
                $fieldsToSearch .= $fieldref['date'];
            }
            if ($fieldsToSearch) {
                $queryparts[] = $fieldsToSearch;
            }
            if ($f1info && $f2info) {
                $queryparts[] = 'format:(Book OR "Serial Volume")';
            }
            else {
                // not sure what to search since the volume and year reference are missing, so just search the main journal record
                $queryparts[] = 'format:Journal';
            }
            // Assemble the query parts and filter out current record:
            $query = implode(" AND ", $queryparts);
            $query = '('.$query.')';
            //$query = '(ppnlink:'.$rid.' AND '.$fieldref.')';

            // Perform the search and return either results or an error:
            $this->setHiddenFilters();

            $result = $index->search($query, null, $this->hiddenFilters, 0, 1000, null, '', null, null, '',  HTTP_REQUEST_METHOD_POST, false, false, false);

            // If we got no results, do another query with the title instead of ISSN - but only if we have volume information
            if ($result['response']['numFound'] == 0 && $f2info == true && $f1info == true) {
                $altqueryparts = array();
                $altqueryparts[] = $fieldref['title'];
                $altqueryparts[] = $fieldsToSearch;
                $altqueryparts[] = 'format:(Book OR "Serial Volume")';
                // Assemble the query parts and filter out current record:
                $altquery = implode(" AND ", $altqueryparts);
                $altquery = '('.$altquery.')';
                $result = $index->search($altquery, null, $this->hiddenFilters, 0, 1000, null, '', null, null, '',  HTTP_REQUEST_METHOD_POST, false, false, false);
            }

            unset($_SESSION['shards']);
            $_SESSION['shards'] = array();
            $_SESSION['shards'][] = 'Primo Central';

            return ($result['response']['numFound'] > 0) ? $result['response'] : false;
        }
        return false;
    }

    /**
     * Check if at least one article for this item exists.
     * Method to keep performance lean in core.tpl.
     *
     * @return bool
     * @access protected
     */
    public function searchPrintedEbook($fieldref)
    {
        if (in_array('Book', $this->getFormats()) === true || in_array('book_chapter', $this->getFormats()) === true) {
            $isbnsearch = false;
            unset($_SESSION['shards']);
            $_SESSION['shards'] = array();
            $_SESSION['shards'][] = 'GBV Central';
            $_SESSION['shards'][] = 'TUBdok';
            $_SESSION['shards'][] = 'wwwtub';

            $index = $this->getIndexEngine();

            $queryparts = array();
            $queryparts[] = trim(addslashes($fieldref['title']));
            if (count($fieldref['isbn']) > 0) {
                $isbnsearch = true;
                $queryparts[] = 'isbn:('.implode(' OR ', $fieldref['isbn']).')';
            }
            if ($isbnsearch === false) {
                $queryparts[] = 'title:("'.trim(addslashes($fieldref['title'])).'")';

                if ($fieldref['date']) {
                    $queryparts[] = 'publishDate:'.$fieldref['date'];
                }
                if ($fieldref['author']) {
                    $queryparts[] = 'author:"'.addslashes($fieldref['author']).'"';
                }
            }
            $queryparts[] = '(format:Book OR format:"Serial Volume")';
            // Assemble the query parts and filter out current record:
            $query = implode(" AND ", $queryparts);
            $query = '('.$query.')';
            //$query = '(ppnlink:'.$rid.' AND '.$fieldref.')';

            // Perform the search and return either results or an error:
            $this->setHiddenFilters();

            $result = $index->search($query, null, $this->hiddenFilters, 0, 1000, null, '', null, null, '',  HTTP_REQUEST_METHOD_POST, false, false, false);

            unset($_SESSION['shards']);
            $_SESSION['shards'] = array();
            $_SESSION['shards'][] = 'Primo Central';

            return ($result['response'] > 0) ? $result['response'] : false;
        }
        return false;
    }

    /**
     * Check if at least one article for this item exists.
     * Method to keep performance lean in core.tpl.
     *
     * @return bool
     * @access protected
     */
    public function searchGBVPPN($ppn)
    {
        unset($_SESSION['shards']);
        $_SESSION['shards'] = array();
        $_SESSION['shards'][] = 'GBV Central';
        $_SESSION['shards'][] = 'TUBdok';
        $_SESSION['shards'][] = 'wwwtub';

        $index = $this->getIndexEngine();

        $query = 'id:'.$ppn;

        $result = $index->search($query, null, null, 0, 1, null, '', null, null, '',  HTTP_REQUEST_METHOD_POST, false, false, false);

        unset($_SESSION['shards']);
        $_SESSION['shards'] = array();
        $_SESSION['shards'][] = 'Primo Central';

        return ($result['response'] > 0) ? $result['response'] : false;
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
        /*if ($issn === false) {
            $issn = $this->_getFirstFieldValue('773', array('x'));
        }*/
        return $issn;
    }

    public function showPrinted() {
        global $configArray;
        $printed = isset($configArray['Printed']['enabled']) ?
            $configArray['Printed']['enabled'] :
            null;
        return $printed;
    }


    public function getSfxMenuButton() {
        global $configArray;
        $openUrlButton = isset($configArray['OpenURL']['graphic']) ?
            $configArray['OpenURL']['graphic'] :
            null;
        return $openUrlButton;
    }

    public function getSfxMenu() {
        global $configArray;
        $openUrl = isset($configArray['OpenURL']['url']) ?
            $configArray['OpenURL']['url'] :
            null;
        if ($openUrl === null) return null;
        return $openUrl.'?'.$this->getOpenURL(true);
    }

    public function getURLs() {
        global $configArray;
        $openUrl = isset($configArray['OpenURL']['url']) ?
            $configArray['OpenURL']['url'] :
            null;
        if (isset($this->fields['url']) && stristr($this->fields['url'][0], $openUrl) === false) {
            return $this->fields['url'];
        }
        return null;
    }

    /**
     * Get the OpenURL parameters to represent this record (useful for the
     * title attribute of a COinS span tag).
     *
     * @return string OpenURL parameters.
     * @access public
     */
    public function getOpenURL($menu = false)
    {
        // Get the COinS ID -- it should be in the OpenURL section of config.ini,
        // but we'll also check the COinS section for compatibility with legacy
        // configurations (this moved between the RC2 and 1.0 releases).
        global $configArray;

        $openUrl = isset($configArray['OpenURL']['url']) ?
            $configArray['OpenURL']['url'] :
            null;

        if (isset($this->fields['url']) && stristr($this->fields['url'][0], $openUrl) !== false) {
            if ($menu == false) {
                $urlArray = explode('?', $this->fields['url'][0]);
                return $urlArray[1];
            }
            else {
                $params = array();
                $urlArray = explode('?', $this->fields['url'][0]);
                $paramsArray = explode('&', $urlArray[1]);
                foreach ($paramsArray as $paramElement) {
                    $paramElementArray = explode('=', $paramElement);
                    $params[$paramElementArray[0]] = $paramElementArray[1];
                }
                $params['disable_directlink'] = "true";
                $params['sfx.directlink'] = "off";
                // Assemble the URL:
                $parts = array();
                foreach ($params as $key => $value) {
                    if ($key == 'svc.fulltext') continue;
                    if (is_array($value) === true) {
                        $parts[] = $key . '=' . $value[0];
                    }
                    else {
                        $parts[] = $key . '=' . $value;
                    }
                }
                return implode('&', $parts);
            }
        }

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
        if (in_array('Aufsätze', $formats)) {
            $format = 'Article';
        }
        else if (in_array('Book', $formats)) {
            $format = 'Book';
        } else if (in_array('Journal', $formats)) {
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
                /*$articleFields = $this->getArticleFieldedReference();
                if ($articleFields['volume']) $params['rft.volume'] = $articleFields['volume'];
                if ($articleFields['issue']) $params['rft.issue'] = $articleFields['issue'];
                if ($articleFields['spage']) $params['rft.spage'] = $articleFields['spage'];
                if ($articleFields['epage']) $params['rft.epage'] = $articleFields['epage'];
                if ($articleFields['date']) $params['rft.date'] = $articleFields['date'];*/
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

        if ($menu == true) {
            $params['disable_directlink'] = "true";
            $params['sfx.directlink'] = "off";
        }

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

    protected function getAuthorsCount() {
        $authors = array();
        $aut = parent::getPrimaryAuthor();
        $authors = explode(';', $aut);
        return count($authors);
    }

    protected function getPrimaryAuthor() {
        $authors = array();
        $aut = parent::getPrimaryAuthor();
        $authors = explode(';', $aut);
        return $authors[0];
    }

    protected function getSecondaryAuthors() {
        $authors = array();
        $aut = parent::getPrimaryAuthor();
        $authors = explode(';', $aut);
        array_shift($authors);
        /*foreach ($authors_n as $aut_n) {
            $authors[] = urlencode($aut_n);
        }*/
        return $authors;
    }

    /**
     * Get the full title of the record.
     *
     * @return string
     * @access protected
     */
    protected function getShortTitle()
    {
        return isset($this->fields['title']) ?
            $this->fields['title'] : '';
    }

    protected function getTitleSeries() {
        if ($this->fields['title_series']) {
            return $this->fields['title_series'][0];
        }
    }

    public function getEditions() {
        $frbrid = isset($this->fields['frbrid']) ? $this->fields['frbrid'][0] : null;
        $id = isset($this->fields['id']) ? $this->fields['id'] : null;
        $frbrRecords = $this->getFrbrRecords($id, $frbrid);
        return $frbrRecords;
    }

    protected function getFrbrRecords($id, $frbrid) {
        // cannot work without frbr-ID
        if ($frbrid === null) return null;
        // Read in preferred boolean/range behavior:
        $searchSettings = getExtraConfigArray('searches_primocentral');
        // Cannot work without configuration settings in searches_primocentral.ini
        if (isset($searchSettings['Index']['host'])) $host = $searchSettings['Index']['host'];
        else return null;
        if (isset($searchSettings['Index']['institution'])) $institution = $searchSettings['Index']['institution'];
        else return null;
        $oncampus = 'false';
        if (isset($searchSettings['AuthorizedMode']['enabled'])) {
            if (substr($_SERVER['REMOTE_ADDR'], 0, strlen($this->authorizedIPRange)) == $this->authorizedIPRange && $searchSettings['AuthorizedMode']['enabled'] != false) {
                $oncampus = 'true';
            }
        }
        $pc = new DomDocument();
        $pc->load($host.'/PrimoWebServices/xservice/search/brief?institution='.$institution.'&onCampus='.$oncampus.'&loc=adaptor,primo_central_multiple_fe&query=facet_frbrgroupid,exact,'.$frbrid);

        $documentlist = $pc->getElementsByTagName('record');
        $items = array();
        for ($b = 0; $documentlist->item($b) !== null; $b++) {
            $idblock = $documentlist->item($b)->getElementsByTagName('control');
            $pcid = $this->__convertAPIID2PCID($idblock->item(0)->getElementsByTagName('recordid')->item(0)->nodeValue);
            // Skip the current record
            if ($pcid == $id) continue;
            $items[$b] = array();
            $items[$b]['id'] = $pcid;

            $displayblock = $documentlist->item($b)->getElementsByTagName('display');
            $items[$b]['format'] = $displayblock->item(0)->getElementsByTagName('type')->item(0)->nodeValue;
            $items[$b]['title'] = array($displayblock->item(0)->getElementsByTagName('title')->item(0)->nodeValue);
            //$items[$b]['journal'] = array($idblock->item(0)->getElementsByTagName('recordid')->item(0)->nodeValue);
            $searchblock = $documentlist->item($b)->getElementsByTagName('search');
            $items[$b]['publishDate'] = array($searchblock->item(0)->getElementsByTagName('creationdate')->item(0)->nodeValue);
            $facetblock = $documentlist->item($b)->getElementsByTagName('facets');
            $items[$b]['volume'] = '(via '.$facetblock->item(0)->getElementsByTagName('collection')->item(0)->nodeValue.')';
        }
        if (count($items) == 0) return null;
        return $items;
    }

    private function __convertAPIID2PCID($id) {
        $id = str_replace('TN_', 'PC', $id);
        $id = str_replace('.', '__D__', $id);
        $id = str_replace('/', '__S__', $id);
        return ($id);
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
        $retVal['volume'] = $this->fields['jvol'][0];
        $retVal['issue'] = $this->fields['jissue'][0];
        $retVal['spage'] = $this->fields['jspage'][0];
        $retVal['epage'] = $this->fields['jepage'][0];
        $retVal['date'] = $this->fields['publishDate'][0];
        $retVal['title'] = $this->fields['jtitle'][0];
        $retVal['issn'] = $this->fields['issn'];
        $retVal['edition'] = $this->fields['edition'];
        return $retVal;
    }

    /**
     * TUBHH Enhancement for GBV Discovery
     * Return the reference of an eBook
     * An array will be returned with keys=volume, issue, startpage [spage], endpage [epage] and publication year [date].
     *
     * @access  public
     * @return  array
     */
    public function getEbookFieldedReference()
    {
        $retVal = array();
        $retVal['title'] = $this->fields['title'];
        $retVal['date'] = $this->fields['publishDate'][0];
        $retVal['isbn'] = $this->fields['isbn'];
        $retVal['author'] = $this->fields['author'];
        return $retVal;
    }

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

    /**
     * Assign necessary Smarty variables and return a template name to.
     * load in order to export the record in the requested format.  For.
     * legal values, see getExportFormats().  Returns null if format is.
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
            $interface->assign('pcfields', $this->fields);
            return 'RecordDrivers/PC/export-endnote.tpl';
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
            $interface->assign('pcfields', $this->fields);
            return 'RecordDrivers/PC/export-refworks.tpl';
            break;
        case 'bibtex':
            // This makes use of core metadata fields in addition to the
            // assignment below:
            header('Content-type: text/plain; charset=utf-8');
            $interface->assign('pcfields', $this->fields);
            return 'RecordDrivers/PC/export-bibtex.tpl';
            break;
        case 'ris':
            // This makes use of core metadata fields in addition to the
            // assignment below:
            header('Content-type: text/plain; charset=utf-8');
            $interface->assign('displayFormat', $this->getRISType());
            $interface->assign('pcfields', $this->fields);
            return 'RecordDrivers/PC/export-ris.tpl';
            break;
        default:
            return null;
        }
    }

    /**
     * Get an array of strings representing formats in which this record's.
     * data may be exported (empty if none).  Legal values: "RefWorks",.
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
        $possible = array('RefWorks', 'EndNote', 'MARC', 'RDF', 'MARCXML', 'BibTeX', 'RIS');

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

    public function getRISType() {
    /* possible return values
    ABST (abstract reference)
    ADVS (audiovisual material)
    ART (art work)
    BILL (bill/resolution)
    BOOK (whole book reference)
    CASE (case)
    CHAP (book chapter reference)
    COMP (computer program)
    CONF (conference proceeding)
    CTLG (catalog)
    DATA (data file)
    ELEC (electronic citation)
    GEN (generic)
    HEAR (hearing)
    ICOMM (internet communication)
    INPR (in press reference)
    JFULL (journal/periodical - full)
    JOUR (journal/periodical reference)
    MAP (map)
    MGZN (magazine article)
    MPCT (motion picture)
    MUSIC (music score)
    NEWS (newspaper)
    PAMP (pamphlet)
    PAT (patent)
    PCOMM (personal communication)
    RPRT (report)
    SER (serial - book, monograph)
    SLIDE (slide)
    SOUND (sound recording)
    STAT (statute)
    THES (thesis/dissertation)
    UNBILL (unenacted bill/resolution)
    UNPB (unpublished work reference)
    VIDEO (video recording)
    */
        if (is_array($this->fields['format'])) {
            if (in_array('book', $this->fields['format']) || in_array('ebook', $this->fields['format'])) return 'BOOK';
            if (in_array('book_chapter', $this->fields['format'])) return 'CHAP';
            if (in_array('Article', $this->fields['format'])) return 'MGZN';
            if (in_array('Journal', $this->fields['format'])) return 'JOUR';
            if (in_array('dissertation', $this->fields['format'])) return 'THES';
            return 'GEN';
        }
        else {
            if ($this->fields['format'] == 'book' || $this->fields['format'] == 'ebook') return 'BOOK';
            if ($this->fields['format'] == 'Article') return 'MGZN';
            if ($this->fields['format'] == 'Journal') return 'JOUR';
            if ($this->fields['format'] == 'dissertation') return 'THES';
            return 'GEN';
        }
    }

}

?>
