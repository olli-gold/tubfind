<?php
/**
 * Base class shared by most Cart module actions.
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
 * @package  Controller_Cart
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */

require_once 'Bulk.php';
require_once 'sys/Cart_Model.php';


/**
 * Base class shared by most Cart module actions.
 *
 * @category VuFind
 * @package  Controller_Cart
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Cart extends Bulk
{
    public $cart;

    /**
     * Constructor.
     *
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
        $this->cart = Cart_Model::getInstance();
    }

     /**
     * Process parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        $ids = array();

        if (isset($_POST['selectAll']) && is_array($_REQUEST['idsAll'])) {
            $ids = $_POST['idsAll'];
        } else if (isset($_POST['ids'])) {
            $ids = $_POST['ids'];
        }

        if (isset($_POST['empty'])) {
            $this->cart->emptyCart();
        } else if (isset($_POST['delete'])) {
            $this->_deleteItems($ids);
        } else if (isset($_POST['add'])) {
            $this->_addItems($ids);
        }

        if (isset($_GET['lightbox'])) {
            // Use for lightbox
            return $this->viewCartLightBox();
        } else {
            $this->viewCart();
        }
    }

    /**
     * Add Items to Cart
     *
     * @param array $ids IDs to add
     *
     * @return void
     * @access private
     */
    private function _addItems($ids)
    {
        if (!empty($ids)) {
            $addItems = $this->cart->addItems($ids);
            if (!$addItems['success']) {
                $this->infoMsg = translate('bookbag_full_msg') . ". " .
                $addItems['notAdded'] . " " .
                translate('items_already_in_bookbag') . ".";
            }
        } else {
            $this->errorMsg = "bulk_noitems_advice";
        }
    }

    /**
     * Delete Items from Cart
     *
     * @param array $ids IDs to delete.
     *
     * @return void
     * @access private
     */
    private function _deleteItems($ids)
    {
        if (!empty($ids)) {
            $this->cart->removeItems($ids);
        } else {
            $this->errorMsg =  "bulk_noitems_advice";
        }
    }

    /**
     * Process parameters and display cart contents.
     *
     * @return void
     * @access public
     */
    public function viewCart()
    {
        global $interface;
        $interface->assign('errorMsg', $this->errorMsg);
        $interface->assign('infoMsg', $this->infoMsg);
        $interface->assign('showExport', $this->showExport);
        $interface->assign('exportOptions', $this->exportOptions);
        $interface->setTemplate('view.tpl');
        $interface->assign('subTemplate', 'cart-view.tpl');
        $interface->setPageTitle('Book Bag');
        $interface->display('layout.tpl');
    }

    /**
     * Process parameters and display cart contents.
     *
     * @return void
     * @access public
     */
    public function viewCartLightBox()
    {
        global $interface;
        $interface->assign('title', $_GET['message']);
        $interface->assign('errorMsg', $this->errorMsg);
        $interface->assign('infoMsg', $this->infoMsg);
        $interface->assign('showExport', $this->showExport);
        $interface->assign('exportOptions', $this->exportOptions);
        return $interface->fetch('Cart/cart-view.tpl');
    }
}
