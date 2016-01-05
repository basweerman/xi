<?php
/* 
------------------------------------------------------------------------
Copyright (C) 2015 Albert Weerman
This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.
This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
------------------------------------------------------------------------
*/

class Template {
    
    var $name;
    var $location;
    var $content;
    var $type;
    
    function Template($name, $type, $location, $content = null){
        $this->setName($name);
        $this->setLocation($location);
        $this->content = $content;
        $this->setType($type);
    }
    
    function getName(){
        return $this->name;
    }

    function setName($name){
        $this->name = $name;
    }
    
    function getType(){
        return $this->type;
    }

    function setType($type){
        $this->type = $type;
    }
    
    
    function getLocation(){
        return $this->location;
    }

    function setLocation($location){
        $this->location = $location;
    }
    
    function getContent(){
        if (is_array($this->content)){
            if (isset($this->content[$_SESSION['language']]) && $this->content[$_SESSION['language']] != ''){ //only if there is content
                return $this->content[$_SESSION['language']];
            }
            else { //otherwise return the default language
                global $survey;
                return $this->content[$survey->getDefaultLanguage()];
            }
        }
        else {
          return $this->content;
        }
            
    }

    function setContent($content){
        $this->content[$_SESSION['language']] = $content;
    }
    
    
    function getTemplateContent(){
        if ($this->content == null){
            global $xi;
            require_once($xi['surveys']['metadata']['path'] . 'templates/' . $this->getLocation() . '.php');
            $this->content = $template;
        } 
        return $this->getContent();
    }
  
    function write(){
        $write = '$' . $this->getName() . ' = new Template(';
        $write .= '\'' . $this->getName() . '\', ';
        $write .= array_search($this->getType(), returnConstants("TEMPLATE_")) . ', ';
        $write .= '\'' . addcslashes($this->getLocation(), '\'') . '\', ';
        $write .= arrayToString($this->content);
//        $write .= arrayToString($this->getContent());
//        $write .= '\'' . addcslashes($this->getContent(), '\'') . '\'';
        $write .= ');';
        return $write;        
    }

    function read($description){
//        $this->description = $description;
    }
    
    
}


?>