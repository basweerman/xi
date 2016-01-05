<?php

$template = array( 1 => 
'<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="getfile.php?f=external/bootstrap.min.css">
    <!-- Optional theme -->
    <!-- <link rel="stylesheet" href="getfile.php?f=external/bootstrap-theme.min.css"> -->
    <link rel="icon" type="image/png" href="getfile.php?f=images/xi.png">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn\'t work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="external/html5shiv.min.js"></script>
      <script src="external/respond.min.js"></script>
    <![endif]-->
        <meta charset="utf-8" />
        <title><surveyName /></title>

  
        <style>
          .btn_dkrf:focus {
              color: white;
              background-color: #6834DC;
            }
            .btn_dkrf_selected {
              color: white;
              background-color: #6834DC;
            }
        </style>

  
      </head>
    <body>
    <br>
    <div class="container">
        <div class="panel panel-default">
            <div class="panel-body">
                <form id=xi method=post>

                    <div id="myxi">

                    </div>
 
                   <input type=hidden name=button id=button>
                    <br/>
                    <div class="panel-footer text-center">
                        <backButton />
                        <nextButton />    
                    </div>
                </form>
            </div><!-- panel body -->
        </div>
    </div><!-- container -->
    <!-- jQuery (necessary for Bootstrap\'s JavaScript plugins) -->
    <script src="getfile.php?f=external/jquery.min.js"></script>
    <!-- Latest compiled and minified JavaScript -->
    <script src="getfile.php?f=external/bootstrap.min.js"></script>
    <script>

        function myCall(caller, button) {
              if (typeof button === "undefined" || button == 1 || button == 2){ //next or back clicked

                if (button == 1 || button == 2){            
                  $("#button").val(button);
                }
                var request = $.ajax({
                    url: "ajax.php",
                    type: "POST",            
                    //dataType: "html",
                    data: $("#xi").serialize(),
                });

                request.done(function(msg) {
                    $("#myxi").html(msg);          
                });

                request.fail(function(jqXHR, textStatus) {
                    alert( "Request failed: " + textStatus );
                });
            }
            else { //dk/rf clicked
                $(\'input:hidden[name="\' + $(caller).attr(\'id\') + \'"]\').val(button);
            }
        }




        $( document ).ready(function() {
          myCall();




        });

    </script>

    </body>
</html>'
);

?>
