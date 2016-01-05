<?php
/* 
------------------------------------------------------------------------
Copyright (C) 2015 Albert Weerman
This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.
This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
------------------------------------------------------------------------
*/


class Display {
    
    var $survey;
    
    function Display(Survey $survey = null){
        $this->survey = $survey;
    }
    
    function showHeader(){
        return '
        <!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="../images/xi.png">
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="../external/bootstrap.min.css">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn\'t work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
        <meta charset="utf-8" />
        <title>Survey tool</title>
    </head>
    <body><div id="wrap">';
    }
    
    function showFooter(){
return '</div><!-- well -->
    </div><!-- container -->
    </div> <!-- content -->
    </div> <!-- wrap -->
    <!-- jQuery (necessary for Bootstrap\'s JavaScript plugins) -->
    <script src="../external/jquery.min.js"></script>
    <!-- Latest compiled and minified JavaScript -->
    <script src="../external/bootstrap.min.js"></script>
    </body>
</html>';
    }
       //https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js
    
    function showNavBar(){
                $showNavBar = '<style>.dropdown:hover .dropdown-menu {
    display: block;
}</style>

        <nav class="navbar navbar-default">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header" style="width:100px">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">Xi</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">';

        /*$showNavBar .= '<li ';
        if (isset($_SESSION['lastpage']) && startsWith($_SESSION['lastpage'], 'data')){
            $showNavBar .= 'class="active"';
        }
        $showNavBar .= '><a href="?page=data">Data <span class="sr-only">(current)</span></a></li>';*/
        
        $showNavBar .= '
        <!-- <li><a href="?page=survey">Survey</a></li> -->
        <li class="dropdown';
        if (isset($_SESSION['lastpage']) && startsWith($_SESSION['lastpage'], 'survey')){
            $showNavBar .= ' active';
        }
        $showNavBar .= '">';
        
        global $surveys, $survey;
                
        if (isset($_SESSION['syid'])){
            $showNavBar .= '<a href="?page=survey&syid=' . $_SESSION['syid'] . '" class="dropdown-toggle" data-hoover="dropdown" role="button" aria-haspopup="true" aria-expanded="false">' . $survey->getName($_SESSION['language']);
        }
        else {            
            $showNavBar .= '<a href="#" class="dropdown-toggle" data-hoover="dropdown" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Surveys';
        }    
        $showNavBar .= ' <span class="caret"></span></a>
          <ul class="dropdown-menu">';
        foreach($surveys as $key => $surveyDetail){
            $checkMark = '';
            if (isset($_SESSION['syid']) && $key == $_SESSION['syid']){
              $checkMark = ' <span class="glyphicon glyphicon-ok"></span>';
            }
            $showNavBar .= '<li><a href="?page=survey&syid=' . $key . '">' . $surveyDetail->getName($_SESSION['language']) . $checkMark . '</a></li>';
        
        }
        $showNavBar .= '
          </ul>
        </li>
       
<li class="dropdown';
        //sections short cut
        $showNavBar .= '">';
        
               
        if (isset($_SESSION['section'])){
            $showNavBar .= '<a href="?page=survey.section.questions" class="dropdown-toggle" data-hoover="dropdown" role="button" aria-haspopup="true" aria-expanded="false">' . $survey->getSection($_SESSION['section'])->getName();
        }
        else {            
            $showNavBar .= '<a href="#" class="dropdown-toggle" data-hoover="dropdown" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Sections';
        }    
        $showNavBar .= ' <span class="caret"></span></a>
          <ul class="dropdown-menu">';
        if (isset($_SESSION['syid'])){
            global $survey;
            foreach($survey->getSections() as $key => $section){
                $checkMark = '';
                if (isset($_SESSION['section']) && $key == $_SESSION['section']){
                    $checkMark = ' <span class="glyphicon glyphicon-ok"></span>';
                }
                $showNavBar .= '<li><a href="?page=survey.section.questions&section=' . $key . '">' . $section->getName(/*$_SESSION['language']*/) . $checkMark . '</a></li>';
        
            }
        }
        $showNavBar .= '
          </ul>
        </li>

        <li class="dropdown">'; //language bar
        
        if (isset($_SESSION['language'])){
            $showNavBar .= '<a href="?page=language.setlanguage&language=' . $_SESSION['language'] . '" class="dropdown-toggle" data-hoover="dropdown" role="button" aria-haspopup="true" aria-expanded="false">' . $survey->getLanguage($_SESSION['language']);
        }
        else {            
            $showNavBar .= '<a href="#" class="dropdown-toggle" data-hoover="dropdown" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Language';
        }    
        $showNavBar .= ' <span class="caret"></span></a>
          <ul class="dropdown-menu">';
        foreach($this->survey->getLanguages() as $key => $language){
            $checkMark = '';
            if (isset($_SESSION['language']) && $key == $_SESSION['language']){
              $checkMark = ' <span class="glyphicon glyphicon-ok"></span>';
            }
            $showNavBar .= '<li><a href="?page=language.setlanguage&language=' . $key . '">' . $language . $checkMark . '</a></li>';
        }
        $showNavBar .= '
          </ul>
        </li>        
      </ul>

      <ul class="nav navbar-nav navbar-right">

      <form class="navbar-form navbar-left" role="search" method="post">
      <input type="hidden" name="page" value="search.res">
        <div class="form-group">
          <input type="text" class="form-control" placeholder="Search" name="search">
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
      </form>

<!-- <li><a href="#">Link</a></li> -->
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">';
        $showNavBar .= $_SESSION['username'];
        $showNavBar .= ' <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="#">Preferences</a></li>
            <li><a href="?page=other.data">Other data</a></li>
            <!-- <li><a href="#">Another action</a></li>
            <li><a href="#">Something else here</a></li> -->
            <li role="separator" class="divider"></li>
            <li><a href="?logout=1">Logout</a></li>
          </ul>
        </li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>';
        return $showNavBar;
        
}
    
    function showSettings($message = ''){

        echo '
        <ol class="breadcrumb">
  <li><a href="?page=surveys">Surveys</a></li>
  <li><a href="?page=survey">' . $this->survey->getName($_SESSION['language']) . '</a></li>
  <li class="active">' . 'Settings' . '</li>
</ol>';
        echo '<div class="well" style="background: white;">';
        
        echo $message;
        
        echo 'languages, encryption, templates, location, buttons';
        
    }

    
    function showSurveyMetaData($message = ''){
        echo '
        <ol class="breadcrumb">
  <li><a href="?page=surveys">Surveys</a></li>
  <li><a href="?page=survey">' . $this->survey->getName($_SESSION['language']) . '</a></li>
  <li class="active">' . 'Meta data' . '</li>
</ol>';
                echo mb_wordwrap('this is a very long <b>string</b> ah yes wee ais a very long string yes is a very long string yes yesyes', 25, '--<br/>-----');

        
        
        echo '<div class="well" style="background: white;">';
        
        echo $message;
        global $survey;
        $sections = $survey->getSections();
        $level = 0;
        $lastq = '';
        $condition = '';
        $output = '';
        $state = 0;
        foreach($sections as $section){
            $filename = prependPath($section->getRulesLocation());
            //echo $filename;
            $tokens = token_get_all(file_get_contents($filename));
            foreach ($tokens as $key=>$token){
                echo "<b>$key</b>:" . print_r($token);
                echo '<br/>';    
                
                if (is_array($token)){
                    echo '---' . token_name($token[0]) . '---<br/>';
                    if ($token[0] == T_IF) {
                        echo 'THIS IS AN IFFFFFF!';
                        $condition = 'IF ';
                        $state = 1;
                    }
                    elseif($token[0] == T_ELSE){
                        $output .= '<br/>ELSE ';
                    }
                    
                    if ($token[1] == 'ask'){
                        //question to ask!!
                        $output .= '<hr>ASK!!(' . $level . '):' . $lastq . '<hr>';
//$output .= substr($lastq, 1) . '<br/>';
                        $question = $survey->getQuestionOrGroup(substr($lastq, 1));
                        if ($question != null){
                           // $output .= $question;
    //                        $output .= '<br/>NOT NULL<br/>';
  //                          $output .= json_encode($question);
      //                      $output .= '-----<br/>----' . get_class($question) . '-----<br/>----';
        //                    $output .= 'end expr';
                            if ($question instanceof Question){
                              $output .= tagwrap($question->getQuestionText(), 25, '<br/>');
                              $output .= '<hr>';
                            }
                        }
                        
                    }
                    if ($token[1] == 'value'){
                    }
                    else if ($token[1] == '->'){

                    }
                    else { //question
                        if ($state == 1){
                            $condition .= ' ' . $token[1];
                        }
                        $lastq = $token[1];
                    }
                }
                else {  //level?
                  if ($token == '{'){
                      $state = 0;
                      $output .= '<b>' . $condition . '</b>'; //'IF???';
                      $output .= '<br/>';
                      $condition = '';
                      $level++;
                  }   
                  elseif ($token == '}'){
                      $output .= '<b>END</b>'; //'IF???';
                      $output .= '<br/>';
                      $condition = '';
                      $state = 0;
                      $level--;
                      
                  }
                }
                
                
            }
        }
        echo '<hr>OUTPUT<hr>';
        echo $output;
        
        echo '<hr><hr>';
        echo tagwrap('this is a very long string yes is a very long string yes is a very long string yes yesyes', 25, '--<br/>-----');
        
    }
  

    
    
    function showSurveyData($message = ''){

        echo '
        <ol class="breadcrumb">
  <li><a href="?page=surveys">Surveys</a></li>
  <li><a href="?page=survey">' . $this->survey->getName($_SESSION['language']) . '</a></li>
  <li class="active">' . 'Data' . '</li>
</ol>';
        echo '<div class="well" style="background: white;">';
        
        echo $message;
        
        
        echo 'Number of records: ';
        $db = $this->survey->getDatabase();
        echo $db->count();
        echo '<br/><br/>';

        echo '<div class="list-group">';
        echo '<a href="?page=survey.data.truncate" class="list-group-item" '. confirmAction('Are you sure you want to truncate the data? Type \\\'TRUNCATE\\\' to continue.', 'TRUNCATE') . '>Truncate data</a>';
        echo '<a href="?page=survey.data.stata" class="list-group-item">Export to stata</a>';
        echo '<a href="?page=survey.data.csv" class="list-group-item">Export to csv</a>';
        echo '</div>';
    }

    function showSurveys(){
        echo '
        <ol class="breadcrumb">
  <li class="active">Surveys</a></li>
                </ol>';
        
        echo '<div class="well" style="background: white;">';

        
        echo '<table class="table table-striped table-hover table-condensed">';
        echo '<tr><th>Name</th><th>Description</th><th>Observations</td></tr>';
        
        global $surveys;
        foreach($surveys as $key => $survey){
            echo '<tr><td><a href="?page=survey&syid=' . $key . '">' . $survey->getName($_SESSION['language']) . '</a></td><td>' . $survey->getDescription($_SESSION['language']) . '</td><td>' . $survey->getDatabase()->count() . '</td></tr>';
        }
        
        
    }
    
    function showSurvey(){

        echo '
        <ol class="breadcrumb">
  <li><a href="?page=surveys">Surveys</a></li>
  <li class="active">' . $this->survey->getName($_SESSION['language']) . '</li>
</ol>';
        echo '<div class="well" style="background: white;">';


        
        
        
        echo '<div class="list-group">';
        echo '<a href="?page=survey.settings" class="list-group-item">Settings</a>';
        echo '<a href="?page=survey.sections" class="list-group-item">Sections</a>';
        echo '<a href="?page=survey.metadata" class="list-group-item">Meta data</a>';
        echo '<a href="?page=survey.data" class="list-group-item">Data</a>';
        echo '</div>';
        
    }
    
    function showSections(){
        echo '
        <ol class="breadcrumb">
  <li><a href="?page=surveys">Surveys</a></li>
  <li><a href="?page=survey">' . $this->survey->getName($_SESSION['language']) . '</a></li>
  <li class="active">' . 'Sections' . '</li>
</ol>';
        echo '<div class="well" style="background: white;">';
        

        echo '<table class="table table-striped table-hover table-condensed">';
        echo '<tr><th>Name</th><th>Description</th></tr>';
        
        foreach($this->survey->getSections() as $name => $section){
            echo '<tr><td><a href="?page=survey.section.questions&section=' . $name . '">' . $name . '</a></td><td>' . $section->getDescription() . '</td></tr>';
        }
        echo '</table>';        
        
    }
    
    function showSectionQuestionRulesHeader($subIndex = 1){
        $return = '';
        $section = $this->survey->getSection($_SESSION['section']);
        $return .= '
        <ol class="breadcrumb">
  <li><a href="?page=surveys">Surveys</a></li>
  <li><a href="?page=survey">' . $this->survey->getName($_SESSION['language']) . '</a></li>
  <li><a href="?page=survey.sections">' . 'Sections' . '</a></li>
<li class="active">' . $section->getName() . '</li>
</ol>';
        $return .= '<table><tr>';
        $return .= '<td>';
        $label = ($subIndex == 1)? $label = 'primary' : 'default';
        $return .= '<a href="?page=survey.section.questions" style="text-decoration:none;"><span class="label label-' . $label .'">Questions</span></a>';

        $return .= '</td><td>&nbsp;';
        $label = ($subIndex == 2)? $label = 'primary' : 'default';
        $return .= '<a href="?page=survey.section.rules" style="text-decoration:none;"><span class="label label-' . $label . '">Rules</span></a>';

        $return .= '</td><td>&nbsp;';
        $label = ($subIndex == 3)? $label = 'primary' : 'default';
        $return .= '<a href="?page=survey.section.groups" style="text-decoration:none;"><span class="label label-' . $label . '">Groups</span></a>';
        
        
        $return .= '</td><td>&nbsp;';
        $label = ($subIndex == 4)? $label = 'primary' : 'default';
        $return .= '<a href="?page=survey.section.templates" style="text-decoration:none;"><span class="label label-' . $label . '">Templates</span></a>';

                $return .= '</td><td>&nbsp;';
        $label = ($subIndex == 5)? $label = 'primary' : 'default';
        $return .= '<a href="?page=survey.section.types" style="text-decoration:none;"><span class="label label-' . $label . '">Types</span></a>';

        
        
        $return .= '</td></tr></table>';
        return $return;
        
    }
    
    function showSectionRules($message = ''){
        echo $this->showSectionQuestionRulesHeader(2);
        $section = $this->survey->getSection($_SESSION['section']);
        echo '<div class="well" style="background: white;">'; 
        
        echo $message;

        $filename = prependPath($section->getRulesLocation());
        if (is_writable($filename)){
            echo '<form method=post>';
            echo '<input type=hidden name=page value="survey.section.rules.save">';
            echo '<textarea name="rules" class="form-control" rows="18">';
            echo file_get_contents(prependPath($section->getRulesLocation()));
            echo '</textarea>';
//            echo '<input type=submit class="btn btn-default">';
//            echo '</form>';

            
            
        }
        else { //just show
          $routing = highlight_string(file_get_contents(prependPath($section->getRulesLocation())), true);
          echo $routing;
        }
        echo $this->displayWhenWritable(prependPath($section->getRulesLocation()), '<input type=submit class="btn btn-default"></form>', 'rules');        
        
    }
    
    
    
    function showSectionQuestions($message = ''){
        echo $this->showSectionQuestionRulesHeader(1);

        
        echo '<div class="well" style="background: white;">';        
       
        echo $message;
        
        $section = $this->survey->getSection($_SESSION['section']);

        echo '<table class="table table-striped table-hover table-condensed">';
        echo '<tr><th>Name</th><th>Description</th><th>Question text</th><th>Question type</th><th>Array</th></tr>';
        foreach($section->getQuestions() as $name => $question){
            echo '<tr><td>';
            echo '<a href="?page=survey.section.question&question=' . $name . '">' . $name . '</a>';
            echo '</td><td>' . $question->getDescription() . '</td><td>' . $question->getQuestionText($_SESSION['language']) . '</td><td>';
            echo array_search($question->getQuestionType()->getAnswerType(), returnConstants("QUESTION_TYPE_"));
            echo '</td><td>';
            foreach($question->getArray() as $array){
                echo '[' . $array[0] . '..' . $array[1] . ']';
            }            
            echo '</td></tr>';
        }
        echo '</table>';
        echo '<hr>';
        echo $this->displayWhenWritable(prependPath($section->getQuestionLocation()), '<a href="?page=survey.section.questions.add">add question</a>', 'questions');
    }

    function displayWhenWritable($filename, $link = '', $type = ''){
        if (is_writable($filename)){
           if ($link != ''){
             echo $link . '<hr>';
           }
           echo '<div class="alert alert-warning" role="alert">Make sure to change the file permissions when the survey is ready.</div>';
        }
        else {
            echo '<div class="alert alert-info" role="alert">No access to the ' . $type . ' file.</div>';
        }
    }    
    
    
    
    function showTemplates($message = ''){
        echo $this->showSectionQuestionRulesHeader(4);
        $section = $this->survey->getSection($_SESSION['section']);
        echo '<div class="well" style="background: white;">'; 
        echo $message;
        echo '<table class="table table-striped table-hover table-condensed">';
        echo '<tr><th>Name</th><th>Type</th></tr>';
        global $survey;
        foreach ($survey->getTemplates() as $name => $template){
            echo '<tr><td>';
            echo '<a href="?page=survey.section.template&template=' . $name . '">' . $name . '</a>';
            echo '</td><td>';
            echo array_search($template->getType(), returnConstants("TEMPLATE_"));
            echo '</td></tr>';
        }
        echo '</table>';
        echo '<hr>';
        echo $this->displayWhenWritable(prependPath($this->survey->getTemplatesLocation()), '<a href="?page=survey.section.templates.add">add template</a>', 'templates');
    }

    function showSectionGroups($message = ''){
        echo $this->showSectionQuestionRulesHeader(3);
        $section = $this->survey->getSection($_SESSION['section']);
        echo '<div class="well" style="background: white;">'; 

        echo $message;
        
        global $survey;
        if (sizeof($section->getQuestionGroups()) > 0){
            echo '<table class="table table-striped table-hover table-condensed">';
            echo '<tr><th>Name</th></tr>';
            foreach ($section->getQuestionGroups() as $name => $questionGroup){
                echo '<tr><td>';
                echo '<a href="?page=survey.section.group&questiongroup=' . $name . '">' . $name . '</a>';
    //            echo '</td><td>';
    //            echo array_search($type->getAnswerType(), returnConstants("QUESTION_TYPE_"));
    //            echo '</td><td>';
    //            echo $type->getDetailsAsString();
                echo '</td></tr>';
            }
            echo '</table>';
        }
        else {
            echo '<div class="alert alert-info" role="alert">No groups in this section.</div>';
        }
        echo '<hr>';
        echo $this->displayWhenWritable(prependPath($section->getQuestionLocation()), '<a href="?page=survey.section.groups.add">add group</a>', 'groups');
    }
    
    function showTypes($message = ''){
        echo $this->showSectionQuestionRulesHeader(5);
        $section = $this->survey->getSection($_SESSION['section']);
        echo '<div class="well" style="background: white;">'; 

        echo $message;
        
        echo '<table class="table table-striped table-hover table-condensed">';
        echo '<tr><th>Name</th><th>Type</th><th>Details</th></tr>';
        global $survey;
        foreach ($survey->getTypes() as $name => $type){
            echo '<tr><td>';
            echo '<a href="?page=survey.section.type&type=' . $name . '">' . $name . '</a>';
            echo '</td><td>';
            echo array_search($type->getAnswerType(), returnConstants("QUESTION_TYPE_"));
            echo '</td><td>';
            echo $type->getDetailsAsString();
            echo '</td></tr>';
        }
        echo '</table>';
        echo '<hr>';
        echo $this->displayWhenWritable(prependPath($this->survey->getTypesLocation()), '<a href="?page=survey.section.types.add">add type</a>', 'types');
    }

    function showLogin($message = ''){
        echo $this->showHeader();
        
        echo '<div class="wrapper" style="width:300px; margin:0 auto;">';
        echo $message;
        echo '<h3 class="form-signin-heading">Please login</h3>
      <center>
      <form class="form-signin" method=post>       
      <input type="text" class="form-control" name="username" placeholder="Username" required="" autofocus="" />
      <input type="password" class="form-control" name="password" placeholder="Password" required=""/>      
      <br/>
      <button class="btn btn-default btn-block" type="submit">Login</button>   
    </form>
    </center>
  </div>';
        echo $this->showFooter();
        
        //return $showLogin;
        
    }
    
    function showTypeAdd(){
        $section = $this->survey->getSection($_SESSION['section']);
        echo '
        <ol class="breadcrumb">
  <li><a href="?page=surveys">Surveys</a></li>
  <li><a href="?page=survey">' . $this->survey->getName($_SESSION['language']) . '</a></li>
  <li><a href="?page=survey.sections">' . 'Sections' . '</a></li>
<li class="active"><a href="?page=survey.section.types">' . $section->getName() . '</a></li>
<li class="active">' . 'Add type' . '</li>
</ol>';
        $this->showTypeEdit($TNone = new Type('', QUESTION_TYPE_NONE, ''));
    }
    
    function showType(){
        $section = $this->survey->getSection($_SESSION['section']);
        $type = $this->survey->getType($_SESSION['type']);
        echo '
        <ol class="breadcrumb">
  <li><a href="?page=surveys">Surveys</a></li>
  <li><a href="?page=survey">' . $this->survey->getName($_SESSION['language']) . '</a></li>
  <li><a href="?page=survey.sections">' . 'Sections' . '</a></li>
<li class="active"><a href="?page=survey.section.types">' . $section->getName() . '</a></li>
<li class="active">' . $type->getName() . '</li>
</ol>';
        $this->showTypeEdit($type);       
        
    }
    
    function showTypeEdit($type){
        echo '<div class="well" style="background: white;">';        

        echo '<form method=post>';
        echo '<input type=hidden name=page value="survey.section.type.save">';

        echo '<table style="border-spacing: 5px; border-collapse: separate;">';
        
        echo '<tr><td width=100px>Name</td><td><input type=text class="form-control" name="name" value="' . $type->getName() . '"></td></tr>';
        echo '<tr><td>Type</td><td>';
        echo '<select name="answertype" class="form-control">';
        foreach(returnConstants("QUESTION_TYPE_") as $constant=>$key){
            $selected = ($key == $type->getAnswerType()) ? ' SELECTED' : '';
            echo '<option value="' . $key . '"' . $selected . '>' . $constant . '</option>';
        }
        echo '</select>';
        echo '</td></tr>';

        echo '<tr><td valign=top>Details</td><td><textarea name="details" class="form-control" cols=50 rows=4>' . $type->getDetailsAsString() . '</textarea></td></tr>';
       
        echo '</td></tr>';

        
        
        
        echo '<tr><td valign=top>Template</td><td>';
        
        echo '<select name=template class="form-control">';
        global $survey;
        foreach ($survey->getTemplates() as $name => $template){
            if ($template->getType() == TEMPLATE_TYPE){
                $selected = ($type->getTemplate()->getName() == $name) ? ' SELECTED' : '';
                echo '<option value="' . $name . '"' . $selected . '>' . $name . '</option>';
            }
        }
        echo '</select>';
        echo '</td></tr>';

        echo '<tr><td valign=top>Buttons</td><td>';

        echo '<table width=100%>';
        echo '<tr><td>';
        $checked = ($type->showDKButton()) ? ' CHECKED' : '';
        echo '<label style="font-weight: normal !important;"><input type="checkbox" name="dkbutton" value="1"'. $checked . '> Show DK button</label>';
        echo '</td><td>';
        $checked = ($type->showRFButton()) ? ' CHECKED' : '';
        echo '<label style="font-weight: normal !important;"><input type="checkbox" name="rfbutton" value="1"'. $checked . '> Show RF button</label>';
        echo '</td></tr></table>';
        
        echo '</td></tr>';
        
        echo '<tr><td width=100px>Validation</td><td>';
        echo '<select name="validationoption" class="form-control">';
        foreach(returnConstants("VALIDATION_OPTION_") as $constant=>$key){
            $selected = ($key == $type->getValidationOption()) ? ' SELECTED' : '';
            echo '<option value="' . $key . '"' . $selected . '>' . $constant . '</option>';
        }
        echo '</select>';
        echo '</td></tr>';        
        
        
        
        echo '</table>';
        echo $this->displayWhenWritable(prependPath($this->survey->getTypesLocation()), '<input type=submit class="btn btn-default">', 'types');        
        echo '</form>';
        
    }
    
    function showTemplateAdd(){
        $section = $this->survey->getSection($_SESSION['section']);
        echo '
        <ol class="breadcrumb">
  <li><a href="?page=surveys">Surveys</a></li>
  <li><a href="?page=survey">' . $this->survey->getName($_SESSION['language']) . '</a></li>
  <li><a href="?page=survey.sections">' . 'Sections' . '</a></li>
<li class="active"><a href="?page=survey.section.templates">' . $section->getName() . '</a></li>
<li class="active">' . 'Add template' . '</li>
</ol>';        
        $this->showTemplateEdit(new Template('', TEMPLATE_QUESTION, 'questiontemplate', ''));
    }
    
    function showTemplate(){
        $section = $this->survey->getSection($_SESSION['section']);
        $template = $this->survey->getTemplate($_SESSION['template']);
        echo '
        <ol class="breadcrumb">
  <li><a href="?page=surveys">Surveys</a></li>
  <li><a href="?page=survey">' . $this->survey->getName($_SESSION['language']) . '</a></li>
  <li><a href="?page=survey.sections">' . 'Sections' . '</a></li>
<li class="active"><a href="?page=survey.section.templates">' . $section->getName() . '</a></li>
<li class="active">' . $template->getName() . '</li>
</ol>';
        $this->showTemplateEdit($template);
    }
    
    function showTemplateEdit($template){
        echo '<div class="well" style="background: white;">';        

        echo '<form method=post>';
        echo '<input type=hidden name=page value="survey.section.template.save">';

        echo '<table style="border-spacing: 5px; border-collapse: separate;">';
        
        echo '<tr><td width=100px>Name</td><td><input type=text class="form-control" name="name" value="' . $template->getName() . '"></td></tr>';
        echo '<tr><td width=100px>Type</td><td>';
        echo '<select name="type" class="form-control">';
        foreach(returnConstants("TEMPLATE_") as $constant=>$key){
            $selected = ($key == $template->getType()) ? ' SELECTED' : '';
            echo '<option value="' . $key . '"' . $selected . '>' . $constant . '</option>';
        }
        echo '</select>';
        echo '</td></tr>';

        echo '<tr><td width=100px>Location</td><td><input type=text class="form-control" name="location" value="' . $template->getLocation() . '"></td></tr>';
        echo '<tr><td valign=top>Content</td><td><textarea name="content" class="form-control" cols=50 rows=4>' . $template->getContent() . '</textarea></td></tr>';
        
        
        echo '</table>';
        echo $this->displayWhenWritable(prependPath($this->survey->getTemplatesLocation()), '<input type=submit class="btn btn-default">', 'templates');
        echo '</form>';
        
    }
    
    function showGroupAdd(){
        global $survey;
        $section = $this->survey->getSection($_SESSION['section']);
        
        echo '
        <ol class="breadcrumb">
  <li><a href="?page=surveys">Surveys</a></li>
  <li><a href="?page=survey">' . $this->survey->getName($_SESSION['language']) . '</a></li>
  <li><a href="?page=survey.sections">' . 'Sections' . '</a></li>
<li class="active"><a href="?page=survey.section.groups">' . $section->getName() . '</a></li>
<li class="active">' . 'Add group' . '</li>
</ol>';
        $this->showSectionGroupEdit(new QuestionGroup('', array(), $survey->getTemplate(GROUP_STANDARD_TEMPLATE)));
    }
    
    function showSectionGroup(){
        $section = $this->survey->getSection($_SESSION['section']);
        $questiongroup = $section->getQuestionGroup($_SESSION['questiongroup']);
        
        echo '
        <ol class="breadcrumb">
  <li><a href="?page=surveys">Surveys</a></li>
  <li><a href="?page=survey">' . $this->survey->getName($_SESSION['language']) . '</a></li>
  <li><a href="?page=survey.sections">' . 'Sections' . '</a></li>
<li class="active"><a href="?page=survey.section.groups">' . $section->getName() . '</a></li>
<li class="active">' . $questiongroup->getName() . '</li>
</ol>';
        $this->showSectionGroupEdit($questiongroup);

    }
    
    function showSectionGroupEdit($questiongroup){
        
        echo '<div class="well" style="background: white;">';        

        echo '<form method=post>';
        
        echo '<input type=hidden name=page value="survey.section.group.save">';
        echo '<table style="border-spacing: 5px; border-collapse: separate;">';

        echo '<tr><td width=100px>Name</td><td><input type=text class="form-control" name="name" value="' . $questiongroup->getName() . '"></td></tr>';
        echo '<tr><td valign=top>Rules</td><td><textarea name="rules" class="form-control" cols=50 rows=4>' . $questiongroup->getRules() . '</textarea></td></tr>';
        echo '<tr><td valign=top>Template</td><td>';
        echo '<select name="template" class="form-control">';
        global $survey;
        foreach ($survey->getTemplates() as $name => $template){
            $selected = ($questiongroup->getTemplate()->getName() == $name) ? ' SELECTED' : '';
            if ($template->getType() == TEMPLATE_GROUP){
                echo '<option value="' . $name . '">' . $name . '</option>';
            }
        }
        echo '</select>';
        echo '</td></tr>';
        echo '</table>';
        
        $section = $this->survey->getSection($_SESSION['section']);
        echo $this->displayWhenWritable(prependPath($section->getQuestionLocation()), '<input type=submit class="btn btn-default">', 'groups');

        echo '</form>';        
        
    }
    
    function ShowSectionQuestionAdd(){
        $section = $this->survey->getSection($_SESSION['section']);

        echo '
        <ol class="breadcrumb">
  <li><a href="?page=surveys">Surveys</a></li>
  <li><a href="?page=survey">' . $this->survey->getName($_SESSION['language']) . '</a></li>
  <li><a href="?page=survey.sections">' . 'Sections' . '</a></li>
<li class="active"><a href="?page=survey.section.questions">' . $section->getName() . '</a></li>
<li class="active">' . 'Add question' . '</li>
</ol>';
        $this->showSectionQuestionEdit(new Question('','',array(),new Type('TNone', QUESTION_TYPE_NONE)));
    }

    function showSectionQuestion(){
        $section = $this->survey->getSection($_SESSION['section']);
        $question = $section->getQuestion($_SESSION['question']);
        echo '
        <ol class="breadcrumb">
  <li><a href="?page=surveys">Surveys</a></li>
  <li><a href="?page=survey">' . $this->survey->getName($_SESSION['language']) . '</a></li>
  <li><a href="?page=survey.sections">' . 'Sections' . '</a></li>
<li class="active"><a href="?page=survey.section.questions">' . $section->getName() . '</a></li>
<li class="active">' . $question->getName() . '</li>
</ol>';
        $this->showSectionQuestionEdit($question);
    }
    
    function showSectionQuestionEdit($question){
     
        
        echo '<div class="well" style="background: white;">';        

        echo '<form method=post>';
        echo '<input type=hidden name=page value="survey.section.question.save">';
        echo '<table style="border-spacing: 5px; border-collapse: separate;">';

        echo '<tr><td width=100px>Name</td><td><input type=text class="form-control" name="name" value="' . $question->getName() . '"></td></tr>';
        echo '<tr><td>Description</td><td><input type=text class="form-control" name="description" value="' . $question->getDescription() . '"></td></tr>';
        echo '<tr><td valign=top>Text</td><td><textarea name="questiontext" class="form-control" cols=50 rows=4>' . $question->getQuestionText($_SESSION['language']) . '</textarea></td></tr>';

        
        echo '<tr><td valign=top>Type</td><td>';

        echo '<select name=questiontype class="form-control">';
        global $survey;
        foreach ($survey->getTypes() as $name => $type){
            $selected = ($question->getQuestionType()->getName() == $name) ? ' SELECTED' : '';
            echo '<option value="' . $name . '"' . $selected . '>' . $name . '</option>';
        }
        
        echo '</select>';
        echo '</td></tr>';
        
        
        echo '<tr><td valign=top>Template</td><td>';
        echo '<select name="template" class="form-control">';
        global $survey;
        foreach ($survey->getTemplates() as $name => $template){
            
            if ($template->getType() == TEMPLATE_QUESTION){
                echo '<option value="' . $name . '">' . $name . '</option>';
            }
        }
        
        echo '</select>';
        echo '</td></tr>';
        echo '</table>';

        $section = $this->survey->getSection($_SESSION['section']);
        
        echo $this->displayWhenWritable(prependPath($section->getQuestionLocation()), '<input type=submit class="btn btn-default">', 'questions');
        echo '</form>';
        
    }
    
    
    function showOtherData(){
        
        echo '
        <ol class="breadcrumb">
              <li class="active">Other data</a></li>
        </ol>';
        
        echo '<div class="well" style="background: white;">';        

        echo 'Download records field as csv: <br/>';
        global $survey;
        $tables = $survey->db->getAllTablesInDb();
	foreach($tables as $table){
              echo '<a href=?page=other.data.download&t=' . $table . '>' . $table . '</a><br/>';
	}
    }
    
    function showSearchRes($searchTerm){
        
        echo '
        <ol class="breadcrumb">
              <li class="active">Search results</a></li>
        </ol>';
        echo '<div class="well" style="background: white;">';        

        if ($searchTerm == ''){
            echo '<div class="alert alert-warning" role="alert">Please enter a search term.</div>';
        }
        else {
            echo 'Results for "<b>' . $searchTerm . '</b>:<hr>';
            global $survey;
            $questions = $survey->getQuestionOrGroup($searchTerm);
            foreach($questions as $question){
                if ($question instanceof Question){
                  echo $question->getName() . '<br/>';
                }
            }
            echo '<br>DONE<br/>';
        }
        
        
    }
    
}

?>
