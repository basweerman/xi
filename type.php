<?php
/* 
------------------------------------------------------------------------
Copyright (C) 2015 Albert Weerman
This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.
This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
------------------------------------------------------------------------
*/

class Type {
  
    var $name;
    var $answerType;
    var $inputWidth = TYPE_STANDARD_WIDTH;
    var $inputHeight = TYPE_STANDARD_HEIGHT;
    var $answerDetails;
    var $template = null;
    var $textTemplate = null;
    var $validationOption;
    
    var $showDKButton = false;
    var $showRFButton = false;
    
    function Type($name, $answertype, $details = null){
        $this->setName($name);
        $this->setAnswerType($answertype);
        $this->setAnswerDetails ($details);
        //preload from survey
        global $survey;
        $this->setValidationOption($survey->getValidationOption());
    }
    
    function getName(){
      return $this->name;
    } 

   function setName($name){
      $this->name = $name;
    } 

    
/*    function getEnumeratedTemplate(){
      return $this->enumeratedTemplate;
    }

    function setEnumeratedTemplate(Template $template){
      $this->enumeratedTemplate = $template;
    }*/
    
    function getTemplate(){
        if ($this->template == null){
            //default type:
            if (isset($_SESSION['admin'])){ //admin: get from survey templates
                global $survey;
                $this->template = $survey->getTemplate('TTextTemplate');
                if(in_array($this->getAnswerType(), array(QUESTION_TYPE_ENUMERATED, QUESTION_TYPE_SETOF, QUESTION_TYPE_SETOF))){
                    $this->template = $survey->getTemplate('TEnumeratedTemplate');
                }
            }
            else{  //regular survey: access directly
                global $TTextTemplate, $TEnumeratedTemplate;
                $this->template = $TTextTemplate;
                if(in_array($this->getAnswerType(), array(QUESTION_TYPE_ENUMERATED, QUESTION_TYPE_SETOF, QUESTION_TYPE_SETOF))){
                    $this->template = $TEnumeratedTemplate;
                }
                
            }
               
        }
        return $this->template;
    }

    function setTemplate(Template $template){
      $this->template = $template;
    }

    
    function setAnswerType($answertype){
        $this->answerType = $answertype;
    }

    function getAnswerDetails(){
        return $this->answerDetails;
    }

    function setAnswerDetails($answerDetails){
        $this->answerDetails = $answerDetails;
    }

    function getAnswerType(){
        return $this->answerType;
    }

    
    function setInputWidth($inputWidth){
        $this->inputWidth = $inputWidth;
    }

    function getInputWidth(){
        return $this->inputWidth;
    }

    function setInputHeight($inputHeight){
        $this->inputHeight = $inputHeight;
    }

    function getInputHeight(){
        return $this->inputHeight;
    }
    
    
    function getMinimum(){
        return $this->getFromDetails(0);
    }
    
    function getMaximum(){
        return $this->getFromDetails(1);
    }
    
    function getEnumeratedOptions(){
        return $this->answerDetails;
    }
    
    function setEnumeratedOptions($options){
        $this->answerDetails[$_SESSION['language']] = $options;
    }
    
    function getFromDetails($index){
        if (is_array($this->getAnswerDetails())){
            return $this->getAnswerDetails()[$index];
        }
        return null;
    }
    
    function getAnswerOption($question){
      switch ($this->getAnswerType()){
        case QUESTION_TYPE_NONE: return '<input type=hidden name="' . loadFromPostRemoveBrackets($question->getName()) .'" id="' . $question->getName() .'" value="1">';
        case QUESTION_TYPE_INTEGER: //fall down
        case QUESTION_TYPE_RANGE:  //fall down
        case QUESTION_TYPE_OPEN:  //fall down
        case QUESTION_TYPE_STRING: return $this->getTextBox($question);
        case QUESTION_TYPE_ENUMERATED: return $this->getEnumerated($question);
        case QUESTION_TYPE_SETOF: return $this->getSetOf($question);
        case QUESTION_TYPE_SELECT: return $this->getSelect($question);
        default: return '<input type=hidden name="' . loadFromPostRemoveBrackets($question->getName()) .'" id="' . $question->getName() .'" value="1">';  
      }
    }

    function getTextBox($question){
        $textTemplate = $this->getTemplate()->getTemplateContent();
        $patterns = array('/<questionName \/>/', '/<idName \/>/', '/<value \/>/', '/<style \/>/');
        $replacements = array(loadFromPostRemoveBrackets($question->getName()), loadFromPostRemoveBrackets($question->getName()),  $question->textboxValue(), $this->getStyle(), $this->getDKButton($question, $_SESSION['language']), $this->getRFButton($question, $_SESSION['language']));
        $getTextBoxreturn = preg_replace($patterns, $replacements, $textTemplate);
        return $this->getDKRFReplacements($question, $getTextBoxreturn);
    }
    
    function getSelect($question){
        $enumeratedTemplate = $this->getTemplate()->getTemplateContent();
        $textwithoutrepeats = getTextBetweenTags($enumeratedTemplate, 'repeat'); //expecting repeat!!
        $patterns = array('/<inputType \/>/', '/<questionName \/>/', '/<idName \/>/');
        $arrayStr = ($this->getAnswerType() == QUESTION_TYPE_SETOF)? '[' . $key . ']' : '';
        $replacements = array($this->getInputType(), loadFromPostRemoveBrackets($question->getName()) . $arrayStr, loadFromPostRemoveBrackets($question->getName()));
        $getSelect = preg_replace($patterns, $replacements, $textwithoutrepeats[1]);
        $getSelect .= $this->replaceEnumerated($question, $textwithoutrepeats[2]);
        $getSelect .= $this->getDKRFReplacements($question, $textwithoutrepeats[3]);
        return $getSelect;     
    }
    
    function getSetOf($question){
        return $this->getEnumerated($question);
    }
    
    
    function replaceEnumerated($question, $string){
        $replaceEnumerated = '';
        $enumerated = $this->getEnumeratedOptions()[$_SESSION['language']]; //enumerated stored in details
        foreach($enumerated as $key=>$enum){
          if (is_array($question->value())){
            $checked = (array_key_exists($key, $question->value())) ? ($this->getAnswerType() == QUESTION_TYPE_SELECT) ? ' SELECTED' : ' CHECKED' : '';
          }
          else {
            $checked = ($key == $question->value()) ? ($this->getAnswerType() == QUESTION_TYPE_SELECT) ? ' SELECTED' : ' CHECKED' : '';
          }
          $patterns = array('/<inputType \/>/', '/<questionName \/>/', '/<idName \/>/', '/<optionKey \/>/', '/<checked \/>/', '/<optionText \/>/');
          $arrayStr = ($this->getAnswerType() == QUESTION_TYPE_SETOF)? '[' . $key . ']' : '';
          $optionKey = ($this->getAnswerType() == QUESTION_TYPE_SETOF)? 1 : $key;
          $replacements = array($this->getInputType(), loadFromPostRemoveBrackets($question->getName()) . $arrayStr, loadFromPostRemoveBrackets($question->getName()), $optionKey, $checked, $enum);
          $replaceEnumerated .= preg_replace($patterns, $replacements, $string);
        }    
        return $replaceEnumerated;
        
    }
    
    function getEnumerated($question){
        $enumeratedTemplate = $this->getTemplate()->getTemplateContent();
        $textwithoutrepeats = getTextBetweenTags($enumeratedTemplate, 'repeat'); //expecting repeat!!
        $getEnumerated = $textwithoutrepeats[1];
        $getEnumerated .=  $this->replaceEnumerated($question, $textwithoutrepeats[2]);
        $getEnumerated .=  $this->getDKRFReplacements($question, $textwithoutrepeats[3]);
        return $getEnumerated;
    }
    
    function getInputType(){
        switch ($this->getAnswerType()){
            case QUESTION_TYPE_NONE: return 'hidden';
            case QUESTION_TYPE_STRING: return 'text';
            case QUESTION_TYPE_INTEGER: return 'text';
            case QUESTION_TYPE_ENUMERATED: return 'radio';
            case QUESTION_TYPE_RANGE: return 'radio';
            case QUESTION_TYPE_SETOF: return 'checkbox';
            case QUESTION_TYPE_SELECT: return 'select';
            case QUESTION_TYPE_OPEN: return 'textarea';
            default: return 'text';  
        }
    }
    
    function getDKRFReplacements($question, $getDKRFReplacements){
        $patterns = array('/<dkrfInput \/>/', '/<dkButton \/>/', '/<rfButton \/>/');
        $replacements = array($this->getDKRFInput($question), $this->getDKButton($question, $_SESSION['language']), $this->getRFButton($question, $_SESSION['language']));
        //$replacements = array('','');=
        return preg_replace($patterns, $replacements, $getDKRFReplacements);
    }
    
    function getDKRFInput(Question $question){
      $value = ($question->isDontKnow() || $question->isRefusal())? $question->value() : '';
      return '<input hidden name="' . loadFromPostRemoveBrackets($question->getName()) . '_button" value="' . $value . '">';
    }

    function validate($question){
        if ($_SESSION['currentQuestion'] == $_SESSION['lastQuestion']){ //check only if we have been on this screen before
            if ($_SESSION['buttonClicked'] == BUTTON_NEXT){ //only check if next button is checked
                if ($question->isRefusal() || $question->isDontKnow()){ //if dk/rf, always continue
                    //no validation
                }
                else {
//                    echo $this->getValidationOption();
                    if ($question->value() == null || $question->value() == ''){
                        if ($this->getValidationOption() == VALIDATION_OPTION_FORCE_ANSWER){
                            return ERROR_NO_ANSWER;
                        }
                        elseif($this->getValidationOption() == VALIDATION_OPTION_REQUEST_ONE_TIME){
                            if (!isset($_SESSION[$question->getName() . '_NO_ANSWER'])){ //no answer for this question
                                $_SESSION[$question->getName() . '_NO_ANSWER'] = 0; //set default to 1
                            }
                            if (isset($_SESSION[$question->getName() . '_NO_ANSWER']) && $_SESSION[$question->getName() . '_NO_ANSWER'] <= 2){
                                $_SESSION[$question->getName() . '_NO_ANSWER'] = $_SESSION[$question->getName() . '_NO_ANSWER'] + 1;
                                return ERROR_NO_ANSWER;
                            }
                        }
                    }
                    if ($this->getAnswerType() == QUESTION_TYPE_RANGE){
                        if ($question->value() < $this->getMinimum() || $question->value() > $this->getMaximum()){
                            //OUTSIDE RANGE
                            return ERROR_INPUT_RANGE;
                        }
                    }
                }
            }
        }    
        return 0;
    }

    //FOR STATA
    function getVFMT(){
        
        switch ($this->getAnswerType()){
        case QUESTION_TYPE_NONE: return '%1s';
        case QUESTION_TYPE_STRING: return '%240s';
        case QUESTION_TYPE_ENUMERATED: return '%9.0g';
        case QUESTION_TYPE_INTEGER: return '%9.0g';
        case QUESTION_TYPE_RANGE: return '%9.0g';
        case QUESTION_TYPE_SETOF: return '%9.0g';
        case QUESTION_TYPE_SELECT: return '%9.0g';
        case QUESTION_TYPE_OPEN: return '%240s';


        default: return '%240s';
      }        
    }
    
    function getValueType(){
        
#define STATA_SE_STRINGOFFSET 0
#define STATA_SE_FLOAT  254
#define STATA_SE_DOUBLE 255
#define STATA_SE_INT    253
#define STATA_SE_SHORTINT 252
#define STATA_SE_BYTE  251 
        switch ($this->getAnswerType()){
        case QUESTION_TYPE_NONE: return 1;
        case QUESTION_TYPE_STRING: return 240;
        case QUESTION_TYPE_ENUMERATED: return 255;
        case QUESTION_TYPE_INTEGER: return 255;
        case QUESTION_TYPE_RANGE: return 255;
        case QUESTION_TYPE_SETOF: return 255;
        case QUESTION_TYPE_SELECT: return 255;
        case QUESTION_TYPE_OPEN: return 240;
        default: return 240;
      }        
    }
    //END FOR stata

    function getValidationOption(){
        return $this->validationOption;
    }
    
    function setValidationOption($validationOption){
        $this->validationOption = $validationOption;
    }    

    function getRFButton($question, $language = 1){
        if ($this->showRFButton()){
            if ($this->getAnswerType() != QUESTION_TYPE_NONE){ //no dk/rf for none answer types
                $class = ($question->isRefusal()) ? ' btn_dkrf_selected' : '';    
                global $survey;  
                $patterns = array('/<questionName \/>/', '/<style \/>/', '/<class \/>/');
                $replacements = array(loadFromPostRemoveBrackets($question->getName()), '', $class);
                return preg_replace($patterns, $replacements, $survey->getRFButton($language));
            }
        }
        return '';
    }
    
    function getDKButton($question, $language = 1){
        if ($this->showDKButton()){
            if ($this->getAnswerType() != QUESTION_TYPE_NONE){ //no dk/rf for none answer types
                $class = ($question->isDontKnow()) ? ' btn_dkrf_selected' : '';
                global $survey;  
                $patterns = array('/<questionName \/>/', '/<style \/>/', '/<class \/>/');
                $replacements = array(loadFromPostRemoveBrackets($question->getName()), '', $class);
                return preg_replace($patterns, $replacements, $survey->getDKButton($language));
            }
        }
        return '';
    }

    function setShowDKButton($visible){
        $this->showDKButton = $visible;
    }

    function showDKButton(){
       return $this->showDKButton;
    }  

    function setShowRFButton($visible){
       $this->showRFButton = $visible;
    }

    function showRFButton(){
       return $this->showRFButton;
    }    

    function getStyle(){
        $style = ($this->getInputWidth() != '') ? 'width:' . $this->getInputWidth() . ';' : '';
        $style .= ($this->getInputHeight() != '') ? 'height:' . $this->getInputHeight() . ';' : '';
        return $style;
    }
    
    function getEnumeratedOptionsAsString($options){
        $getEnumeratedOptionsAsString = '';
        foreach($options as $key => $enum){
            $getEnumeratedOptionsAsString .= $key . ' ' . $enum . "\n";
        }
        return $getEnumeratedOptionsAsString;
    }
    
    function setEnumeratedOptionsAsString($string){
        $setEnumeratedOptionsAsString = array();
        $options = explode("\r\n", $string);
        foreach($options as $enum){
            if ($enum != ''){
              $split = explode(' ', $enum, 2);
              $setEnumeratedOptionsAsString[$split[0]] = $split[1];
            }
        }
        return $setEnumeratedOptionsAsString;
    }
    
    
    function getDetailsAsString(){
        switch ($this->getAnswerType()){
            case QUESTION_TYPE_NONE: 
            case QUESTION_TYPE_STRING: 
            case QUESTION_TYPE_INTEGER: 
            case QUESTION_TYPE_OPEN: return '';
            case QUESTION_TYPE_ENUMERATED: return $this->getEnumeratedOptionsAsString($this->getEnumeratedOptions()[$_SESSION['language']]);
            case QUESTION_TYPE_RANGE: return $this->getMinimum() . '..' . $this->getMaximum();
            case QUESTION_TYPE_SETOF: return $this->getEnumeratedOptionsAsString($this->getEnumeratedOptions()[$_SESSION['language']]);
            case QUESTION_TYPE_SELECT: return $this->getEnumeratedOptionsAsString($this->getEnumeratedOptions()[$_SESSION['language']]);
            
            default: return '';
        }

    }
    
    function setDetailsAsString($string){
        switch ($this->getAnswerType()){
            case QUESTION_TYPE_NONE: 
            case QUESTION_TYPE_OPEN: 
            case QUESTION_TYPE_INTEGER:
            case QUESTION_TYPE_STRING: return $this->setAnswerDetails(null);
            case QUESTION_TYPE_RANGE: return $this->setAnswerDetails(explode('..', $string));
            case QUESTION_TYPE_ENUMERATED: 
            case QUESTION_TYPE_SETOF: 
            case QUESTION_TYPE_SELECT: return $this->setEnumeratedOptions($this->setEnumeratedOptionsAsString($string));
            
            default: return $this->setAnswerDetails(null);
        }
        
        
    }
    
    function writeDetails(){
       switch($this->getAnswerType()){
           case QUESTION_TYPE_RANGE: return 'array(' . implode(', ', $this->getAnswerDetails()) . ')';
           case QUESTION_TYPE_ENUMERATED:
           case QUESTION_TYPE_SETOF:
           case QUESTION_TYPE_SELECT: //remove newlines and extra comma's from var_export
               return arrayToString($this->getAnswerDetails());
           default: return '\'\'';
       }
        
    }
    
    function write(){
        global $survey;
        $write = '$' . $this->getName() . ' = new Type(';
        $write .= '\'' . $this->getName() . '\', ';
        $write .= array_search($this->getAnswerType(), returnConstants("QUESTION_TYPE_")) . ', ';
        $write .= $this->writeDetails();  //$write .= '\'' . addcslashes($this->getContent(), '\'') . '\'';
        $write .= ');' . "\n";

        $write .= '$' . $this->getName() . '->setTemplate($' . $this->getTemplate()->getName() . ');' . "\n";
        if ($this->ShowDKButton() != $survey->ShowDKButton()){ //different than survey: write!
            $write .= '$' . $this->getName() . '->setShowDKButton(' . var_export($this->showDKButton(), true) . ');' . "\n";
        }
        if ($this->ShowRFButton() != $survey->ShowRFButton()){ //different than survey: write!
            $write .= '$' . $this->getName() . '->setShowRFButton(' . var_export($this->showRFButton(), true) . ');' . "\n";
        }
        if ($this->getInputWidth() != TYPE_STANDARD_WIDTH){
            $write .= '$' . $this->getName() . '->setInputWidth(\'' . $this->getInputWidth() . '\');' . "\n";
        }
        if ($this->getInputHeight() != TYPE_STANDARD_HEIGHT){
            $write .= '$' . $this->getName() . '->setInputHeight(\'' . $this->getInputHeight() . '\');' . "\n";
        }
        if ($this->getValidationOption() != $survey->getValidationOption()){
            $write .= '$' . $this->getName() . '->setValidationOption(' . array_search($this->getValidationOption(), returnConstants("VALIDATION_OPTION_"))  . ');' . "\n";
        }
        
        return $write;        
        
    }
}

?>
