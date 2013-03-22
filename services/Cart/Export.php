<?php
/**
 * Bulk Exporter
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

require_once 'Bulk.php';

/**
 * Bulk Exporter
 *
 * @category VuFind
 * @package  Bulk_Emailer
 * @author   Luke O'Sullivan <l.osullivan@swansea.ac.uk>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Export extends Bulk
{

    /**
     * Process parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $configArray;
        global $interface;

        $doExport = false;

        // Check for Session Info
        if (isset($_REQUEST['exportInit'])) {
            $doExport = $this->_exportInit();
        }

        if (!$doExport) {
            // Check for submit
            if (isset($_POST['submit'])) {
                $this->_processSubmit();
            }
            // Display
            if (isset($_GET['lightbox'])) {
                return $this->_processLightbox();
            } else {
                $this->_processNonLightbox();
            }
        }
    }

    /**
     * Get the URL for exporting the current set of resources.
     *
     * @return string
     * @access public
     */
    public static function getExportUrl()
    {
        global $configArray;

        if (strtolower($_REQUEST['format']) == 'refworks') {
            // can't pass the ids through the session, so need to stringify
            $parts = array();
            foreach ($ids as $id) {
                $parts[] = urlencode('ids[]') . '=' . urlencode($id);
            }
            $id_str = implode('&', $parts);
            // Build the URL to pass data to RefWorks:
            $exportUrl = $configArray['Site']['url'] . '/Cart/Home' .
                '?export=true&exportInit=true&exportToRefworks=true' . $id_str;
            // Build up the RefWorks URL:
            return $configArray['RefWorks']['url'] . '/express/expressimport.asp' .
                '?vendor=' . urlencode($configArray['RefWorks']['vendor']) .
                '&filter=RefWorks%20Tagged%20Format&url=' . urlencode($exportUrl);
        }

        // Default case:
        return $configArray['Site']['url'] . '/Cart/Home?exportInit';
    }

    /**
     * Process submitted details
     * Display error page on terminal error, success page on successs
     *
     * @return void
     * @access private
     */
    private function _processSubmit()
    {
        // Check for essentials
        if (isset($_REQUEST['format']) && isset($_REQUEST['ids'])) {
            $_SESSION['exportIDS'] =  $_REQUEST['ids'];
            $_SESSION['exportFormat'] = $_REQUEST['format'];

            if ($_SESSION['exportIDS'] && $_SESSION['exportFormat']) {
                // Special case -- for RefWorks, go directly there;
                // for everything else, provide a save dialog.
                if (strtolower($_POST['format']) == 'refworks') {
                    header('Location: ' . self::getExportUrl());
                } else {
                    header(
                        "Location: " . $this->followupUrl .
                        "?infoMsg=export_success&showExport=" .
                        urlencode(self::getExportUrl())
                    );
                }
                exit();
            } else {
                $this->errorMsg = 'bulk_fail';
            }
        } else {
            $this->errorMsg = 'export_missing';
        }
    }

    /**
     * Support method - display appropriate headers for the export.
     *
     * @param string $type The content-type value.
     * @param string $name The filename of the output.
     *
     * @return void
     * @access private
     */
    private function _exportHeaders($type, $name)
    {
        // For some reason, IE has trouble handling content types under SSL
        // (possibly only when self-signed certificates are involved -- further
        // testing is needed).  For now, as a work-around, let's always use the
        // text/plain content type when we're dealing with IE and SSL -- the
        // file extension should still allow the browser to do the right thing.
        if (array_key_exists('HTTPS', $_SERVER) && ($_SERVER['HTTPS'] == 'on')
            && strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')
        ) {
            $type = 'text/plain';
        }
        header('Content-type: ' . $type);
        header("Content-Disposition: attachment; filename=\"{$name}\";");
    }

    /**
     * Begin Export
     * Set headers for download and "display" file for export on success or error
     * message on failure
     *
     * @return boolean false On unrecoverable error
     * @access private
     */
    private function _exportInit()
    {
        global $configArray;
        global $interface;

        // Check for essentials
        $ids = $_SESSION['exportIDS'];
        $format = $_SESSION['exportFormat'];
        if (isset($format) && is_array($ids)) {
            $result = $this->exportAll($format, $ids);
            if ($result && !PEAR::isError($result)
                && !empty($result['exportDetails'])
            ) {
                $export = true;
                switch(strtolower($format)) {
                case 'bibtex':
                    $this->_exportHeaders(
                        'application/x-bibtex', 'VuFindExport.bibtex'
                    );
                    break;
                case 'endnote':
                    $this->_exportHeaders(
                        'application/x-endnote-refer', 'VuFindExport.enw'
                    );
                    break;
                case 'marc':
                    $this->_exportHeaders('application/MARC', 'VuFindExport.mrc');
                    break;
                case 'refworks_data':
                    // No extra work necessary.
                    break;
                default:
                    $export = false;
                }
            }

            if ($export) {
                $interface->assign('bulk', $result['exportDetails']);
                $interface->display('Cart/export/bulk.tpl');
                return true;
            } else {
                $this->errorMsg = 'bulk_fail';
            }
        } else {
            // Missing Vital Information
            $this->errorMsg = 'export_missing';
        }
        return false;
    }

    /**
     * Support method -- assign details about records based on an array of IDs.
     *
     * @param array $ids IDs to look up.
     *
     * @return void
     * @access private
     */
    private function _assignExportList($ids)
    {
        global $interface;

        $exportList = array();

        // Get the export options determined by the parent class based on config
        // settings.  We'll filter them down based on what the selected records
        // actually support.
        $formats = $this->exportOptions;

        foreach ($ids as $id) {
            $record = $this->db->getRecord($id);
            $driver = RecordDriverFactory::initRecordDriver($record);
            $exportList[] = array(
                'id'      => $id,
                'isbn'    => $record['isbn'],
                'author'  => $record['author'],
                'title'   => $driver->getBreadcrumb(),
                'format'  => $record['format']
            );

            // Filter out unsupported export formats:
            $newFormats = array();
            foreach ($formats as $current) {
                if (in_array($current, $driver->getExportFormats())) {
                    $newFormats[] = $current;
                }
            }
            $formats = $newFormats;
        }

        $interface->assign('exportOptions', $formats);
        $interface->assign('exportList', $exportList);
    }

    /**
     * Process Light Box Request
     * Display error message on terminal error or export details page on success
     *
     * @return void
     * @access private
     */
    private function _processLightbox()
    {
        global $configArray;
        global $interface;

        if (!empty($_REQUEST['ids'])) {
            // Assign Item Info
            $interface->assign('exportIDS', $_POST['ids']);
            $this->_assignExportList($_REQUEST['ids']);
            $interface->assign('title', $_GET['message']);
            return $interface->fetch('Cart/export.tpl');
        } else {
            $interface->assign('title', $_GET['message']);
            $interface->assign('errorMsg', 'bulk_noitems_advice');
            return $interface->fetch('Cart/bulkError.tpl');
        }
    }

    /**
     * Process Non-LightBox Request
     * Display error message on terminal error or email details page on success
     *
     * @return void
     * @access private
     */
    private function _processNonLightbox()
    {
        global $configArray;
        global $interface;

        // Assign IDs
        if (isset($_REQUEST['selectAll']) && is_array($_REQUEST['idsAll'])) {
            $ids = $_REQUEST['idsAll'];
        } else if (isset($_REQUEST['ids'])) {
            $ids = $_REQUEST['ids'];
        }
        $_POST['ids'] = "";
        // Check we have an array of IDS
        if (is_array($ids)) {
            // Assign Item Info
            $interface->assign('errorMsg', $this->errorMsg);
            $interface->assign('infoMsg', $this->infoMsg);
            $interface->setPageTitle('Export Favorites');
            $interface->assign('subTemplate', 'export.tpl');
            $interface->assign('exportIDS', $ids);
            $this->_assignExportList($ids);

            if ($this->origin == "Favorites") {
                $interface->assign('followupModule', "MyResearch");
                $interface->assign('followupAction', "Favorites");
                // If we're on a particular list, save the ID so we can redirect to
                // the appropriate page after exporting.
                if (isset($_REQUEST['listID']) && !empty($_REQUEST['listID'])) {
                    $interface->assign('listID', $_REQUEST['listID']);
                }
            }
            $interface->setTemplate('view.tpl');
            $interface->display('layout.tpl');
        } else {
            // Without an array of IDS, we can't perform any operations
            header(
                "Location: " . $this->followupUrl . "?errorMsg=bulk_noitems_advice"
            );
            exit();
        }
    }

    /**
     * Get record and export data
     * Display error message on terminal error or email details page on success
     *
     * @param string $format The desired export format
     * @param array  $ids    A list of bib IDs
     *
     * @return array Record data for each ID, plus an list of IDs without results
     * @access public
     */
    public function exportAll($format, $ids)
    {
        global $interface;
        global $configArray;

        $exportDetails = array();
        $errorMsgDetails = array();

        foreach ($ids as $id) {
            // Retrieve the record from the index
            if (!($record = $this->db->getRecord($id))) {
                $errorMsgDetails[] = $id;
            } else {
                $recordDetails = RecordDriverFactory::initRecordDriver($record);
                // Assign core metadata to be sure export has all necessary values
                // available:
                $recordDetails->getCoreMetadata();
                $result = $recordDetails->getExport($format);
                if (!empty($result)) {
                    $exportDetails[] = $interface->fetch($result);
                } else {
                    $errorMsgDetails[] = $id;
                }
            }
        }
        $results = array(
            'exportDetails' => $exportDetails,
            'errorDetails' => $errorMsgDetails
        );
        return $results;
    }
}
?>
