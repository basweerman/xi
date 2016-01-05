/*
 *  PHP Stata Extension 
 *  Copyright (C) 2014 Adrian Montero
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, a copy is available at
 *  http://www.gnu.org/licenses/gpl-2.0.html
 */




#ifndef STATA_H
#define STATA_H 1

#define PHP_STATA_VERSION "1.0"
#define PHP_STATA_EXTNAME "stata"
#define PHP_STATA_AUTHOR "Adrian Montero"

PHP_MINIT_FUNCTION(stata);
PHP_FUNCTION(stata_open);
PHP_FUNCTION(stata_observations);
PHP_FUNCTION(stata_variables);
PHP_FUNCTION(stata_nvariables);
PHP_FUNCTION(stata_data);
PHP_FUNCTION(stata_labels);
PHP_FUNCTION(stata_close);
PHP_FUNCTION(stata_write);
PHP_MINFO_FUNCTION(stata);


extern zend_module_entry stata_module_entry;
#define phpext_stata_ptr &stata_module_entry

struct StataDataFile * do_readStata(char * fileName);
int do_stataClose(struct StataDataFile * dta);

#endif
