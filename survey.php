<?php
/* 
------------------------------------------------------------------------
Copyright (C) 2015 Albert Weerman
This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.
This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
------------------------------------------------------------------------
*/

class Survey {

  var $name;
  var $description;
  var $backButton;
  var $nextButton;
  var $rfButton;
  var $dkButton;
  var $surveyTemplate = null;
  var $languages;
  var $templatesLocation;
  var $typesLocation;
  var $db;
  var $users = array();
  var $sections = array();
  var $showDKButton = false;
  var $showRFButton = false;
  var $types = null;
  var $templates = null;
  var $encryptionKey = null;
    
  //defaults for questions are set for these
  var $validationOption = VALIDATION_OPTION_FORCE_ANSWER;   // VALIDATION_OPTION_REQUEST_ONE_TIME;
  
  
  
  
  
  function Survey(){
  }

  function getName($language = 1){
    return $this->name[$language];
  }
  
  function setName($name){
      return $this->name = $name;
  }

  function getDescription($language = 1){
    return $this->description[$language];
  }

  function setDescription($description){
      $this->description = $description;
  }
  
  function getNextButton($language = 1){
    return $this->nextButton[$language];
  }

  function setNextButton($button){
    $this->nextButton = $button;
  }

  
  function getBackButton($language = 1){
    return $this->backButton[$language];
  }

  function setBackButton($button){
    $this->backButton = $button;
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
  

  function getRFButton($language = 1){
    return $this->rfButton[$language];
  }

  function setRFButton($button){
    $this->rfButton = $button;
  }

  function getDKButton($language = 1){
    return $this->dkButton[$language];
  }

  function setDKButton($button){
    $this->dkButton = $button;
  }  
  
  function getSurveyTemplate($language = 1){
    return $this->surveyTemplate[$language];
  }

  function setSurveyTemplate($surveyTemplate){ //expecting array
    $this->surveyTemplate = $surveyTemplate;
  }
  
  function showMainTemplate(){
    $showMainTemplate = $this->getSurveyTemplate()->getTemplateContent();
    $patterns = array('/<surveyName \/>/', '/<backButton \/>/', '/<nextButton \/>/');
    $replacements = array($this->getName(), $this->getBackButton(), $this->getNextButton());
    return preg_replace($patterns, $replacements, $showMainTemplate);
  }
  
    function setLanguages($languages){
        $this->languages = $languages;
    }
  
    function getLanguages(){
        return $this->languages;
    }
    
    function getLanguage($index){
        return $this->getLanguages()[$index];
    }
  
    function setDatabase($database){
        $this->db = $database;
    }
    
    function getDatabase(){
        return $this->db;
    }
    
    function setTemplatesLocation($location){
        $this->templatesLocation = $location;
    }
    
    function getTemplatesLocation(){
        return $this->templatesLocation;
    }
    
    function setTypesLocation($location){
        $this->typesLocation = $location;
    }
    
    function getTypesLocation(){
        return $this->typesLocation;
    }

    function getValidationOption(){
        return $this->validationOption;
    }
    
    function setValidationOption($validationOption){
        $this->validationOption = $validationOption;
    }
    
    function setUsers($users){
        $this->users = $users;
    }

    function addUser(User $user){
        $this->users[] = $users;
    }
    
    function isAuthorizedUser($username, $password){
        foreach ($this->users as $user){
            if ($username == $user->getUsername() && $password == $user->getPassword()){
                return true;
            }
        }
    }
    
    function addSection(Section $section){
        $this->sections[$section->getName()] = $section;
    }
    
    function getSections(){
        return $this->sections;
    }
    

    function loadAllRules(){
        foreach ($this->getSections() as $section){
          require_once($section->getRulesLocation());
        }
    }
    
    function getTemplates(){
        if ($this->templates == null){
            //$this->getAllQuestions();
        }
        return $this->templates;
    }
  
    function getTemplate($name){
        return $this->getTemplates()[$name];
    }

    function getQuestion($questionname){
        echo $questionname;
        echo '<hr>';
        print_r($this->getAllQuestions());
        echo '<hr>';
        foreach($this->getAllQuestions() as $question){
            if ($question->getName() == $questionname){
                return $question;
            }
        }
        return null;
    }

    function getQuestionGroup($name){
        foreach($this->getAllQuestionGroups() as $group){
            if ($group->getName() == $name){
                return $group;
            }
        }
        return null;
    }
    
    
    function getQuestionOrGroup($name){
        $getQuestionOrGroup = $this->getQuestion($name);
        if ($getQuestionOrGroup == null){
            $getQuestionOrGroup = $this->getQuestionGroup($name);
        }
        return $getQuestionOrGroup;
        
    }
    
    
    function getTypes(){
        if ($this->types == null){
         //   $this->getAllQuestions();
        }
        return $this->types;
    }
    
    function getType($name){
        return $this->getTypes()[$name];
    }
    
    function getUsernames(){
        $usernames = array();
        foreach($this->users as $user){
            $usernames[] = $user->getUsername();
        }
        return $usernames;
    }
    
    function getSection($name){
        return $this->getSections()[$name];
    }
  
    function getAllQuestions(){
        $questions = array();
        foreach($this->getSections() as $section){
            $questions = array_merge($questions, $section->getQuestions());
        }
        return $questions;        
    }
    
    function loadMetadata(){
        //load templates and types
        require_once($this->getTemplatesLocation());
        require_once($this->getTypesLocation());       

        $lastitem = 0;
        foreach($this->getSections() as $section){
            if ($section->questions == null){
                $section->questions = array();
//                $metadata = file_get_contents(prependPath($section->getQuestionLocation()));
//                $metadata = replaceFillsToAdmin($metadata);
/*//                eval('?>'.$metadata.'<?'); //ai :(*/
              
                require_once($section->getQuestionLocation());
                                
                
                $arr = get_defined_vars(); //get the defined vars from php and loop
                $startitem = 0;
                //echo '<hr>';
                foreach($arr as $key=>$obj){
                    if ($lastitem <= $startitem){ //only if not already analyzed
                        $array = (is_array($obj) && sizeof($obj) > 0) ? getArray($obj, array()) : array();
                        if ($obj instanceof Question){
                            if ($key == getTextWithoutBrackets($obj->getName())){
                                //$array = getArray($obj, array()); //get the array index if array
                                $obj->setArray($array);
                                $obj->setName($key);
                                $section->questions[$key] = $obj;
                            }
                           // echo '<b>' . $obj->getName() . '</b>';
                        }
                        else if ($obj instanceof QuestionGroup){
                            if ($key == $obj->getName()){
//                                $array = getArray($obj, array()); //get the array index if array
                                $obj->setArray($array);
                                $obj->setName($key);
                                $section->questionGroups[getTextWithoutBrackets($obj->getName())] = $obj;
                            }
                            //echo $obj->getName();
                        }
                        else if ($obj instanceof Type){
                            $this->types[$obj->getName()] = $obj;
                            //echo $obj->getName();
                        }
                        else if ($obj instanceof Template){
                            $this->templates[$obj->getName()] = $obj;
                            //echo $obj->getName();
                        }
                        //echo '<br/>';
                    }
                    $startitem++;
                }
                $lastitem = $startitem;
            }
        }
    }
    
    function getAllQuestionGroups(){
        $questionsGroups = array();
        foreach($this->getSections() as $section){
            $questionsGroups = array_merge($questionsGroups, $section->getQuestionGroups());
        }
        return $questionsGroups;
    }
 
    function getAllDataQuestions(){
        $questions = array();
        foreach($this->getSections() as $section){
            $questions = array_merge($questions, $section->getDataQuestions());
        }
        return $questions;
    }
    
    function writeTemplates(){
        $output = '<?php' . "\n" . '//xi templates (admin generated)';
        foreach ($this->getTemplates() as $template){
          $output .= "\n" . $template->write();
        }
        $output .= "\n" . '?>';
        $filename = prependPath($this->getTemplatesLocation());
        if (is_writable($filename)){
          file_put_contents($filename, $output);
        //  sleep(2); //give php a change to load the new file
          //gc_collect_cycles();
          return true;
        }
        return false;
    }
  
    function writeTypes(){
        $output = '<?php' . "\n" . '//xi types (admin generated)';
        foreach ($this->getTypes() as $type){
          $output .= "\n" . $type->write();
        }
        $output .= "\n" . '?>';
        $filename = prependPath($this->getTypesLocation());
//        $filename = '/tmp/types.php';
        if (is_writable($filename)){
          file_put_contents($filename, $output);
        //  sleep(2); //give php a change to load the new file
          //gc_collect_cycles();
          return true;
        }
        return false;
    }    

    function addType($type){
        $this->types[$type->getName()] = $type;
    }

    function addTemplate($template){
        $this->templates[$template->getName()] = $template;
    }

    function setEncryptionKey($key){
        $this->encryptionKey = $key;
    }
    
    function getEncryptionKey(){
        return $this->encryptionKey;
    }

    function getDefaultLanguage(){
        return 1;
    }
}

?>
