<?php
/* 
------------------------------------------------------------------------
Copyright (C) 2015 Albert Weerman
This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.
This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
------------------------------------------------------------------------
*/

class Database {
  
  var $db = null;
  var $dataCollection = null;
  var $logCollection = null;
  var $record = null;
  var $dbType = DATABASE_MONGODB;
  
  var $dataTable;
  var $logTable;

  function Database($dbtype, $hostname, $username, $password, $database, $datatable, $logtable){
    if ($this->db == null) {
        $this->dbType = $dbtype;
        $this->dataTable = $datatable;
        $this->logTable = $logtable;
        
        if ($this->dbType == DATABASE_MONGODB){
            $m = new MongoClient();
            $this->db = $m->selectDB($database); 
            $this->dataCollection = $this->db->selectCollection($this->dataTable );
            $this->logCollection = $this->db->selectCollection($this->logTable);
        }
        else { //mysql
            //$this->db = @mysqli_connect($hostname, $username, $password, $database);
            $this->db = new mysqli($hostname, $username, $password /*, $database*/);
            if (!mysqli_select_db ($this->db, $database)){
            //if (mysqli_connect_errno() /*$this->db == null*/){ //db doesn't exist: create
                $this->db = new mysqli($hostname, $username, $password);
                //$this->db = @mysqli_connect($hostname, $username, $password);

                @mysqli_query($this->db, 'CREATE DATABASE IF NOT EXISTS ' . $database . ' COLLATE=utf8_unicode_ci');
                @mysqli_select_db($this->db, $database);
                @mysqli_query($this->db, 'SET CHARACTER SET utf-8;');
                @mysqli_query($this->db, 'SET collation_connection = \'utf8_general_ci\';');
               
                //create tables!
                $dataQuery = '
                CREATE TABLE IF NOT EXISTS `' . $this->dataTable  . '` (
                    `primkey` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
                    `record` blob NOT NULL,
                    `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
                @mysqli_query($this->db, $dataQuery);
                $dataQuery = 'ALTER TABLE `data` ADD PRIMARY KEY (`primkey`);';
                @mysqli_query($this->db, $dataQuery);
                $logQuery = '
                CREATE TABLE IF NOT EXISTS `' . $this->logTable . '` (
                    `primkey` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
                    `record` blob NOT NULL,
                    `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
                @mysqli_query($this->db, $logQuery);
            }
            else {
                @mysqli_query($this->db, 'SET CHARACTER SET utf-8;');
                @mysqli_query($this->db, 'SET collation_connection = \'utf8_general_ci\';');
            }
            
        }
    }        

  }

  function findRecord($primkey){
    if ($this->record == null){
        if ($this->dbType == DATABASE_MONGODB){
            $cursor = $this->dataCollection->find(array('primkey'=> $_SESSION["primkey"]));
            if (sizeof($cursor) > 0){
              $this->record = null;
              foreach ( $cursor as $this->record ){
              }
              if ($this->record != null){ //this is a restart, load data from mongo
                    array_shift($this->record);
                    global $survey;
                    if ($survey->getEncryptionKey() != null){
                      return decryptObj($this->record, $survey->getEncryptionKey());
                    }
                    return $this->record;
              }
            }
        }
        else { //mysql
            $result = @mysqli_query($this->db, 'SELECT * FROM `' . $this->dataTable  . '` WHERE primkey = "' . $_SESSION["primkey"] . '"');
            //echo 'hererer!';
            if ($result != null){
                $row = @mysqli_fetch_array($result, MYSQLI_BOTH);
                if ($row['record'] == ''){
                    $this->record = array();
                }
                else {
                    $this->record = json_decode($row['record'], true);
                }
            }
            else {
                $this->record = array();
            }
        }
    }
    return $this->record;
  }

    function updateRecord($primkey, $data){
        global $survey;
        if ($survey->getEncryptionKey() != null){
            $data = encryptObj($data);
        }
        if ($this->dbType == DATABASE_MONGODB){
            $this->dataCollection->update(array('primkey'=> $primkey), array('$set' => $data), array('upsert' => true));
        }
        else { //mysql
            if ($this->record == null){
                $this->record = array();
            }
            if ($stmt = $this->db->prepare("REPLACE INTO " . $this->dataTable . " (primkey, record) VALUES (?, ?)")){
                $this->record = array_replace($this->record, $data);
                $record = json_encode($this->record);
                $stmt->bind_param("ss", $primkey, $record);
                if(!$stmt->execute()){
                    //error
                }
            }
        }
    }
 
    function insertLogRecord($primkey, $variablename, $value){
        global $survey;
        if ($survey->getEncryptionKey() != null){
            $value = encryptObj($value);
        }
        $logArray = array('primkey'=> $primkey, 'syid' => $_SESSION['syid'], $variablename => $value, 'language' => $_SESSION['language'], 'ts' => date('Y-m-d H:i:s'));
        if ($this->dbType == DATABASE_MONGODB){
            $this->logCollection->insert($logArray);
        }
        else {  //mysql
            if ($stmt = $this->db->prepare("REPLACE INTO " . $this->logTable . " (primkey, record) VALUES (?, ?)")){
                $record = json_encode($logArray);
                $stmt->bind_param("ss", $primkey, $record);
                if(!$stmt->execute()){
                        //error
                }
            }

        }
    }

    function truncate(){
        if ($this->dbType == DATABASE_MONGODB){
            $this->dataCollection->drop();  
            $this->logCollection->drop();  
        }
        else { //mysql
            $dataQuery = 'TRUNCATE `' . $this->dataTable  . '`';
            @mysqli_query($this->db, $dataQuery);
            $logQuery = 'TRUNCATE `' . $this->logTable . '`';
            @mysqli_query($this->db, $logQuery);
        }
    }

    function count($condition = array()){
        if ($this->dbType == DATABASE_MONGODB){
            return $this->dataCollection->count($condition); //array('x'=>1)));
        }
        else { //mysql
            $dataQuery = 'SELECT * FROM `' . $this->dataTable  . '`';
            $result = @mysqli_query($this->db, $dataQuery);
            return @mysqli_num_rows($result);
        }
    }

  function getCollection(){
      return $this->dataCollection;
  }
  
  function viewContent(){
	//VIEW MONGO CONTENT
	$cursor = $this->dataCollection->find();
	foreach ($cursor as $doc) {
          print_r($doc);
   	  echo '<br/>';
	}

  }

    function getContent(){
        if ($this->dbType == DATABASE_MONGODB){
            $collection = $this->db->getCollection();
            return $cursor = $collection->find();
        }
        else {
            return @mysqli_query($this->db, 'SELECT * FROM `' . $this->dataTable  . '`');
        }
    }
    
    
    function toArray($doc){
        if ($this->dbType == DATABASE_MONGODB){
            //already array
            return $doc;
        }
        else {
            return json_decode($doc['record'], true);
        }
    }
    
    function getAllTablesInDb(){
        $tables = array();
        if ($this->dbType == DATABASE_MONGODB){
        
            
        }   
        else {
            $query = 'show tables';
            $result = @mysqli_query($this->db, $query);
            while ($row = @mysqli_fetch_array($result, MYSQLI_BOTH)){
		$tables[] = $row[0];
            }
        }
        return $tables;
    }
    
    
}

?>
