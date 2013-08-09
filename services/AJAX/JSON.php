<?php
/**
 * Common AJAX functions using JSON as output format.
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
 * @package  Controller_AJAX
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */

require_once 'Action.php';

/**
 * Common AJAX functions using JSON as output format.
 *
 * @category VuFind
 * @package  Controller_AJAX
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class JSON extends Action
{
    // define some status constants
    const STATUS_OK = 'OK';                  // good
    const STATUS_ERROR = 'ERROR';            // bad
    const STATUS_NEED_AUTH = 'NEED_AUTH';    // must login first

    /**
     * Constructor.
     *
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Process parameters and display the response.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        // Call the method specified by the 'method' parameter as long as it is
        // valid and will not result in an infinite loop!
        if ($_GET['method'] != 'launch'
            && $_GET['method'] != '__construct'
            && is_callable(array($this, $_GET['method']))
        ) {
            $this->$_GET['method']();
        } else {
            return $this->output(translate('Invalid Method'), JSON::STATUS_ERROR);
        }
    }

    /**
     * Check if user is logged in.
     *
     * @return void
     * @access public
     */
    public function isLoggedIn()
    {
        include_once 'services/MyResearch/lib/User.php';

        $user = UserAccount::isLoggedIn();
        if ($user) {
            return $this->output(true, JSON::STATUS_OK);
        } else {
            return $this->output(false, JSON::STATUS_OK);
        }
    }

    /**
     * Login with post'ed username and encrypted password.
     *
     * @return void
     * @access public
     */
    public function login()
    {
        global $configArray;

        // Fetch Salt
        $salt = $this->_generateSalt();

        // HexDecode Password
        $password = pack('H*', $_GET['password']);

        // Decrypt Password
        include_once 'Crypt/rc4.php';
        $password = rc4Encrypt($salt, $password);

        // Put the username/password in POST fields where the authentication module
        // expects to find them:
        $_POST['username'] = $_GET['username'];
        $_POST['password'] = $password;

        // Authenticate the user:
        $user = UserAccount::login();
        if (PEAR::isError($user)) {
            return $this->output(translate($user->getMessage()), JSON::STATUS_ERROR);
        }

        return $this->output(true, JSON::STATUS_OK);
    }

    /**
     * Send the "salt" to be used in the salt'ed login request.
     *
     * @return void
     * @access public
     */
    public function getSalt()
    {
        return $this->output($this->_generateSalt(), JSON::STATUS_OK);
    }

    /**
     * Check Request is Valid
     *
     * @return void
     * @access public
     */
    public function checkRequestIsValid()
    {
        if (isset($_REQUEST['id']) && isset($_REQUEST['data'])) {
            // check if user is logged in
            $user = UserAccount::isLoggedIn();
            if (!$user) {
                return $this->output(
                    array(
                        'status' => false,
                        'msg' => translate('You must be logged in first')
                    ), JSON::STATUS_NEED_AUTH
                );
            }

            $catalog = ConnectionManager::connectToCatalog();
            if ($catalog && $catalog->status) {
                if ($patron = UserAccount::catalogLogin()) {
                    if (!PEAR::isError($patron)) {
                        $results = $catalog->checkRequestIsValid(
                            $_REQUEST['id'], $_REQUEST['data'], $patron
                        );

                        if (!PEAR::isError($results)) {
                            $msg = $results
                                ? translate('request_place_text')
                                : translate('hold_error_blocked');
                            return $this->output(
                                array(
                                    'status' => $results, 'msg' => $msg
                               ), JSON::STATUS_OK
                            );
                        }
                    }
                }
            }
        }
        return $this->output(translate('An error has occurred'), JSON::STATUS_ERROR);
    }

    /**
     * Support method for getItemStatuses() -- filter suppressed locations from the
     * array of item information for a particular bib record.
     *
     * @param array $record Information on items linked to a single bib record
     *
     * @return array        Filtered version of $record
     * @access private
     */
    private function _filterSuppressedLocations($record)
    {
        global $configArray;
        static $hideHoldings = false;
        if ($hideHoldings === false) {
            $hideHoldings = isset($configArray['Record']['hide_holdings'])
                ? $configArray['Record']['hide_holdings'] : array();
        }

        $filtered = array();
        foreach ($record as $current) {
            if (!in_array($current['location'], $hideHoldings)) {
                $filtered[] = $current;
            }
        }
        return $filtered;
    }

    /**
     * Get Item Statuses
     *
     * This is responsible for printing the holdings information for a
     * collection of records in JSON format.
     *
     * @return void
     * @access public
     * @author Chris Delis <cedelis@uillinois.edu>
     * @author Tuan Nguyen <tuan@yorku.ca>
     */
    public function getItemStatuses()
    {
        global $interface;
        global $configArray;

        $catalog = ConnectionManager::connectToCatalog();
        if (!$catalog || !$catalog->status) {
            return $this->output(
                translate('An error has occurred'), JSON::STATUS_ERROR
            );
        }
        $results = $catalog->getStatuses($_GET['id']);
        if (PEAR::isError($results)) {
            return $this->output($results->getMessage(), JSON::STATUS_ERROR);
        } else if (!is_array($results)) {
            // If getStatuses returned garbage, let's turn it into an empty array
            // to avoid triggering a notice in the foreach loop below.
            $results = array();
        }

        // In order to detect IDs missing from the status response, create an
        // array with a key for every requested ID.  We will clear keys as we
        // encounter IDs in the response -- anything left will be problems that
        // need special handling.
        $missingIds = array_flip($_GET['id']);

        // Load messages for response:
        $messages = array(
            'available' => $interface->fetch('AJAX/status-available.tpl'),
            'unavailable' => $interface->fetch('AJAX/status-unavailable.tpl'),
            'missing' => $interface->fetch('AJAX/status-missing.tpl'),
            'lost' => $interface->fetch('AJAX/status-lost.tpl')
        );

        // Load callnumber and location settings:
        $callnumberSetting = isset($configArray['Item_Status']['multiple_call_nos'])
            ? $configArray['Item_Status']['multiple_call_nos'] : 'msg';
        $locationSetting = isset($configArray['Item_Status']['multiple_locations'])
            ? $configArray['Item_Status']['multiple_locations'] : 'msg';
        $showFullStatus = isset($configArray['Item_Status']['show_full_status'])
            ? $configArray['Item_Status']['show_full_status'] : false;

        // Loop through all the status information that came back
        $statuses = array();
        foreach ($results as $record) {
            // Skip errors and empty records:
            if (!PEAR::isError($record) && count($record)) {
                // Filter out suppressed locations, and skip record if none remain:
                $record = $this->_filterSuppressedLocations($record);
                if (empty($record)) {
                    continue;
                }

                if ($locationSetting == "group") {
                    $current = $this->_getItemStatusGroup(
                        $record, $messages, $callnumberSetting
                    );
                } else {
                    $current = $this->_getItemStatus(
                        $record, $messages, $locationSetting, $callnumberSetting
                    );
                }

                // If a full status display has been requested, append the HTML:
                if ($showFullStatus) {
                    $current['full_status'] = $this->_getItemStatusFull($record);
                }

                $statuses[] = $current;

                // The current ID is not missing -- remove it from the missing list.
                unset($missingIds[$current['id']]);
            }
        }

        // If any IDs were missing, send back appropriate dummy data, including a
        // "missing data" flag which can be used to completely suppress status info:
        foreach ($missingIds as $missingId => $junk) {
            $statuses[] = array(
                'id'                   => $missingId,
                'availability'         => '-1',
                'availability_message' => $messages['unavailable'],
                'location'             => translate('Unknown'),
                'locationList'         => false,
                'reserve'              => 'false',
                'reserve_message'      => translate('Not On Reserve'),
                'callnumber'           => '',
                'missing_data'         => true
            );
        }

        // Done
        return $this->output($statuses, JSON::STATUS_OK);
    }

    /**
     * Check one or more records to see if they are saved in one of the user's list.
     *
     * @return void
     * @access public
     */
    public function getSaveStatuses()
    {
        include_once 'services/MyResearch/lib/Resource.php';
        include_once 'services/MyResearch/lib/User.php';

        // check if user is logged in
        $user = UserAccount::isLoggedIn();
        if (!$user) {
            return $this->output(
                translate('You must be logged in first'), JSON::STATUS_NEED_AUTH
            );
        }

        // loop through each ID check if it is saved to any of the user's lists
        $result = array();
        foreach ($_GET['id'] as $id) {
            // Check if resource is saved to favorites
            $resource = new Resource();
            $resource->record_id = $id;

            if ($resource->find(true)) {
                $data = $user->getSavedData($id);
                if ($data) {
                    // if this item was saved, add it to the list of saved items.
                    foreach ($data as $list) {
                        $result[] = array(
                            'record_id' => $id,
                            'resource_id' => $list->id,
                            'list_id' => $list->list_id,
                            'list_title' => $list->list_title
                        );
                    }
                }
            }
        }
        return $this->output($result, JSON::STATUS_OK);
    }

    /**
     * Save a record to a list.
     *
     * @return void
     * @access public
     */
    public function saveRecord()
    {
        include_once 'services/Record/Save.php';

        // check if user is logged in
        $user = UserAccount::isLoggedIn();
        if (!$user) {
            return $this->output(
                translate('You must be logged in first'), JSON::STATUS_NEED_AUTH
            );
        }

        if (!Save::saveRecord($user)) {
            return $this->output(
                translate($result->getMessage()), JSON::STATUS_ERROR
            );
        }

        return $this->output(translate('Done'), JSON::STATUS_OK);
    }

    /**
     * Email a record.
     *
     * @return void
     * @access public
     */
    public function emailRecord()
    {
        // Load the appropriate module based on the "type" parameter:
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'Record';
        include_once 'services/' . $type . '/Email.php';

        $emailService = new Email();
        $result = $emailService->sendEmail(
            $_REQUEST['to'], $_REQUEST['from'], $_REQUEST['message']
        );

        if (PEAR::isError($result)) {
            return $this->output(
                translate($result->getMessage()), JSON::STATUS_ERROR
            );
        }

        return $this->output(translate('email_success'), JSON::STATUS_OK);
    }

    /**
     * SMS a record.
     *
     * @return void
     * @access public
     */
    public function smsRecord()
    {
        // Load the appropriate SMS module based on the "type" parameter:
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'Record';
        include_once 'services/' . $type . '/SMS.php';

        $sms = new SMS();
        $result = $sms->sendSMS();

        if (PEAR::isError($result)) {
            return $this->output(
                translate($result->getMessage()), JSON::STATUS_ERROR
            );
        }

        return $this->output(translate('sms_success'), JSON::STATUS_OK);
    }

    /**
     * Tag a record.
     *
     * @return void
     * @access public
     */
    public function tagRecord()
    {
        $user = UserAccount::isLoggedIn();
        if ($user === false) {
            return $this->output(
                translate('You must be logged in first'), JSON::STATUS_NEED_AUTH
            );
        }

        include_once 'services/Record/AddTag.php';
        if (!AddTag::save($user)) {
            return $this->output(translate('Failed'), JSON::STATUS_ERROR);
        }

        return $this->output(translate('Done'), JSON::STATUS_OK);
    }

    /**
     * Get all tags for a record.
     *
     * @return void
     * @access public
     */
    public function getRecordTags()
    {
        include_once 'services/MyResearch/lib/Resource.php';

        $tagList = array();
        $resource = new Resource();
        $resource->record_id = $_GET['id'];
        if ($resource->find(true)) {
            $tags = $resource->getTags();
            foreach ($tags as $tag) {
                $tagList[] = array('tag'=>$tag->tag, 'cnt'=>$tag->cnt);
            }
        }

        // If we don't have any tags, provide a user-appropriate message:
        if (empty($tagList)) {
            $msg = translate('No Tags') . ', ' .
                translate('Be the first to tag this record') . '!';
            return $this->output($msg, JSON::STATUS_ERROR);
        } else {
            return $this->output($tagList, JSON::STATUS_OK);
        }
    }

    /**
     * Comment on a record.
     *
     * @return void
     * @access public
     */
    public function commentRecord()
    {
        include_once 'services/Record/UserComments.php';

        $user = UserAccount::isLoggedIn();
        if ($user === false) {
            return $this->output(
                translate('You must be logged in first'), JSON::STATUS_NEED_AUTH
            );
        }

        if (!UserComments::saveComment($user)) {
            return $this->output(
                translate('comment_error_save'), JSON::STATUS_ERROR
            );
        }

        return $this->output(translate('Done'), JSON::STATUS_OK);
    }

    /**
     * Get list of comments for a record as HTML.
     *
     * @return void
     * @access public
     */
    public function getRecordCommentsAsHTML()
    {
        global $interface;

        include_once 'services/Record/UserComments.php';

        $interface->assign('id', $_GET['id']);
        UserComments::assignComments();
        $html = $interface->fetch('Record/view-comments-list.tpl');
        return $this->output($html, JSON::STATUS_OK);
    }

    /**
     * Delete a record comment.
     *
     * @return void
     * @access public
     */
    public function deleteRecordComment()
    {
        include_once 'services/Record/UserComments.php';

        $user = UserAccount::isLoggedIn();
        if ($user === false) {
            return $this->output(
                translate('You must be logged in first'), JSON::STATUS_NEED_AUTH
            );
        }

        if (!UserComments::deleteComment($_GET['id'], $user)) {
            return $this->output(
                translate('An error has occurred'), JSON::STATUS_ERROR
            );
        }
        return $this->output(translate('Done'), JSON::STATUS_OK);
    }

    /**
     * Email Bulk Results
     *
     * @return void
     * @access public
     */
    public function emailBulk()
    {
        include_once 'services/Cart/Email.php';

        $emailService = new Email();
        $result = $emailService->sendEmail(
            $_REQUEST['url'], $_REQUEST['to'], $_REQUEST['from'],
            $_REQUEST['message']
        );

        if (PEAR::isError($result)) {
            return $this->output(
                translate($result->getMessage()), JSON::STATUS_ERROR
            );
        }

        return $this->output(translate('email_success'), JSON::STATUS_OK);
    }

    /**
     * Email Search Results
     *
     * @return void
     * @access public
     */
    public function emailSearch()
    {
        include_once 'services/Search/Email.php';

        $emailService = new Email();
        $result = $emailService->sendEmail(
            $_REQUEST['url'], $_REQUEST['to'], $_REQUEST['from'],
            $_REQUEST['message']
        );

        if (PEAR::isError($result)) {
            return $this->output(
                translate($result->getMessage()), JSON::STATUS_ERROR
            );
        }

        return $this->output(translate('email_success'), JSON::STATUS_OK);
    }

    /**
     * Create new list
     *
     * @return void
     * @access public
     */
    public function addList()
    {
        include_once 'services/MyResearch/ListEdit.php';

        $user = UserAccount::isLoggedIn();
        if ($user === false) {
            return $this->output(
                translate('You must be logged in first'), JSON::STATUS_NEED_AUTH
            );
        }

        $listService = new ListEdit();
        $result = $listService->addList();
        if (PEAR::isError($result)) {
            return $this->output(
                translate($result->getMessage()), JSON::STATUS_ERROR
            );
        }

        return $this->output(array('newId'=>$result), JSON::STATUS_OK);
    }

    /**
     * Export a list of checked favorites -- actually, we just save the ID list to
     * the session so that a separate link can subsequently be used to retrieve the
     * actual content.
     *
     * @return void
     * @access public
     */
    public function exportFavorites()
    {
        include_once 'services/Cart/Export.php';

        global $configArray;
        $_SESSION['exportIDS'] =  $_POST['ids'];
        $_SESSION['exportFormat'] = $_POST['format'];

        $url = Export::getExportUrl();
        $html = '<p><a class="save" onclick="hideLightbox();" href="';
        if (strtolower($_POST['format']) == 'refworks') {
            $html .= $url . '" target="_blank">' . translate('export_refworks') .
                '</a></p>';
        } else {
            $html .= $url . '">' . translate('export_download') . '</a></p>';
        }
        return $this->output(
            array('result'=>translate('Done'), 'result_additional'=>$html),
            JSON::STATUS_OK
        );
    }

    /**
     * Saves records to a User's favorites
     *
     * @return void
     * @access public
     */
    public function bulkSave()
    {
        // Without IDs, we can't continue
        if (empty($_REQUEST['ids'])) {
            return $this->output(
                array('result'=>translate('bulk_error_missing')),
                JSON::STATUS_ERROR
            );
        }

        include_once 'services/Cart/Save.php';

        $user = UserAccount::isLoggedIn();
        if ($user === false) {
            return $this->output(
                translate('You must be logged in first'), JSON::STATUS_NEED_AUTH
            );
        }

        $saveService = new Save();
        $result = $saveService->saveRecord();
        if ($result) {
            return $this->output(
                array('result' => $result, 'info' => translate("bulk_save_success")),
                JSON::STATUS_OK
            );
        } else {
            return $this->output(
                array('info' => translate('bulk_save_error')),
                JSON::STATUS_ERROR
            );
        }

    }

    /**
     * Delete a set of favorites.
     *
     * @return void
     * @access public
     */
    public function deleteFavorites()
    {
        include_once 'services/MyResearch/Delete.php';
        $ids = $_POST['ids'];
        if (is_array($ids)) {
            $listID = isset($_POST['listID'])?$_POST['listID']:false;
            $deleteFavorites = new Delete();
            $result = $deleteFavorites->deleteFavorites($ids, $listID);
            if (!PEAR::isError($result) && !empty($result['deleteDetails'])) {
                return $this->output(
                    array('result' => translate($result['deleteDetails'])),
                    JSON::STATUS_OK
                );
            }
        } else {
             return $this->output(
                 array('result'=>translate('delete_missing')), JSON::STATUS_ERROR
             );
        }
    }

    /**
     * Delete records from a User's cart
     *
     * @return void
     * @access public
     */
    public function removeItemsCart()
    {
        include_once 'services/Cart/Cart.php';
        $cart = new Cart();
        // Without IDs, we can't continue
        if (empty($_POST['ids'])) {
            $this->output(
                array('result'=>translate('bulk_error_missing')),
                JSON::STATUS_ERROR
            );
        }
        $cart->cart->removeItems($_REQUEST['ids']);
        return $this->output(array('delete' => true), JSON::STATUS_OK);
    }

    /**
     * Output the HTML to be used in a dialog box (aka 'lightbox').
     * NOTE: this method outputs HTML instead of JSON so it can be $.load() into
     * any div.
     *
     * @return void
     * @access public
     */
    public function getLightbox()
    {
        global $configArray;
        global $interface;

        // Assign our followup
        $interface->assign('followupModule', $_GET['followupModule']);
        $interface->assign('followupAction', $_GET['followupAction']);
        $interface->assign('followupId',     $_GET['followupId']);

        // Sanitize incoming parameters
        $module = preg_replace('/[^\w]/', '', $_GET['submodule']);
        $action = preg_replace('/[^\w]/', '', $_GET['subaction']);

        // Assign updated module/action to interface so lightbox can be more
        // self-aware -- we don't want everything treated as AJAX/JSON!
        $interface->assign('module', $module);
        $interface->assign('action', $action);

        // Use our version of login lightbox
        if ($module == 'AJAX' && $action=='Login') {
            $page = $interface->fetch('AJAX/login.tpl');
            $interface->assign('title', $_GET['message']);
            $interface->assign('page', $page);
            $interface->display('AJAX/lightbox.tpl');
            exit;
        }

        // Call Action
        $path = 'services/' . $module . '/' . $action. '.php';
        if (is_readable($path)) {
            include_once $path;
            if (class_exists($action)) {
                $service = new $action();
                $page = $service->launch();
                $interface->assign('page', $page);
            } else {
                echo translate('Unknown Action');
            }
        } else {
            echo translate('Cannot Load Action');
        }
        return $interface->display('AJAX/lightbox.tpl');
    }

    /**
     * Fetch Links from resolver given an OpenURL and format as HTML
     * and output the HTML content in JSON object.
     *
     * @return void
     * @access public
     * @author Graham Seaman <Graham.Seaman@rhul.ac.uk>
     */
    public function getResolverLinks()
    {
        global $configArray;
        global $interface;

        include_once 'sys/Resolver/ResolverConnection.php';

        $openUrl = isset($_GET['openurl']) ? $_GET['openurl'] : '';
        $keywords = array('module', 'action', 'method');

        $resolverType = isset($configArray['OpenURL']['resolver'])
            ? $configArray['OpenURL']['resolver'] : 'other';
        $resolver = new ResolverConnection($resolverType);
        if (!$resolver->driverLoaded()) {
            return $this->output(
                translate("Could not load driver for $resolverType"),
                JSON::STATUS_ERROR
            );
        }

        $result = $resolver->fetchLinks($openUrl);

        // Sort the returned links into categories based on service type:
        $electronic = $print = $services = array();
        foreach ($result as $link) {
            switch (isset($link['service_type']) ? $link['service_type'] : '') {
            case 'getHolding':
                $print[] = $link;
                break;
            case 'getWebService':
                $services[] = $link;
                break;
            case 'getDOI':
                // Special case -- modify DOI text for special display:
                $link['title'] = translate('Get full text');
                $link['coverage'] = '';
            case 'getFullTxt':
            default:
                $electronic[] = $link;
                break;
            }
        }

        // Render the links using Smarty:
        $interface->assign('openUrl', $openUrl);
        $interface->assign('print', $print);
        $interface->assign('electronic', $electronic);
        $interface->assign('services', $services);
        $html = $interface->fetch('AJAX/resolverLinks.tpl');

        // output HTML encoded in JSON object
        return $this->output($html, JSON::STATUS_OK);
    }

    /**
     * Send output data and exit.
     *
     * @param mixed  $data   The response data
     * @param string $status Status of the request
     *
     * @return void
     * @access public
     */
    protected function output($data, $status)
    {
        header('Content-type: application/javascript');
        header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        $output = array('data'=>$data,'status'=>$status);
        echo json_encode($output);
        exit;
    }

    /**
     * Support method for getItemStatuses() -- when presented with multiple values,
     * pick which one(s) to send back via AJAX.
     *
     * @param array  $list Array of values to choose from.
     * @param string $mode config.ini setting -- first, all or msg
     * @param string $msg  Message to display if $mode == "msg"
     *
     * @return string
     * @access private
     */
    private function _pickValue($list, $mode, $msg)
    {
        // Make sure array contains only unique values:
        $list = array_unique($list);

        // If there is only one value in the list, or if we're in "first" mode,
        // send back the first list value:
        if ($mode == 'first' || count($list) == 1) {
            return $list[0];
        } else if (count($list) == 0) {
            // Empty list?  Return a blank string:
            return '';
        } else if ($mode == 'all') {
            // All values mode?  Return comma-separated values:
            return implode(', ', $list);
        } else {
            // Message mode?  Return the specified message, translated to the
            // appropriate language.
            return translate($msg);
        }
    }

    /**
     * Support method for getItemStatuses() -- process a single bibliographic record
     * for location settings other than "group".
     *
     * @param array  $record            Information on items linked to a single bib
     *                                  record
     * @param array  $messages          Custom status HTML
     *                                  (keys = available/unavailable)
     * @param string $locationSetting   The location mode setting used for
     *                                  _pickValue()
     * @param string $callnumberSetting The callnumber mode setting used for
     *                                  _pickValue()
     *
     * @return array                    Summarized availability information
     * @access private
     */
    private function _getItemStatus($record, $messages, $locationSetting,
        $callnumberSetting
    ) {
        // Summarize call number, location and availability info across all items:
        $callNumbers = $locations = array();
        $available = false;
        $placeaholdneeded = 0;
        $presenceOnly = 0;
        $presenceOnlyIndicator = '0';
        $available_copies = 0;
        $lent = 0;
        foreach ($record as $key => $info) {
            $timestamp[$key]  = $info['duedate_timestamp'];
            // Find an available copy
            if ($info['availability']) {
                $available = true;
                $available_copies++;
                // Check if this copy has a recallhref
                if ($info['recallhref']) {
                    $placeaholdneeded++;
                    $placeaholdhref = $info['recallhref'];
                }
                if ($info['presenceOnly'] === '1') {
                    $presenceOnly++;
                }
            }
            if ($info['duedate_timestamp']) {
                $lent++;
            }
            if ($info['status'] === 'missing') {
                $availability = 'missing';
            }
            if ($info['status'] === 'lost') {
                $availability = 'lost';
            }
            // Store call number/location info:
            $callNumbers[] = $info['callnumber'];
            $locations[] = $info['location'];
        }

        // Sort the records with their duedate timestamp ascending
        $recallhref = '';
        $duedate = '';
        array_multisort($timestamp, SORT_ASC, $record);
        foreach ($record as $rec) {
            if ($rec['recallhref'] && $recallhref === '') {
                $recallhref = $rec['recallhref'];
                if ($rec['duedate']) {
                    $duedate = $rec['duedate'];
                }
            }
        }

        // Determine call number string based on findings:
        $callNumber = $this->_pickValue(
            $callNumbers, $callnumberSetting, 'Multiple Call Numbers'
        );

        // Determine location string based on findings:
        $location = $this->_pickValue(
            $locations, $locationSetting, 'Multiple Locations'
        );

        // Check if all available items can be used presence only
        if ($presenceOnly === $available_copies && $available_copies > 0) {
            $presenceOnlyIndicator = '1';
        }
        if ($presenceOnly === $available_copies && $available_copies > 0 && $lent > 0) {
            $presenceOnlyIndicator = '2';
        }
        // Collect details about links to show in result list
        $reservationLink = '';
        if ($available) $availability = 'available';
        else if ($availability != 'missing' && $availability != 'lost') $availability = 'notforloan';
        // if all available copies must be recalled, we should show the place-a-hold-button
        if ($available && $placeaholdneeded === $available_copies) {
            $reservationLink = ' <a href="'.htmlspecialchars($placeaholdhref).'" target="_blank">'.translate('Place a Hold').'</a>'; 
        }
        // if no copy is available, we should show a recall button
        if ($available === false && $recallhref !== '') {
            $reservationLink = ' <a href="'.htmlspecialchars($recallhref).'" target="_blank">'.translate('Recall this').'</a>';
            $availability = 'unavailable';
        }
        // Location should not get transformed, if it is a hyperlink
        $electronic = '0';
        $locationArr = explode(">", $location);
        if (count($locationArr) === 1) {
            $location = htmlentities($location, ENT_COMPAT, 'UTF-8');
        }
        else {
            $electronic = '1';
            $locArr = explode("<", $locationArr[1]);
            $locArr[0] = htmlentities($locArr[0], ENT_COMPAT, 'UTF-8');
            $locationArr[1] = implode("<", $locArr);
            $location = implode(">", $locationArr);
        }

        // Send back the collected details:
        return array(
            'id' => $record[0]['id'],
            'availability' => ($available ? 'true' : 'false'),
            'availability_message' =>
                $messages[$availability],
            'location' => $location,
            'locationList' => false,
            'reserve' =>
                ($record[0]['reserve'] == 'Y' ? 'true' : 'false'),
            'reserve_message' => $record[0]['reserve'] == 'Y'
                ? translate('on_reserve') : translate('Not On Reserve'),
            'callnumber' => '<span class="callnumberResult">'.htmlentities($callNumber, ENT_COMPAT, 'UTF-8').'</span>',
            'reservationUrl' => $reservationLink,
            'duedate' => $duedate,
            'presenceOnly' => $presenceOnlyIndicator,
            'electronic' => $electronic
        );
    }

    /**
     * Support method for getItemStatuses() -- process a single bibliographic record
     * for "group" location setting.
     *
     * @param array  $record            Information on items linked to a single
     *                                  bib record
     * @param array  $messages          Custom status HTML
     *                                  (keys = available/unavailable)
     * @param string $callnumberSetting The callnumber mode setting used for
     *                                  _pickValue()
     *
     * @return array                    Summarized availability information
     * @access private
     */
    private function _getItemStatusGroup($record, $messages, $callnumberSetting)
    {
        // Summarize call number, location and availability info across all items:
        $locations =  array();
        $available = false;
        foreach ($record as $info) {
            // Find an available copy
            if ($info['availability']) {
                $available = $locations[$info['location']]['available'] = true;
            }
            if ($info['status'] === 'missing') {
                $availability = 'missing';
            }
            if ($info['status'] === 'lost') {
                $availability = 'lost';
            }
            // Store call number/location info:
            $locations[$info['location']]['callnumbers'][] = $info['callnumber'];
        }

        // Build list split out by location:
        $locationList = false;
        foreach ($locations as $location => $details) {
            $locationCallnumbers = array_unique($details['callnumbers']);
            // Determine call number string based on findings:
            $locationCallnumbers = $this->_pickValue(
                $locationCallnumbers, $callnumberSetting, 'Multiple Call Numbers'
            );
            $locationInfo = array(
                'availability' =>
                    isset($details['available']) ? $details['available'] : false,
                'location' => htmlentities($location, ENT_COMPAT, 'UTF-8'),
                'callnumbers' =>
                    htmlentities($locationCallnumbers, ENT_COMPAT, 'UTF-8')
            );
            $locationList[] = $locationInfo;
        }

        // Send back the collected details:
        $reservationLink = '';
        if ($available) $availability = 'available';
        else if ($availability != 'missing' && $availability != 'lost') $availability = 'notforloan';
        if ($available && $record[0]['recallhref']) {
            $reservationLink = ' <a href="'.htmlspecialchars($record[0]['recallhref']).'" target="_blank">'.translate('Place a Hold').'</a>';
        }
        if ($available === false && $record[0]['recallhref']) {
            $reservationLink = ' <a href="'.htmlspecialchars($record[0]['recallhref']).'" target="_blank">'.translate('Recall this').'</a>';
            $availability = 'unavailable';
        }
        return array(
            'id' => $record[0]['id'],
            'availability' => ($available ? 'true' : 'false'),
            'availability_message' =>
                $messages[$availability],
            'location' => false,
            'locationList' => $locationList,
            'reserve' =>
                ($record[0]['reserve'] == 'Y' ? 'true' : 'false'),
            'reserve_message' => $record[0]['reserve'] == 'Y'
                ? translate('on_reserve') : translate('Not On Reserve'),
            'callnumber' => false,
            'reservationUrl' => $reservationLink
        );
    }

    /**
     * Support method for getItemStatuses() -- process a single bibliographic record
     * for "details" location setting.
     *
     * @param array $record Information on items linked to a single bib record
     *
     * @return array        Detailed availability information
     * @access private
     */
    private function _getItemStatusFull($record)
    {
        global $interface;
        $interface->assign('statusItems', $record);
        return $interface->fetch('AJAX/status-full.tpl');
    }

    /**
     * Generate the "salt" used in the salt'ed login request.
     *
     * @return string
     * @access private
     */
    private function _generateSalt()
    {
        return str_replace('.', '', $_SERVER['REMOTE_ADDR']);
    }
}
?>
