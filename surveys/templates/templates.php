<?php
//xi templates (admin generated)
$TQuestionTemplate = new Template('TQuestionTemplate', TEMPLATE_QUESTION, 'questiontemplate', '');
$TStandardTemplate = new Template('TStandardTemplate', TEMPLATE_GROUP, 'grouptemplate', '');
$TTextTemplate = new Template('TTextTemplate', TEMPLATE_TYPE, 'texttemplate', '');
$TEnumeratedTemplate = new Template('TEnumeratedTemplate', TEMPLATE_TYPE, 'enumeratedtemplate', '');
$TSelectTemplate = new Template('TSelectTemplate', TEMPLATE_TYPE, 'selecttemplate', '');
$TOpenTemplate = new Template('TOpenTemplate', TEMPLATE_TYPE, 'opentemplate', '');
$TAnimalFollowupTemplate = new Template('TAnimalFollowupTemplate', TEMPLATE_GROUP, '', array ( 1 => ' Please rate the animals that you like on a scale from 1 to 5:<table class="table"> <tr><td></td><td align=center><b>1</b></td><td align=center><b>2</b></td><td align=center><b>3</b></td><td align=center><b>4</b><td align=center><b>5</b></td></td></tr> <repeat> <question /> </repeat> </table> '));
$TYesNoTemplate = new Template('TYesNoTemplate', TEMPLATE_TYPE, '', array ( 1 => 'some top text <table class="table"> <tr><td></td><td align=center><b>Yes</b></td><td align=center><b>No</b></td></tr> <repeat> <question /> </repeat> </table> some botton text'));
$TRowTemplate = new Template('TRowTemplate', TEMPLATE_TYPE, '', array ( 1 => '<td align=center><div class="radio"><label><input type="radio" name="<questionName />" value="<optionKey />"<checked />></label></div></td>'));
$TRowQuestionTemplate = new Template('TRowQuestionTemplate', TEMPLATE_QUESTION, '', array ( 1 => '<tr><td><errorMessage /><questionText /></td><answerOption /></tr>'));
$TBas = new Template('TBas', TEMPLATE_GROUP, '', array ( 1 => 'this is my group'));
?>