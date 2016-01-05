<?php

class Admin {

    var $survey;

    function Admin(Survey $survey){
        $this->survey = $survey;
        
    }
    
    
    
    function truncateData(){
        $this->survey->db->truncate();
        return '<div class="alert alert-success" role="alert">Data truncated</div>';
    }
    
    function exportStata(){

        if (function_exists('stata_write')){
            $metadata['observations'] = $this->survey->db->count();
            $metadata['variables'] = sizeof($this->survey->getAllDataQuestions());
//            echo '<br/><b>metadata</b> '; //METADATA NOT USED
//            print_r($metadata);

            //getVariables first        
            $variablesraw = array();
            $variables = array();
            foreach($this->survey->getAllDataQuestions() as $name => $question){
                $vlables = '';
                if ($question->getQuestionType()->getAnswerType() == QUESTION_TYPE_ENUMERATED){
                  $vlables = $question->getQuestionType()->getName();
                }
                $variablesraw[$name] = '';
                $variables[$name] = 
                        array("vlabels" =>$vlables ,
                              "dlabels" => $question->getDescription(),
                              "vfmt" => $question->getQuestionType()->getVFMT(),
                              "valueType" => $question->getQuestionType()->getValueType());

            }
//            echo '<br/><b>variables</b> ';
//            print_r($variables);

            $labels = array();
            foreach($this->survey->getTypes() as $type){
                if ($type->getAnswerType() == QUESTION_TYPE_ENUMERATED){
                    $enumerated = $type->getEnumeratedOptions(); //enumerated stored in details
                    $typeArray = array();
                    foreach($enumerated as $key=>$enum){
                        $typeArray[$key] = $key . ' ' . $enum[1];
                    }
                    $labels[$type->getName()] = $typeArray;
                }        
            }
            //echo '<br/><b>labels</b> ';
            //print_r($labels);

            //$collection = $this->survey->db->getCollection();
            //$cursor = $collection->find();
            
            
            $cursor = $this->survey->db->getContent();

            
            
            $i = 1;

            //standard: array with missings
            $baseArray = array();
            foreach($variablesraw as $name=>$question){
                if ($this->survey->getQuestion(getTextWithoutBrackets($name))->getQuestionType()->getValueType() < 250){
                  $baseArray[$name] = (string) '.';
                }
                else {
                  $baseArray[$name] = (double) $this->getValDouble('.');
                }
            }
            
            
            foreach ($cursor as $doc) {
                $baseArr = $baseArray;
               
                array_shift($doc);
                $doc = $this->survey->db->toArray($doc); //convert to array for STATA

                //remove variables that are not in the list!
                foreach($doc as $varname => $value){
                  //  echo $varname . ':' . $value . '<br/>';
                    if (!isset($variables[$varname])){ //$this->survey->getQuestion($varname) == null){
                        //remove from array: not present in question list
                    //    unset($doc[$varname]);
                     //   echo $varname .  ' is not in array<br/>';
                    }
                    else {
                        if (is_array($value)){  //array: SET OF QUESTION
                            //unset($doc[$varname]);
                            foreach($value as $key => $val){
                               // $doc[$varname . '[' . $key . ']'] = (double) $this->getValDouble($val); //floatval($val);
                                $baseArr[$varname . '[' . $key . ']'] = (double) $this->getValDouble($val); //floatval($val);
                                
                            }
                        }                        
                        else {
                           // echo 'update' . $varname;
                            if ($this->survey->getQuestion(getTextWithoutBrackets($varname))->getQuestionType()->getValueType() < 250){
                             //   echo 'string';
                             //   $doc[$varname] = (string) $value;
                                $baseArr[$varname] = (string) $value;
                            }
                            else {
                             //   echo $varname . 'float : ' . $value . '<br/>';
                             //   $doc[$varname] = (double) $this->getValDouble($value); //floatval($val);
                                $baseArr[$varname] = (double) $this->getValDouble($value); //floatval($val);
                            }
                        }
                    }
                }
                $data[$i++] = $baseArr;  //make the table square.. mongo only stores fields that have been on the route
               
//                $data[$i++] = array_replace($baseArr, $doc);  //make the table square.. mongo only stores fields that have been on the route
//                $data[$i++] = $doc;
            }
           // echo '<br/><b>data</b>';
           // print_r($data);
/*
            $stataExport = array();
            $stataExport['data'] = $data;
            $stataExport['variables'] = $variables;
            $stataExport['labels'] = $labels;
            $stataExport['metadata'] = $metadata;
  */
            $filename = 'xi_' . date('ymdhis') . '.dta';
            $tempName = tempnam(sys_get_temp_dir(), '') . '_' . $filename;
            
            try {
                stata_write($tempName, array('data' => $data), $variables, array('labels' => $labels));
                ob_clean();
                header('Content-Type: application/stata; charset=utf-8');
                header('Content-Disposition: attachment; filename=' . $filename);
                echo file_get_contents($tempName);
                unlink($tempName);
                exit;
            }
            catch (Exception $e) {
                echo $e->getMessage();
            }
            
            if (file_exists($tempName)){
                unlink($tempName);
            }
        }
        else {
            echo 'Stata module not loaded.';        
        }
        
    }
    
    function exportJson(){
        $collection = $this->survey->db->getCollection();
        $json = json_encode(iterator_to_array($collection->find()));
        echo $json;
    }

    function exportTableToCsv($table){
        global $survey;
      	$query = 'SELECT * FROM ' . $table;
	$result = @mysqli_query($survey->db, $query);
	$csvFields = array();
        $data = array();
        while ($row = @mysqli_fetch_array($result, MYSQLI_BOTH)){
            $i = $row['primkey'];
            $fields = json_decode($row['record'], true);
            array_shift($fields); 
            foreach($fields as $varname => $value){
                if (is_array($value)){  //array: SET OF QUESTION
                    foreach($value as $key => $val){
                        $csvFields[$varname . '[' . $key . ']'] = '';
                        $data[$i][$varname . '[' . $key . ']'] = $val;
                    }
                }
                else {
                    $csvFields[$varname] = '1';
                    $data[$i][$varname] = $value;
                }
            }
            $data[$i]['ts'] = $row['ts'];
        }
        $csvFields['ts'] = '';

        ob_clean();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $table . '_' . date('YmdHis') . '.csv');
        echo '"' . 'primkey' . "\"\t\"" . implode("\"\t\"" , array_keys($csvFields)) . "\"\n";

        foreach($data as $primkey=>$line){
            echo '"' . $primkey . "\"\t";
            foreach ($csvFields as $key=>$val){
                echo "\"";
                if (isset($line[$key])){
                    echo $line[$key];
                }
                else {
                    echo '.';
                }	
                echo "\"\t";
           }
           echo "\n";
         }
         exit;
    }
    
    
    function exportCsv(){
        $data = array();
        $variables = array();
        foreach($this->survey->getAllDataQuestions() as $name => $question){
            $variables[] = $name;
        }
       // print_r($variables);
       // asort($variables);
        $data[] = $variables;

//        $collection = $this->survey->db->getCollection();
//        $cursor = $collection->find();
//        foreach ($cursor as $doc) {
          foreach($this->survey->db->getContent() as $doc){
            $baseArr = array();
            foreach($variables as $question){
                $baseArr[$question] = '.';
            }
            array_shift($doc); //take primkey off
            $doc = $this->survey->db->toArray($doc);
            //remove variables that are not in the list!
            foreach($doc as $varname => $value){
                if (!in_array($varname, $variables)){ //remove from array: not present in question list
                    unset($doc[$varname]);
                }
                elseif (is_array($value)){  //array: SET OF QUESTION
                    unset($doc[$varname]);
                    foreach($value as $key => $val){
                        $doc[$varname . '[' . $key . ']'] = $val;
                    }
                }
            }
            //ksort($doc);
            $data[] = array_replace($baseArr, $doc);  //make the table square.. mongo only stores fields that have been on the route
        }        
        global $survey;
        ob_clean();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $survey->getName(1) . '_' . date('YmdHis') . '.csv');
        foreach($data as $line){
            echo '"' . implode("\"\t\"" , $line) . "\"\n";
        }
/*        $outstream = fopen("php://output", 'w');
        function __outputCSV(&$vals, $key, $filehandler) {
            fputcsv($filehandler, $vals, "\t", '"');
        }
        array_walk($data, '__outputCSV', $outstream);
        fclose($outstream);*/
        exit;
    }

  //  function replaceBrackets($string){
        //$string = str_replace("[", "_", $string);
        //$string = str_replace("]", "_", $string);
    //    return $string;

    //}
    
    function getValDouble($val){
        switch ($val){
            case '.': return (double) pow(2.0, 1023); //sprintf('%.0F', "8.98846567431158E307"); // 2^1013            
            case '.d': return (double)  sprintf('%.0F', "8.99724347282165E307");
            case '.r': return (double) sprintf('%.0F', "9.027965767606894E307");
            case '.e': return (double) sprintf('%.0F', "8.98846567431158E307") * 1.001220703125000;
            default: return (double) doubleval($val);
        }
        
/*        
        $this->doubleempty = sprintf('%.0F', "8.98846567431158E307"); // 2^1013            
        $this->doubledk = sprintf('%.0F', "8.99724347282165E307");
        $this->doublerf = sprintf('%.0F', "9.027965767606894E307");
        $this->doublemarkempty = sprintf('%.0F', "8.98846567431158E307") * 1.001220703125000;*/
        //$this->doublena = sprintf('%.0F', "8.98846567431158E307") * 1.003417968750000000;
        //$this->doubleerror = sprintf('%.0F', "8.98846567431158E307") * 1.005371093750000000;
        
    }
    
    
    function saveGroup(){
        global $survey;
        $section = $survey->getSection($_SESSION['section']);
        $questiongroup = $section->getQuestionGroup($_SESSION['questiongroup']);
        if (isset($_POST['name']) && $survey->getQuestionGroup($_SESSION['questiongroup'])->getName() != $_POST['name']){
            //name change or new question added
            $section->addQuestionGroup(new QuestionGroup($_POST['name'], array(), $survey->getTemplate(GROUP_STANDARD_TEMPLATE)));
            $questiongroup = $section->getQuestionGroup($_POST['name']);
        }
        if (isset($_POST['template'])){
            $questiongroup->setTemplate($survey->getTemplate($_POST['template']));
            $questiongroup->setRules($_POST['rules']);
            if ($section->writeQuestionsAndGroups()){
                return '<div class="alert alert-success" role="alert">Question written</div>';
            }
            return '<div class="alert alert-danger" role="alert">Question could not be written</div>';
        }
        else {  //come here from language switch
            return '';
        }                
        
        
    }
    
    
    function saveQuestion(){
        global $survey;
        $section = $survey->getSection($_SESSION['section']);

        $question = $survey->getQuestion($_SESSION['question']);
        if (isset($_POST['name']) && $survey->getQuestion($_SESSION['question'])->getName() != $_POST['name']){
            //name change or new question added
            $section->addQuestion(new Question($_POST['name'],'',array(),new Type('TNone', QUESTION_TYPE_NONE)));
            $question = $section->getQuestion($_POST['name']);
        }
        if (isset($_POST['questiontype'])){
            $question->setDescription($_POST['description']);
            $question->setQuestionType($survey->getType($_POST['questiontype']));
            $question->setTemplate($survey->getTemplate($_POST['template']));
            $question->setQuestionText($_POST['questiontext'], $_SESSION['language']);
            if ($section->writeQuestionsAndGroups()){
                return '<div class="alert alert-success" role="alert">Question written</div>';
            }
            return '<div class="alert alert-danger" role="alert">Question could not be written</div>';
        }
        else {  //come here from language switch
            return '';
        }        
    }
    
    function saveTemplate(){
        global $survey;
        $template = $survey->getTemplate($_SESSION['template']);
        if (isset($_POST['name']) && $survey->getTemplate($_SESSION['template'])->getName() != $_POST['name']){
            //name change or new template added
            $survey->addTemplate(new Template($_POST['name'], TEMPLATE_QUESTION, 'questiontemplate', ''));
            $template = $survey->getTemplate($_POST['name']);
        }
        if (isset($_POST['type'])){
            $template->setLocation($_POST['location']);
            $template->setType($_POST['type']);
            $template->setContent($_POST['content']);
            if ($survey->writeTemplates()){
                return '<div class="alert alert-success" role="alert">Template written</div>';
            }
            return '<div class="alert alert-danger" role="alert">Template could not be written</div>';
        }
        else {  //come here from language switch
            return '';
        }
    }
    
    function saveType(){
        global $survey;
        $type = $survey->getType($_SESSION['type']);
        if (isset($_POST['name']) && $survey->getType($_SESSION['type'])->getName() != $_POST['name']){
            //name change or new type added
            $survey->addType(new Type($_POST['name'], QUESTION_TYPE_NONE, ''));
            $type = $survey->getType($_POST['name']);
        }
        if (isset($_POST['answertype'])){
            $type->setAnswerType($_POST['answertype']);
            $type->setDetailsAsString($_POST['details']);
            $type->setShowRFButton(isset($_POST['rfbutton']) && $_POST['rfbutton'] == 1);
            $type->setShowDKButton(isset($_POST['dkbutton']) && $_POST['dkbutton'] == 1);
            $type->setTemplate($survey->getTemplate($_POST['template']));
            $type->setValidationOption($_POST['validationoption']);
            if ($survey->writeTypes()){
                return '<div class="alert alert-success" role="alert">Type written</div>';
            }
            return '<div class="alert alert-danger" role="alert">Type could not be written</div>';
        }
        else { //come here from language load
            return '';
        }
    }
    
    function saveRules(){
        global $survey;
        $section = $survey->getSection($_SESSION['section']);
        if (isset($_POST['rules'])){
            $filename = prependPath($section->getRulesLocation());
            if (is_writable($filename)){
                file_put_contents($filename, $_POST['rules']);
                return '<div class="alert alert-success" role="alert">Rules written</div>';
            }
            return '<div class="alert alert-danger" role="alert">Rules could not be written</div>';
        }
        else {  //come here from language switch
            return '';
        }        
       
        
    }
}