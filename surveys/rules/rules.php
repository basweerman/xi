<?php

$startsurvey->ask();
$selecttest->ask();
$opentest->ask();

$q1->ask();
if (sizeof($q1->value()) > 1){
  $PA001Group->ask();
}


$willing = 12;
//print_r($q1->value());
if (isset($q1->value()[2])){
  for ($qi->value(1);   $qi->value() < 5;  $qi->value($qi->value() + 1)){
    for ($qi2->value(3); $qi2->value() < 6; $qi2->value($qi2->value() + 1)){
      $forq2q3group[$qi->value()][$qi2->value()]->ask();
        //$q2[$qi->value()][$qi2->value()]->ask();

      
    }
  }
}
else {
  $q3->ask();
}
$q4->ask();

?>
