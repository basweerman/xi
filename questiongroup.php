<?php
/* 
------------------------------------------------------------------------
Copyright (C) 2015 Albert Weerman
This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.
This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
------------------------------------------------------------------------
*/

class QuestionGroup extends QuestionObject {

 // var $rules;
  var $questions = array();
  
  function QuestionGroup($name, $questions, Template $template = null){
    $this->setName($name);
    $this->setQuestions($questions);
    if ($template == null){
        $template = $TStandardTemplate;
    }
    $this->setTemplate($template);
  }
  
  function getRules(){
      return $this->rules;
  }
  
    function setQuestions($questions){
        $this->questions = $questions;
    }
  
    function getQuestions(){
     /*   if ($this->questions == null){

            global $cleanQuestions;
            $_SESSION['clean'] = 1; //set clean session so ->ask knows to just store the questions

            //get all basic variablenames and set the as global;
            $globalsStr = getVariablesAsGlobal($this->getRules());
            
            eval($globalsStr . $this->getRules());
            
            unset($_SESSION['clean']);
            
            foreach($cleanQuestions as $questionname){
                $this->questions[$questionname] = $$questionname;
            }
        }*/
        return $this->questions;
    }
    
 //   function setRules($rules){
//        $this->rules = $rules;
//    }
  
    function getQuestionNames(){
        $output = array();
        foreach($this->getQuestions() as $question){
            $output[] = $question->getName();
        }
        return $output;
    }
    
  function showQuestion(){
    $groupTemplate = $this->getTemplate()->getTemplateContent();
    //needs to have 'repeat'!!!   
    $textwithoutrepeats = getTextBetweenTags($groupTemplate, 'repeat'); //expecting repeat!!
    $showQuestion = $textwithoutrepeats[1];
    foreach($this->getQuestions() as $question){
        $patterns = array('/<question \/>/');
        $replacements = array($question->showQuestion());
        $showQuestion .= preg_replace($patterns, $replacements, $textwithoutrepeats[2]);
    }
    $showQuestion .= $textwithoutrepeats[3];
    return $showQuestion;
  }
    
  function validate(){
    foreach($this->getQuestions() as $question){
       $code = $question->validate($this);
       if ($code > 0){
           return $code;
       }
    }
    return 0;
  }

  
    function write(){
        global $survey;
        $write = '$' . $this->getName() . ' = new QuestionGroup(';
        $write .= '\'' . $this->getName() . '\', ';
//        $write .= '\'' . addcslashes($this->getRules(), '\'') . '\', ';        
        $write .= 'array( $' . implode(', $', $this->getQuestionNames()) . '), ';
        $write .= '$' . $this->getTemplate()->getName();
        $write .= ');' . "\n";
        
        return $write;           
    }
  
}

?>