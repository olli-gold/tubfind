<?php
/**
 * Koha ILS Driver
 *
 * PHP version 5
 *
 * Copyright (C) Ayesha Abed Library, BRAC University 2010.
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
 * @author   Altaf Mahmud, System Programmer <altaf.mahmud@gmail.com>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_an_ils_driver Wiki
 */
require_once 'Interface.php';

/**
 * VuFind Driver for Koha (version: 3.02)
 *
 * last updated: 12/21/2010
 *
 * @category VuFind
 * @package  ILS_Drivers
 * @author   Altaf Mahmud, System Programmer <altaf.mahmud@gmail.com>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_an_ils_driver Wiki
 */
class Koha implements DriverInterface
{
    private $_db;
    private $_ilsBaseUrl;
    private $_locCodes;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
        //Using relative path that always work
        $configArray = parse_ini_file(dirname(__FILE__).'/../conf/Koha.ini', true);

        //Connect to MySQL
        $this->_db = mysql_pconnect(
            $configArray['Catalog']['host'] . ':' . $configArray['Catalog']['port'],
            $configArray['Catalog']['username'],
            $configArray['Catalog']['password']
        );

        //Select the database
        mysql_select_db($configArray['Catalog']['database']);

        //Storing the base URL of ILS
        $this->_ilsBaseUrl = $configArray['Catalog']['url'];

        // Location codes are defined in 'Koha.ini' file according to current
        // version (3.02)
        $this->_locCodes = $configArray['Location_Codes'];
    }

    /**
     * Get Holding
     *
     * This is responsible for retrieving the holding information of a certain
     * record.
     *
     * @param string $id     The record id to retrieve the holdings for
     * @param array  $patron Patron data
     *
     * @return mixed     On success, an associative array with the following keys:
     * id, availability (boolean), status, location, reserve, callnumber, duedate,
     * number, barcode; on failure, a PEAR_Error.
     * @access  public
     */
    public function getHolding($id, $patron = false)
    {
        $holding = array();
        $available = true;
        $duedate = $status = '';
        $inum = 0;
        $loc = $shelf = '';
        $sql = "select itemnumber as ITEMNO, location as LOCATION, " .
            "holdingbranch as HLDBRNCH, reserves as RESERVES, itemcallnumber as " .
            "CALLNO, barcode as BARCODE, copynumber as COPYNO, " .
            "notforloan as NOTFORLOAN from items where biblionumber = " . $id .
            " order by itemnumber";
        try {
            $itemSqlStmt = mysql_query($sql);
            while ($rowItem = mysql_fetch_assoc($itemSqlStmt)) {
                $inum = $rowItem['ITEMNO'];
                $sql = "select date_due as DUEDATE from issues where itemnumber = " .
                    $inum;

                switch ($rowItem['NOTFORLOAN']) {
                case 0:
                    // If the item is available for loan, then check its current
                    // status
                    $issueSqlStmt = mysql_query($sql);
                    if ($rowIssue = mysql_fetch_assoc($issueSqlStmt)) {
                        $available = false;
                        $status = 'Checked out';
                        $duedate = $rowIssue['DUEDATE'];
                    } else {
                        $available = true;
                        $status = 'Available';
                        // No due date for an available item
                        $duedate = '';
                    }
                    break;
                case 1: // The item is not available for loan
                default: $available = false;
                    $status = 'Not for loan';
                    $duedate = '';
                    break;
                }

                //Retrieving the full branch name
                if (null != ($loc = $rowItem['HLDBRNCH'])) {
                    $sql = "select branchname as BNAME from branches where " .
                        "branchcode = \"$loc\"";
                    $locSqlStmt = mysql_query($sql);
                    if ($row = mysql_fetch_assoc($locSqlStmt)) {
                        $loc = $row['BNAME'];
                    }
                } else {
                    $loc = "Unknown";
                }

                //Retrieving the location (shelf types)
                $shelf = $rowItem['LOCATION'];
                $loc = (null != $shelf)
                    ? $loc . ": " . $this->_locCodes[$shelf]
                    : $loc . ": " . 'Unknown';

                //A default value is stored for null
                $holding[] = array(
                    'id' => $id,
                    'availability' => $available,
                    'item_num' => $rowItem['ITEMNO'],
                    'status' => $status,
                    'location' => $loc,
                    'reserve' => (null == $rowItem['RESERVES'])
                        ? 'Unknown' : $rowItem['RESERVES'],
                    'callnumber' => (null == $rowItem['CALLNO'])
                        ? 'Unknown' : $rowItem['CALLNO'],
                    'duedate' => $duedate,
                    'barcode' => (null == $rowItem['BARCODE'])
                        ? 'Unknown' : $rowItem['BARCODE'],
                    'number' => (null == $rowItem['COPYNO'])
                        ? 'Unknown' : $rowItem['COPYNO']
                );
            }
            return $holding;
        }
        catch (PDOException $e) {
            return new PEAR_Error($e->getMessage());
        }
    }

    /**
     * Get Hold Link
     *
     * The goal for this method is to return a URL to a "place hold" web page on
     * the ILS OPAC. This is used for ILSs that do not support an API or method
     * to place Holds.
     *
     * @param string $id      The id of the bib record
     * @param array  $details Item details from getHoldings return array
     *
     * @return string         URL to ILS's OPAC's place hold screen.
     * @access public
     */
    public function getHoldLink($id, $details)
    {
        // Web link of the ILS for placing hold on the item
        return $this->_ilsBaseUrl . "/cgi-bin/koha/opac-detail.pl?biblionumber=$id";
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
     * @access  public
     */
    public function getMyFines($patron)
    {
        $sql = $sqlStmt = $row = '';
        $id = 0;
        $fineLst = array();
        try {
            $id = $patron['id'];
            $sql = "select round(accountlines.amount*100) as AMOUNT, " .
                "issues.issuedate as CHECKOUT, " .
                "accountlines.description as FINE, " .
                "round(accountlines.amountoutstanding*100) as BALANCE, " .
                "issues.date_due as DUEDATE, items.biblionumber as BIBNO " .
                "from accountlines join issues on " .
                "accountlines.borrowernumber = issues.borrowernumber and " .
                "accountlines.itemnumber = issues.itemnumber " .
                "join items on accountlines.itemnumber = items.itemnumber " .
                "where accountlines.borrowernumber = $id";
            $sqlStmt = mysql_query($sql);
            while ($row = mysql_fetch_assoc($sqlStmt)) {
                $fineLst[] = array(
                    'amount' => (null == $row['AMOUNT'])? 0 : $row['AMOUNT'],
                    'checkout' => $row['CHECKOUT'],
                    'fine' => (null == $row['FINE'])? 'Unknown' : $row['FINE'],
                    'balance' => (null == $row['BALANCE'])? 0 : $row['BALANCE'],
                    'duedate' => $row['DUEDATE'],
                    'id' => $row['BIBNO']
                );
            }
            return $fineLst;
        }
        catch (PDOException $e) {
            return new PEAR_Error($e->getMessage());
        }
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
     * @access  public
     */
    public function getMyHolds($patron)
    {
        $sql = $sqlStmt = $row = '';
        $id = 0;
        $holdLst = array();
        try {
            $id = $patron['id'];
            $sql = "select reserves.biblionumber as BIBNO, " .
                "branches.branchname as BRNAME, " .
                "reserves.expirationdate as EXDATE, " .
                "reserves.reservedate as RSVDATE from reserves " .
                "join branches on reserves.branchcode = branches.branchcode " .
                "where reserves.borrowernumber = $id";
            $sqlStmt = mysql_query($sql);
            while ($row = mysql_fetch_assoc($sqlStmt)) {
                $holdLst[] = array(
                    'id' => $row['BIBNO'],
                    'location' => $row['BRNAME'],
                    'expire' => $row['EXDATE'],
                    'create' => $row['RSVDATE']
                );
            }
            return $holdLst;
        }
        catch (PDOException $e) {
            return new PEAR_Error($e->getMessage());
        }
    }

    /**
     * Get Patron Profile
     *
     * This is responsible for retrieving the profile for a specific patron.
     *
     * @param array $patron The patron array
     *
     * @return mixed        Array of the patron's profile data on success,
     * PEAR_Error otherwise.
     * @access  public
     */
    public function getMyProfile($patron)
    {
        $id = 0;
        $sql = $sqlStmt = $row = '';
        $profile = array();
        try {
            $id = $patron['id'];
            $sql = "select address as ADDR1, address2 as ADDR2, zipcode as ZIP, " .
                "phone as PHONE, categorycode as GRP from borrowers " .
                "where borrowernumber = $id";
            $sqlStmt = mysql_query($sql);
            if ($row = mysql_fetch_assoc($sqlStmt)) {
                $profile = array(
                    'firstname' => $patron['firstname'],
                    'lastname' => $patron['lastname'],
                    'address1' => $row['ADDR1'],
                    'address2' => $row['ADDR2'],
                    'zip' => $row['ZIP'],
                    'phone' => $row['PHONE'],
                    'group' => $row['GRP']
                );
                return $profile;
            }
            return null;
        }
        catch (PDOException $e) {
            return new PEAR_Error($e->getMessage());
        }
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
     * @access  public
     */
    public function getMyTransactions($patron)
    {
        $id = 0;
        $transactionLst = array();
        $row = $sql = $sqlStmt = '';
        try {
            $id = $patron['id'];
            $sql = "select issues.date_due as DUEDATE, items.biblionumber as " .
                "BIBNO, items.barcode BARCODE, issues.renewals as RENEWALS " .
                "from issues join items on issues.itemnumber = items.itemnumber " .
                "where issues.borrowernumber = $id";
            $sqlStmt = mysql_query($sql);
            while ($row = mysql_fetch_assoc($sqlStmt)) {
                $transactionLst[] = array(
                    'duedate' => $row['DUEDATE'],
                    'id' => $row['BIBNO'],
                    'barcode' => $row['BARCODE'],
                    'renew' => $row['RENEWALS']
                );
            }
            return $transactionLst;
        }
        catch (PDOException $e) {
            return new PEAR_Error($e->getMessage());
        }
    }

    /**
     * Get Purchase History
     *
     * This is responsible for retrieving the acquisitions history data for the
     * specific record (usually recently received issues of a serial).
     *
     * @param string $id The record id to retrieve the info for
     *
     * @return mixed     An array with the acquisitions data on success, PEAR_Error
     * on failure
     * @access public
     */
    public function getPurchaseHistory($id)
    {
        return array();
    }

    /**
     * Get Status
     *
     * This is responsible for retrieving the status information of a certain
     * record.
     *
     * @param string $id The record id to retrieve the holdings for
     *
     * @return mixed     On success, an associative array with the following keys:
     * id, availability (boolean), status, location, reserve, callnumber; on
     * failure, a PEAR_Error.
     * @access  public
     */
    public function getStatus($id)
    {
        return $this->getHolding($id);
    }

    /**
     * Get Statuses
     *
     * This is responsible for retrieving the status information for a
     * collection of records.
     *
     * @param array $idLst The array of record ids to retrieve the status for
     *
     * @return mixed       An array of getStatus() return values on success,
     * a PEAR_Error object otherwise.
     * @access public
     */
    public function getStatuses($idLst)
    {
        $statusLst = array();
        foreach ($idLst as $id) {
            $statusLst[] = $this->getStatus($id);
        }
        return $statusLst;
    }

    /**
     * Get suppressed records.
     *
     * NOTE: This function needs to be modified only if Koha has
     *       suppressed records in OPAC view
     *
     * @return array ID numbers of suppressed records in the system.
     * @access public
     */
    public function getSuppressedRecords()
    {
        return array();
    }

    /**
     * Patron Login
     *
     * This is responsible for authenticating a patron against the catalog.
     *
     * @param string $username The patron's username
     * @param string $password The patron's password
     *
     * @return mixed           Associative array of patron info on successful login,
     * null on unsuccessful login, PEAR_Error on error.
     * @access public
     */
    public function patronLogin($username, $password)
    {
        $patron = array();
        $row = '';

        // Koha uses MD5_BASE64 encoding to save borrowers' passwords, function
        // 'rtrim' is used to discard trailing '=' signs, suitable for pushing
        // into MySQL database
        $db_pwd = rtrim(base64_encode(pack('H*', md5($password))), '=');

        $sql = "select borrowernumber as ID, firstname as FNAME, " .
            "surname as LNAME, email as EMAIL from borrowers " .
            "where userid = \"$username\" and password = \"$db_pwd\"";

        try {
            $sqlStmt = mysql_query($sql);
            if ($row = mysql_fetch_assoc($sqlStmt)) {
                // NOTE: Here, 'cat_password' => $password is used, password is
                // saved in a clear text as user provided.  If 'cat_password' =>
                // $db_pwd was used, then password will be saved encrypted as in
                // 'borrowers' table of 'koha' database
                $patron = array(
                    'id' => $row['ID'],
                    'firstname' => $row['FNAME'],
                    'lastname' => $row['LNAME'],
                    'cat_username' => $username,
                    'cat_password' => $password,
                    'email' => $row['EMAIL'],
                    'major' => null,
                    'college' => null
                );

                return $patron;
            }
            return null;
        }
        catch (PDOException $e) {
            return new PEAR_Error($e->getMessage());
        }
    }
}

?>
