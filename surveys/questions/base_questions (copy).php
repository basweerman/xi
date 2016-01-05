<?php
//xi questions (admin generated)
$primkey = new Question('primkey', 'test string', array ( 1 => 'Dit is de empty test'), $TString);

$language = new Question('language', 'survey language', array ( ), $TString);

$browserinfo = new Question('browserinfo', 'browser information', array ( ), $TString);

$startts = new Question('startts', 'survey start timestamp', array ( ), $TString);

$endts = new Question('endts', 'survey end timestamp', array ( ), $TString);

$q_intro = new Question('q_intro', 'description intro', array ( 1 => 'welcome to this survey. Press next to continue!', 2 => 'Welkom bij deze eerste vragenlijst. Kies verder om door te gaan!'), $TNone);

$q1 = new Question('q1', 'description1', array ( 1 => 'question 1 what animal do you like!'), $TAnimals);

$PA1 = new Question('PA1', 'description1', array ( 1 => 'question 1 what animal do you like!'), $TAnimals);

$PA2 = new Question('PA2', 'dasd', array ( 1 => 'asdsd', 2 => 'dra a'), $TNone);

$bastest = new Question('bastest', '', array ( 1 => ''), $TString);

$a1 = new Question('a1', '', array ( ), $TNone);

$b1 = new Question('b1', 'description yes', array ( 1 => 'Don\'t know why'), $TNone);

$teststring = new Question('teststring', 'test string question', array ( 1 => 'Use this to test remind once to answer'), $TOpen);

//xi groups (admin generated)
$PAGroup = new QuestionGroup('PAGroup', '$PA1->ask(); $PA2->ask();', $TStandardTemplate);

?>