<?php
/**
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
 */

require_once 'services/MyResearch/MyResearch.php';

class Renew extends MyResearch
{
    function launch()
    {
        global $interface;

        // Get My Transactions
        if ($patron = UserAccount::catalogLogin()) {
            $this->catalog->renew($_GET['VB']);
            $result = $this->catalog->getMyTransactions($patron);
            if (!PEAR::isError($result)) {
                $transList = array();
                foreach ($result as $data) {
                    $current = array('ils_details' => $data);
                    if ($record = $this->db->getRecord($data['id'])) {
                        $current += array(
                            'id' => $record['id'],
                            'isbn' => isset($record['isbn']) ? $record['isbn'] : null,
                            'author' =>
                                isset($record['author']) ? $record['author'] : null,
                            'title' =>
                                isset($record['title']) ? $record['title'] : null,
                            'journal' =>
                                isset($record['journal']) ? $record['journal'] : null,
                            'format' =>
                                isset($record['format']) ? $record['format'] : null,
                            'duedate' =>
                                $data['duedate'],
                            'renewals' =>
                                $data['renewals'],
                            'vb' =>
                                $data['vb'],
                            'reservations' => $data['reservations']
                        );
                    }
                    $transList[] = $current;
                }
            }
        }
        $interface->assign('transList', $transList);
        $interface->setTemplate('checkedout.tpl');
        $interface->setPageTitle('Checked Out Items');
        $interface->display('layout.tpl');
    }
}

?>