<?php
/**
 * Bulk Function Controller
 *
 * PHP version 5
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
 * @category VuFind
 * @package  Controller_Cart
 * @author   Luke O'Sullivan <l.osullivan@swansea.ac.uk>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */

require_once 'Action.php';
require_once 'sys/Mailer.php';
require_once 'sys/Language.php';
require_once 'CatalogConnection.php';
require_once 'RecordDrivers/Factory.php';

/**
 * Bulk Function Controller
 *
 * @category VuFind
 * @package  Controller_Cart
 * @author   Luke O'Sullivan <l.osullivan@swansea.ac.uk>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Bulk extends Action
{
    protected $recordDriver;
    protected $cacheId;
    protected $db;
    protected $catalog;
    protected $errorMsg;
    protected $infoMsg;
    protected $exportOptions;
    protected $followupUrl;
    protected $user;

    /**
     * Constructor.
     *
     * @access public
     */
    public function __construct()
    {
        global $interface;
        global $configArray;
        global $user;

        parent::__construct();

        $this->user = UserAccount::isLoggedIn();

        // Setup Search Engine Connection
        $this->db = ConnectionManager::connectToIndex();

        // Connect to Database
        $this->catalog = ConnectionManager::connectToCatalog();

        // Assign Exporter Options
        $exportOptions = array();
        if ($configArray['BulkExport']['enabled']) {
            $options = explode(':', $configArray['BulkExport']['options']);
            foreach ($options as $option) {
                if ($configArray['Export'][$option] == true) {
                    $exportOptions[] = $option;
                }
            }
            $this->exportOptions = $exportOptions;
        }

        // Get Messages
        $this->infoMsg = isset($_GET['infoMsg']) ? $_GET['infoMsg'] : false;
        $this->errorMsg = isset($_GET['errorMsg']) ? $_GET['errorMsg'] : false;
        $this->showExport = isset($_GET['showExport']) ? $_GET['showExport'] : false;
        $this->origin = isset($_REQUEST['origin']) ? $_REQUEST['origin'] : false;

        // Set FollowUp URL
        if (isset($_REQUEST['followup'])) {
            $this->followupUrl =  $configArray['Site']['url'] . "/" .
            $_REQUEST['followupModule'];
            $this->followupUrl .= "/" . $_REQUEST['followupAction'];
        } else if (isset($_REQUEST['listID']) && !empty($_REQUEST['listID'])) {
            $this->followupUrl = $configArray['Site']['url'] .
                "/MyResearch/MyList/" . urlencode($_REQUEST['listID']);
        } else {
            $this->followupUrl = $configArray['Site']['url'] . "/Cart/Home";
        }
    }

    /**
     * Support method -- get details about records based on an array of IDs.
     *
     * @param array $ids IDs to look up.
     *
     * @return array
     * @access protected
     */
    protected function getRecordDetails($ids)
    {
        $recordList = array();

        foreach ($ids as $id) {
            $record = $this->db->getRecord($id);
            $driver = RecordDriverFactory::initRecordDriver($record);
            $recordList[] = array(
                'id'      => $id,
                'isbn'    => $record['isbn'],
                'author'  => $record['author'],
                'title'   => $driver->getBreadcrumb(),
                'format'  => $record['format']
            );
        }

        return $recordList;
    }
}
?>
