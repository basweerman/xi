<?php

$TYesNo = new Type('TYesNo', QUESTION_TYPE_ENUMERATED, array(1 => array(1 => 'Yes'), 2 => array( 1 => 'No')));


$TYesNoRow = new Template('$TYesNoRow', 'tyesnowrow', '<td align=center><div class="radio"><label><input type="radio" name="<questionName />" value="<optionKey />"<checked />></label></div></td>');

$TYesNo->setEnumeratedTemplate($TYesNoRow); //new Template('tyesnowrow', '<td align=center><div class="radio"><label><input type="radio" name="<questionName />" value="<optionKey />"<checked />></label></div></td>'));
$TYesNoReg = new Type('TYesNoReg', QUESTION_TYPE_SETOF, array(1 => array(1 => 'Yes'), 2 => array( 1 => 'No')));
$TYesNoReg->setShowDKButton(true);
$TYesNoReg->setShowRFButton(true);

$TAnimals = new Type('TAnimals', QUESTION_TYPE_SETOF, array(1 => array(1 => 'Horse'), 2 => array( 1 => 'Mouse'), 3 => array(1 => 'Rat')));
$TAnimals->setShowDKButton(true);
$TAnimals->setShowRFButton(true);

$TString = new Type('TString', QUESTION_TYPE_STRING);
$TString->setInputWidth(300);
$TString->setShowDKButton(true);
$TString->setShowRFButton(true);

$TOpen = new Type('TOpen', QUESTION_TYPE_OPEN);
$TOpen->setTextTemplate($TOpenTemplate); //new Template('opentemplate'));
$TOpen->setInputWidth('100%');
$TOpen->setInputHeight('120px');
$TOpen->setShowDKButton(true);
$TOpen->setShowRFButton(true);


$TDropdown = new Type('TDropdown', QUESTION_TYPE_SELECT, array(1 => array(1 => 'Horse'), 2 => array( 1 => 'Mouse'), 3 => array(1 => 'Rat')));
$TDropdown->setEnumeratedTemplate($TSelectTemplate); //new Template('selecttemplate'));
$TDropdown->setShowDKButton(true);
$TDropdown->setShowRFButton(true);



$TNone = new Type('TNone', QUESTION_TYPE_NONE);
$TRange4to100 = new Type('TRange4to100', QUESTION_TYPE_RANGE, array(4, 100));
$TRange4to100->setShowDKButton(true);
$TRange4to100->setShowRFButton(true);

        

$primkey = new Question('primkey', 'survey primary key', array(), $TString);
$language = new Question('language', 'survey language', array(), $TString);

$startts = new Question('startts', 'survey start timestamp', array(), $TString);
$endts = new Question('endts', 'survey end timestamp', array(), $TString);

$startsurvey = new Question('startsurvey', 'description intro', array(1=>'welcome to this survey. Press next to contiune!', 2=>'Welkom bij deze eerste vragenlijst. Kies verder om door te gaan!'), $TNone);
$q1 = new Question('q1', 'description1', array(1=>'question 1 what animal do you like!'), $TAnimals);


$opentest = new Question('opentest', 'open test', array(1=>'This is to test open'), $TOpen);
$selecttest = new Question('selecttest', 'dropdown test', array(1=>'This is to test dropdown'), $TDropdown);


for ($i = 1; $i < 5; $i++){
  for ($i2 = 3; $i2 < 6; $i2++){
     $q2[$i][$i2] = new Question('q2[' . $i . '][' . $i2 . ']', 'description2', array(1=>'This is question 2 ' . $i . '..' . $i2), $TYesNoReg);
     $q11[$i][$i2] = new Question('q11[' . $i . '][' . $i2 . ']', 'description3', array(1=>'This is question 11 on same screen 2 ' . $i . '..' . $i2), $TString);
     $forq2q3group[$i][$i2] = new QuestionGroup('forq2q3group[' . $i . '][' . $i2 . ']', array($q2[$i][$i2], $q11[$i][$i2]), $TStandardTemplate);
   }
}

//$PA001Group = new QuestionGroup('PA001Group', array($PA001, $PA002));

$PA001_intro = new Question('PA001_intro', 'pa 001', array(1=>'Please select yes no below'), $TNone);
$PA001 = new Question('PA001', 'pa 001', array(1=>'this is the first in the group'), $TYesNo);
$PA001->setTemplate(new Template('row', '<tr><td><errorMessage /><questionText /></td><answerOption /></tr>'));
$PA002 = new Question('PA002', 'pa 002', array(1=>'this is the second in the group'), $TYesNo);
$PA002->setTemplate(new Template('row', '<tr><td><errorMessage /><questionText /></td><answerOption /></tr>'));
$PA003 = new Question('PA003', 'pa 003', array(1=>'this is the third in the group'), $TYesNo);
$PA003->setTemplate(new Template('row', '<tr><td><errorMessage /><questionText /></td><answerOption /></tr>'));
$PA002Group = new QuestionGroup('PA002Group', array($PA001, $PA002, $PA003), $TYesNoTemplate);
$PA001Group = new QuestionGroup('PA001Group', array($PA001_intro, $PA002Group), $TStandardTemplate);


$q3 = new Question('q3', 'description2', array(1=>'This is question 3! Fill:' . showFill($q1) . 'end fill'), $TRange4to100);
$q4 = new Question('q4', 'description2', array(1=>'This is the last question!--' . showFill($q1) . '--end fill' ), $TNone);
//$q4->setShowNextButton(false);

$willing = new Question('willing', 'temp var', array(), $TString);
$qi = new Question('i', 'temp var', array(), $TString);
$qi2 = new Question('i2', 'temp var', array(), $TString);




?>
