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
 * TUBdok Record Driver
 *
 * This class is designed to handle TUBdok records.  Much of its functionality
 * is inherited from the default index-based driver.
 */
class TubdokRecord extends IndexRecord
{
    public function getSearchResult($view = 'list')
    {
        global $interface;

        parent::getSearchResult($view);

        $interface->assign('summDocId', $this->getDocumentID());
        $interface->assign('summFileUrl', $this->getFileUrl());
        $interface->assign('summFiles', $this->getFiles());
        $interface->assign('summDocUrl', $this->getDocUrl());
        $interface->assign('summFileName', $this->getFileName());
        $interface->assign('summTeaser', $this->getTeaser());
        $interface->assign('summDate', $this->getPublicationDates());
        return 'RecordDrivers/TUBdok/result.tpl';
    }

    /** 
     * Get the ID for the record. 
     * 
     * @access  protected 
     * @return  string 
     */
    protected function getDocumentID()
    {
        return isset($this->fields['docid']) ?
            $this->fields['docid'] : '';
    }

    protected function getFiles() {
        $result = array();
        $elements = $this->getFileUrl();
        foreach ($elements as $url) {
            $result[basename($url)] = $url;
        }
        return $result;
    }

    /**
     * Get the URL for the records file.
     *
     * @access  protected
     * @return  string
     */
    protected function getFileUrl()
    {
        return isset($this->fields['docurl_str_mv']) ?
            $this->fields['docurl_str_mv'] : '';
    }

    /**
     * Get the name for the records file.
     *
     * @access  protected
     * @return  string
     */
    protected function getFileName()
    {
        $result = array();
        foreach ($this->fields['docurl_str_mv'] as $file) {
            $result[] = basename($file);
        }
        return $result;
    }

    /** 
     * Get the URL for the whole record. 
     * 
     * @access  protected 
     * @return  string 
     */
    protected function getDocUrl()
    {
        return isset($this->fields['werkurl']) ?
            $this->fields['werkurl'] : '';
    }

    /**. 
     * Get the Teaser for the record.
     *. 
     * @access  protected. 
     * @return  string. 
     */
    protected function getTeaser()
    {
        return isset($this->fields['teaser']) ?
            $this->fields['teaser'] : '';
    }

    /**
     * Get the publication dates of the record.  See also getDateSpan().
     *
     * @access  protected
     * @return  string
     */
    protected function getPublicationDates()
    {
        return isset($this->fields['created']) ? 
            $this->fields['created'] : '';
    }

    /**
     * Get the full title of the record.
     *
     * @access  protected
     * @return  string
     */
    protected function getTitle()
    {
        return isset($this->fields['title']) ?
            $this->fields['title'] : '';
    }

}
?>
