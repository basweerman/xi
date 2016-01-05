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

register_shutdown_function( "fatal_handler" );

ob_start();

session_name('admin');
session_start();

session_regenerate_id();


if(!isset($_POST['username']) && isset($_GET['logout'])){
    unset($_SESSION['username']);
    session_destroy();
}


require_once('globals.php');
//put metadata dir on the path

set_include_path($xi['surveys']['metadata']['path']);
require_once($xi['surveys']['metadata']['surveyslocation']);    

if(!isset($_SESSION['username'])) {
    if( isset($_POST['username']) && isset($_POST['password']) ){
        if (auth($_POST['username'], $_POST['password'])){
            $_SESSION['username'] = $_POST['username'];
        }
        else {
            $display = new Display();
            echo $display->showLogin('<div class="alert alert-danger" role="alert">Invalid username or password</div>');
            exit;
        }
    }
    else {
        $display = new Display();
        echo $display->showLogin();
        exit;
    }
}


if (isset($_GET['clear']) && $_GET['clear'] == 1){
  session_unset();
  session_destroy();
  session_name('admin');
  session_start();
}
$_SESSION['admin'] = 1;


if (isset($_GET['page']) && $_GET['page'] == 'survey.setsurvey'){
    $_SESSION['syid'] = $_GET['syid'];
}
elseif (!isset($_SESSION['syid'])){
    $_SESSION['syid'] = 1;
}

//get survey object and cast
//$survey = cast('AdminSurvey', $surveys[$_SESSION['syid']]); //cast to adminsurvey for extra functions
$survey = $surveys[$_SESSION['syid']];
$survey->loadMetadata();


if (isset($_GET['page']) && $_GET['page'] == 'language.setlanguage'){
    $_SESSION['language'] = $_GET['language'];
    if (isset($_SESSION['lastpage'])){ //language change: show last page
      $_GET['page'] = $_SESSION['lastpage'];
    }
}
elseif (!isset($_SESSION['language'])){
    $_SESSION['language'] = 1;
}
if (isset($_GET['page'])){  //store the last page shown
    $_SESSION['page'] = $_GET['page'];
    $_SESSION['lastpage'] = $_GET['page'];
}
if (isset($_POST['page'])){  //store the last page shown
    $_SESSION['page'] = $_POST['page'];
    $_SESSION['lastpage'] = $_POST['page'];
}
if (isset($_GET['section'])){
  $_SESSION['section'] = $_GET['section'];
}
if (isset($_GET['question'])){
  $_SESSION['question'] = $_GET['question'];
}
if (isset($_GET['questiongroup'])){
  $_SESSION['questiongroup'] = $_GET['questiongroup'];
}
if (isset($_GET['template'])){
  $_SESSION['template'] = $_GET['template'];
}
if (isset($_GET['type'])){
  $_SESSION['type'] = $_GET['type'];
}



if (!isset($_SESSION['section'])){
    $_SESSION['section'] = array_values($survey->getSections())[0]->getName();  //default
}
if (!isset($_SESSION['question'])){
    $_SESSION['question'] = array_values($survey->getAllQuestions())[0]->getName();  //default
}
if (!isset($_SESSION['type'])){
    $_SESSION['type'] = array_values($survey->getTypes())[0]->getName();  //default
}
if (!isset($_SESSION['template'])){
    $_SESSION['templatetype'] = array_values($survey->getTemplates())[0]->getName();  //default
}
if (!isset($_SESSION['questiongroup'])){
    $_SESSION['questiongroup'] = array_values($survey->getAllQuestionGroups())[0]->getName();  //default
}


$display = new Display($survey);
echo $display->showHeader();
echo $display->showNavBar();


echo '<div id=content style="background-color:white;">';
echo '<div class="container" style="background-color:white;">';

//Does the user have access to this survey?
if (in_array($_SESSION['username'], $survey->getUsernames())){
    $admin = new Admin($survey);
    echo getPage($display, $admin);
}
else {
    echo '<div class="alert alert-danger" role="alert">You do not have admin access to this survey</div>';       
}

echo $display->showFooter();


function getPage($display, $admin){
    if (isset($_SESSION['page'])){
      //  echo '<br/><br/><br/>' . $_SESSION['page'];
        switch($_SESSION['page']){

            case 'surveys': return $display->showSurveys();  
            case 'survey': return $display->showSurvey();  
                
            case 'survey.settings': return $display->showSettings();  
                
            case 'survey.sections': return $display->showSections();

            case 'survey.section.rules': return $display->showSectionRules();
            case 'survey.section.rules.save': return $display->showSectionRules($admin->saveRules());

            case 'survey.section.questions': return $display->showSectionQuestions();
            case 'survey.section.questions.add': return $display->ShowSectionQuestionAdd();
            case 'survey.section.question': return $display->showSectionQuestion();
            case 'survey.section.question.save': return $display->showSectionQuestions($admin->saveQuestion());                

            case 'survey.section.templates': return $display->showTemplates();
            case 'survey.section.templates.add': return $display->showTemplateAdd();
            case 'survey.section.template': return $display->showTemplate();
            case 'survey.section.template.save': return $display->showTemplates($admin->saveTemplate());
                
            case 'survey.section.types': return $display->showTypes();
            case 'survey.section.types.add': return $display->showTypeAdd();
            case 'survey.section.type':  return $display->showType();
            case 'survey.section.type.save': return $display->showTypes($admin->saveType());
                
            case 'survey.section.groups': return $display->showSectionGroups();
            case 'survey.section.groups.add': return $display->showGroupAdd();
            case 'survey.section.group': return $display->showSectionGroup();
            case 'survey.section.group.save': return $display->showSectionGroups($admin->saveGroup());
                
            case 'survey.metadata': return $display->showSurveyMetaData();    
                
            case 'survey.data': return $display->showSurveyData();
            case 'survey.data.truncate': return $display->showSurveyData($admin->truncateData());
            case 'survey.data.stata': return $display->showSurveyData($admin->exportStata());
            case 'survey.data.csv': return $display->showSurveyData($admin->exportCsv());
                
            case 'other.data': return $display->showOtherData();    
            case 'other.data.download': return $display->showSurveyData($admin->exportTableToCsv($_GET['t']));
                
                
            case 'search.res': return $display->showSearchRes($_POST['search']);
                
            default: return $display->showSurveys();

        }


    }
    else {
      return $display->showSurveys();
    }
}


function auth($username, $password){
    global $surveys;
    foreach ($surveys as $survey){
      if ($survey->isAuthorizedUser($username, $password)){
          return true;
      }
  }
  return false;  
}

function fatal_handler() {
  $errfile = "unknown file";
  $errstr  = "shutdown";
  $errno   = E_CORE_ERROR;
  $errline = 0;

  $error = error_get_last();

  if( $error !== NULL) {
    $errno   = $error["type"];
    $errfile = $error["file"];
    $errline = $error["line"];
    $errstr  = $error["message"];
    
    
    echo '<div class="alert alert-danger" role="alert">' . $errstr .' on ' . $errline . ' in ' . $errfile . '</div>';
    
  }
}



?>
