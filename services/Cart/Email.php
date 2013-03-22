<?php
/**
 * Bulk Emailer
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
 * Bulk Emailer
 *
 * @category VuFind
 * @package  Bulk_Emailer
 * @author   Luke O'Sullivan <l.osullivan@swansea.ac.uk>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Email extends Bulk
{
    /**
     * Process parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $interface;
        global $configArray;

        if (isset($_POST['submit'])) {
            $process = $this->_processSubmit();
        }

        // Display Page
        if (isset($_GET['lightbox'])) {
            return $this->_processLightbox();
        } else {
            $this->_processNonLightbox();
        }
    }

    /**
     * Process submitted details
     * Display error page on terminal error, success page on successs or current
     * page on recoverable error
     *
     * @return void
     * @access private
     */
    private function _processSubmit()
    {
        global $interface;
        global $configArray;

        // Without IDs, we can't continue
        if (empty($_REQUEST['ids'])) {
            header(
                "Location: " . $this->followupUrl . "?errorMsg=bulk_noitems_advice"
            );
            exit();
        }

        $url = $configArray['Site']['url'] . "/Search/Results?lookfor=" .
            urlencode(implode(" ", $_POST['ids'])) . "&type=ids";
        $result = $this->sendEmail(
            $url, $_POST['to'], $_POST['from'], $_POST['message']
        );

        if (!PEAR::isError($result)) {
            $this->followupUrl .= "?infoMsg=" . urlencode("bulk_email_success");
            header("Location: " . $this->followupUrl);
            exit();
        } else {
            // Assign Error Message and Available Data
            $this->errorMsg = $result->getMessage();
            $interface->assign('formTo', $_POST['to']);
            $interface->assign('formFrom', $_POST['from']);
            $interface->assign('formMessage', $_POST['message']);
            $interface->assign('formIDS', $_POST['ids']);
        }
    }

    /**
     * Process Light Box Request
     * Display error message on terminal error or email details page on success
     *
     * @return void
     * @access private
     */
    private function _processLightbox()
    {
        global $interface;
        global $configArray;

        // Should really get here with JS validation but... without IDs,
        // we can't continue
        if (empty($_REQUEST['ids'])) {
            $interface->assign('title', $_GET['message']);
            $interface->assign('errorMsg', 'bulk_noitems_advice');
            return $interface->fetch('Cart/bulkError.tpl');
        }

        $interface->assign('title', $_GET['message']);
        $interface->assign('emailIDS', $_REQUEST['ids']);
        $interface->assign('emailList', $this->getRecordDetails($_REQUEST['ids']));
        return $interface->fetch('Cart/email.tpl');
    }

    /**
     * Process Non-Light Box Request
     * Display error message on terminal error or email details page on success
     *
     * @return void
     * @access private
     */
    private function _processNonLightbox()
    {
        global $interface;
        global $configArray;

        // Assign IDs
        if (isset($_REQUEST['selectAll']) && is_array($_REQUEST['idsAll'])) {
            $ids = $_REQUEST['idsAll'];
        } else if (isset($_REQUEST['ids'])) {
            $ids = $_REQUEST['ids'];
        }

        // Without IDs, we can't continue
        if (empty($ids)) {
            header(
                "Location: " . $this->followupUrl . "?errorMsg=bulk_noitems_advice"
            );
            exit();
        }

        if ($this->origin == "Favorites") {
            // If we're on a particular list, save the ID so we can redirect to
            // the appropriate page after exporting.
            if (isset($_REQUEST['listID']) && !empty($_REQUEST['listID'])) {
                $interface->assign('listID', $_REQUEST['listID']);
            } else {
                $interface->assign('followupModule', "MyResearch");
                $interface->assign('followupAction', "Favorites");
            }
        }

        $interface->assign('errorMsg', $this->errorMsg);
        $interface->assign('emailList', $this->getRecordDetails($ids));
        $interface->setPageTitle('email_selected_favorites');
        $interface->assign('subTemplate', 'email.tpl');
        $interface->assign('emailIDS', $ids);
        $interface->setTemplate('view.tpl');
        $interface->display('layout.tpl');
    }

    /**
     * Send the email.
     *
     * @param string $url     URL to include in message
     * @param string $to      Message recipient
     * @param string $from    Message sender
     * @param string $message Extra note to add to message
     *
     * @return mixed          Boolean true on success, PEAR_Error on failure.
     * @access public
     */
    public function sendEmail($url, $to, $from, $message)
    {
        global $interface;

        $subject = translate('bulk_email_title');
        $interface->assign('from', $from);
        $interface->assign('message', $message);
        $interface->assign('msgUrl', $url);
        $body = $interface->fetch('Emails/share-link.tpl');

        $mail = new VuFindMailer();
        return $mail->send($to, $from, $subject, $body);
    }
}
?>
