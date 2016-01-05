<?php
//xi questions (admin generated)
$married = new Question('married', 'R married', array ( 1 => 'Are you currently married?', 2 => 'Bent u getrouwd?'), $TYesNo);
$marriedyear = new Question('marriedyear', 'R married year', array ( 1 => 'In what year did you get married? Just putting some extra<br/>text in <b>here</b> to see if it will wrap! This text is getting very very long.. Will it move to the next line. It needs more than 80 characters to move..', 2 => 'In welk jaar bent u getrouwd?'), $TRange1900to2015);

$anykids = new Question('anykids', 'R has kids', array ( 1 => 'Do you have any children?', 2 => 'Heeft u kinderen?'), $TYesNo);

$howmanykids = new Question('howmanykids', 'number of kids', array ( 1 => 'How many children do you have?', 2 => 'Hoeveel kinderen heeft u?'), $TRange1to15 );

$kidscounter  = new Question('kidscounter', 'kids counter', array(), $TInteger);
for ($i0 = 1; $i0 <= 15; $i0++){
$agechild[$i0] = new Question('agechild[' . $i0 . ']', 'age child', array ( 1 => 'How old is child ^$kidscounter->value()^?', 2 => 'How oud is kind nummer ^$kidscounter->value()^?'), $TRange0to100);
}

$animals = new Question('animals', 'what animals do you like', array(1=> 'What animals do you like?<br/>Check all that apply.', 2=> 'Welke dieren vindt u leuk?<br/>U kunt meerdere antwoorden kiezen'), $TAnimals);

$animal_horse = new Question('animal_horse', 'rate horse', array(1=> 'Horse', 2=> 'Paard'), $TRate1to5);
$animal_horse->settemplate($TRowQuestionTemplate);
$animal_mouse = new Question('animal_mouse', 'rate mouse', array(1=> 'Mouse', 2=> 'Muis'), $TRate1to5);
$animal_mouse->settemplate($TRowQuestionTemplate);
$animal_rat = new Question('animal_rat', 'rate rat', array(1=> 'Rat', 2=> 'Rat'), $TRate1to5);
$animal_rat->settemplate($TRowQuestionTemplate);
$animal_cow = new Question('animal_cow', 'rate cow', array(1=> 'Cow', 2=> 'Koe'), $TRate1to5);
$animal_cow->settemplate($TRowQuestionTemplate);
$animal_elephant = new Question('animal_elephant', 'rate elephant', array(1=> 'Elephant', 2=> 'Olifant'), $TRate1to5);
$animal_elephant->settemplate($TRowQuestionTemplate);

$animalcounter  = new Question('animalcounter', 'animal counter', array(), $TInteger);

for ($i0 = 1; $i0 <= 5; $i0++){
  $animal_followup[$i0] = new Question('animal_followup[' . $i0 . ']' , 'rate animal', array(), $TRate1to5);
  $animal_followup[$i0]->settemplate($TRowQuestionTemplate);
}

$animalnames = new Question('animalcounter', 'animal counter', array(1 => 'horse
    mouse
    rat
    cow
    elephant', 2 => 'paart
muis
rat
koe
olifant'), $TNone);

$GAnimals = new QuestionGroup('GAnimals', null, $TAnimalFollowupTemplate);
$GAnimals2 = new QuestionGroup('GAnimals2', null, $TAnimalFollowupTemplate);

$qi2 = new Question('qi2', 'temp var', array(), $TString);

//xi groups (admin generated)
//$Tbastest = new QuestionGroup('Tbastest', '$PA1->ask()', $TStandardTemplate);

//$aarg = new QuestionGroup('aarg', '$PA2->ask()', $TStandardTemplate);

?>