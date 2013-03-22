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
 * Website Record Driver
 *
 * This class is designed to handle Website records.  Much of its functionality
 * is inherited from the default index-based driver.
 */
class WebpageTUHHRecord extends IndexRecord
{
    public function getSearchResult($view = 'list')
    {
        parent::getSearchResult($view);
        global $interface;
        $interface->assign('summContent', $this->getContent());
        $interface->assign('summTitleGer', $this->getGermanTitle());
        $interface->assign('summTitleEng', $this->getEnglishTitle());
        return 'RecordDrivers/WebpageTUHH/result.tpl';
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

    /**. 
     * Get the full german title of the record.. 
     *. 
     * @access  protected. 
     * @return  string. 
     */
    protected function getGermanTitle()
    {
        return isset($this->fields['titleGer']) ?
            $this->fields['titleGer'] : '';
    }

    /**.. 
     * Get the full english title of the record... 
     *.. 
     * @access  protected.. 
     * @return  string.. 
     */
    protected function getEnglishTitle()
    {
        return isset($this->fields['titleEng']) ?
            $this->fields['titleEng'] : '';
    }

    /**. 
     * Get the content of the record.. 
     *. 
     * @access  protected. 
     * @return  string. 
     */
    protected function getContent()
    {
        return isset($this->fields['contents']) ?
            $this->fields['contents'] : '';
    }
                                                             
}

?>
