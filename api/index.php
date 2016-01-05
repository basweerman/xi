<?php

/* 
------------------------------------------------------------------------
Copyright (C) 2015 Albert Weerman
This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.
This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
------------------------------------------------------------------------
*/

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once('apiconstants.php');

$output = json_encode(array('error' => API_RESULT_NO_COMMAND));
if ($_GET['q']){
    
    require_once('../globals.php');
    require_once('ximcrypt.php');
    
    $hostname = 'localhost'; $username = 'root'; $password = '{password here}'; $database = 'xi';
    $db = new mysqli($hostname, $username, $password /*, $database*/);
    @mysqli_select_db($db, $database);

    
  //  echo $_GET['q'];
  //  echo '<hr>';

    
    //echo base64_decode($_GET['q']);
    //$q = json_decode(base64_decode($_GET['q']), true);
    
    
    $mcrypt = new MCrypt();
    $q = json_decode($mcrypt->decrypt($_GET['q']), true);

//    print_r($q);
    //echo $q['api'];
    switch ($q['API']){
        case 'xiIsRemoteServerUp': $output = (isRemoteServerUp()) ? json_encode(array('RESULT' => API_RESULT_REMOTE_SERVER_UP)) : json_encode(array('RESULT' => API_RESULT_REMOTE_SERVER_DOWN)); break;
        case 'xiIsLocalServerUp': $output = (isLocalServerUp()) ? json_encode(array('RESULT' => API_RESULT_LOCAL_SERVER_UP)) : json_encode(array('RESULT' => API_RESULT_LOCAL_SERVER_DOWN)); break;
        case 'xiRunLocalQuery': $output = (runLocalQuery($q)) ? json_encode(array('RESULT' => API_RESULT_LOCAL_QUERY_SUCCESS)) : json_encode(array('RESULT' => API_RESULT_LOCAL_QUERY_FAILED)); break;
        case 'xiUploadToRemoteServer': echo uploadToRemoteServer($q); exit;
        case 'xiCreateTablesIfNotExistsLocal': $output = (createTablesIfNotExistsLocal($q)) ? json_encode(array('RESULT' => API_RESULT_LOCAL_QUERY_SUCCESS)) : json_encode(array('RESULT' => API_RESULT_LOCAL_QUERY_FAILED)); break;
        case 'xiUploadFileLocal': $output = (uploadFileToLocalServer($q)) ? json_encode(array('RESULT' => API_RESULT_LOCAL_QUERY_SUCCESS)) : json_encode(array('RESULT' => API_RESULT_LOCAL_QUERY_FAILED)); break;

    }
    
    
    
}
returnOutput($output);
    

function returnOutput($output){
  $mcrypt = new MCrypt();
  $encrypted = $mcrypt->encrypt($output);
  echo base64_encode(gzcompress($encrypted, 9));
}


function isRemoteServerUp(){
    error_reporting (0);
    //http://forums.winamp.com/showthread.php?t=166910
    $servername = substr(XI_REMOTE_SERVER, 0, strpos(XI_REMOTE_SERVER, '/'));
    $fp = fsockopen($servername, '80', $errno, $errstr, 2);
    if ($fp) { //up
      fclose($fp);
      return true;
    }
    return false;        
}     

function isLocalServerUp(){
    error_reporting (0);
    //http://forums.winamp.com/showthread.php?t=166910
    $servername = 'localhost:8080/index.php'; //this should look at mysql
    $fp = fsockopen($servername, '80', $errno, $errstr, 2);
    if ($fp) { //up
      fclose($fp);
      return true;
    }
    return false;        
}     

function runLocalQuery($parameters){
    try {
        global $db;
        @mysqli_query($db, 'SET CHARACTER SET utf-8;');
        @mysqli_query($db, 'SET collation_connection = \'utf8_general_ci\';');
        @mysqli_query($db, $parameters['QUERY']);
        return true;
    } catch (Exception $e) {
        return false;
    }
    
}

function uploadFileToLocalServer($parameters){
    global $output;
    try {
		if (isset($_FILES["uploadedfile"])){
			$uploads_dir = '/tmp';
		    $output .= "filefound!";
			if ($_FILES["uploadedfile"]["error"] == 0) {
			    $tmp_name = $_FILES["uploadedfile"]["tmp_name"];
			    $name = $_FILES["uploadedfile"]["name"];
			    $image =  file_get_contents($_FILES['uploadedfile']['tmp_name']); 

		        global $db;
		        @mysqli_query($db, 'SET CHARACTER SET utf-8;');
		        @mysqli_query($db, 'SET collation_connection = \'utf8_general_ci\';');

	            $query = 'INSERT INTO appdata (primkey, record, filecontent) values ("' . $parameters['ID'] . '", "' . addslashes($parameters['RECORD']) . '", "' . addslashes($image) . '")';
		        @mysqli_query($db, $query);

            }
		}

        return true;
    } catch (Exception $e) {
        return false;
    }
}


function createTablesIfNotExistsLocal($parameters){
    global $db;
    $tables = $parameters['TABLES'];
    if (!is_array($tables)){
        $tables = json_decode($tables, true);
    }
    foreach ($tables as $table){
		if ($table != 'appdata'){
		    $tableQuery = '
		    CREATE TABLE IF NOT EXISTS `' . $table . '` (
		        `primkey` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
		        `record` blob NOT NULL,
		        `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
		    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
		}
        else { // files table
		    $tableQuery = '
		    CREATE TABLE IF NOT EXISTS `' . $table . '` (
		        `primkey` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
		        `record` blob NOT NULL,
		        `filecontent` blob NULL,
		        `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
		    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
        }
	    @mysqli_query($db, $tableQuery);
    }
}

function uploadToRemoteServer($parameters){
    $tables = $parameters['TABLES'];
    $lastupdated = $parameters['LASTUPDATE'];
    if ($lastupdated == null){
      $lastupdated = '';
    }

    if (!is_array($tables)){
        $tables = json_decode($tables, true);
      //  print_r($tables);
    }
    $data = exportTables($tables, $lastupdated);

    return sendToServer($data, $parameters['ID']); //success sending data to server
/*//echo '----!' . $resultCode . '!---';
    if ($resultCode == 'ok'){
    //if ($resultCode == COMM_NO_ERRORS){
        //update lastdate!
        //$this->user->setLastData(date('Y-m-d H:i:s'));
        //$this->user->saveChanges();
        return true;
    }
    else {
        return false;
    }*/
}

function exportTables($tables, $ts = '', $extraCondition = ''){
    global $db;
    $return = '';
    $wherets = ' WHERE 1 = 1';
    if (trim($ts) != ''){
        $wherets .= ' AND ts > "' . $ts . '"';
    }
    if (trim($extraCondition) != ''){
        $wherets .= ' AND ' . $extraCondition;
    }
    foreach($tables as $table) {
        //$result = $db->selectQuery('select * from ' . Config::dbSurvey() . '_' . $table . $wherets);
//echo 'select * from ' . $table . $wherets;
		$result = @mysqli_query($db, 'select * from ' . $table . $wherets);
        if ($result->num_rows > 0){
        //if ($db->getNumberOfRows($result) > 0 ){
          $finfo = $result->fetch_fields();
          $fieldnames = array();
          foreach ($finfo as $val) {
            $fieldnames[] = $val->name;
          }
          $num_fields = sizeof($finfo);

          $return.= 'REPLACE INTO ' . $table.' (' . implode(',', $fieldnames) . ') VALUES ';
          $first = true;
          while($row = mysqli_fetch_array($result)) {  //$db->getRow($result
            if (!$first){
              $return.= ', ';
            }
            $return.= ' ( ';
            for($j = 0; $j < $num_fields; $j++) {
              $rowUp = addslashes($row[$j]);
              $rowUp = preg_replace("/\n/","\\n",$rowUp);
              if (isset($rowUp)) { $return.= '"'.$rowUp.'"' ; } else { $return.= '""'; }
              if ($j<($num_fields-1)) { $return.= ','; }
            }
            $return.= ")";
            $first = false;
          }

          $return .= ";\n";
      }

    }
    return $return;

}

function sendToServer($str, $id){
    $postUrl = XI_REMOTE_SERVER;    
    //$str = urlencode($this->encryptAndCompress($str));
//    $str = encryptAndCompress($str);
//    $data['q'] = encryptAndCompress(json_encode(array('api' => 'upload', 'id' => $id, 'query' => $str)));
//    echo 'length before json' . strlen($str) . '---';
    $mcrypt = new MCrypt();
    $strToEncrypt = json_encode(array('API' => 'upload', 'ID' => $id, 'QUERY' => base64_encode($str)));
//    echo 'length after json:' . strlen($strToEncrypt);
    $data['q'] = $mcrypt->encrypt($strToEncrypt);

    $result = curlToServer($data, $postUrl);
    return trim($result);
}


function encryptAndCompress($str){
   global $db;
   $query = 'SELECT COMPRESS(AES_ENCRYPT("' . addslashes($str) . '","basbas"))';
   $result = $result = @mysqli_query($db, $query);
   $row = mysqli_fetch_array($result);
   return bin2hex($row[0]);
 }

 function decryptAndUncompress($str){
   global $db;
   $query = 'SELECT AES_DECRYPT(UNCOMPRESS("' . addslashes(hex2bin($str)) . '"),"basbas")';
   $result = @mysqli_query($db, $query);
   $row = mysqli_fetch_array($result);
   return $row[0];
 }

 function curlToServer($fields, $url){
   $ch = curl_init();
//   print_r($fields);
   curl_setopt($ch, CURLOPT_URL,$url);
   curl_setopt($ch, CURLOPT_POST,1);
   curl_setopt($ch, CURLOPT_POSTFIELDS,$fields);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   $res = curl_exec($ch);
//   echo '----!' . $res . '!----';
   return $res;
 }
       

?>
