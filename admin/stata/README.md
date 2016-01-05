php_stata
=========

PHP Extension for reading and writing STATA files

I am writing this extension to facilitate the data dissemination of our projects: The Understanding America Study Datapages (http://uasdata.usc.edu) and the Gateway to Global Aging Data (http://g2aging.org). It has already been implemented in:<br>

1) Read and display Highcharts charts directly from Stata<br>
2) Provide descriptive information<br>
3) Generate question carts by picking and combining Stata files<br>

Example use:
<pre>

/* Reading */
$res = stata_open("/var/www/html/filename.dta");

echo "Stata observations: " . stata_observations($res);
echo "Stata variables: " . stata_nvariables($res);


print_r(stata_variables($res));

$df = stata_data($res);

echo $df['data'][0]['variablename']
$labels = stata_labels($res)['labels'];
stata_close($res);


/* Writing */

stata_write("filename.dta", array("data" => array(
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
</pre>
