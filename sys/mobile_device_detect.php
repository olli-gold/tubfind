<?php
/**
 * Mobile Device Detection Wrapper
 *
 * PHP version 5
 *
 * This file is a wrapper around the mobileesp library for browser detection.
 * We chose mobileesp as VuFind's default option because it is fairly robust
 * and has an Apache license which allows free redistribution.  However, it
 * is not the only option available.
 *
 * You can also replace this entire file with the code available for download
 * at http://detectmobilebrowsers.mobi/ if you would like to try alternative
 * detection rules.  Other detection libraries beyond these two options also
 * exist; it should be relatively easy to plug any of them in by modifying the
 * mobile_device_detect function below.
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
 * @package  Support_Classes
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://code.google.com/p/mobileesp/ MobileESP Project
 */
require_once 'sys/mobileesp/mdetect.php';

/**
 * Function to detect if a mobile device is being used.
 *
 * @return bool
 */ // @codingStandardsIgnoreStart
function mobile_device_detect()
{   // @codingStandardsIgnoreEnd
    // Do the most exhaustive device detection possible; other method calls
    // may be used instead of DetectMobileLong if you want to target a narrower
    // class of devices.
    $mobile = new uagent_info();
    return $mobile->DetectMobileLong();
}

?>
