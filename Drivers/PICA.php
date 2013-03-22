<?php
/**
 * ILS Driver for VuFind to get information from PICA
 *
 * Authentication in this driver is handled via LDAP, not via normal PICA!
 * First check local vufind database, and if no user is found, check LDAP.
 * LDAP configuration settings are taken from vufinds config.ini
 *
 * PHP version 5
 *
 * Copyright (C) Oliver Marahrens 2010.
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
 * @package  ILS_Drivers
 * @author   Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_an_ils_driver Wiki
 */
require_once 'Interface.php';
require_once 'DAIA.php';
require_once 'services/MyResearch/lib/User.php';
require_once 'sys/authn/LDAPConfigurationParameter.php';

/**
 * ILS Driver for VuFind to get information from PICA
 *
 * Holding information is obtained by DAIA, so it's not necessary to implement those
 * functions here; we just need to extend the DAIA driver.
 *
 * @category VuFind
 * @package  ILS_Drivers
 * @author   Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_an_ils_driver Wiki
 */
class PICA extends DAIA
{
    private $_username;
    private $_password;
    private $_ldapConfigurationParameter;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
        parent::__construct();

        $configArray = parse_ini_file(
            dirname(__FILE__) . '/../conf/PICA.ini', true
        );

        $this->catalogHost = $configArray['Catalog']['Host'];
        $this->renewalsScript = $configArray['Catalog']['renewalsScript'];
    }

    // public functions implemented to satisfy Driver Interface

    /**
     * Patron Login
     *
     * This is responsible for authenticating a patron against the catalog.
     *
     * @param string $barcode  The patron barcode
     * @param string $password The patron password
     *
     * @return mixed           Associative array of patron info on successful login,
     * null on unsuccessful login, PEAR_Error on error.
     * @access public
     */
    public function patronLogin($barcode, $password)
    {
        $this->_ldapConfigurationParameter = new LDAPConfigurationParameter();
        if (isset($_SESSION['picauser']) === true) {
            $barcode = $_SESSION['picauser']->username;
            $password = $_SESSION['picauser']->cat_password;
        }
        if ($barcode == '' || $password == '') {
            return new PEAR_Error('Invalid Login, Please try again.');
        }
        $this->_username = $barcode;
        $this->_password = $password;

        // first look into local database
        $loginUser = new User();
        $loginUser->username = $barcode;
        $loginUser->password = $password;
        if ($loginUser->find(true)) {
            $loginUser->cat_username = $barcode;
            $loginUser->cat_password = $password;
            $userArray = array(
                'id' => $loginUser->id,
                'firstname' =>  $loginUser->firstname,
                'lastname' => $loginUser->lastname,
                'email' => $loginUser->email,
                'username' => $barcode,
                'password' => $password,
                'cat_username' => $barcode,
                'cat_password' => $password
            );
            $_SESSION['picauser'] = $loginUser;
            return $userArray;
        } else {
            // if not found locally, look into LDAP for user data
            $result = $this->_bindUser();
        }

        if (get_class($result) === 'PEAR_Error') {
            return false;
        }

        return $result;
    }

    /**
     * Get Patron Profile
     *
     * This is responsible for retrieving the profile for a specific patron.
     *
     * @param array $user The patron array
     *
     * @return mixed      Array of the patron's profile data on success,
     * PEAR_Error otherwise.
     * @access public
     */
    public function getMyProfile($user)
    {
        $userinfo = $this->_getUserdataFromLdap();
        // firstname
        $recordList['firstname'] = $userinfo->firstname;
        // lastname
        $recordList['lastname'] = $userinfo->lastname;
        // email
        $recordList['email'] = $userinfo->email;
        //Street and Number $ City $ Zip
        if ($userinfo->address) {
            $address = explode("\$", $userinfo->address);
            // address1
            $recordList['address1'] = $address[1];
            // address2
            $recordList['address2'] = $address[2];
            // zip (Post Code)
            $recordList['zip'] = $address[3];
        }
        else if ($userinfo->homeaddress) {
            $address = explode("\$", $userinfo->homeaddress);
            $recordList['address2'] = $address[0];
            $recordList['zip'] = $address[1];
        }
        // phone
        $recordList['phone'] = $userinfo->phone;
        // group
        $recordList['group'] = $userinfo->group;
        if ($recordList['firstname'] === null) {
            $recordList = $user;
            // add a group
            $recordList['group'] = 'No library account';
        }
        $recordList['expiration'] = $userinfo->libExpire;
        $recordList['status'] = $userinfo->borrowerStatus;
        // Get the LOANS-Page to extract a message for the user
        $URL = "/loan/DB=1/USERINFO";
        $POST = array(
            "ACT" => "UI_DATA",
            "LNG" => "DU",
            "BOR_U" => $_SESSION['picauser']->username,
            "BOR_PW" => $_SESSION['picauser']->cat_password
        );
        $postit = $this->_postit($URL, $POST);
        // How many messages are there?
        $messages = substr_count($postit, '<strong class="alert">');
        $position = 0;
        if ($messages === 2) {
            // ignore the first message (its only the message to close the window after finishing)
            for ($n = 0; $n<2; $n++) {
                $pos = strpos($postit, '<strong class="alert">', $position);
                $pos_close = strpos($postit, '</strong>', $pos);
                $value = substr($postit, $pos+22, ($pos_close-$pos-22));
                $position = $pos + 1;
            }
            $recordList['message'] = $value;
        }
        return $recordList;
    }

    /**
     * Get Patron Transactions
     *
     * This is responsible for retrieving all transactions (i.e. checked out items)
     * by a specific patron.
     *
     * @param array $patron The patron array from patronLogin
     *
     * @return mixed        Array of the patron's transactions on success,
     * PEAR_Error otherwise.
     * @access public
     */
    public function getMyTransactions($patron)
    {
        $URL = "/loan/DB=1/USERINFO";
        $POST = array(
            "ACT" => "UI_LOL",
            "LNG" => "DU",
            "BOR_U" => $_SESSION['picauser']->username,
            "BOR_PW" => $_SESSION['picauser']->cat_password
        );
        $postit = $this->_postit($URL, $POST);
        // How many items are there?
        $holds = substr_count($postit, 'input type="checkbox" name="VB"');
        $iframes = $holdsByIframe = substr_count($postit, '<iframe');
        $ppns = array();
        $expiration = array();
        $transList = array();
        $barcode = array();
        $reservations = array();
        $titles = array();
        if ($holdsByIframe >= $holds) {
            $position = strpos($postit, '<iframe');
            for ($i = 0; $i < $iframes; $i++) {
                $pos = strpos($postit, 'VBAR=', $position);
                $value = substr($postit, $pos+9, 8);
                $completeValue = substr($postit, $pos+5, 12);
                $barcode[] = $completeValue;
                $bc = $this->_getPpnByBarcode($value);
                $ppns[] = $bc;
                $position = $pos + 1;
                $current_position = $position;
                $position_state = null;
                for ($n = 0; $n<6; $n++) {
                    $current_position = $this->_strposBackwards(
                        $postit, '<td class="value-small">', $current_position-1
                    );
                    if ($n === 1) {
                        $position_reservations = $current_position;
                    }
                    if ($n === 2) {
                        $position_expire = $current_position;
                    }
                    if ($n === 4) {
                        $position_state = $current_position;
                    }
                    if ($n === 5) {
                        $position_title = $current_position;
                    }
                }
                if ($position_state !== null && substr($postit, $position_state+24, 8) !== 'bestellt') {
                    $reservations[] = substr($postit, $position_reservations+24, 1);
                    $expiration[] = substr($postit, $position_expire+24, 10);
                    $renewals[] = $this->_getRenewals($completeValue);
                    $closing_title = strpos($postit, '</td>', $position_title);
                    $titles[] = $completeValue." ".substr($postit, $position_title+24, ($closing_title-$position_title-24));
                }
                else {
                    $holdsByIframe--;
                    array_pop($ppns);
                    array_pop($barcode);
                }
            }
            $holds = $holdsByIframe;
        } else {
            // no iframes in PICA catalog, use checkboxes instead
            // Warning: reserved items have no checkbox in OPC! They wont appear
            // in this list
            $position = strpos($postit, 'input type="checkbox" name="VB"');
            for ($i = 0; $i < $holds; $i++) {
                $pos = strpos($postit, 'value=', $position);
                $value = substr($postit, $pos+11, 8);
                $completeValue = substr($postit, $pos+7, 12);
                $barcode[] = $completeValue;
                $ppns[] = $this->_getPpnByBarcode($value);
                $position = $pos + 1;
                $position_expire = $position;
                for ($n = 0; $n<4; $n++) {
                    $position_expire = strpos(
                        $postit, '<td class="value-small">', $position_expire+1
                    );
                }
                $expiration[] = substr($postit, $position_expire+24, 10);
                $renewals[] = $this->_getRenewals($completeValue);
            }
        }
        for ($i = 0; $i < $holds; $i++) {
            if ($ppns[$i] !== false) {
                $transList[] = array(
                    'id'      => $ppns[$i],
                    'duedate' => $expiration[$i],
                    'renewals' => $renewals[$i],
                    'reservations' => $reservations[$i],
                    'vb'      => $barcode[$i],
                    'title'   => $titles[$i]
                );
            } else {
                // There is a problem: no PPN found for this item... lets take id 0
                // to avoid serious error (that will just return an empty title)
                $transList[] = array(
                    'id'      => 0,
                    'duedate' => $expiration[$i],
                    'renewals' => $renewals[$i],
                    'reservations' => $reservations[$i],
                    'vb'      => $barcode[$i],
                    'title'   => $titles[$i]
                );
            }
        }
        //print_r($transList);
        return $transList;
    }

    /**
     * Support method - reverse strpos.
     *
     * @param string $haystack String to search within
     * @param string $needle   String to search for
     * @param int    $offset   Search offset
     *
     * @return int             Offset of $needle in $haystack
     * @access private
     */
    private function _strposBackwards($haystack, $needle, $offset = 0)
    {
        if ($offset === 0) {
            $haystack_reverse = strrev($haystack);
        } else {
            $haystack_reverse = strrev(substr($haystack, 0, $offset));
        }
        $needle_reverse = strrev($needle);
        $position_brutto = strpos($haystack_reverse, $needle_reverse);
        if ($offset === 0) {
            $position_netto = strlen($haystack)-$position_brutto-strlen($needle);
        } else {
            $position_netto = $offset-$position_brutto-strlen($needle);
        }
        return $position_netto;
    }

    /**
     * get the number of renewals
     *
     * @param string $barcode Barcode of the medium
     *
     * @return int number of renewals, if renewals script has not been set, return
     * false
     * @access private
     */
    private function _getRenewals($barcode)
    {
        $renewals = false;
        if (isset($this->renewalsScript) === true) {
            $POST = array(
                "DB" => '1',
                "VBAR" => $barcode,
                "U" => $_SESSION['picauser']->username
            );
            $URL = $this->renewalsScript;
            $postit = $this->_postit($URL, $POST);

            $renewalsString = $postit;
            $pos = strpos($postit, '<span');
            $renewals = strip_tags(substr($renewalsString, $pos));
        }
        return $renewals;
    }
    /**
     * Renew item(s)
     *
     * @param string $recordId Record identifier
     *
     * @return bool            True on success
     * @access public
     */
    public function renew($recordId)
    {
        $URL = "/loan/DB=1/LNG=DU/USERINFO";
        $POST = array(
            "ACT" => "UI_RENEWLOAN",
            "BOR_U" => $_SESSION['picauser']->username,
            "BOR_PW" => $_SESSION['picauser']->cat_password
        );
        if (is_array($recordId) === true) {
            foreach ($recordId as $rid) {
                array_push($POST['VB'], $recordId);
            }
        } else {
            $POST['VB'] = $recordId;
        }
        $postit = $this->_postit($URL, $POST);

        return true;
    }

    /**
     * Get Patron Fines
     *
     * This is responsible for retrieving all fines by a specific patron.
     *
     * @param array $patron The patron array from patronLogin
     *
     * @return mixed        Array of the patron's fines on success, PEAR_Error
     * otherwise.
     * @access public
     */
    public function getMyFines($patron)
    {
        // The patron comes as an array...
        $p = $patron[0];
        $URL = "/loan/DB=1/LNG=DU/USERINFO";
        $POST = array(
            "ACT" => "UI_LOC",
            "BOR_U" => $_SESSION['picauser']->username,
            "BOR_PW" => $_SESSION['picauser']->cat_password
        );
        $postit = $this->_postit($URL, $POST);

        // How many items are there?
        $holds = substr_count($postit, '<td class="plain"')/3;
        $ppns = array();
        $fineDate = array();
        $description = array();
        $fine = array();
        $position = strpos($postit, '<td class="infotab2" align="left">Betrag<td>');
        for ($i = 0; $i < $holds; $i++) {
            $pos = strpos($postit, '<td class="plain"', $position);
            // first class=plain => description
            // length = position of next </td> - startposition
            $nextClosingTd = strpos($postit, '</td>', $pos);
            $description[$i] = substr($postit, $pos+18, ($nextClosingTd-$pos-18));
            $position = $pos + 1;
            // next class=plain => date of fee creation
            $pos = strpos($postit, '<td class="plain"', $position);
            $nextClosingTd = strpos($postit, '</td>', $pos);
            $fineDate[$i] = substr($postit, $pos+18, ($nextClosingTd-$pos-18));
            $position = $pos + 1;
            // next class=plain => amount of fee
            $pos = strpos($postit, '<td class="plain"', $position);
            $nextClosingTd = strpos($postit, '</td>', $pos);
            $fineString = substr($postit, $pos+32, ($nextClosingTd-$pos-32));
            $feeString = explode(',', $fineString);
            $feeString[1] = substr($feeString[1], 0, 2);
            $fine[$i] = (double) implode('', $feeString);
            $position = $pos + 1;
        }

        $fineList = array();
        $amountAll = 0;
        for ($i = 0; $i < $holds; $i++) {
            $fineList[] = array(
                "amount"   => $fine[$i],
                "checkout" => "",
                "fine"     => $fineDate[$i] . ': ' .
                    utf8_encode(html_entity_decode($description[$i])),
                "duedate"  => ""
            );
            $amountAll += $fine[$i];
            // id should be the ppn of the book resulting the fine but there's
            // currently no way to find out the PPN (we have neither barcode nor
            // signature...)
        }
        $fineList[] = array(
            "balance"  => $amountAll
        );
        return $fineList;
    }

    /**
     * Get Patron Holds
     *
     * This is responsible for retrieving all holds by a specific patron.
     *
     * @param array $patron The patron array from patronLogin
     *
     * @return mixed        Array of the patron's holds on success, PEAR_Error
     * otherwise.
     * @access public
     */
    public function getMyHolds($patron)
    {
        $URL = "/loan/DB=1/LNG=DU/USERINFO";
        $POST = array(
            "ACT" => "UI_LOR",
            "BOR_U" => $_SESSION['picauser']->username,
            "BOR_PW" => $_SESSION['picauser']->cat_password
        );
        $postit = $this->_postit($URL, $POST);

        // How many items are there?
        $holds = substr_count($postit, 'input type="checkbox" name="VB"');
        $ppns = array();
        $creation = array();
        $position = strpos($postit, 'input type="checkbox" name="VB"');
        for ($i = 0; $i < $holds; $i++) {
            $pos = strpos($postit, 'value=', $position);
            $value = substr($postit, $pos+11, 8);
            $ppns[] = $this->_getPpnByBarcode($value);
            $position = $pos + 1;
            $position_create = $position;
            for ($n = 0; $n<3; $n++) {
                $position_create = strpos(
                    $postit, '<td class="value-small">', $position_create+1
                );
            }
            $creation[] = str_replace('-', '.', substr($postit, $position_create+24, 10));
        }
        /* items, which are ordered and have no signature yet, are not included in
         * the for-loop getthem by checkbox PPN
         */
        $moreholds = substr_count($postit, 'input type="checkbox" name="PPN"');
        $position = strpos($postit, 'input type="checkbox" name="PPN"');
        for ($i = 0; $i < $moreholds; $i++) {
            $pos = strpos($postit, 'value=', $position);
            // get the length of PPN
               $x = strpos($postit, '"', $pos+7);
            $value = substr($postit, $pos+7, $x-$pos-7);
            // problem: the value presented here does not contain the checksum!
            // so its not a valid identifier
            // we need to calculate the checksum
            $checksum = 0;
            for ($i=0; $i<strlen($value);$i++) {
                $checksum += $value[$i]*(9-$i);
            }
            if ($checksum%11 === 1) {
                $checksum = 'X';
            } else if ($checksum%11 === 0) {
                $checksum = 0;
            } else {
                $checksum = 11 - $checksum%11;
            }
            $ppns[] = $value.$checksum;
            $position = $pos + 1;
            $position_create = $position;
            for ($n = 0; $n<3; $n++) {
                $position_create = strpos(
                    $postit, '<td class="value-small">', $position_create+1
                );
            }
            $creation[] = str_replace('-', '.', substr($postit, $position_create+24, 10));
        }

        /* media ordered from closed stack is not visible on the UI_LOR page
         * requested above... we need to do another request and filter the
         * UI_LOL-page for requests
         */
        $POST_LOL = array(
            "ACT" => "UI_LOL",
            "BOR_U" => $_SESSION['picauser']->username,
            "BOR_PW" => $_SESSION['picauser']->cat_password
        );
        $postit_lol = $this->_postit($URL, $POST_LOL);

        $requests = substr_count(
            $postit_lol, '<td class="value-small">bestellt</td>'
        );
        $position = 0;
        for ($i = 0; $i < $requests; $i++) {
            $position = strpos(
                $postit_lol, '<td class="value-small">bestellt</td>', $position+1
            );
            $pos = strpos($postit_lol, '<td class="value-small">', ($position-100));
            $nextClosingTd = strpos($postit_lol, '</td>', $pos);
            $value = substr($postit_lol, $pos+27, ($nextClosingTd-$pos-27));
            $ppns[] = $this->_getPpnByBarcode($value);
            $creation[] = date('d.m.Y');
        }

        for ($i = 0; $i < ($holds+$moreholds+$requests); $i++) {
            $holdList[] = array(
                "id"       => $ppns[$i],
                "create"   => $creation[$i]
            );
        }
        return $holdList;
    }

    /**
     * Place Hold
     *
     * Attempts to place a hold or recall on a particular item and returns
     * an array with result details or a PEAR error on failure of support classes
     *
     * TODO: implement it for PICA
     * Make a request on a specific record
     *
     * @param array $holdDetails An array of item and patron data
     *
     * @return mixed An array of data on the request including
     * whether or not it was successful and a system message (if available) or a
     * PEAR error on failure of support classes
     * @access public
     */
    //public function placeHold($holdDetails)
    //{
    //}


    /**
     * Get Funds
     *
     * Return a list of funds which may be used to limit the getNewItems list.
     *
     * TODO: implement it for PICA
     *
     * @return array An associative array with key = fund ID, value = fund name.
     * @access public
     */
    public function getFunds()
    {
        return null;
    }


    // private functions to connect to PICA

    /**
     * post something to a foreign host
     *
     * @param string $file         POST target URL
     * @param string $data_to_send POST data
     *
     * @return string              POST response
     * @access private
     */
    private function _postit($file, $data_to_send)
    {
        // Parameter verarbeiten
        //print_r($data_to_send); # Zum Debuggen
        foreach ($data_to_send as $key => $dat) {
            $data_to_send[$key]
                = "$key=".rawurlencode(utf8_encode(stripslashes($dat)));
        }
        $postData = implode("&", $data_to_send);

        // HTTP-Header vorbereiten
        $out  = "POST $file HTTP/1.1\r\n";
        $out .= "Host: " . $this->catalogHost . "\r\n";
        $out .= "Content-type: application/x-www-form-urlencoded\r\n";
        $out .= "Content-length: ". strlen($postData) ."\r\n";
        $out .= "User-Agent: ".$_SERVER["HTTP_USER_AGENT"]."\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "\r\n";
        $out .= $postData;
        if (!$conex = @fsockopen($this->catalogHost, "80", $errno, $errstr, 10)) {
            return 0;
        }
        fwrite($conex, $out);
        $data = '';
        while (!feof($conex)) {
            $data .= fgets($conex, 512);
        }
        fclose($conex);
        return $data;
    }


    /**
     * gets a PPN by its barcode
     *
     * @param string $barcode Barcode to use for lookup
     *
     * @return string         PPN
     * @access private
     */
    private function _getPpnByBarcode($barcode)
    {
        $searchUrl = "http://" . $this->catalogHost .
            "/DB=1/XML=1.0/CMD?ACT=SRCHA&IKT=1016&SRT=YOP&TRM=sgn+$barcode";
        $doc = new DomDocument();
        $doc->load($searchUrl);
        // get Availability information from DAIA
        $itemlist = $doc->getElementsByTagName('SHORTTITLE');
        if (count($itemlist->item(0)->attributes) > 0) {
            $ppn = $itemlist->item(0)->attributes->getNamedItem('PPN')->nodeValue;
        } else {
            return false;
        }
        return $ppn;
    }

    /**
     * gets holdings of magazine and journal exemplars
     *
     * @param string $ppn PPN identifier
     *
     * @return array
     * @access public
     */
    public function getJournalHoldings($ppn)
    {
        $searchUrl = "http://" . $this->catalogHost .
            "/DB=1/XML=1.0/SET=1/TTL=1/FAM?PPN=" . $ppn . "&SHRTST=100";
        $doc = new DomDocument();
        $doc->load($searchUrl);
        $itemlist = $doc->getElementsByTagName('SHORTTITLE');
        $ppn = array();
        for ($n = 0; $itemlist->item($n); $n++) {
            if (count($itemlist->item($n)->attributes) > 0) {
                $ppn[] = $itemlist->item($n)->attributes->getNamedItem('PPN')->nodeValue;
            }
        }
        return $ppn;
    }

    /**
     * private authentication function adapted from Authentication class
     * we are using LDAP for authentication - not OCLC PICA standard
     *
     * @return mixed Associative array of patron info on successful login,
     * null on unsuccessful login, PEAR_Error on error.
     * @access private
     */
    private function _bindUser()
    {
        $ldapConnectionParameter
            = $this->_ldapConfigurationParameter->getParameter();
        // Try to connect to LDAP and die if we can't; note that some LDAP setups
        // will successfully return a resource from ldap_connect even if the server
        // is unavailable -- we need to check for bad return values again at search
        // time!
        $ldapConnection = @ldap_connect(
            $ldapConnectionParameter['host'], $ldapConnectionParameter['port']
        );
        if (!$ldapConnection) {
            return new PEAR_ERROR('Unable to connect to LDAP server.');
        }

        // Set LDAP options -- use protocol version 3 and then initiate TLS so we
        // can have a secure connection over the standard LDAP port.
        @ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);
        #if (!@ldap_start_tls($ldapConnection)) {
        #    return new PEAR_ERROR('Problem starting LDAP TLS.');
        #}

        // If bind_username and bind_password were supplied in the config file, use
        // them to access LDAP before proceeding.  In some LDAP setups, these
        // settings can be excluded in order to skip this step.
        if (isset($ldapConnectionParameter['bind_username'])
            && isset($ldapConnectionParameter['bind_password'])
        ) {
            $ldapBind = @ldap_bind(
                $ldapConnection, $ldapConnectionParameter['bind_username'],
                $ldapConnectionParameter['bind_password']
            );
            if (!$ldapBind) {
                return new PEAR_ERROR('Unable to bind to LDAP server.');
            }
        }

        // Search for username
        $ldapFilter = $ldapConnectionParameter['username'] . '=' . $this->_username;
        $ldapSearch = @ldap_search(
            $ldapConnection, $ldapConnectionParameter['basedn'], $ldapFilter
        );
        if (!$ldapSearch) {
            return new PEAR_ERROR('Unable to connect to LDAP server.');
        }

        $info = ldap_get_entries($ldapConnection, $ldapSearch);
        if ($info['count']) {
            // Validate the user credentials by attempting to bind to LDAP:
            $ldapBind = @ldap_bind($ldapConnection, $info[0]['dn'], $this->_password);
            if ($ldapBind) {
                // If the bind was successful, we can look up the full user info:
                $ldapSearch = ldap_search(
                    $ldapConnection, $ldapConnectionParameter['basedn'], $ldapFilter
                );
                $data = ldap_get_entries($ldapConnection, $ldapSearch);
                return $this->_processLDAPUser($data, $ldapConnectionParameter);
            }
        }

        return new PEAR_ERROR('Username or password wrong! Access denied.');
    }

    /**
     * Support method for _bindUser() -- load user details into session and return
     * array of basic user data.
     *
     * @param array $data                    Data to process
     * @param array $ldapConnectionParameter LDAP settings
     *
     * @return array
     * @access private
     */
    private function _processLDAPUser($data, $ldapConnectionParameter)
    {
        $user = array();
        $user['username'] = $this->_username;
        for ($i=0; $i<$data["count"];$i++) {
            for ($j=0;$j<$data[$i]["count"];$j++) {

                if (($data[$i][$j] == $ldapConnectionParameter['firstname'])
                    && ($ldapConnectionParameter['firstname'] != "")
                ) {
                    $user['firstname'] = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['lastname']
                    && ($ldapConnectionParameter['lastname'] != "")
                ) {
                    $user['lastname'] = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['email']
                    && ($ldapConnectionParameter['email'] != "")
                ) {
                     $user['email'] = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['cat_username']
                    && ($ldapConnectionParameter['cat_username'] != "")
                ) {
                     $user['cat_username'] = $data[$i][$data[$i][$j]][0];
                }
            }
        }
        // do not store cat_password into database, but assign it to Session user
        $sessionuser = new User();
        $sessionuser->username = $this->_username;
        $sessionuser->cat_password = $this->_password;
        $_SESSION['picauser'] = $sessionuser;
        return $user;
    }

    /**
     * Support method for _getUserdataFromLdap() -- load LDAP data into User object.
     *
     * @param array $data                    Data to process
     * @param array $ldapConnectionParameter LDAP settings
     *
     * @return User
     * @access private
     */
    private function _getUserdata($data, $ldapConnectionParameter)
    {
        $user = new User();
        $user->username = $this->_username;
        for ($i=0; $i<$data["count"];$i++) {
            for ($j=0;$j<$data[$i]["count"];$j++) {

                if (($data[$i][$j] == $ldapConnectionParameter['firstname'])
                    && ($ldapConnectionParameter['firstname'] != "")
                ) {
                    $user->firstname = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['lastname']
                    && ($ldapConnectionParameter['lastname'] != "")
                ) {
                    $user->lastname = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['email']
                    && ($ldapConnectionParameter['email'] != "")
                ) {
                    $user->email = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['cat_username']
                    && ($ldapConnectionParameter['cat_username'] != "")
                ) {
                     $user->cat_username = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['address']
                    && ($ldapConnectionParameter['address'] != "")
                ) {
                     $user->address = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['homeaddress']
                    && ($ldapConnectionParameter['homeaddress'] != "")
                ) {
                     $user->homeaddress = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['phone']
                    && ($ldapConnectionParameter['phone'] != "")
                ) {
                     $user->phone = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['group']
                    && ($ldapConnectionParameter['group'] != "")
                ) {
                     $user->group = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['expiration']
                    && ($ldapConnectionParameter['expiration'] != "")
                ) {
                     $libExpireYear = substr($data[$i][$data[$i][$j]][0], 0, 4);
                     $libExpireMonth = substr($data[$i][$data[$i][$j]][0], 4, 2);
                     $libExpireDay = substr($data[$i][$data[$i][$j]][0], 6, 2);
                     $user->libExpire = $libExpireDay.".".$libExpireMonth.".".$libExpireYear;
                }

                if ($data[$i][$j] == $ldapConnectionParameter['status']
                    && ($ldapConnectionParameter['status'] != "")
                ) {
                     $user->borrowerStatus = $data[$i][$data[$i][$j]][0];
                }
            }
        }
        return $user;
    }

    /**
     * Support method for getMyProfile() -- connect to LDAP to get detailed user
     * data.
     *
     * @return mixed User object on success, PEAR_Error otherwise.
     * @access private
     */
    private function _getUserdataFromLdap()
    {
        $ldapConnectionParameter
            = $this->_ldapConfigurationParameter->getParameter();

        // Try to connect to LDAP and die if we can't; note that some LDAP setups
        // will successfully return a resource from ldap_connect even if the server
        // is unavailable -- we need to check for bad return values again at search
        // time!
        $ldapConnection = @ldap_connect(
            $ldapConnectionParameter['host'], $ldapConnectionParameter['port']
        );
        if (!$ldapConnection) {
            return new PEAR_ERROR('Unable to connect to LDAP server.');
        }

        // Set LDAP options -- use protocol version 3 and then initiate TLS so we
        // can have a secure connection over the standard LDAP port.
        @ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);
        if (!@ldap_start_tls($ldapConnection)) {
            return new PEAR_ERROR('Problem starting LDAP TLS.');
        }

        // If bind_username and bind_password were supplied in the config file, use
        // them to access LDAP before proceeding.  In some LDAP setups, these
        // settings can be excluded in order to skip this step.
        if (isset($ldapConnectionParameter['bind_username'])
            && isset($ldapConnectionParameter['bind_password'])
        ) {
            $ldapBind = @ldap_bind(
                $ldapConnection, $ldapConnectionParameter['bind_username'],
                $ldapConnectionParameter['bind_password']
            );
            if (!$ldapBind) {
                return new PEAR_ERROR('Unable to bind to LDAP server.');
            }
        }

        // Search for username
        $ldapFilter = $ldapConnectionParameter['username'] . '=' . $this->_username;
        $ldapSearch = @ldap_search(
            $ldapConnection, $ldapConnectionParameter['basedn'], $ldapFilter
        );
        if (!$ldapSearch) {
            return new PEAR_ERROR('Unable to connect to LDAP server.');
        }

        $info = ldap_get_entries($ldapConnection, $ldapSearch);
        if ($info['count']) {
            // Validate the user credentials by attempting to bind to LDAP:
            $ldapBind = @ldap_bind($ldapConnection, $info[0]['dn'], $this->_password);
            if ($ldapBind) {
                // If the bind was successful, we can look up the full user info:
                $ldapSearch = ldap_search(
                    $ldapConnection, $ldapConnectionParameter['basedn'], $ldapFilter
                );
                $data = ldap_get_entries($ldapConnection, $ldapSearch);
                return $this->_getUserdata($data, $ldapConnectionParameter);
            }
        }
    }
}
?>