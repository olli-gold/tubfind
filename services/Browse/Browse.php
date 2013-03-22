<?php
/**
 * Browse module base class.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2009.
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
 * @package  Controller_Browse
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'Action.php';

/**
 * Browse module base class.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2009.
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
 * @package  Controller_Browse
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Browse extends Action
{
    /**
     * Build an array containing options describing a top-level Browse option.
     *
     * @param string $action      The name of the Action for this option
     * @param string $description A description of this Browse option
     *
     * @return array              The Browse option array
     * @access private
     */
    private function _buildBrowseOption($action, $description)
    {
        return array('action' => $action, 'description' => $description);
    }

    /**
     * Constructor
     *
     * Sets up the common data shared by all Browse modules.
     *
     * @access public
     */
    public function __construct()
    {
        global $interface;
        global $configArray;

        // Initialize the array of top-level browse options.
        $browseOptions = array();

        // First option: tags -- is it enabled in config.ini?  If no setting is
        // found, assume it is active.
        if (!isset($configArray['Browse']['tag'])
            || $configArray['Browse']['tag'] == true
        ) {
            $browseOptions[] = $this->_buildBrowseOption('Tag', 'Tag');
            $interface->assign('tagEnabled', true);
        }

        // Read configuration settings for LC / Dewey call number display; default
        // to LC only if no settings exist in config.ini.
        if (!isset($configArray['Browse']['dewey'])
            && !isset($configArray['Browse']['lcc'])
        ) {
            $lcc = true;
            $dewey = false;
        } else {
            $lcc = (isset($configArray['Browse']['lcc']) &&
                $configArray['Browse']['lcc']);
            $dewey = (isset($configArray['Browse']['dewey']) &&
                $configArray['Browse']['dewey']);
        }

        // Add the call number options as needed -- note that if both options exist,
        // we need to use special text to disambiguate them.
        if ($dewey) {
            $browseOptions[] = $this->_buildBrowseOption(
                'Dewey', ($lcc ? 'browse_dewey' : 'Call Number')
            );
            $interface->assign('deweyEnabled', true);
        }
        if ($lcc) {
            $browseOptions[] = $this->_buildBrowseOption(
                'LCC', ($dewey ? 'browse_lcc' : 'Call Number')
            );
            $interface->assign('lccEnabled', true);
        }

        // Loop through remaining browse options.  All may be individually disabled
        // in config.ini, but if no settings are found, they are assumed to be on.
        $remainingOptions = array(
            'Author', 'Topic', 'Genre', 'Region', 'Era'
        );
        foreach ($remainingOptions as $current) {
            $config = strtolower($current);
            if (!isset($configArray['Browse'][$config])
                || $configArray['Browse'][$config] == true
            ) {
                $browseOptions[] = $this->_buildBrowseOption($current, $current);
                $interface->assign($config . 'Enabled', true);
            }
        }

        $interface->assign('browseOptions', $browseOptions);
    }
}

?>
