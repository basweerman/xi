<?php

$married->ask();
if ($married->value() == 1){
    $marriedyear->ask();
}
else if ($married->value() == 2){
    //$marriedyear->ask();
}
else {
    //$marriedyear->ask();
}

$anykids->ask();
if ($anykids->value() == 1){
    $howmanykids->ask();
    for ($kidscounter->value(1);   $kidscounter->value() <= $howmanykids->value();  $kidscounter->value($kidscounter->value() + 1)){
        $agechild[$kidscounter->value()]->ask();
    }
}

$animals->ask();
//print_r($animals->value());

if (sizeof($animals->value() > 0)){
    
    //followup approach 1
    //create rules and push in..
    $groupquestions = array();           
    if (inSet(1, $animals->value())) $groupquestions[] = $animal_horse;
    if (inSet(2, $animals->value())) $groupquestions[] = $animal_mouse;
    if (inSet(3, $animals->value())) $groupquestions[] = $animal_rat;
    if (inSet(4, $animals->value())) $groupquestions[] = $animal_cow;
    if (inSet(5, $animals->value())) $groupquestions[] = $animal_elephant;
    $GAnimals->setQuestions($groupquestions);
    $GAnimals->ask();   //followup asking about animals 1st implementation
    
    //followup approach 2
    $groupquestions = array();           
    for ($animalcounter->value(1); $animalcounter->value() <= 5; $animalcounter->value($animalcounter->value() + 1)){
        $animal_followup[$animalcounter->value()]->setQuestionText($animalnames->getLine($animalcounter->value()));
        if (inSet($animalcounter->value(), $animals->value())) $groupquestions[] = $animal_followup[$animalcounter->value()];
    }
    $GAnimals2->setQuestions($groupquestions);
    $GAnimals2->ask();   
    
}     


$qi2->ask();

?>