<?php

$template = array( 1 => 
'<select name="<questionName />" id="<idName />">
    <option value="">Click to select</option>
<repeat>
    <div class="<inputType />"><label><option value="<optionKey />"<checked />><optionText /></option></label></div>
</repeat> 
</select>
<br/>
<br/>
<dkrfInput /><dkButton /> <rfButton />'
);

?>