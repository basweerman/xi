<?php
/* 
------------------------------------------------------------------------
Copyright (C) 2015 Albert Weerman
This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.
This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
------------------------------------------------------------------------
*/

class Question extends QuestionObject{

  var $questionText = array();
  var $description;
  var $questionType = null;

  

  
  function __construct($questionName, $questionDescription, $questionText, Type $questionType){
    $this->setName($questionName);
    $this->setDescription($questionDescription);
    $this->questionText = $questionText;
    $this->setQuestionType($questionType);    
  }

    function getTemplate(){ //overwrite
        if ($this->template == null){
            //default type:
            global $survey, $TQuestionTemplate;
            $this->template = (isset($TQuestionTemplate)) ? $TQuestionTemplate : $survey->getTemplate(QUESTION_STANDARD_TEMPLATE);            
        }
        return $this->template;
    }
  function getQuestionText($language = 1){
    if (isset($this->questionText[$language])){
      return $this->questionText[$language];
    }
    return null;
  }

  function getLine($line){
      $lines = explode("\n", $this->getQuestionText($_SESSION['language']));
      if (isset($lines[$line - 1])){
          return $lines[$line - 1];
      }
      return '';
      
  }
  
  
  function setQuestionText($questionText = '', $language = 1){
    $this->questionText[$language] = $questionText;
  }

  function getDescription(){
    return $this->description;
  }

  function setDescription($description){
    $this->description = $description;
  }

  function getQuestionType(){
    return $this->questionType;
  }

  function setQuestionType($questionType){
    $this->questionType = $questionType;
  }

  function isRefusal(){
      return $this->value() == '.r';
  }

  function isDontKnow(){
      return $this->value() == '.d';
  }
  
  function isEmpty(){
      return $this->value() == '.e';
  }
  
  function textboxValue(){ //value without .d / .r  / .e
      if ($this->isDontKnow() || $this->isRefusal() || $this->isEmpty()){
          return '';
      }
      return $this->value();
  }
  
  function val($setdata = null){
    return $this->value($setdata);  
  }
  
  function value($setdata = null){
    if ($setdata != null){
      $_SESSION['data'][$this->getName()] = $setdata;
      return $setdata;
    }
    elseif (isset($_SESSION['data'][$this->getName()])){
      return $_SESSION['data'][$this->getName()];
    }
    return null;
  }

  function showQuestion(){
    global $survey;
    $showQuestion = $this->getTemplate()->getTemplateContent();
    $patterns = array('/<errorMessage \/>/', '/<questionName \/>/', '/<questionText \/>/', '/<answerOption \/>/');
    $replacements = array($this->showError($this->validate()), loadFromPostRemoveBrackets($this->getName()), replaceFills($this->getQuestionText($_SESSION['language'])), $this->getQuestionType()->getAnswerOption($this));
    return preg_replace($patterns, $replacements, $showQuestion);
  }
  
  function validate(){
    return $this->getQuestionType()->validate($this);
  }

  function showError($code){
        switch($code){
          case 0: return '';
          case ERROR_INPUT_RANGE: return '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> Number outside range</div>';    
          case ERROR_NO_ANSWER: return '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> Please answer the question</div>';    

          default: return '';    
        }
    }
    
    function write(){
        global $survey;
        $write = '';
        if ($this->isArray()){
            $arrayStr = '';
            $arrayStrName = '';
            foreach($this->getArray() as $key=>$array){
                $write .= 'for ($i' . $key . ' = ' . $array[0] . '; $i' . $key . ' <= ' . $array[1] . '; $i' . $key . '++){' . "\n";
                $arrayStr .= '[$i' . $key . ']';
                $arrayStrName = '[\' . $i' . $key . ' . \']';
            }                 
            $write .= $this->writeSingleQuestion($this->getName() . $arrayStr, $this->getName() . $arrayStrName);
            foreach($this->getArray() as $key=>$array){
                $write .= '}' . "\n";
            }                 
        }
        else {
            $write .= $this->writeSingleQuestion($this->getName(), $this->getName());
            
        }
        return $write;
    }
    
    function writeSingleQuestion($name1, $name2){
        $write = '$' . $name1 . ' = new Question(';
        $write .= '\'' . $name2 . '\', ';
        $write .= '\'' . addcslashes($this->getDescription(), '\'') . '\', ';        
        $write .= arrayToString($this->questionText) . ', ';
        $write .= '$' . $this->getQuestionType()->getName();
        $write .= ');' . "\n";
        
        if ($this->getTemplate()->getName() != QUESTION_STANDARD_TEMPLATE){
          $write .= '$' . $name . '->setTemplate($' . $this->getTemplate()->getName() . ');' . "\n";
        }
        return $write;           
    }

    
}

?>
