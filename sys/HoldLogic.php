<?php
/**
 * Hold Logic Class
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
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Luke O'Sullivan <l.osullivan@swansea.ac.uk>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes#index_interface Wiki
 */

require_once 'CatalogConnection.php';
require_once 'Crypt/generateHMAC.php';

/**
 * Hold Logic Class
 *
 * @category VuFind
 * @package  Support_Classes
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Luke O'Sullivan <l.osullivan@swansea.ac.uk>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes#index_interface Wiki
 */
class HoldLogic
{
    protected $catalog;
    protected $hideHoldings;

    /**
     * Constructor
     *
     * @param object $catalog A catalog connection
     *
     * @access public
     */
    public function __construct($catalog = false)
    {
        global $configArray;

        $this->hideHoldings = isset($configArray['Record']['hide_holdings'])
            ? $configArray['Record']['hide_holdings'] : array();

        $this->catalog = ($catalog == true)
            ? $catalog : ConnectionManager::connectToCatalog();
    }

    /**
     * Support method to rearrange the holdings array for displaying convenience.
     * VuFind is currently set up to display only the notes and summaries found
     * in the first item for a location; this method gathers notes and summaries
     * from other items and pushes them into the first for convenience.
     *
     * Note: This is not very elegant -- it would make more sense to have separate
     * elements in the return array for holding collected notes and summaries rather
     * than stuffing them into an arbitrary item; this will be addressed in VuFind
     * 2.0.
     *
     * @param array $holdings An associative array of location => item array
     *
     * @return array          An associative array keyed by location with each
     * entry being a list of items where the first holds the summary
     * and notes for all.
     * @access protected
     */
    protected function formatHoldings($holdings)
    {
        foreach ($holdings as $location => $items) {
            $notes = array();
            $summaries = array();
            foreach ($items as $item) {
                if (isset($item['notes'])) {
                    if (!is_array($item['notes'])) {
                        $item['notes'] = empty($item['notes'])
                            ? array() : array($item['notes']);
                    }
                    foreach ($item['notes'] as $note) {
                        if (!in_array($note, $notes)) {
                            $notes[] = $note;
                        }
                    }
                }
                if (isset($item['summary'])) {
                    if (!is_array($item['summary'])) {
                        $item['summary'] = empty($item['summary'])
                            ? array() : array($item['summary']);
                    }
                    foreach ($item['summary'] as $summary) {
                        if (!in_array($summary, $summaries)) {
                            $summaries[] = $summary;
                        }
                    }
                }
            }
            $holdings[$location][0]['notes'] = $notes;
            $holdings[$location][0]['summary'] = $summaries;
        }
        return $holdings;
    }

    /**
     * Public method for getting item holdings from the catalog and selecting which
     * holding method to call
     *
     * @param string $id     An Bib ID
     * @param array  $patron An array of patron data
     *
     * @return array A sorted results set
     * @access public
     */

    public function getHoldings($id, $patron = false)
    {
        $holdings = array();

        // Get Holdings Data
        if ($this->catalog && $this->catalog->status) {
            $result = $this->catalog->getHolding($id, $patron);
            if (PEAR::isError($result)) {
                PEAR::raiseError($result);
            }

            $mode = CatalogConnection::getHoldsMode();

            if ($mode == "disabled") {
                 $holdings = $this->standardHoldings($result);
            } else if ($mode == "driver") {
                $holdings = $this->driverHoldings($result);
            } else {
                $holdings = $this->generateHoldings($result, $mode);
            }
        }
        return $this->formatHoldings($holdings);
    }

    /**
     * Protected method for standard (i.e. No Holds) holdings
     *
     * @param array $result A result set returned from a driver
     *
     * @return array A sorted results set
     * @access protected
     */
    protected function standardHoldings($result)
    {
        $holdings = array();
        if (count($result)) {
            foreach ($result as $copy) {
                $show = !in_array($copy['location'], $this->hideHoldings);
                if ($show) {
                    $holdings[$copy['location']][] = $copy;
                }
            }
        }
        return $holdings;
    }

    /**
     * Protected method for driver defined holdings
     *
     * @param array $result A result set returned from a driver
     *
     * @return array A sorted results set
     * @access protected
     */
    protected function driverHoldings($result)
    {
        global $user;

        $holdings = array();

        // Are holds allows?
        $checkHolds = $this->catalog->checkFunction("Holds");

        if (count($result)) {
            foreach ($result as $copy) {
                $show = !in_array($copy['location'], $this->hideHoldings);
                if ($show) {
                    if ($checkHolds != false) {
                        // Is this copy holdable / linkable
                        if ($copy['addLink']) {
                            // If the hold is blocked, link to an error page
                            // instead of the hold form:
                            $copy['link'] = (strcmp($copy['addLink'], 'block') == 0)
                                ? "?errorMsg=hold_error_blocked"
                                : $this->_getHoldDetails(
                                    $copy, $checkHolds['HMACKeys']
                                );
                            // If we are unsure whether hold options are available,
                            // set a flag so we can check later via AJAX:
                            $copy['check'] = (strcmp($copy['addLink'], 'check') == 0)
                                ? true : false;
                        }
                    }
                    $holdings[$copy['location']][] = $copy;
                }
            }
        }
        return $holdings;
    }

    /**
     * Protected method for vufind (i.e. User) defined holdings
     *
     * @param array  $result A result set returned from a driver
     * @param string $type   The holds mode to be applied from:
     * (all, holds, recalls, availability)
     *
     * @return array A sorted results set
     * @access protected
     */
    protected function generateHoldings($result, $type)
    {
        global $user;
        global $configArray;

        $holdings = array();
        $any_available = false;

        $holds_override = isset($configArray['Catalog']['allow_holds_override'])
            ? $configArray['Catalog']['allow_holds_override'] : false;

        if (count($result)) {
            foreach ($result as $copy) {
                $show = !in_array($copy['location'], $this->hideHoldings);
                if ($show) {
                    $holdings[$copy['location']][] = $copy;
                    // Are any copies available?
                    if ($copy['availability'] == true) {
                        $any_available = true;
                    }
                }
            }

            // Are holds allows?
            $checkHolds = $this->catalog->checkFunction("Holds");

            if ($checkHolds != false) {
                if (is_array($holdings)) {
                    // Generate Links
                    // Loop through each holding
                    foreach ($holdings as $location_key => $location) {
                        foreach ($location as $copy_key => $copy) {
                            // Override the default hold behavior with a value from
                            // the ILS driver if allowed and applicable:
                            $switchType
                                = ($holds_override && isset($copy['holdOverride']))
                                ? $copy['holdOverride'] : $type;

                            switch($switchType) {
                            case "all":
                                $addlink = true; // always provide link
                                break;
                            case "holds":
                                $addlink = $copy['availability'];
                                break;
                            case "recalls":
                                $addlink = !$copy['availability'];
                                break;
                            case "availability":
                                $addlink = !$copy['availability']
                                    && ($any_available == false);
                                break;
                            default:
                                $addlink = false;
                                break;
                            }
                            // If a valid holdable status has been set, use it to
                            // determine if a hold link is created
                            $addlink = isset($copy['is_holdable'])
                                ? ($addlink && $copy['is_holdable']) : $addlink;

                            if ($addlink) {
                                $holdLink = "";
                                if ($checkHolds['function'] == "getHoldLink") {
                                    /* Build opac link */
                                    $holdings[$location_key][$copy_key]['link']
                                        = $this->catalog->getHoldLink(
                                            $copy['id'], $copy
                                        );
                                } else {
                                    /* Build non-opac link */
                                    $holdings[$location_key][$copy_key]['link']
                                        = $this->_getHoldDetails(
                                            $copy, $checkHolds['HMACKeys']
                                        );
                                }
                            }
                        }
                    }
                }
            }
        }
        return $holdings;
    }

    /**
     * Get Hold Form
     *
     * Supplies holdLogic with the form details required to place a hold
     *
     * @param array $holdDetails An array of item data
     * @param array $HMACKeys    An array of keys to hash
     *
     * @return string A url link (with HMAC key)
     * @access private
     */
    private function _getHoldDetails($holdDetails, $HMACKeys)
    {
        global $configArray;

        $siteUrl = $configArray['Site']['url'];
        $id = $holdDetails['id'];

        // Generate HMAC
        $HMACkey = generateHMAC($HMACKeys, $holdDetails);

        // Add Params
        foreach ($holdDetails as $key => $param) {
            $needle = in_array($key, $HMACKeys);
            if ($needle) {
                $queryString[] = $key. "=" .urlencode($param);
            }
        }

        //Add HMAC
        $queryString[] = "hashKey=" . $HMACkey;

        // Build Params
        $urlParams = "?" . implode("&", $queryString);

        $holdLink = $siteUrl."/Record/".urlencode($id)."/Hold".$urlParams."#tabnav";

        return $holdLink;
    }
}
?>
