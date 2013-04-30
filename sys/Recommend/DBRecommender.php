<?php

/**
 * DBRecommender Driver
 *
 * PHP version 5
 *
 * SUB Hamburg / Hajo Seng
 *
**/

class DBRecommender {

        private $recommenderURL = 'http://suche.suub.uni-bremen.de/cgi-bin/CiXbase/brewis/CiXbase_search?index=L&CLUSTER=3&RELEVANCE=45&PRECISION=220&n_rtyp=ceED&n_dytp=1L&dtyp=ab&LAN=DE&act=search&term=';
        private $mysqlConnector = null;
        private $mysqlConnectData = array( 'host' => 'localhost' , 'user' => 'vufind' , 'password' => 'vufinder2010' , 'db' => 'db_recommender' );
        private $timeOut = 10;
        private $dbData = null;
        private $databases = null;

        public function __construct() {
                if ( $connector = mysql_connect( $this->mysqlConnectData['host'] , $this->mysqlConnectData['user'] , $this->mysqlConnectData['password'] ) ) {
                        if ( mysql_query( "USE ".$this->mysqlConnectData['db'] , $connector ) ) {
                                mysql_query( "SET NAMES utf8" , $connector );
                                $this->mysqlConnector = $connector;
                        }
                }
        }

        public function retrieveDbData( $searchTerm ) {
                $recommenderURL = $this->recommenderURL.$searchTerm;
                $dbrHandle = curl_init();
                curl_setopt( $dbrHandle , CURLOPT_URL , $recommenderURL );
                curl_setopt( $dbrHandle , CURLOPT_RETURNTRANSFER , true );
                curl_setopt( $dbrHandle , CURLOPT_CONNECTTIMEOUT , $this->timeout );
                curl_setopt( $dbrHandle , CURLOPT_TIMEOUT_MS , 1000 * $this->timeout );
                $reply = curl_exec( $dbrHandle );
                curl_close( $dbrHandle );

                $xml_parser = xml_parser_create();
                xml_parse_into_struct ( $xml_parser , $reply , &$parseValues , &$parseIndex );

                $dbData = array();
                foreach ( $parseIndex['CLUSTER'] as $index ) {
                        $dbData[$parseValues[$index]['value']] = array( 'freq' => $parseValues[$index]['attributes']['FREQ'] , 'rank' => $parseValues[$index]['attributes']['RANK'] );
                }
                $this->dbData = $dbData;
        }

        public function getFromDbis() {
                $databases = array();
                $done = array();
                foreach ( $this->dbData as $id => $data ) {
                    $counter = 0;
                    $query = "
                          SELECT * from dbr_database
                          JOIN dbr_database_dbis ON dbr_database.dbr_database=dbr_db_id
                          JOIN dbr_dbis ON dbr_database_dbis.dbis_id = dbr_dbis.dbis_id
                          JOIN dbr_id ON dbr_id.dbr_id=dbr_dbis.dbr_id
                          WHERE dbr_id.id='".$id."'
                          ORDER BY dbr_dbis.dbis_id, ranking, dbr_database.bezeichnung ASC
                    ";
                    $dbisresult = mysql_query( $query , $this->mysqlConnector );
                    while ( $row = mysql_fetch_assoc( $dbisresult ) ) {
                        if (!in_array($row['dbr_database'], $done) && $counter < 3) {
                            $done[] = $row['dbr_database'];
                            $databases[] = array( 'name' => $row['bezeichnung'] , 'id' => $id , 'url' => $row['url'] , 'rank' => $data['rank'], 'group' => $row['name'] );
                            $counter++;
                        }
                    }
                }
                mysql_free_result( $dbisresult );
                $this->databases = array_map( 'unserialize' , array_unique( array_map( 'serialize' , $databases ) ) );

        }

        public function selectDatabases() {
                $databases = array();
                $done = array();
                foreach ( $this->dbData as $id => $data ) {
                        $query = "SELECT dbr_id.name, dbr_database.bezeichnung , dbr_database.url FROM dbr_database , dbr_database_id , dbr_id WHERE dbr_database.dbr_database = dbr_database_id.dbr_database 
                                  AND dbr_database_id.dbr_id = dbr_id.dbr_id AND dbr_id.id = '".$id."'
                                  ORDER BY dbr_database.bezeichnung ASC";
                        $counter = 0;
                        if ( $result = mysql_query( $query , $this->mysqlConnector ) ) {
                                while ( $row = mysql_fetch_assoc( $result ) ) {
                                        if (!in_array($row['bezeichnung'], $done) && $counter < 3) {
                                            $done[] = $row['bezeichnung'];
                                            $databases[] = array( 'name' => $row['bezeichnung'] , 'id' => $id , 'url' => $row['url'] , 'rank' => $data['rank'], 'group' => $row['name'] );
                                            $counter++;
                                        }
                                }
                                mysql_free_result( $result );
                        }
                }
                $this->databases = array_map( 'unserialize' , array_unique( array_map( 'serialize' , $databases ) ) );
        }

        public function getDatabases() {
                return $this->databases;
        }

}

?>