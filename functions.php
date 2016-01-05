<?php
/* 
------------------------------------------------------------------------
Copyright (C) 2015 Albert Weerman
This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.
This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
------------------------------------------------------------------------
*/

function loadFromPostRemoveBrackets($varname){
  return preg_replace('/\[([1-9][0-9]*)\]/', "<$1>", $varname);     
}

function loadFromPostAddBrackets($varname){
  return preg_replace('/\<([1-9][0-9]*)\>/', "[$1]", $varname);     
}

function getTextWithoutBrackets($varname){
    if (stripos($varname, '[') !== false){
        return substr($varname, 0, stripos($varname, '['));
    }
    return $varname;
}

function getTextBetweenTags($string, $tagname) {
    $pattern = '/(.*)<' . $tagname . '>(.*)<\/' . $tagname . '>(.*)/si';
    preg_match($pattern, $string, $matches);
    //if no match: build array
    if (sizeof($matches) == 0){
        $matches = array();
        $matches[1] = '';
        $matches[2] = $string;
        $matches[3] = '';
    }
    return $matches;
 }

//http://stackoverflow.com/questions/2226103/how-to-cast-objects-in-php
 /**
 * Class casting
 *
 * @param string|object $destination
 * @param object $sourceObject
 * @return object
 */
function cast($destination, $sourceObject)
{
    if (is_string($destination)) {
        $destination = new $destination();
    }
    $sourceReflection = new ReflectionObject($sourceObject);
    $destinationReflection = new ReflectionObject($destination);
    $sourceProperties = $sourceReflection->getProperties();
    foreach ($sourceProperties as $sourceProperty) {
        $sourceProperty->setAccessible(true);
        $name = $sourceProperty->getName();
        $value = $sourceProperty->getValue($sourceObject);
        if ($destinationReflection->hasProperty($name)) {
            $propDest = $destinationReflection->getProperty($name);
            $propDest->setAccessible(true);
            $propDest->setValue($destination,$value);
        } else {
            $destination->$name = $value;
        }
    }
    return $destination;
}

//http://php.net/manual/en/function.get-defined-constants.php
function returnConstants ($prefix) {
    foreach (get_defined_constants() as $key=>$value) 
        if (substr($key,0,strlen($prefix))==$prefix)  $dump[$key] = $value; 
    if(empty($dump)) { return "Error: No Constants found with prefix '".$prefix."'"; }
    else { return $dump; }
} 

//http://stackoverflow.com/questions/12864582/javascript-prompt-cancel-button-to-terminate-the-function
function confirmAction($message, $code) {
    $returnStr = ' onclick="';
    $returnStr .= ' input=prompt(\'' . $message . '\', \'\');';
    $returnStr .= ' if (input == \'' . $code . '\') return true; else return false;"';
    return $returnStr;
}

//http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}
function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

function showFill(Question $question){
    $value = $question->value();
    if (is_array($value)){
        return implode(', ', array_keys($value));
    }
    if ($question->isRefusal()){
        return 'RF';
    }
    if ($question->isDontKnow()){
        return 'DK';        
    }
    return $value;
    
}

function expandArray($startstr, $array, &$resarray){
    if (sizeof($array) == 1){
        $current = $array[0];
        $start = $current[0];
        $end = $current[1];
        for($i = $start; $i <= $end; $i++){
          $resarray[] = $startstr . '[' . $i . ']';
        }
    }
    else {
        $current = array_shift($array);
        $start = $current[0];
        $end = $current[1];
        for($i = $start; $i <= $end; $i++){
          expandArray('[' . $i . ']', $array, $resarray);
        }
    }
}

function getArray(&$obj, $array){
    if (is_array($obj)){
        $array[] = array( key($obj), (key($obj) + sizeof($obj) - 1));
        $obj = $obj[key($obj)];
        return getArray($obj, $array);
    }
    return $array;
}

function arrayToString($array){
    return preg_replace('/\s+/', ' ', preg_replace('/(?!\B"[^[]]]*),\n.*\)(?![^]]*"\B)/', ')', var_export($array, TRUE)));
}

function prependPath($location){
    global $xi;
    return $xi['surveys']['metadata']['path'] . $location;
}

function replaceFillsToAdmin($string){
    //   \'.\s*(.*?)\s*\./si'    ===>     '.      .'
    //from ' .   . ' to ^   ^
    preg_match_all('/\'.\s*(.*?)\s*\./si', $string, $matches);
    print_r($matches);
    if (isset($matches[1])){
        global $survey;
        foreach($matches[1] as $match){
//            echo '<br/>' . $match . '<br/>';
            
            
//          $string = preg_replace('/' . $match . '/', '^' . $match . '^', $string);
        }
    }
    return $string;
}

function getVariablesAsGlobal($string){    
    $globalsStr = '';
    preg_match_all('/\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/si', $string, $matches);
    if (isset($matches[0])){
      $matches = $matches[0];
      $globalsStr = 'global ' . implode(', ', $matches) . ';';
    }
    return $globalsStr;
}


function replaceFills($string){
    //get all basic variablenames and set the as global;
    $globalsStr = getVariablesAsGlobal($string);

    //get fills
    preg_match_all('/\^\s*(.*?)\s*\^/si', $string, $matches);
    if (isset($matches[1])){
        
        if (class_exists('Runkit_Sandbox')){  //save eval!
          $options = array(
            'safe_mode'=>true,
            'open_basedir'=>'/var/www/users/jdoe/',
            'allow_url_fopen'=>'false',
            'disable_functions'=>'exec,shell_exec,passthru,system',
            'disable_classes'=>'myAppClass');
          $sandbox = new Runkit_Sandbox($options);
          $sandbox->ini_set('html_errors',true);       
        }
        global $survey;
        foreach($matches[1] as $match){
            $value = (isset($sandbox)) ? $sandbox->eval($globalsStr . 'return ' . $match . ';') : eval($globalsStr . 'return ' . $match . ';');
            $string = str_replace('^' . $match . '^', $value, $string);
        }
    }
    return $string;
}


//http://stackoverflow.com/questions/606179/what-encryption-algorithm-is-best-for-encrypting-cookies
function encryptObj($obj){
   if (is_array($obj)){
       return array_map("encryptObj", $obj);
   }
   global $survey;
   return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $survey->getEncryptionKey(), $obj, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)))); 
   //return base64_encode($obj);
}

function decryptObj($obj){
   if (is_array($obj)){
       return array_map("decryptObj", $obj, $key);
   }
   global $survey;
   return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $survey->getEncryptionKey(), base64_decode($obj), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))); 
//   return base64_decode($obj);
}


/* regular expressions
 
      //  (\$[a-zA-Z0-9\[\]\-\>\(\)]+)     gets oen? ik $weet[23]->value() het niet
    //  (\$[a-zA-Z0-9\[\]]+)   gets  gets oen? ik $weet[23]
    //  \$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)   finds all the separate $cnt..
    // \$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)   with [xxx]
    preg_match_all('/\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/si', $string, $matches);

  
  
     // \$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)   with [xxx]
//    \^\s*(.*?)\s*\^      fills will be between ^ and 

  
  
 */



function mb_wordwrap($str, $width=75, $break="rn")
{
    // Return short or empty strings untouched
    if(empty($str) || mb_strlen($str, 'UTF-8') <= $width)
        return $str;
   
    $br_width  = mb_strlen($break, 'UTF-8');
    $str_width = mb_strlen($str, 'UTF-8');
    $return = '';
    $last_space = false;
    
    for($i=0, $count=0; $i < $str_width; $i++, $count++)
    {
        // If we're at a break
        if (mb_substr($str, $i, $br_width, 'UTF-8') == $break)
        {
            $count = 0;
            $return .= mb_substr($str, $i, $br_width, 'UTF-8');
            $i += $br_width - 1;
            continue;
        }

        // Keep a track of the most recent possible break point
        if(mb_substr($str, $i, 1, 'UTF-8') == " ")
        {
            $last_space = $i;
        }

        // It's time to wrap
        if ($count >= $width)
        {
            // There are no spaces to break on!  Going to truncate :(
            if(!$last_space)
            {
                $return .= $break;
                $count = 0;
            }
            else
            {
                // Work out how far back the last space was
                $drop = $i - $last_space;

                // Cutting zero chars results in an empty string, so don't do that
                if($drop > 0)
                {
                    $return = mb_substr($return, 0, -$drop);
                }
                
                // Add a break
                $return .= $break;

                // Update pointers
                $i = $last_space + ($br_width - 1);
                $last_space = false;
                $count = 0;
            }
        }

        // Add character from the input string to the output
        $return .= mb_substr($str, $i, 1, 'UTF-8');
    }
    return $return;
}

function tagwrap($html, $width = 75, $break = "nr") 
{
    $html = '<div>' . $html . '</div>';
    
    //using dom object
    $dom = new domDocument;

    //load the html into the object
    $dom->loadXML($html);

    // preserve white space
    $dom->preserveWhiteSpace = true;

    //getting all tags
    $content = $dom->getElementsByTagname("*");
    
    $html = "";
    foreach ($content as $item)
    {
        //wrapping contents of tags, function described above is used, 
        //but you can use your own function or simple wordwrap() php function
        $item->nodeValue = mb_wordwrap($item->nodeValue, $width, $break);
        $html .= $dom->saveXML($item);
    }
    //return the results 
    return $html; //html_entity_decode($html);
}


/* XI FUNCTIONS TO USE IN RULES
 
  
 */

function inSet($value, $array){ //array_key_exists replacement in case $array is not an array
    if (is_array($array)){
        return array_key_exists($value, $array);
    }
    else {
        return $value == $array;
    }
}

   
?>