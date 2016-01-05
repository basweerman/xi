<?php


class QuestionNoBack extends Question {  //THIS IS THE FIRST 'QUESTION' OR INTRO

//  public function __construct() {
//    parent::__construct();
//    $this->setQuestionText('hallo dit is question1');
//  }

  function getBackButton(){
     return false;
  }


}

$question = new Question0('intro', 'description intro', array(1=>'welcome to this survey. Press next to contiune!'), QUESTION_TYPE_NONE);

?>
