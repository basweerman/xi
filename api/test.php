<?php

/* 
------------------------------------------------------------------------
Copyright (C) 2015 Albert Weerman
This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.
This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
------------------------------------------------------------------------
*/

require_once('ximcrypt.php');

$mcrypt = new MCrypt();

echo $mcrypt->encrypt(json_encode(array('api' => 'xiIsRemoteServerUp')));
echo '<hr>';


echo '<hr>';

//echo base64_encode(json_encode(array('api' => 'xiUploadToRemoteServer', 'tables' => array('data'), 'urid' => 1)));

echo $mcrypt->encrypt(json_encode(array('api' => 'xiUploadToRemoteServer', 'tables' => array('data'), 'urid' => 1)));

echo '<hr>';

echo json_encode(array('api' => 'xiUploadToRemoteServer', 'tables' => array('data', 'log'), 'urid' => 1));

?>