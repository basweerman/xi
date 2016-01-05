<?php


/* Reading */
/*
$res = stata_open("/var/www/html/filename.dta");

echo "Stata observations: " . stata_observations($res);
echo "Stata variables: " . stata_nvariables($res);


print_r(stata_variables($res));

$df = stata_data($res);

echo $df['data'][0]['variablename']
$labels = stata_labels($res)['labels'];
stata_close($res);
*/

/* Writing */
echo 'write';
if (function_exists(stata_write)){
stata_write("/tmp/filename.dta", array("data" => array(
              1 => array("prim_key" => "232342342", 
                                 "testswitch" => 32.3234, 
                                 "mode" => 32741), 
                      2 => array("prim_key" => "33333333333333333", 
                                 "testswitch" => pow(2.0, 1023), 
                                 "mode" => 2147483621) )) ,  
                array("prim_key" => array("vlabels" => "",
                                           "dlabels" => "PRIM KEY",
                                           "vfmt" => "%17s",
                                           "valueType" => 20 ),
                      "testswitch" => array("vlabels" => "",
                                            "dlabels" => "TEST SWITCH",
                                            "vfmt" => "%9.0g",
                                            "valueType" => 255), 
                      "mode"  => array("vlabels" => "gfk2_live_vl5", 
                                       "dlabels" => "INTERVIEW MODE", 
                                       "vfmt" => "%9.0g", "valueType" => 253)), 
               array("labels" => array( "gfk2_live_vl5" => 
                                              array(44 => "44 Face" ,
                                                    55 => "55 Call center"))));

echo 'done';
}
else {
echo 'aaa';
}
?>
