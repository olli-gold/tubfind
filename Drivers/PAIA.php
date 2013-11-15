<?php
/**
 * ILS Driver for VuFind to get information from PICA using PAIA interface
 *
 * PHP version 5
 *
 * Copyright (C) Oliver Goldschmidt 2013.
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
 * @author   Oliver Goldschmidt <o.goldschmidt@tuhh.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_an_ils_driver Wiki
 */
require_once 'Interface.php';
require_once 'DAIA.php';
require_once 'services/MyResearch/lib/User.php';

/**
 * ILS Driver for VuFind to get information from PICA
 *
 * Holding information is obtained by DAIA, so it's not necessary to implement those
 * functions here; we just need to extend the DAIA driver.
 *
 * @category VuFind
 * @package  ILS_Drivers
 * @author   Oliver Goldschmidt <o.goldschmidt@tuhh.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_an_ils_driver Wiki
 */
class PAIA extends DAIA
{
    private $_username;
    private $_password;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
        parent::__construct();

        $configArray = parse_ini_file('conf/PAIA.ini', true);

        $this->config = $configArray;

        $this->catalogHost = $configArray['Catalog']['Host'];

        $this->paiaHost = $configArray['PAIA']['Host'];
        $this->paiaPort = $configArray['PAIA']['Port'];
    }

    // public functions implemented to satisfy Driver Interface

/*

    cancelHolds
    checkRequestIsValid
    findReserves
    getCancelHoldDetails
    getCancelHoldLink
    getConfig
    getCourses
    getDefaultPickUpLocation
    getDepartments
    getFunds
    getHolding
    getHoldings -- DEPRECATED
    getHoldLink
    getInstructors
    getMyFines
    getMyHolds
    getMyProfile
    getMyTransactions
    getNewItems
    getOfflineMode
    getPickUpLocations
    getPurchaseHistory
    getRenewDetails
    getStatus
    getStatuses
    getSuppressedAuthorityRecords
    getSuppressedRecords
    hasHoldings
    loginIsHidden
    patronLogin
    placeHold
    renewMyItems
    renewMyItemsLink

*/

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
            // if not found locally, get user data from PAIA
            $result = $this->_paiaLogin($barcode, $password);
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
        $userinfo = $this->_getUserdata($user['username']);
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
        /* Not yet supported by PAIA
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
        */
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
        $loans_response = $this->_getAsArray('/paia/core/'.$patron['cat_username'].'/items');

        $holds = count($loans_response['doc']);
        for ($i = 0; $i < $holds; $i++) {
            if ($loans_response['doc'][$i]['status'] == '3') {
                // TODO: set renewable dynamically (not yet supported by PAIA)
                $renewable = true;
                $renew_details = $loans_response['doc'][$i]['item'];
                if ($loans_response['doc'][$i]['canrenew'] == 1) {
                    $renewable = true;
                    $renew_details = $loans_response['doc'][$i]['item'];
                }
                // get PPN from PICA catalog since it is not part of PAIA
                $ppn = $this->_getPpnByBarcode(substr($loans_response['doc'][$i]['item'], -8));
                if ($ppn !== false) {
                    $transList[] = array(
                        'id'      => $ppn,
                        'duedate' => $loans_response['doc'][$i]['duedate'],
                        'renewals' => $loans_response['doc'][$i]['renewals'],
                        'reservations' => $loans_response['doc'][$i]['queue'],
                        'vb'      => $loans_response['doc'][$i]['item'],
                        'title'   => $loans_response['doc'][$i]['about'],
                        'renewable' => $renewable,
                        'renew_details' => $renew_details
                    );
                } else {
                    // There is a problem: no PPN found for this item... lets take id 0
                    // to avoid serious error (that will just return an empty title)
                    $transList[] = array(
                        'id'      => 0,
                        'duedate' => $loans_response['doc'][$i]['duedate'],
                        'renewals' => $loans_response['doc'][$i]['renewals'],
                        'reservations' => $loans_response['doc'][$i]['queue'],
                        'vb'      => $loans_response['doc'][$i]['item'],
                        'title'   => $loans_response['doc'][$i]['about'],
                        'renewable' => $renewable,
                        'renew_details' => $renew_details
                    );
                }
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
    public function renewMyItems($details)
    {
        $it = $details['details'];
        $items = array();
        foreach ($it as $item) {
            $items[] = array('item' => stripslashes($item));
        }
        $patron = $details['patron'];
        $post_data = array("doc" => $items);
        $array_response = $this->_postAsArray('/paia/core/'.$patron['cat_username'].'/renew', $post_data);

        $details = array();

        if (array_key_exists('error', $array_response)) {
            $details[] = array('success' => false, 'sysMessage' => $array_response['error_description']);
        }
        else {
            $elements = $array_response['doc'];
            foreach ($elements as $element) {
                if ($element['status'] == '3') {
                    $details[] = array('success' => true, 'new_date' => $element['duedate'], 'new_time' => '23:59:59', 'item_id' => 0, 'sysMessage' => 'Successfully renewed');
                }
                else {
                    $details[] = array('success' => false, 'new_date' => $element['duedate'], 'new_time' => '23:59:59', 'item_id' => 0, 'sysMessage' => 'Request rejected');
                }
            }
        }
        $returnArray = array('blocks' => false, 'details' => $details);

        return $returnArray;
    }

    public function getRenewDetails($checkOutDetails) {
        return($checkOutDetails['renew_details']);
    }

    /**
     * Cancel item(s)
     *
     * @param string $recordId Record identifier
     *
     * @return bool            True on success
     * @access public
     */
    public function cancelHolds($cancelDetails)
    {
        $it = $cancelDetails['details'];
        $items = array();
        foreach ($it as $item) {
            $items[] = array('item' => stripslashes($item));
        }
        $patron = $cancelDetails['patron'];
        $post_data = array("doc" => $items);

        $array_response = $this->_postAsArray('/paia/core/'.$patron['cat_username'].'/cancel', $post_data);
        $details = array();

        if (array_key_exists('error', $array_response)) {
            $details[] = array('success' => false, 'status' => $array_response['error_description'], 'sysMessage' => $array_response['error']);
        }
        else {
            $count = 0;
            $elements = $array_response['doc'];
            foreach ($elements as $element) {
                if ($element['error']) {
                    $details[] = array('success' => false, 'status' => $element['error'], 'sysMessage' => 'Cancel request rejected');
                }
                else {
                    $details[] = array('success' => true, 'status' => 'Success', 'sysMessage' => 'Successfully cancelled');
                    $count++;
                }
            }
        }
        $returnArray = array('count' => $count, 'items' => $details);

        return $returnArray;
    }

    public function getCancelHoldDetails($checkOutDetails) {
        return($checkOutDetails['cancel_details']);
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
        $fees_response = $this->_getAsArray('/paia/core/'.$patron['cat_username'].'/fees');

        $fineList = array();
        foreach ($fees_response['fee'] as $fine) {
            $ppn = $this->_getPpnByBarcode(substr($fine['item'], -8));
            $fineList[] = array(
                "id"       => $ppn,
                "amount"   => $fine['amount'],
                "checkout" => "",
                "title"    => $fine['about'],
                "feedate"  => $fine['date'],
                "duedate"  => "",
                "fine"     => $fine['feetype']
            );
            // id should be the ppn of the book resulting the fine but there's
            // currently no way to find out the PPN (we have neither barcode nor
            // signature...)
        }
        $fineList[] = array(
            "balance"  => $fees_response['amount']
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
        $loans_response = $this->_getAsArray('/paia/core/'.$patron['cat_username'].'/items');

        $holds = count($loans_response['doc']);
        for ($i = 0; $i < $holds; $i++) {
            // TODO: get date of creation from a reservation
            // this is not yet supported by PAIA
            if ($loans_response['doc'][$i]['status'] == '1' || $loans_response['doc'][$i]['status'] == '2') {
                // get PPN from PICA catalog since it is not part of PAIA
                $ppn = $this->_getPpnByBarcode(substr($loans_response['doc'][$i]['item'], -8));
                $cancel_details = false;
                if ($loans_response['doc'][$i]['cancancel'] == 1) {
                    $cancel_details = $loans_response['doc'][$i]['item'];
                }
                if ($ppn !== false) {
                    $transList[] = array(
                        'id'             => $ppn,
                        'create'         => $loans_response['doc'][$i]['create'],
                        'title'          => $loans_response['doc'][$i]['about'],
                        'expire'         => $loans_response['doc'][$i]['duedate'],
                        'cancel_details' => $cancel_details
                    );
                } else {
                    // There is a problem: no PPN found for this item... lets take id 0
                    // to avoid serious error (that will just return an empty title)
                    $transList[] = array(
                        'id'             => 0,
                        'create'         => $loans_response['doc'][$i]['create'],
                        'title'          => $loans_response['doc'][$i]['about'],
                        'expire'         => $loans_response['doc'][$i]['duedate'],
                        'cancel_details' => $cancel_details
                    );
                }
            }
        }
        //print_r($transList);
        return $transList;
    }

    /**
     * Place Hold
     *
     * Attempts to place a hold or recall on a particular item and returns
     * an array with result details or a PEAR error on failure of support classes
     *
     * Make a request on a specific record
     *
     * @param array $holdDetails An array of item and patron data
     *
     * @return mixed An array of data on the request including
     * whether or not it was successful and a system message (if available) or a
     * PEAR error on failure of support classes
     * @access public
     */
    public function placeHold($holdDetails)
    {
        $item = $holdDetails['item_id'];
        $items = array();
        $items[] = array('item' => stripslashes($item));
        $patron = $holdDetails['patron'];
        $post_data = array("doc" => $items);
        $array_response = $this->_postAsArray('/paia/core/'.$patron['cat_username'].'/request', $post_data);
        $details = array();

        if (array_key_exists('error', $array_response)) {
            $details = array('success' => false, 'sysMessage' => $array_response['error_description']);
        }
        else {
            $elements = $array_response['doc'];
            foreach ($elements as $element) {
                if (array_key_exists('error', $element)) {
                    $details = array('success' => false, 'sysMessage' => $element['error']);
                }
                else {
                    $details = array('success' => true, 'sysMessage' => 'Successfully requested');
                }
            }
        }
        $returnArray = $details;
        return $returnArray;
    }


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


    // private functions to connect to PAIA

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
        // json-encoding
        $postData = stripslashes(json_encode($data_to_send));

        // HTTP-Header vorbereiten
        $out  = "POST $file HTTP/1.1\r\n";
        $out .= "Host: " . $this->paiaHost . ":" . $this->paiaPort . "\r\n";
        $out .= "Content-type: application/json; charset=UTF-8\r\n";
        $out .= "Content-length: ". strlen($postData) ."\r\n";
        $out .= "User-Agent: ".$_SERVER["HTTP_USER_AGENT"]."\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "\r\n";
        $out .= $postData;

        if (!$conex = @fsockopen($this->paiaHost, $this->paiaPort, $errno, $errstr, 10)) {
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
     * post something to a foreign host
     *
     * @param string $file         POST target URL
     * @param string $data_to_send POST data
     *
     * @return string              POST response
     * @access private
     */
    private function _postitresponse($file, $data_to_send, $access_token)
    {
        // json-encoding
        $postData = stripslashes(json_encode($data_to_send));

        // HTTP-Header vorbereiten
        $out  = "POST $file HTTP/1.1\r\n";
        $out .= "Host: " . $this->paiaHost . ":" . $this->paiaPort . "\r\n";
        $out .= "Authorization: Bearer ".$access_token."\r\n";
        $out .= "Content-type: application/json; charset=UTF-8\r\n";
        $out .= "Content-length: ". strlen($postData) ."\r\n";
        $out .= "User-Agent: ".$_SERVER["HTTP_USER_AGENT"]."\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "\r\n";
        $out .= $postData;

        if (!$conex = @fsockopen($this->paiaHost, $this->paiaPort, $errno, $errstr, 10)) {
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

    private function _getit($file, $access_token)
    {
        // HTTP-Header vorbereiten
        $out  = "GET $file HTTP/1.1\r\n";
        $out .= "Host: " . $this->paiaHost . ":" . $this->paiaPort . "\r\n";
        $out .= "Authorization: Bearer ".$access_token."\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "\r\n";

        if (!$conex = @fsockopen($this->paiaHost, $this->paiaPort, $errno, $errstr, 10)) {
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

    private function _getAsArray($file) {
        $pure_response = $this->_getit($file, $_SESSION['paiaToken']);
        $json_start = strpos($pure_response, '{');
        $json_response = substr($pure_response, $json_start);
        $loans_response = json_decode($json_response, true);

        // if the login auth token is invalid, renew it (this is possible unless the session is expired)
        if ($loans_response['error'] && $loans_response['code'] == '401') {
            $sessionuser = $_SESSION['picauser'];
            $this->_paiaLogin($sessionuser->username, $sessionuser->cat_password);

            $pure_response = $this->_getit($file, $_SESSION['paiaToken']);
            $json_start = strpos($pure_response, '{');
            $json_response = substr($pure_response, $json_start);
            $loans_response = json_decode($json_response, true);
        }

        return $loans_response;
    }

    private function _postAsArray($file, $data) {
        $pure_response = $this->_postitresponse($file, $data, $_SESSION['paiaToken']);
        $json_start = strpos($pure_response, '{');
        $json_response = substr($pure_response, $json_start);
        $loans_response = json_decode($json_response, true);

        // if the login auth token is invalid, renew it (this is possible unless the session is expired)
        if ($loans_response['error'] && $loans_response['code'] == '401') {
            $sessionuser = $_SESSION['picauser'];
            $this->_paiaLogin($sessionuser->username, $sessionuser->cat_password);

            $pure_response = $this->_postitresponse($file, $data, $_SESSION['paiaToken']);
            $json_start = strpos($pure_response, '{');
            $json_response = substr($pure_response, $json_start);
            $loans_response = json_decode($json_response, true);
        }

        return $loans_response;
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
     * private authentication function
     * use PAIA for authentication
     *
     * @return mixed Associative array of patron info on successful login,
     * null on unsuccessful login, PEAR_Error on error.
     * @access private
     */
    private function _paiaLogin($username, $password)
    {
        $post_data = array("username" => $username, "password" => $password, "grant_type" => "password", "scope" => "read_patron read_fees read_items write_items change_password");
        $login_response = $this->_postit('/paia/auth/login', $post_data);

        $json_start = strpos($login_response, '{');
        $json_response = substr($login_response, $json_start);
        $array_response = json_decode($json_response, true);

        if (array_key_exists('access_token', $array_response)) {
            $sessionuser = new User();
            $sessionuser->username = $this->_username;
            $sessionuser->cat_password = $this->_password;
            $_SESSION['picauser'] = $sessionuser;
            $_SESSION['paiaToken'] = $array_response['access_token'];
            return $this->_getUserDetails($array_response);
        }
        else if (array_key_exists('error', $array_response)) {
            return new PEAR_ERROR($array_response['error'].": ".$array_response['error_description']);
        }

        return new PEAR_ERROR('Unknown error! Access denied.');
    }

    /**
     * Support method for _paiaLogin() -- load user details into session and return
     * array of basic user data.
     *
     * @param array $data                    Data to process
     *
     * @return array
     * @access private
     */
    private function _getUserDetails($data)
    {
        $pure_response = $this->_getit('/paia/core/'.$data['patron'], $_SESSION['paiaToken']);
        $json_start = strpos($pure_response, '{');
        $json_response = substr($pure_response, $json_start);
        $user_response = json_decode($json_response, true);

        // if the login auth token is invalid, renew it (this is possible unless the session is expired)
        if ($user_response['error'] && $user_response['code'] == '401') {
            $sessionuser = $_SESSION['picauser'];
            $this->_paiaLogin($sessionuser->username, $sessionuser->cat_password);

            $pure_response = $this->_getit('/paia/core/'.$data, $_SESSION['paiaToken']);
            $json_start = strpos($pure_response, '{');
            $json_response = substr($pure_response, $json_start);
            $user_response = json_decode($json_response, true);
        }

        $username = $user_response['name'];
        $nameArr = explode(',', $username);
        $firstname = $nameArr[1];
        $lastname = $nameArr[0];

        $user = array();
        $user['username'] = $data['patron'];
        $user['firstname'] = $firstname;
        $user['lastname'] = $lastname;
        $user['email'] = $user_response['email'];
        $user['cat_username'] = $data['patron'];

        // do not store cat_password into database, but assign it to Session user
        /*
        $sessionuser = new User();
        $sessionuser->username = $this->_username;
        $sessionuser->cat_password = $this->_password;
        */
        return $user;
    }

    /**
     * Support method for getMyProfile() -- building a user object from PAIA user data
     *
     * @param array $data                    Data to process
     *
     * @return User
     * @access private
     */
    private function _getUserdata($data)
    {
        $pure_response = $this->_getit('/paia/core/'.$data, $_SESSION['paiaToken']);
        $json_start = strpos($pure_response, '{');
        $json_response = substr($pure_response, $json_start);
        $user_response = json_decode($json_response, true);

        // if the login auth token is invalid, renew it (this is possible unless the session is expired)
        if ($user_response['error'] && $user_response['code'] == '401') {
            $sessionuser = $_SESSION['picauser'];
            $this->_paiaLogin($sessionuser->username, $sessionuser->cat_password);

            $pure_response = $this->_getit('/paia/core/'.$data, $_SESSION['paiaToken']);
            $json_start = strpos($pure_response, '{');
            $json_response = substr($pure_response, $json_start);
            $user_response = json_decode($json_response, true);
        }

        $username = $user_response['name'];
        $nameArr = explode(',', $username);
        $firstname = $nameArr[1];
        $lastname = $nameArr[0];

        $user = new User();
        $user->username = $data['patron'];
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->email = $user_response['email'];
        $user->cat_username = $data['patron'];
        $user->borrowerStatus = $user_response['status'];
        // not yet supported by PAIA - PAIA only gives very essential data
        /*
        $user->address = $data[$i][$data[$i][$j]][0];
        $user->homeaddress = $data[$i][$data[$i][$j]][0];
        $user->phone = $data[$i][$data[$i][$j]][0];
        $user->group = $data[$i][$data[$i][$j]][0];
        $libExpireYear = substr($data[$i][$data[$i][$j]][0], 0, 4);
        $libExpireMonth = substr($data[$i][$data[$i][$j]][0], 4, 2);
        $libExpireDay = substr($data[$i][$data[$i][$j]][0], 6, 2);
        $user->libExpire = $libExpireDay.".".$libExpireMonth.".".$libExpireYear;
        return $user;
        */
        return $user;
    }

    /**
     * Public Function which retrieves renew, hold and cancel settings from the
     * driver ini file.
     *
     * @param string $function The name of the feature to be checked
     *
     * @return array An array with key-value pairs.
     * @access public
     */
    public function getConfig($function)
    {
        if (isset($this->config[$function]) ) {
            $functionConfig = $this->config[$function];
        } else {
            $functionConfig = false;
        }
        return $functionConfig;
    }

    /**
     * Public Function which changes the password in the library system
     *
     * @param string $function The name of the feature to be checked
     *
     * @return array An array with patron information.
     * @access public
     */
    public function changePassword($patron, $newpassword)
    {
        $sessionuser = $_SESSION['picauser'];

        $post_data = array("patron"       => $patron['username'],
                           "username"     => $patron['firstname']." ".$patron['lastname'],
                           "old_password" => $sessionuser->cat_password,
                           "new_password" => $newpassword);

        $array_response = $this->_postAsArray('/paia/auth/change', $post_data);

        $details = array();

        if (array_key_exists('error', $array_response)) {
            $details = array('success' => false, 'status' => $array_response['error'], 'sysMessage' => $array_response['error_description']);
        }
        else {
            $element = $array_response['patron'];
            if (array_key_exists('error', $element)) {
                $details = array('success' => false, 'status' => 'Failure changing password', 'sysMessage' => $element['error']);
            }
            else {
                $details = array('success' => true, 'status' => 'Successfully changed');

                // TODO: push password also to LDAP (but make that configurable since this is non-standard)

                // replace password for currently logged in user with the new one
                $sessionuser->password = $newsecret;
                $sessionuser->cat_password = $newsecret;
                $_SESSION['picauser'] = $sessionuser;
            }
        }
        $returnArray = $details;
        return $returnArray;
    }

}
?>