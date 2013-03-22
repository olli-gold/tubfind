<?php
/**
 * Solr Statistics Class
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2007.
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
 * @link     http://vufind.org/wiki/system_classes#index_interface Wiki
 */

require_once 'Solr.php';

/**
 * Solr Statistics Class
 *
 * Offers functionality for recording usage statistics data into Solr
 *
 * @category VuFind
 * @package  Support_Classes
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes#index_interface Wiki
 */
class SolrStats extends Solr
{
    private $_institution = '';

    /**
     * Constructor
     *
     * @param string $host The URL for the local Solr Server
     *
     * @access public
     */
    public function __construct($host)
    {
        parent::__construct($host, 'stats');
    }

    /**
     * Set the institution name saved in the statistics.
     *
     * @param string $institution Name to use.
     *
     * @return void
     * @access public
     */
    public function setInstitution($institution)
    {
        $this->_institution = $institution;
    }

    /**
     * Record a search.
     *
     * @param string $phrase Search term(s)
     * @param string $type   Search type
     *
     * @return mixed         Boolean true on success, PEAR_Error otherwise
     * @access public
     */
    public function saveSearch($phrase, $type)
    {
        $doc = array();
        $doc['phrase'] = $phrase;
        $doc['type'] = $type;

        return $this->_save($doc);
    }

    /**
     * Record a search that yielded zero hits.
     *
     * @param string $phrase Search term(s)
     * @param string $type   Search type
     *
     * @return mixed         Boolean true on success, PEAR_Error otherwise
     * @access public
     */
    public function saveNoHits($phrase, $type)
    {
        $doc = array();
        $doc['phrase'] = $phrase;
        $doc['type'] = $type;
        $doc['noresults'] = 'T';

        return $this->_save($doc);
    }

    /**
     * Record access to a particular record.
     *
     * @param string $id Record that was accessed
     *
     * @return mixed     Boolean true on success, PEAR_Error otherwise
     * @access public
     */
    public function saveRecordView($id)
    {
        $doc = array();
        $doc['recordId'] = $id;

        return $this->_save($doc);
    }

    /**
     * Build and store a Solr document from an array of fields.
     *
     * @param array $data Fields to store
     *
     * @return mixed     Boolean true on success, PEAR_Error otherwise
     * @access private
     */
    private function _save($data = array())
    {
        $userAgent = $this->_determineBrowser();

        $data['id'] = uniqid('', true);
        $data['datestamp'] = substr(date('c', strtotime('now')), 0, -6) . 'Z';
        $data['institution'] = $this->_institution;
        $data['browser'] = $userAgent['browser'];
        $data['browserVersion'] = $userAgent['browserVersion'];
        $data['ipaddress'] = $_SERVER['REMOTE_ADDR'];
        $data['referrer'] = isset($_SERVER['HTTP_REFERER'])
            ? $_SERVER['HTTP_REFERER'] : '';
        $data['url'] = $_SERVER['REQUEST_URI'];

        $xml = $this->getSaveXML($data);
        if ($this->saveRecord($xml)) {
            $this->commit();
            return true;
        } else {
            return new PEAR_Error('Could not record statistics');
        }
    }

    /**
     * Determine the browser type.
     *
     * @return string
     * @access private
     */
    private function _determineBrowser()
    {
        // Parse User Agent String
        $code = $_SERVER['HTTP_USER_AGENT'];
        preg_match_all('/\([^"]*\)|[^ ]+/', $code, $info);
        $info = $info[0];

        // Determine Browser
        if (isset($info[5])) {
            // Safari
            $browser = explode('/', $info[5]);
            $version = explode('/', $info[4]);
            $product = array($browser[0], $version[1]);
        } elseif (isset($info[3])) {
            // Firefox
            $product = $info[3];
            $product = explode('/', $product);
        } else {
            $product = explode('; ', $info[1]);
            if ($product[2] == 'MSIE') {
                // IE
                $product = explode(' ', $product[2]);
            } else {
                $product = array('Other');
            }
        }

        // Parse System Info
        $system = $info[1];

        // Build new return array
        $info = array('browser' => $product[0],
                      'browserVersion' => $product[1],
                      'system' => $system);
        return $info;
    }
}
?>