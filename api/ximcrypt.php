<?php

/* 
------------------------------------------------------------------------
Copyright (C) 2015 Albert Weerman
This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.
This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
------------------------------------------------------------------------
*/

class MCrypt {
  private $iv = 'fei38fjwf24f8sf2'; #Same as in JAVA
  private $key = 'f39fjamvcnvq01md'; #Same as in JAVA


  function __construct()
  {
  }

  function encrypt($str) {

    //$key = $this->hex2bin($key);    
    $iv = $this->iv;

    $td = mcrypt_module_open('rijndael-128', '', 'cbc', $iv);

    mcrypt_generic_init($td, $this->key, $iv);
    $encrypted = mcrypt_generic($td, $str);

    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);

    return bin2hex($encrypted);
  }

  function decrypt($code) {
    //$key = $this->hex2bin($key);
    $code = $this->hex2bin($code);
    $iv = $this->iv;

    $td = mcrypt_module_open('rijndael-128', '', 'cbc', $iv);

    mcrypt_generic_init($td, $this->key, $iv);
    $decrypted = mdecrypt_generic($td, $code);

    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);

    return utf8_encode(trim($decrypted));
  }

  protected function hex2bin($hexdata) {
    $bindata = '';

    for ($i = 0; $i < strlen($hexdata); $i += 2) {
          $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
    }

    return $bindata;
  }

}
