<?php
//xi types (admin generated)
$TNone = new Type('TNone', QUESTION_TYPE_NONE, '');
$TNone->setTemplate($TTextTemplate);

$TString = new Type('TString', QUESTION_TYPE_STRING, '');
$TString->setTemplate($TTextTemplate);
$TString->setShowDKButton(true);
$TString->setShowRFButton(true);
$TString->setInputWidth('300');

$TOpen = new Type('TOpen', QUESTION_TYPE_OPEN, '');
$TOpen->setTemplate($TOpenTemplate);
$TOpen->setShowDKButton(true);
$TOpen->setShowRFButton(true);
$TOpen->setInputWidth('100%');
$TOpen->setInputHeight('120px');
$TOpen->setValidationOption(VALIDATION_OPTION_REQUEST_ONE_TIME);

$TYesNo = new Type('TYesNo', QUESTION_TYPE_ENUMERATED, array ( 1 => array ( 1 => 'Yes', 2 => 'No'), 2 => array ( 1 => 'Ja', 2 => 'Nee')));
$TYesNo->setTemplate($TEnumeratedTemplate);

$TInteger = new Type('TInteger', QUESTION_TYPE_INTEGER, '');

$TRange1900to2015 = new Type('TRange1900to2015', QUESTION_TYPE_RANGE, array(1900, 2015));
$TRange1900to2015->setTemplate($TTextTemplate);
$TRange1900to2015->setShowDKButton(true);
$TRange1900to2015->setShowRFButton(true);


$TRange1to15 = new Type('TRange1to15', QUESTION_TYPE_RANGE, array(1, 15));
$TRange0to100 = new Type('TRange0to100', QUESTION_TYPE_RANGE, array(1, 100));


$TYesNoReg = new Type('TYesNoReg', QUESTION_TYPE_RANGE, array(0, 100));
$TYesNoReg->setTemplate($TTextTemplate);
$TYesNoReg->setShowDKButton(true);
$TYesNoReg->setShowRFButton(true);
$TYesNoReg->setValidationOption(VALIDATION_OPTION_ALLOW_CONTINUE);

$TAnimals = new Type('TAnimals', QUESTION_TYPE_SETOF, array ( 1 => array ( 1 => 'Horse', 2 => 'Mouse', 3 => 'Rat', 4 => 'Cow', 5 => 'Elephant'), 2 => array ( 1 => 'Paard', 2 => 'Muis', 3 => 'Rat', 4 => 'Koe', 5 => 'Olifant')));
$TAnimals->setTemplate($TEnumeratedTemplate);
$TAnimals->setShowDKButton(true);
$TAnimals->setShowRFButton(true);
$TAnimals->setValidationOption(VALIDATION_OPTION_REQUEST_ONE_TIME);

$TDropdown = new Type('TDropdown', QUESTION_TYPE_SELECT, array ( 1 => array ( 1 => 'Horse', 2 => 'Mouse', 3 => 'Rat', 4 => 'Cow', 5 => 'Elephant'), 2 => array ( 1 => 'Paard', 2 => 'Muis', 3 => 'Rat', 4 => 'Koe', 5 => 'Olifant')));
$TDropdown->setTemplate($TSelectTemplate);
$TDropdown->setShowDKButton(true);
$TDropdown->setShowRFButton(true);

$TRate1to5 = new Type('TRate1to5', QUESTION_TYPE_ENUMERATED, array ( 1 => array ( 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'), 2 => array ( 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5')));
$TRate1to5->setTemplate($TRowTemplate);


$TBasTest = new Type('TBasTest', QUESTION_TYPE_RANGE, array(0, 100));
$TBasTest->setTemplate($TTextTemplate);
$TBasTest->setShowDKButton(true);

$TBasTest2 = new Type('TBasTest2', QUESTION_TYPE_STRING, '');
$TBasTest2->setTemplate($TTextTemplate);
$TBasTest2->setShowDKButton(true);
$TBasTest2->setShowRFButton(true);
$TBasTest2->setValidationOption(VALIDATION_OPTION_REQUEST_ONE_TIME);

$TChecktis = new Type('TChecktis', QUESTION_TYPE_RANGE, array(0, 121));
$TChecktis->setTemplate($TTextTemplate);
$TChecktis->setShowDKButton(true);

$TLanguage  = new Type('TLanguage', QUESTION_TYPE_ENUMERATED, array ( 1 => array ( 1 => 'English', 2 => 'Hindi', 3 => 'Bengali', 4 => 'Telugu', 5 => 'Marathi'), 2 => array ( 1 => 'Ja', 2 => 'Nee')));
$TLanguage->setTemplate($TEnumeratedTemplate);

        

?>