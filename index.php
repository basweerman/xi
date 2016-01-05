<?php
/* 
------------------------------------------------------------------------
Copyright (C) 2015 Albert Weerman
This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.
This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
------------------------------------------------------------------------
*/

/* TODO
 * V 1) allow empty one time
 * V 2) .e on skip
 * V 3) encrypt data records
 * V 4) stata export
 * V 5) rules in groups
 * V 6) save groups, questions, rules, types  including 'add type/group etc' arrays!!
 * 7) Clean data on last question
 * 8) start survey in different language (language change button?)
 * 9) meta data export
 * V 10) templates: multiple languages
 * 11) no back button on first question
 * V 12) mysql support
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();

if (isset($_GET['clear']) && $_GET['clear'] == 1){
  session_unset();
  session_destroy();
  session_start();
}

//PRIMKEY CHECK!
if (isset($_SESSION["primkey"]) && isset($_GET['primkey']) && $_GET['primkey'] != '' ){
  if ($_SESSION["primkey"] != $_GET['primkey']) { //session primkey doesn't match input!
    session_unset();
    session_destroy();
    session_start();  //new session with $_GET primkey
  }
}

if (!isset($_SESSION["primkey"])){
  $_SESSION['primkey'] = (isset($_GET['primkey']) && $_GET['primkey'] != '') ? $_GET['primkey'] : uniqid();
}
$_SESSION['language'] = (isset($_GET['language'])) ?$_GET['language'] : 1;



$_SESSION['syid'] = 1;

require_once('globals.php');
//set metadata path
echo '&nbsp;';
set_include_path($xi['surveys']['metadata']['path']);
require_once($xi['surveys']['metadata']['surveyslocation']);    
//get survey object
$survey = $surveys[$_SESSION['syid']]; // new Survey(); 

echo $survey->showMainTemplate();

?>

