<?php
/* 
------------------------------------------------------------------------
Copyright (C) 2015 Albert Weerman
This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.
This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
------------------------------------------------------------------------
*/

error_reporting(E_ALL);
ini_set("display_errors", 1);


session_start();
//TEST FOR TIMOUT
//print_r($_SESSION);
//echo '<br/>';
//print_r($_POST);
//echo '<hr>';

if (!isset($_SESSION["primkey"])){
  showSurveyTimedOut();
}

$_SESSION['data']['primkey'] = $_SESSION["primkey"];


require_once('globals.php');
//setup metadata path
set_include_path($xi['surveys']['metadata']['path']);
require_once($xi['surveys']['metadata']['surveyslocation']);    
//setup survey object
$survey = $surveys[$_SESSION['syid']]; // new Survey(); 

if (!isset($_SESSION['data']['startts']) && $survey->getDatabase()->findRecord($_SESSION["primkey"]) != null){  //preload data if there is data already for this primkey
  $_SESSION['data'] = $survey->getDatabase()->findRecord($_SESSION["primkey"]);
}

if (isset($_POST['button'])){
  $_SESSION['buttonClicked'] = $_POST['button'];
}



$_SESSION['lastQuestion'] = array();
if (isset($_SESSION['currentQuestion'])){

    $_SESSION['lastQuestion'] = $_SESSION['currentQuestion'];
   
    
    $currentQuestion = $_SESSION['currentQuestion'];

    //STORE OLD ANSWER
    if (is_array($_SESSION['currentQuestion'])){ //always an array
      foreach($_SESSION['currentQuestion'] as $var){
        $postvar = '';
        if (isset($_POST[loadFromPostRemoveBrackets($var)]) && $_POST[loadFromPostRemoveBrackets($var)] != '' ){
            $postvar = $_POST[loadFromPostRemoveBrackets($var)];
        }       
        elseif (isset($_POST[loadFromPostRemoveBrackets($var) . '_button']) && $_POST[loadFromPostRemoveBrackets($var) . '_button'] != '') { //rf/dk clicked
            $postvar = $_POST[loadFromPostRemoveBrackets($var) . '_button'];
        }
        if ($postvar == ''){
            $postvar = '.e'; //set to empty
        }
        $_SESSION['data'][$var] = $postvar;
        $survey->getDatabase()->insertLogRecord($_SESSION["primkey"], $var, $postvar);
      }
    }       
    
    $survey->getDatabase()->updateRecord($_SESSION["primkey"], $_SESSION['data']);

    //load all questions  LOAD HERE OTHERWISE FILLS FOR THIS Q WON'T BE UPDATED with previous answer!
    //require_once($survey->getQuestionsLocation());
    require_once($survey->getTemplatesLocation());
    require_once($survey->getTypesLocation());
    foreach ($survey->getSections() as $section) 
        require_once($section->getQuestionLocation());

    //validate last answer!
    $errorcodes = array();
    foreach($_SESSION['currentQuestion'] as $var){
        $question = getCurrentQuestion($var); //for arrays
        if ($question->validate() > 0){
            $errorcodes[$question->validate()] = $question;
        }
    }

    if (sizeof($errorcodes) > 0){  //this needs to move to the question!
        //Just display the current question again: error display is handled in the question
        $currentRouting = $_SESSION['currentRouting'];
    }
    else {
        //HANDLE NAVIGATION AND FIND NEXT QUESTION
        $currentRouting = null;
        try {
            $_SESSION['parsedQuestion'] = '';
            $_SESSION['nextQuestion'] = 0;
            foreach ($survey->getSections() as $section){
              require_once($section->getRulesLocation());
            }
        }
        catch (Exception $e) {
            $currentRouting = $e->getMessage();
        }

    }
}
else {
    //load all questions  LOAD HERE OTHERWISE FILLS FOR THIS Q WON'T BE UPDATED
    require_once($survey->getTemplatesLocation());
    require_once($survey->getTypesLocation());
    foreach ($survey->getSections() as $section) {
        require_once($section->getQuestionLocation());
    }

    $currentRouting = null;
    $_SESSION['currentRouting'] = null;
    try {
        $_SESSION['parsedQuestion'] = '';
        $_SESSION['nextQuestion'] = 1; //get the first question
        foreach ($survey->getSections() as $section){
          require_once($section->getRulesLocation());
        }
    }
    catch (Exception $e) {
        $currentRouting = $e->getMessage();
    }
    
 //   echo '--------'  . $currentRouting ;
    if (isset($_SESSION['data']['startts'])){ //    echo '<b>Return to survey</b>';
        if ($_SESSION['data']['currentRouting']){
            $currentRouting = $_SESSION['data']['currentRouting']; //question left off
        } 
    }
    else { //     echo '<b>FIRST TIME!</b>';
       $_SESSION['data']['startts'] = date('Y-m-d H:i:s');
       $_SESSION['data']['browserinfo'] = $_SERVER['HTTP_USER_AGENT'];
       $_SESSION['data']['language'] = $_SESSION['language'];
    }

}

if ($currentRouting == null){ //no questions found
    //this was the last question
    if (!isset($_SESSION['data']['endts'])){ //don't overwrite when going back
        $_SESSION['data']['endts'] = date('Y-m-d H:i:s');
        //clean data!!
        global $cleanQuestions;
        $cleanQuestions = array('startts', 'browserinfo', 'language', 'endts');
        $_SESSION['clean'] = 1; //set clean session so ->ask knows to just store the questions
        //get all basic variablenames and set the as global;
        global $xi;
        foreach ($survey->getSections() as $section){
          eval('?>' . file_get_contents($xi['surveys']['metadata']['path'] . $section->getRulesLocation()) . '<?');
        }
        unset($_SESSION['clean']);
        $_SESSION['data'] = array_intersect_key($_SESSION['data'], array_flip($cleanQuestions)); //delete the ones we don't want
        $survey->getDatabase()->updateRecord($_SESSION["primkey"], $_SESSION['data']); //store endts
    }
    showSurveyPastLastQuestion();
    
}

//DISPLAY QUESTION OR QUESTIONGROUP
$question = getCurrentQuestion($currentRouting); //for arrays
/*if (isset($_SESSION['currentQuestion'])){
  $_SESSION['lastQuestion'] = $_SESSION['currentQuestion'];
}
else {
  $_SESSION['lastQuestion'] = array();
} */
$_SESSION['currentQuestion'] = setCurrentQuestion($question, array());
$_SESSION['currentRouting'] = $question->getName();
$_SESSION['data']['currentRouting'] = $question->getName(); //store as last question answered for when we get back.

echo $question->showQuestion();

//adjust next/back buttons
echo setNextButton($question->showNextButton());
echo setBackButton($question->showBackButton());


echo '<script>

    $("form input").click(function(){ //catch button click
        if ( $(this).is( "input[type=\'button\']" ) ) { //a button has been clicked!
            if ($(this).attr(\'id\').indexOf(\'_button\') >= 0){ //dk or rf button
              //erase answer from other components
              var inputElement = $(this).attr(\'id\').replace(\'_button\',\'\'); //get the question name from the button (remove _button from the end)
              $(\'[id="\' + inputElement + \'"]:radio\').attr(\'checked\', false);  //set radio/checkboxes to unselect
              $(\'[id="\' + inputElement + \'"]:checkbox\').attr(\'checked\', false);  //set radio/checkboxes to unselect
              $(\'[id="\' + inputElement + \'"]:text\').val(\'\');       //set input to empty
              $(\'select[id="\' + inputElement + \'"]\').val(\'\');       //set input to empty
              $(\'textarea[id="\' + inputElement + \'"]\').val(\'\');       //set input to empty

              //reset dk/rf buttons
              $(\'[id="\' + $(this).attr(\'id\') + \'"]:input\').removeClass("btn_dkrf_selected");  //remove class from buttons (in case switch from rf to dk)
              $(this).addClass("btn_dkrf_selected");       //set the class of the pressed button (blue background)
            }
            else {
              //next or back button clicked: do nothing
            }
        }
    });
    

    $("form input, select").change(function(){updateComponent(this);});  //catch input change
    $("form textarea").keydown(function(){updateComponent(this);});  //catch input change
    $("form input").keydown(function(){updateComponent(this);});  //catch input change

    function updateComponent(element){
        if ( $(element).is( "input" ) || $(element).is( "select") || $(element).is( "textarea" ))  {//other component has been clicked
            $(\'[id="\' + $(element).attr(\'id\') + \'_button"]:input\').removeClass("btn_dkrf_selected");  //remove blue from rf/dk button in case someone changes their minds after clicking dk/rf
            $(\'[name="\' + $(element).attr(\'id\') + \'_button"]\').val(\'\');  //set the hidden type to empty
        }
    }

</script>';


//echo mt_rand(1,23123123321);

function setNextButton($visible = true){
  if ($visible){
    return '<script>
    if (! $("#nextbutton").is(":visible") ) {
       $("#nextbutton").show();
    }
    </script>';
  }
  else {
    return '<script>
    if ( $("#nextbutton").is(":visible") ) {
       $("#nextbutton").hide();
    }
    </script>';
  }
}

function setBackButton($visible = true){
  if ($visible){
    return '<script>
    if (! $("#backbutton").is(":visible") ) {
       $("#backbutton").show();
    }
    </script>';
  }
  else {
    return '<script>
    if ( $("#backbutton").is(":visible") ) {
       $("#backbutton").hide();
    }
    </script>';
  }

}

function getCurrentQuestion($currentQuestion){
  preg_match_all('/\[([A-Za-z0-9 ]+?)\]/', $currentQuestion, $out);
  $out = $out[1];

  if (sizeof($out) > 0){
    $currentQuestion = substr($currentQuestion, 0, stripos($currentQuestion, '[')); 
    global $$currentQuestion;
    $question = $$currentQuestion;
    foreach ($out as $index){
      $question = $question[$index];
    }
  }
  else {
    global $$currentQuestion;
    $question = $$currentQuestion;
  }
  return $question;

}
/*
function getIndicesFromArray($currentQuestion){
  preg_match_all('/\[([A-Za-z0-9 ]+?)\]/', $currentQuestion, $out);
  $out = $out[0];
  if (sizeof($out) > 0){
    return explode($out, '');      
  }
  else {
    return '';
  }
}

function getVariablenameWithoutArray($currentQuestion){
    if (strpos($currentQuestion,'[') !== false) {
        return substr($currentQuestion, 0, stripos($currentQuestion, '['));
    }
    return $currentQuestion;
}
*/

function showSurveyTimedOut(){
  echo 'Survey timed out. Please login again!';
  echo setNextButton(false);
  echo setBackButton(false);
  exit;
}

function showSurveyPastLastQuestion(){
  echo 'No more questions, thank you!';
  echo setNextButton(false);
  echo setBackButton(false);
  exit;
}

function setCurrentQuestion($question, $thisarray){
  if ($question instanceof QuestionGroup){
    foreach ($question->getQuestions() as $subQuestion){ //sub can be a group too!
       $thisarray = setCurrentQuestion($subQuestion, $thisarray);
    }
    return $thisarray;
  }
  else {
    $thisarray[] = $question->getName();
    return $thisarray;
  }
}

function getNextQuestion($survey){
    //HANDLE NAVIGATION AND FIND NEXT QUESTION
    return $currentRouting;
}


?>
