<?php
/* 
------------------------------------------------------------------------
Copyright (C) 2015 Albert Weerman
This library/program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.
This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
------------------------------------------------------------------------
*/

define('QUESTION_TYPE_NONE', 1);
define('QUESTION_TYPE_STRING', 2);
define('QUESTION_TYPE_RANGE', 3);
define('QUESTION_TYPE_OPEN', 4);

define ('QUESTION_TYPE_INTEGER', 8);

define('QUESTION_TYPE_ENUMERATED', 5);
define('QUESTION_TYPE_SETOF', 6);
define('QUESTION_TYPE_SELECT', 7);

define('ERROR_NO_ANSWER', 400);
define('ERROR_INPUT_RANGE', 401);

define('BUTTON_BACK', 1);
define('BUTTON_NEXT', 2);

define('VALIDATION_OPTION_FORCE_ANSWER', 1);
define('VALIDATION_OPTION_ALLOW_CONTINUE', 2);
define('VALIDATION_OPTION_REQUEST_ONE_TIME', 3);

define('TEMPLATE_SURVEY', 1);
define('TEMPLATE_QUESTION', 2);
define('TEMPLATE_GROUP', 3);
define('TEMPLATE_TYPE', 4);

define('TYPE_STANDARD_WIDTH', '100px');
define('TYPE_STANDARD_HEIGHT', '');

define('QUESTION_STANDARD_TEMPLATE', 'TQuestionTemplate');
define('GROUP_STANDARD_TEMPLATE', 'TStandardTemplate');

define('DATABASE_MONGODB', 1);
define('DATABASE_MYSQL', 2)

?>
