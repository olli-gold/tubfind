<?php
/**
 * Ajax call to get Google map JSON.
 *
 * PHP version 5
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
 * @author   Lutz Biedinger <lutz.biedinger@gmail.com>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */

require_once 'JSON.php';

/**
 * Ajax call to get Google map JSON.
 *
 * @category VuFind
 * @package  Controller_AJAX
 * @author   Lutz Biedinger <lutz.biedinger@gmail.com>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class JSON_GoogleMap extends JSON
{
    private $_searchObject;
    /**
     * Constructor.
     *
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
        $this->_searchObject = SearchObjectFactory::initSearchObject();

        // Load the desired facet information...
        $config = getExtraConfigArray('facets');
    }

    /**
     * Get data and output in JSON
     *
     * @param array $fields Solr fields to retrieve data from
     *
     * @return void
     * @access public
     */
    public function getMapData($fields = array('long_lat'))
    {
        global $interface;

        if (is_a($this->_searchObject, 'SearchObject_Solr')) {
            $this->_searchObject->init();
            $facets = $this->_searchObject->getFullFieldFacets($fields, false);
            $markers=array();
            $i = 0;
            foreach ($facets['long_lat']['data'] as $location) {
                $longLat = explode(',', $location[0]);
                $markers[$i] = array(
                    'title' => (string)$location[1], //needs to be a string
                    'location_facet' =>
                        $location[0], //needed to load in the items at the location
                    'lon' => $longLat[0],
                    'lat' => $longLat[1]
                );
                $i++;
            }
            $this->output($markers, JSON::STATUS_OK);
        } else {
            $this->output("", JSON::STATUS_ERROR);
        }
    }
}
?>
