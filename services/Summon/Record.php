<?php
/**
 * Record action for Summon module
 *
 * PHP version 5
 *
 * Copyright (C) Andrew Nagy 2008.
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
 * @package  Controller_Summon
 * @author   Andrew Nagy <vufind-tech@lists.sourceforge.net>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'Base.php';

require_once 'sys/Summon.php';

require_once 'services/MyResearch/lib/User.php';
require_once 'services/MyResearch/lib/Resource.php';
require_once 'services/MyResearch/lib/Resource_tags.php';
require_once 'services/MyResearch/lib/Tags.php';

/**
 * Record action for Summon module
 *
 * @category VuFind
 * @package  Controller_Summon
 * @author   Andrew Nagy <vufind-tech@lists.sourceforge.net>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Record extends Base
{
    protected $record;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
        global $interface;
        global $configArray;

        // Call parent constructor
        parent::__construct();

        // Fetch Record
        $summon = new Summon(
            $configArray['Summon']['apiId'], $configArray['Summon']['apiKey']
        );
        $record = $summon->getRecord($_REQUEST['id']);
        if (PEAR::isError($record)) {
            PEAR::raiseError($record);
        } else if (!isset($record['documents'][0])) {
            PEAR::raiseError(
                new PEAR_Error("Cannot access record {$_REQUEST['id']}")
            );
        } else {
            $this->record = $record['documents'][0];
        }

        // Set Proxy URL
        $interface->assign(
            'proxy', isset($configArray['EZproxy']['host'])
            ? $configArray['EZproxy']['host'] : false
        );

        // Send record ID to template
        $interface->assign('id', $_REQUEST['id']);
    }

    /**
     * Process parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $interface;

        // Send basic information to the template.
        $interface->assign('record', $this->record);
        $interface->setPageTitle($this->record['Title'][0]);

        // Assign the ID of the last search so the user can return to it.
        $interface->assign(
            'lastsearch',
            isset($_SESSION['lastSearchURL']) ? $_SESSION['lastSearchURL'] : false
        );

        // Retrieve tags associated with the record
        $resource = new Resource();
        $resource->record_id = $_REQUEST['id'];
        $resource->source = 'Summon';
        $tags = $resource->getTags();
        $interface->assign('tagList', is_array($tags) ? $tags : array());

        // Display Page
        $interface->setTemplate('record.tpl');
        $interface->display('layout.tpl');
    }
}

?>
