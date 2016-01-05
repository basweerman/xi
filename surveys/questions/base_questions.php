<?php
//xi questions (admin generated)
$primkey = new Question('primkey', 'test string', array ( 1 => 'Dit is de empty test'), $TString);

$language = new Question('language', 'survey language', array ( ), $TString);

$browserinfo = new Question('browserinfo', 'browser information', array ( ), $TString);

$startts = new Question('startts', 'survey start timestamp', array ( ), $TString);

$endts = new Question('endts', 'survey end timestamp', array ( ), $TString);

$interviewLanguage = new Question('interviewLanguage', 'description intro', array ( 1 => 'IWER: Please select a survey language.', 2 => 'Welkom bij deze eerste vragenlijst. Kies verder om door te gaan!'), $TLanguage);


$survey_intro = new Question('survey_intro', 'description intro a', array ( 1 => 'welcome to this survey. Press next to continue!', 2 => 'Welkom bij deze eerste vragenlijst. Kies verder om door te gaan!'), $TNone);
$survey_intro->setShowBackButton(false);




?>