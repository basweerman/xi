<?php

/* 
------------------------------------------------------------------------
Copyright (C) 2015 Albert Weerman
This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.
This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
------------------------------------------------------------------------
*/

class QuestionObject {
    
  var $name;
  var $showBackButton = true;
  var $showNextButton = true;
  var $template = null;
  var $array = array();
 
  
  function getName(){
    return $this->name;
  }

  function setName($name){
    $this->name = $name;
  }

  function ask(){
    if (isset($_SESSION['clean'])){
        global $cleanQuestions;
//        echo '====' . $this->getName() . '====<br/>';
        $cleanQuestions[] = $this->getName();
    }
    else if (!isset($_SESSION['admin'])){
        if ($_SESSION['currentRouting'] == $this->getName()){ //we need the next!!
            if ($_SESSION['buttonClicked'] == 1){ //back clicked  this never happen for clean
                throw new Exception($_SESSION['parsedQuestion']);  //throw the previous question as exception
            }
            else { //next clicked:  Next .ask should be on the route
                $_SESSION['nextQuestion'] = 1;
            }
        }
        else if ($_SESSION['nextQuestion'] == 1){  //this is the next question: return by throwing an exception
            throw new Exception($this->getName());
        }
        $_SESSION['parsedQuestion'] = $this->getName();
    }
  }
  
  function setShowBackButton($visible){
    $this->showBackButton = $visible;
  }

  function showBackButton(){
    return $this->showBackButton;
  }

  
 function setShowNextButton($visible){
    $this->showNextButton = $visible;
  }

  function showNextButton(){
    return $this->showNextButton;
  }

  function getTemplate(){
    return $this->template;
  }

  function settemplate(Template $template){
    $this->template = $template;
  }
  
    function getArray(){
        return $this->array;
    }
    
    function setArray($array){
        $this->array = $array;
    }
    
    function isArray(){
        return sizeof($this->getArray()) > 0;
    }
  
  
}

?>