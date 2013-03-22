<?php
/**
 * Evergreen ILS Driver
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
 * @package  ILS_Drivers
 * @author   Warren Layton, NRCan Library <warren.layton@gmail.com>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_an_ils_driver Wiki
 */
require_once 'Interface.php';

/**
 * VuFind Connector for Evergreen
 *
 * Written by Warren Layton at the NRCan (Natural Resources Canada)
 * Library.
 *
 * @category VuFind
 * @package  ILS_Drivers
 * @author   Warren Layton, NRCan Library <warren.layton@gmail.com>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_an_ils_driver Wiki
 */
class Evergreen implements DriverInterface
{
    private $_db;
    private $_dbName;
    private $_config;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
        // Load Configuration for this Module
        $this->_config = parse_ini_file('conf/Evergreen.ini', true);

        // Define Database Name
        $this->_dbName = $this->_config['Catalog']['database'];

        try {
            $this->_db = new PDO(
                'pgsql:host='
                .$this->_config['Catalog']['hostname']
                .' user='
                .$this->_config['Catalog']['user']
                .' dbname='
                .$this->_config['Catalog']['database']
                .' password='
                .$this->_config['Catalog']['password']
                .' port='
                .$this->_config['Catalog']['port']
            );
        } catch (PDOException $e) {
            throw $e;
        }
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
     * @access public
     */
    public function getStatus($id)
    {
        $holding = array();

        // Build SQL Statement
        $sql = "select copy_status.name as status, " .
               "call_number.label as callnumber, " .
               "copy_location.name as location " .
               "from $this->_dbName.config.copy_status, " .
               "$this->_dbName.asset.call_number, " .
               "$this->_dbName.asset.copy_location, " .
               "$this->_dbName.asset.copy " .
               "where copy.id = $id " .
               "and copy.status = copy_status.id " .
               "and copy.call_number = call_number.id " .
               "and copy.location = copy_location.id";

        // Execute SQL
        try {
            $holding = array();
            $sqlStmt = $this->_db->prepare($sql);
            $sqlStmt->execute();
        } catch (PDOException $e) {
            return new PEAR_Error($e->getMessage());
        }

        // Build Holdings Array
        while ($row = $sqlStmt->fetch(PDO::FETCH_ASSOC)) {
            switch ($row['status']) {
            case 'Available':
                $available = true;
                $reserve = false;
                break;
            case 'On holds shelf':
                $available = false;
                $reserve = true;
                break;
            default:
                $available = false;
                $reserve = false;
                break;
            }

            $holding[] = array(
                'id' => $id,
                'availability' => $available,
                'status' => $row['status'],
                'location' => $row['location'],
                'reserve' => $reserve,
                'callnumber' => $row['callnumber']
            );
        }

        return $holding;
    }


    /**
     * Get Statuses
     *
     * This is responsible for retrieving the status information for a
     * collection of records.
     *
     * @param array $idList The array of record ids to retrieve the status for
     *
     * @return mixed        An array of getStatus() return values on success,
     * a PEAR_Error object otherwise.
     * @access public
     */
    public function getStatuses($idList)
    {
        $status = array();
        foreach ($idList as $id) {
            $status[] = $this->getStatus($id);
        }
        return $status;
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
     * @access public
     */
    public function getHolding($id, $patron = false)
    {
        $holding = array();

        // Build SQL Statement
        $sql = "select copy_status.name as status, " .
               "call_number.label as callnumber, " .
               "org_unit.name as location, " .
               "copy.copy_number as copy_number, " .
               "copy.barcode as barcode, " .
               "extract (year from circulation.due_date) as due_year, " .
               "extract (month from circulation.due_date) as due_month, " .
               "extract (day from circulation.due_date) as due_day " .
               "from $this->_dbName.config.copy_status, " .
               "$this->_dbName.asset.call_number, " .
               "$this->_dbName.actor.org_unit, " .
               "$this->_dbName.asset.copy " .
               "FULL JOIN $this->_dbName.action.circulation " .
               "ON (copy.id = circulation.target_copy " .
               " and circulation.checkin_time is null) " .
               "where copy.id = $id " .
               "and copy.status = copy_status.id " .
               "and copy.call_number = call_number.id " .
               "and copy.circ_lib = org_unit.id";

        // Execute SQL
        try {
            $sqlStmt = $this->_db->prepare($sql);
            $sqlStmt->execute();
        } catch (PDOException $e) {
            return new PEAR_Error($e->getMessage());
        }

        // Build Holdings Array
        while ($row = $sqlStmt->fetch(PDO::FETCH_ASSOC)) {
            switch ($row['status']) {
            case 'Available':
                $available = true;
                $reserve = false;
                break;
            case 'On holds shelf':
                // Instead of relying on status = 'On holds shelf',
                // I might want to see if:
                // action.hold_request.current_copy = asset.copy.id
                // and action.hold_request.capture_time is not null
                // and I think action.hold_request.fulfillment_time is null
                $available = false;
                $reserve = true;
                break;
            default:
                $available = false;
                $reserve = false;
                break;
            }

            if ($row['due_year']) {
                $due_date = $row['due_year'] . "-" . $row['due_month'] . "-" .
                            $row['due_day'];
            } else {
                $due_date = "";
            }

            $holding[] = array('id' => $id,
                               'availability' => $available,
                               'status' => $row['status'],
                               'location' => $row['location'],
                               'reserve' => $reserve,
                               'callnumber' => $row['callnumber'],
                               'duedate' => $due_date,
                               'number' => $row['copy_number'],
                               'barcode' => $row['barcode']);
        }

        return $holding;
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
    }


    /**
     * Patron Login
     *
     * This is responsible for authenticating a patron against the catalog.
     *
     * @param string $barcode The patron username OR barcode number
     * @param string $passwd  The patron password
     *
     * @return mixed          Associative array of patron info on successful login,
     * null on unsuccessful login, PEAR_Error on error.
     * @access public
     */
    public function patronLogin($barcode, $passwd)
    {
        $sql = "select usr.id as id, usr.first_given_name as firstName, " .
               "usr.family_name as lastName, usr.email, usrname " .
               "from actor.usr, actor.card " .
               "where usr.card = card.id " .
               "and card.active = true " .
               "and usr.passwd = MD5('$passwd') ";

        if (is_numeric($barcode)) {
            // A barcode was supplied as ID
            $sql .= "and card.barcode = '$barcode'";
        } else {
            // A username was supplied as ID
            $sql .= "and usr.usrname = '$barcode'";
        }

        try {
            $sqlStmt = $this->_db->prepare($sql);
            $sqlStmt->execute();
            $row = $sqlStmt->fetch(PDO::FETCH_ASSOC);
            if (isset($row['id']) && ($row['id'] != '')) {
                $return = array();
                $return['id'] = $row['id'];
                $return['firstname'] = $row['firstname'];
                $return['lastname'] = $row['lastname'];
                $return['cat_username'] = $row['usrname'];
                $return['cat_password'] = $passwd;
                $return['email'] = $row['email'];
                $return['major'] = null;    // Don't know which table this comes from
                $return['college'] = null;  // Don't know which table this comes from
                return $return;
            } else {
                return null;
            }
        } catch (PDOException $e) {
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
     * @access public
     */
    public function getMyTransactions($patron)
    {
        $transList = array();

        $sql = "select circulation.target_copy as bib_id, " .
               "extract (year from circulation.due_date) as due_year, " .
               "extract (month from circulation.due_date) as due_month, " .
               "extract (day from circulation.due_date) as due_day " .
               "from $this->_dbName.action.circulation " .
               "where circulation.usr = '" . $patron['id'] . "' " .
               "and circulation.checkin_time is null";

        try {
            $sqlStmt = $this->_db->prepare($sql);
            $sqlStmt->execute();

            while ($row = $sqlStmt->fetch(PDO::FETCH_ASSOC)) {
                if ($row['due_year']) {
                    $due_date = $row['due_year'] . "-" . $row['due_month'] . "-" .
                                $row['due_day'];
                } else {
                    $due_date = "";
                }

                $transList[] = array('duedate' => $due_date,
                                     'id' => $row['bib_id']);
            }
            return $transList;
        } catch (PDOException $e) {
            return new PEAR_Error($e->getMessage());
        }
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
        $fineList = array();

        $sql = "select billable_xact_summary.total_owed, " .
               "billable_xact_summary.balance_owed, " .
               "billable_xact_summary.last_billing_type, " .
               "extract (year from billable_xact_summary.xact_start) ".
               "as start_year, " .
               "extract (month from billable_xact_summary.xact_start) ".
               "as start_month, " .
               "extract (day from billable_xact_summary.xact_start) ".
               "as start_day, " .
               "billable_cirulations.target_copy " .
               "from $this->_dbName.money.billable_xact_summary " .
               "LEFT JOIN $this->_dbName.action.billable_cirulations " .
               "ON (billable_xact_summary.id = billable_cirulations.id " .
               " and billable_cirulations.xact_finish is null) " .
               "where billable_xact_summary.usr = '" . $patron['id'] . "' " .
               "and billable_xact_summary.xact_finish is null";

        try {
            $sqlStmt = $this->_db->prepare($sql);
            $sqlStmt->execute();

            while ($row = $sqlStmt->fetch(PDO::FETCH_ASSOC)) {
                if ($row['start_year']) {
                    $charge_date = $row['start_year'] . "-" . $row['start_month'] .
                            "-" . $row['start_day'];
                } else {
                    $charge_date = "";
                }

                $fineList[] = array('amount' => $row['total_owed'],
                                    'fine' => $row['last_billing_type'],
                                    'balance' => $row['balance_owed'],
                                    'checkout' => $charge_date,
                                    'duedate' => "",
                                    'id' => $row['target_copy']);
            }
            return $fineList;
        } catch (PDOException $e) {
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
     * @access public
     */
    public function getMyHolds($patron)
    {
        $holdList = array();

        $sql = "select hold_request.hold_type, hold_request.current_copy, " .
               "extract (year from hold_request.expire_time) as exp_year, " .
               "extract (month from hold_request.expire_time) as exp_month, " .
               "extract (day from hold_request.expire_time) as exp_day, " .
               "extract (year from hold_request.request_time) as req_year, " .
               "extract (month from hold_request.request_time) as req_month, " .
               "extract (day from hold_request.request_time) as req_day, " .
               "org_unit.name as lib_name " .
               "from $this->_dbName.action.hold_request, " .
               "$this->_dbName.actor.org_unit " .
               "where hold_request.usr = '" . $patron['id'] . "' " .
               "and hold_request.pickup_lib = org_unit.id " .
               "and hold_request.capture_time is not null " .
               "and hold_request.fulfillment_time is null";

        try {
            $sqlStmt = $this->_db->prepare($sql);
            $sqlStmt->execute();
            while ($row = $sqlStmt->fetch(PDO::FETCH_ASSOC)) {
                if ($row['req_year']) {
                    $req_time = $row['req_year'] . "-" . $row['req_month'] .
                            "-" . $row['req_day'];
                } else {
                    $req_time = "";
                }

                if ($row['exp_year']) {
                    $exp_time = $row['exp_year'] . "-" . $row['exp_month'] .
                            "-" . $row['exp_day'];
                } else {
                    $exp_time = "";
                }

                $holdList[] = array('type' => $row['hold_type'],
                                    'id' => $row['current_copy'],
                                    'location' => $row['lib_name'],
                                    'expire' => $exp_time,
                                    'create' => $req_time);
            }
            return $holdList;
        } catch (PDOException $e) {
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
     * @access public
     */
    public function getMyProfile($patron)
    {
        $sql = "select usr.family_name, usr.first_given_name, " .
               "usr.day_phone, usr.evening_phone, usr.other_phone, " .
               "usr_address.street1, usr_address.street2, " .
               "usr_address.post_code, usr.usrgroup " .
               "from actor.usr, actor.usr_address " .
               "where usr.id = '" . $patron['id'] . "' " .
               "and usr.active = true " .
               "and usr.mailing_address = usr_address.id";

        try {
            $sqlStmt = $this->_db->prepare($sql);
            $sqlStmt->execute();
            $row = $sqlStmt->fetch(PDO::FETCH_ASSOC);

            if ($row['day_phone']) {
                $phone = $row['day_phone'];
            } elseif ($row['evening_phone']) {
                $phone = $row['evening_phone'];
            } else {
                $phone = $row['other_phone'];
            }

            if ($row) {
                $patron = array('firstname' => $row['first_given_name'],
                                'lastname' => $row['family_name'],
                                'address1' => $row['street1'],
                                'address2' => $row['street2'],
                                'zip' => $row['post_code'],
                                'phone' => $phone,
                                'group' => $row['usrgroup']);
                return $patron;
            } else {
                return null;
            }
        } catch (PDOException $e) {
            return new PEAR_Error($e->getMessage());
        }
    }


    /**
     * Only one of the following 2 function should be implemented.
     * Placing a hold directly can be done with placeHold.
     * Otherwise, getHoldLink will link to Evergreen's page to place
     * a hold via the ILS.
     */

    /**
     * Place Hold
     *
     * Attempts to place a hold or recall on a particular item and returns
     * an array with result details or a PEAR error on failure of support classes
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
        // Need to check asset.copy.status -> config.copy_status.holdable = true
        // If it is holdable, place hold in action.hold_request:
        // request_time to now, current_copy to asset.copy.id,
        // usr to action.usr.id of requesting patron,
        // phone_notify to phone number, email_notify to t/f
        // set pickup_lib too?

        /*
        $sql = "";

        try {
            $sqlStmt = $this->_db->prepare($sql);
            $sqlStmt->execute();
        } catch (PDOException $e) {
            return new PEAR_Error($e->getMessage());
        }
        */
    //}

    /**
     * Get Hold Link
     *
     * The goal for this method is to return a URL to a "place hold" web page on
     * the ILS OPAC. This is used for ILSs that do not support an API or method
     * to place Holds.
     *
     * @param string $recordId The id of the bib record
     * @param array  $details  Item details from getHoldings return array
     *
     * @return string          URL to ILS's OPAC's place hold screen.
     * @access public
     */
    //public function getHoldLink($recordId, $details)
    //{
    //}


    /**
     * Get New Items
     *
     * Retrieve the IDs of items recently added to the catalog.
     *
     * @param int $page    Page number of results to retrieve (counting starts at 1)
     * @param int $limit   The size of each page of results to retrieve
     * @param int $daysOld The maximum age of records to retrieve in days (max. 30)
     * @param int $fundId  optional fund ID to use for limiting results (use a value
     * returned by getFunds, or exclude for no limit); note that "fund" may be a
     * misnomer - if funds are not an appropriate way to limit your new item
     * results, you can return a different set of values from getFunds. The
     * important thing is that this parameter supports an ID returned by getFunds,
     * whatever that may mean.
     *
     * @return array       Associative array with 'count' and 'results' keys
     * @access public
     */
    public function getNewItems($page, $limit, $daysOld, $fundId = null)
    {
        $items = array();

        // Prevent unnecessary load
        // (Taken from Voyager driver - does Evergreen need this?)
        if ($daysOld > 30) {
            $daysOld = 30;
        }

        $enddate = date('Y-m-d', strtotime('now'));
        $startdate = date('Y-m-d', strtotime("-$daysOld day"));

        $sql = "select count(distinct copy.id) as count " .
               "from asset.copy " .
               "where copy.create_date >= '$startdate' " .
               "and copy.create_date < '$enddate'";

        try {
            $sqlStmt = $this->_db->prepare($sql);
            $sqlStmt->execute();
            $row = $sqlStmt->fetch(PDO::FETCH_ASSOC);
            $items['count'] = $row['count'];
        } catch (PDOException $e) {
            return new PEAR_Error($e->getMessage());
        }

        $page = ($page) ? $page : 1;
        $limit = ($limit) ? $limit : 20;
        $startRow = (($page-1)*$limit)+1;
        $endRow = ($page*$limit);

        $sql = "select copy.id from asset.copy " .
               "where copy.create_date >= '$startdate' " .
               "and copy.create_date < '$enddate'";

        try {
            $sqlStmt = $this->_db->prepare($sql);
            $sqlStmt->execute();
            while ($row = $sqlStmt->fetch(PDO::FETCH_ASSOC)) {
                $items['results'][]['id'] = $row['id'];
            }
            return $items;
        } catch (PDOException $e) {
            return new PEAR_Error($e->getMessage());
        }
    }

    /**
     * Get Funds
     *
     * Return a list of funds which may be used to limit the getNewItems list.
     *
     * @return array An associative array with key = fund ID, value = fund name.
     * @access public
     */
    public function getFunds()
    {
        /*
        $list = array();

        $sql = "";

        try {
            $sqlStmt = $this->_db->prepare($sql);
            $sqlStmt->execute();
            while ($row = $sqlStmt->fetch(PDO::FETCH_ASSOC)) {
                $list[] = $row['name'];
            }
        } catch (PDOException $e) {
            return new PEAR_Error($e->getMessage());
        }

        return $list;
        */
    }

    /**
     * Get suppressed records.
     *
     * @return array ID numbers of suppressed records in the system.
     * @access public
     */
    public function getSuppressedRecords()
    {
        $list = array();

        $sql = "select copy.id as id " .
               "from $this->_dbName.asset " .
               "where copy.opac_visible = false";

        try {
            $sqlStmt = $this->_db->prepare($sql);
            $sqlStmt->execute();
            while ($row = $sqlStm->fetch(PDO::FETCH_ASSOC)) {
                $list[] = $row['id'];
            }
        } catch (PDOException $e) {
            return new PEAR_Error($e->getMessage());
        }

        return $list;
    }

    // *** The functions below are not (yet) applicable to Evergreen ***

    /**
     * Get Departments
     *
     * Obtain a list of departments for use in limiting the reserves list.
     *
     * @return array An associative array with key = dept. ID, value = dept. name.
     * @access public
     */
    public function getDepartments()
    {
    }

    /**
     * Get Instructors
     *
     * Obtain a list of instructors for use in limiting the reserves list.
     *
     * @return array An associative array with key = ID, value = name.
     * @access public
     */
    public function getInstructors()
    {
    }

    /**
     * Get Courses
     *
     * Obtain a list of courses for use in limiting the reserves list.
     *
     * @return array An associative array with key = ID, value = name.
     * @access public
     */
    public function getCourses()
    {
    }

    /**
     * Find Reserves
     *
     * Obtain information on course reserves.
     *
     * @param string $course ID from getCourses (empty string to match all)
     * @param string $inst   ID from getInstructors (empty string to match all)
     * @param string $dept   ID from getDepartments (empty string to match all)
     *
     * @return mixed An array of associative arrays representing reserve items (or a
     * PEAR_Error object if there is a problem)
     * @access public
     */
    public function findReserves($course, $inst, $dept)
    {
    }

}

?>
