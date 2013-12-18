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
        return 'RecordDrivers/PC/result.tpl';
    }

    public function getCoreMetadata() {
        global $interface;
        parent::getCoreMetadata();
        $interface->assign('primoRecord', true);
        $interface->assign('doi', $this->getDoi());
        $interface->assign('sfxmenu', $this->getSfxMenu());
        $interface->assign('sfxbutton', $this->getSfxMenuButton());
        /*
        $interface->assign('articleChildren', $this->getArticleChildren());
        $interface->assign('coreSubseries', $this->getSubseries());
        */

        return 'RecordDrivers/Index/core.tpl';
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
    protected function getDoi() {
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
     * Get just the base portion of the first listed ISSN (or false if no ISSNs).
     *
     * @access  protected
     * @return  mixed
     */
    protected function getCleanISSN()
    {
        $issn = parent::getCleanISSN();
/*        if ($issn === false) {
            $issn = $this->_getFirstFieldValue('773', array('x'));
        }*/
        return $issn;
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

        if (isset($this->fields['url'])) {
            $params = array();
            $urlArray = explode('?', $this->fields['url'][0]);
            $paramsArray = explode('&', $urlArray[1]);
            foreach ($paramsArray as $paramElement) {
                $paramElementArray = explode('=', $paramElement);
                $params[$paramElementArray[0]] = $paramElementArray[1];
            }
            if ($menu == true) {
                $params['disable_directlink'] = "true";
                $params['sfx.directlink'] = "off";
            }
            // Assemble the URL:
            $parts = array();
            foreach ($params as $key => $value) {
                if ($key == 'svc.fulltext') continue;
                if (is_array($value) === true) {
                    $parts[] = $key . '=' . urlencode($value[0]);
                }
                else {
                    $parts[] = $key . '=' . urlencode($value);
                }
            }
            return implode('&', $parts);
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
                                                                                                                            
}

?>
