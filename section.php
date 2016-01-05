<?php

/* 
------------------------------------------------------------------------
Copyright (C) 2015 Albert Weerman
This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.
This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
------------------------------------------------------------------------
*/

class Section {

    var $name;
    var $description;
    var $questionLocation;
    var $rulesLocation;

    var $questions = array();
    var $questionGroups = array();
    
    function Section($name, $description, $questionLocation, $rulesLocation){
        $this->setName($name);
        $this->setDescription($description);
        $this->setQuestionLocation($questionLocation);
        $this->setRulesLocation($rulesLocation);
    }
    
    function setName($name){
        $this->name = $name;
    }
    
    function getName(){
        return $this->name;
    }

    function setDescription($description){
        $this->description = $description;
    }
    
    function getDescription(){
        return $this->description;
    }
    
    
    function setQuestionLocation($location){
        $this->questionLocation = $location;
    }
    
    function getQuestionLocation(){
        return $this->questionLocation;
    }
    
    function setRulesLocation($rules){
        $this->rulesLocation = $rules;
    }
    
    function getRulesLocation(){
        return $this->rulesLocation;
    }

    
    function getQuestions(){
        return $this->questions;
        //done in surveys now
/*        //load templates and types
        global $survey;
        require_once($survey->getTemplatesLocation());
        require_once($survey->getTypesLocation());       
        if ($this->questions == null){
            $this->questions = array();
            $this->questionGroups = array();
            require_once($this->getQuestionLocation());
            $arr = get_defined_vars(); //get the defined vars from php and loop
            foreach($arr as $obj){
                //print_r($obj);
//                echo get_class($obj);
//                echo '<hr>';
                $array = getArray($obj, array()); //get the array index if array
                if ($obj instanceof Question){
                   $obj->setArray($array);
                   $this->questions[getTextWithoutBrackets($obj->getName())] = $obj;
                }
                else if ($obj instanceof QuestionGroup){
                   $obj->setArray($array);
                   $this->questionGroups[getTextWithoutBrackets($obj->getName())] = $obj;
                }
                else if ($obj instanceof Type){
                   $survey->types[$obj->getName()] = $obj;
                }
                else if ($obj instanceof Template){
                   $survey->templates[$obj->getName()] = $obj;
                }
                
                
                
            }
        }
        return $this->questions;*/
    }    
    
    function addQuestion($question){
        $this->questions[$question->getName()] = $question;
    }

    function addQuestionGroup($questionGroup){
        $this->questionGroups[$questionGroup->getName()] = $questionGroup;
    }
    
    function getQuestionGroups(){
        return $this->questionGroups;
    }
    
    function getQuestionGroup($name){
        return $this->questionGroups[$name];
    }
    
    
    function getDataQuestions(){
        $questions = array();
        
        foreach($this->getQuestions() as $name => $question){
            if ($question->getQuestionType()->getAnswerType() == QUESTION_TYPE_SETOF){
                foreach($question->getQuestionType()->getEnumeratedOptions() as $key => $enum){
                    if ($question->isArray()){
                        $resarray = array();
                        expandArray('', $question->getArray(), $resarray);
                        foreach($resarray as $bracketName){
                          $questions[$name . $bracketName . '[' . $key . ']'] = $question;
                        }
                    }            
                    else {
                      $questions[$name . '[' . $key . ']'] = $question;
                    }
                }
                //array! []
            }
            else if ($question->isArray()){
                $resarray = array();
                expandArray('', $question->getArray(), $resarray);
                foreach($resarray as $bracketName){
                    $questions[$name . $bracketName] = $question;
                }
            }            
            else {
                $questions[$name] = $question;
            }
        }
        return $questions;
    }    
    
    
    function getQuestion($name){
        return $this->questions[$name];
    }

    function writeQuestionsAndGroups(){
        $output = '<?php' . "\n" . '//xi questions (admin generated)';
        foreach ($this->getQuestions() as $question){
            $output .= "\n" . $question->write();
        }
        $output .= "\n" . '//xi groups (admin generated)';
        foreach ($this->getQuestionGroups() as $question){
            $output .= "\n" . $question->write();
        }
        $output .= "\n" . '?>';
        $filename = prependPath($this->getQuestionLocation());
        if (is_writable($filename)){
            file_put_contents($filename, $output);
            return true;
        }
        return false;
    }
    
    
}


?>