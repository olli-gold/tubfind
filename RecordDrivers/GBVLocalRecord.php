<?php
/**
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
 */

require_once 'RecordDrivers/GBVCentralRecord.php';

/**
 * GBVCentral Record Driver
 *
 * This class is designed to handle records recieved from GBV Discovery.
 * Much of its functionality is inherited from the default index-based driver.
 * @author	Oliver Goldschmidt <o.goldschmidt@tu-harburg.de>
 *
 */
class GBVLocalRecord extends GBVCentralRecord
{

    /**
     * checks if this item is in the local stock
     *
     * @access  protected
     * @return  string
     */
    public function checkInterlibraryLoan()
    {
        // Return null if we have no table of contents:
        $fields = $this->marcRecord->getFields('900');
        if (!$fields) {
            return null;
        }

        $configPica = parse_ini_file('conf/GBVCentral.ini', true);

        $mylib = $configPica['isil'];

        // If we got this far, we have libraries owning this item -- check if we have it locally
        foreach ($fields as $field) {
            $subfields = $field->getSubfields();
            foreach ($subfields as $subfield) {
                if ($subfield->getCode() === 'b') {
                    if ($subfield->getData() === $mylib) {
                        return '0';
                    }
                }
            }
        }

        // Is this item an e-ressource?
        if (in_array('eBook', $this->getFormats()) === true || in_array('eJournal', $this->getFormats()) === true || $this->isNLZ() === true) {
            return '0';
        }

        return '1';
    }
}
?>