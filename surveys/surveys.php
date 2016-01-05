<?php

//Define admin users first!
$users = array();
$users[1] = new User('admin', 'admin');


//Define surveys
$surveys = array();

//Survey 1
$i = 1;
$surveys[$i] = new Survey();
$surveys[$i]->setUsers($users);
$surveys[$i]->setLanguages(array(1=>'English', 2=>'Dutch'));
$surveys[$i]->setName(array(1 => 'Xi survey', 2 => 'Xi vragenlijst'));
$surveys[$i]->setDescription(array(1 => 'a first xi test', 2 => 'Een eerste xi test'));
$surveys[$i]->setSurveyTemplate(array(1 => new Template('surveytemplate', TEMPLATE_SURVEY, 'surveytemplate'), 2 => new Template('surveytemplate', TEMPLATE_SURVEY, 'surveytemplate')));
//$surveys[$i]->setEncryptionKey('hallo');

//set the database
$surveys[$i]->setDatabase(new Database(DATABASE_MYSQL, 'localhost', 'root', '{passwordhere}', 'xi', 'data', 'log'));

//set the buttons
$surveys[$i]->setBackButton(array(
        1=>'<input class="btn btn-default" type="button" id="backbutton" value="<< Back" onclick="myCall(this, 1)"/>',
        2=>'<input class="btn btn-default" type="button" id="backbutton" value="<< Terug" onclick="myCall(this, 1)"/>'));  
$surveys[$i]->setNextButton(array(
        1=>'<input class="btn btn-default" type="button" id="nextbutton" value="Next >>" onclick="myCall(this, 2)"/>', 
        2=>'<input class="btn btn-default" type="button" id="nextbutton" value="Verder >>" onclick="myCall(this, 2)"/>'));  
$surveys[$i]->setRFButton(array(
        1=>'<input class="btn btn-default btn_dkrf<class />" <style />type="button" id="<questionName />_button" value="RF" onclick="myCall(this, \'.r\')"/>',
        2=>'<input class="btn btn-default btn_dkrf<class />" <style />type="button" id="<questionName />_button" value="Weiger" onclick="myCall(this, \'.r\')"/>'));  
$surveys[$i]->setDKButton(array(
        1=>'<input class="btn btn-default btn_dkrf<class />" <style />type="button" id="<questionName />_button" value="DK" onclick="myCall(this, \'.d\')"/>',
        2=>'<input class="btn btn-default btn_dkrf<class />" <style />type="button" id="<questionName />_button" value="Weet niet" onclick="myCall(this, \'.d\')"/>'));  
//show the buttons
$surveys[$i]->setShowDKButton(false);
$surveys[$i]->setShowRFButton(false);

//set locations for templates and types
$surveys[$i]->setTypesLocation('types/types.php');
$surveys[$i]->setTemplatesLocation('templates/templates.php');

//add sections (including locations for questions and rules)
$surveys[$i]->addSection(new Section('base', 'Base module', 'questions/base_questions.php', 'rules/base_rules.php'));
$surveys[$i]->addSection(new Section('demo', 'Demographics module', 'questions/demo_questions.php', 'rules/demo_rules.php'));



?>
