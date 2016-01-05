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

// http://www.stata.com/help.cgi?dta_115

#include "stataread.h"
#include "php.h"
static int stata_endian;
#define R_PosInf INFINITY
#define R_NegInf -INFINITY


/* Mainly for use in packages */
int R_finite(double x)
{
#ifdef HAVE_WORKING_ISFINITE
    return isfinite(x);
#else
    return (!isnan(x) & (x != R_PosInf) & (x != R_NegInf));
#endif
}

#define R_FINITE(x)  R_finite(x)

static void OutIntegerBinary(int i, FILE * fp, int naok)
{
    i=((i == NA_INTEGER) & !naok ? STATA_INT_NA : i);
    if (fwrite(&i, sizeof(int), 1, fp) != 1)
	error("a binary write error occurred");

}

static void OutByteBinary(unsigned char i, FILE * fp)
{
    if (fwrite(&i, sizeof(char), 1, fp) != 1)
	error("a binary write error occurred");
}
static void OutDataByteBinary(int i, FILE * fp)
{
    i=(unsigned char) ((i == NA_INTEGER) ? STATA_BYTE_NA : i);
    if (fwrite(&i, sizeof(char), 1, fp) != 1)
	error("a binary write error occurred");
}

static void OutShortIntBinary(int i,FILE * fp)
{
  unsigned char first,second;

#ifdef WORDS_BIGENDIAN
    first = (unsigned char)(i >> 8);
    second = i & 0xff;
#else
    first = i & 0xff;
    second = (unsigned char)(i >> 8);
#endif
  if (fwrite(&first, sizeof(char), 1, fp) != 1)
    error("a binary write error occurred");
  if (fwrite(&second, sizeof(char), 1, fp) != 1)
    error("a binary write error occurred");
}


static void  OutDoubleBinary(double d, FILE * fp, int naok)
{
    d = (R_FINITE(d) ? d : STATA_DOUBLE_NA);
    if (fwrite(&d, sizeof(double), 1, fp) != 1)
	error("a binary write error occurred");
}


static void OutStringBinary(const char *buffer, FILE * fp, int nchar)
{
    if (nchar == 0) return;
    if (fwrite(buffer, nchar, 1, fp) != 1)
	error("a binary write error occurred");
}

static char* nameMangleOut(char *stataname, int len)
{
    int i;
    for(i = 0; i < len; i++)
      if (stataname[i] == '.') stataname[i] = '_';
    return stataname;
}


/* Writes out a value label (name, and then labels and levels). 
 * theselevels can be R_NilValue in which case the level values will be
 * written out as 1,2,3, ...
 */
static int 
writeStataValueLabel(const char *labelName, zval ** theselabels,
		     int theselevels, const int namelength, FILE *fp)
{
   int i = 0, txtlen = 0; 
   size_t len = 0;
   len = 4*2*(zend_hash_num_elements(Z_ARRVAL_PP(theselabels)) + 1);
   txtlen = 0;
   HashPosition labelposition;
   HashPosition labelposition2;
   zval ** currentLabel;
 
   for (zend_hash_internal_pointer_reset_ex(Z_ARRVAL_PP(theselabels), &labelposition), i=0;
                            zend_hash_get_current_data_ex(Z_ARRVAL_PP(theselabels), (void**) &currentLabel, &labelposition) == SUCCESS;
                                     zend_hash_move_forward_ex(Z_ARRVAL_PP(theselabels), &labelposition), i++) {
		printf("type: %d , len: %d\n\r", Z_TYPE_PP(currentLabel), Z_STRLEN_PP(currentLabel));	
	        txtlen += Z_STRLEN_PP(currentLabel) + 1;
   }
   
   len += txtlen;
   OutIntegerBinary((int)len, fp, 0); 
   printf("len: %ld\n\r", len);
/* length of table */
    char labelName2[namelength + 1];
    printf("labelName: %s\n\r", labelName);
    strncpy(labelName2, labelName, namelength + 1); // nameMangleOut changes its arg.
    printf("labelName2: %s\n\r", labelName2);
    OutStringBinary(nameMangleOut(labelName2, strlen(labelName)), fp, namelength);
    OutByteBinary(0, fp);
/* label format name */
    OutByteBinary(0, fp); 
    OutByteBinary(0, fp); 
    OutByteBinary(0, fp);
 /*padding*/
    OutIntegerBinary(zend_hash_num_elements(Z_ARRVAL_PP(theselabels)), fp, 0);
    OutIntegerBinary(txtlen, fp, 0);
    printf("offsets \n\r");
 /* offsets */
    len = 0;
   
    for (zend_hash_internal_pointer_reset_ex(Z_ARRVAL_PP(theselabels), &labelposition), i=0;
                            zend_hash_get_current_data_ex(Z_ARRVAL_PP(theselabels), (void**) &currentLabel, &labelposition) == SUCCESS;
                                     zend_hash_move_forward_ex(Z_ARRVAL_PP(theselabels), &labelposition), i++) {

	

	OutIntegerBinary((int)len, fp, 0);
        printf("%s %d\n\r", Z_STRVAL_PP(currentLabel), Z_STRLEN_PP(currentLabel));
	len += Z_STRLEN_PP(currentLabel) + 1;
    }
   /* values: just 1,2,3,...*/
    
   //if(isNull(theselevels)){
   //	for (i = 0; i < length(theselabels); i++)
   //	    OutIntegerBinary(i+1, fp, 0);
   // }
   // else{
   //	if(TYPEOF(theselevels) == INTSXP){
       for (zend_hash_internal_pointer_reset_ex(Z_ARRVAL_PP(theselabels), &labelposition), i=0;
                            zend_hash_get_current_data_ex(Z_ARRVAL_PP(theselabels), (void**) &currentLabel, &labelposition) == SUCCESS;
                                     zend_hash_move_forward_ex(Z_ARRVAL_PP(theselabels), &labelposition), i++) {
 
		char *keyStr;
        	uint key_len, key_type;
        	long index;
        	int k;

        	key_type = zend_hash_get_current_key_ex(Z_ARRVAL_PP(theselabels), &keyStr, &key_len, &index, 0, &labelposition);
		printf("currentValue: %ld\n\r", index);
		OutIntegerBinary(index, fp, 0);
        }

	printf("levels \n\r");

//	}
//	else{
//		for (i = 0; i < length(theselevels); i++)
//		    OutIntegerBinary((int) REAL(theselevels)[i], fp, 0);
//	}
//  } 
/* the actual labels */

        for (zend_hash_internal_pointer_reset_ex(Z_ARRVAL_PP(theselabels), &labelposition), i=0;
                            zend_hash_get_current_data_ex(Z_ARRVAL_PP(theselabels), (void**) &currentLabel, &labelposition) == SUCCESS;
                                     zend_hash_move_forward_ex(Z_ARRVAL_PP(theselabels), &labelposition), i++) {


	len = Z_STRLEN_PP(currentLabel);
        OutStringBinary(Z_STRVAL_PP(currentLabel), fp, (int)len);
        OutByteBinary(0, fp);
        txtlen -= len+1;

        if (txtlen < 0)
                printf("this should happen: overrun");

         }
    
    if (txtlen > 0) 
	printf("this should happen: underrun");

    return 1;
}


void R_SaveStataData(FILE *fp, zval *data, zval *vars, zval *labels)
{
    int version = 10;
    int i, j, k = 0, l, nvar, nobs, charlen;
    char datalabel[81] = "Created by Nubis (USC CESR) ",
	timestamp[18], aname[33];
    char format9g[50] = "%9.0g", strformat[50] = "";
    const char *thisnamechar;

    zval ** data_inner;
    zval ** variables_inner;

 /*   SEXP names, types, theselabels, orig_names, vlabels, dlabel, 
	exp_fields, exp_field, curr_val_labels, label_table, names_lt,
	theselabelslevels;
*/
    int namelength = 8;
    int fmtlist_len = 12;

    if (version >= 7) namelength=32;
    if (version >= 10) fmtlist_len = 49;

    /* names are 32 characters in version 7 */

    /** first write the header **/
    if (version == 6)
	OutByteBinary((char) VERSION_6, fp);            /* release */
    else if (version == 7)
	OutByteBinary((char) VERSION_7, fp);
    else if (version == 8)  /* and also 9, mapped in R code */
	OutByteBinary((char) VERSION_8, fp);
    else if (version == 10) /* see comment above */
	OutByteBinary((char) VERSION_114, fp);
    OutByteBinary((char) CN_TYPE_NATIVE, fp);
    OutByteBinary(1, fp);            /* filetype */
    OutByteBinary(0, fp);            /* padding */

 

    nvar = zend_hash_num_elements(Z_ARRVAL_P(vars));


    zend_hash_find(Z_ARRVAL_P(data), "data", sizeof("data"), (void **)&data_inner);
    nobs = zend_hash_num_elements(Z_ARRVAL_PP(data_inner)); 

    printf("Observations: %d\n\r", nobs);
    printf("Variables: %d\n\r", nvar);

    OutShortIntBinary(nvar, fp);
    OutIntegerBinary(nobs, fp, 1);


    int ** types = ecalloc(nvar, sizeof(int*));
    int ** wrTypes = ecalloc(nvar, sizeof(int*));

    for (i = 0; i < nvar; i++)
    {
	types[i] = ecalloc(1, sizeof(int*));
	wrTypes[i] = ecalloc(1, sizeof(int*));
	*(types[i]) = -1;
    }

    
	

  /* number of cases */

    datalabel[80] = '\0';
    OutStringBinary(datalabel, fp, 81); 

    time_t rawtime;
    time (&rawtime);
    struct tm  *timeinfo = localtime (&rawtime);

    //for(i = 0; i < 18; i++) 
    //timestamp[i] = 0;
    
    strftime(timestamp, 18, "%d %b %Y %H:%M",timeinfo);
    timestamp[17] = 0;
    OutStringBinary(timestamp, fp, 18);   /* file creation time - zero terminated string */

    /** write variable descriptors **/

    /** types **/
    /* FIXME: writes everything as double or integer to save effort*/
    /*  we should honor the "Csingle" attribute and also write logicals as
	byte rather than long */

/***    PROTECT(types = allocVector(INTSXP, nvar));
    if (version <= 7) {
	for(i = 0;i < nvar; i++){
	    switch(TYPEOF(VECTOR_ELT(df, i))){
	    case LGLSXP:
		OutByteBinary(STATA_BYTE, fp);
		break;
	    case INTSXP:
		OutByteBinary(STATA_INT, fp);
		break;
	    case REALSXP:
		OutByteBinary(STATA_DOUBLE, fp);
		break;
	    case STRSXP:

***/
		/* NB: there is a 244 byte limit on strings */
/***		charlen = 0;
		for(j = 0; j < nobs; j++){
		    k = (int) strlen(CHAR(STRING_ELT(VECTOR_ELT(df, i), j)));
		    if (k > charlen) charlen = k;
		}
		if(charlen > 244)
		    warning("character strings of >244 bytes in column %d will be truncated", i+1);
		charlen =  (charlen < 244) ? charlen : 244;
		OutByteBinary((unsigned char)(charlen+STATA_STRINGOFFSET), fp);
		INTEGER(types)[i] = charlen;
		break;
	    default:
		error(_("unknown data type"));
		break;
	    }
	}

    } else {
***/

        HashPosition position_vars;
	zval **variable_traverse;
	
	HashPosition position_data;
        zval **data_traverse;

	HashPosition position_internal_vars;
	zval **internal_traverse;
	/* version 8, 10 */

        for (zend_hash_internal_pointer_reset_ex(Z_ARRVAL_P(vars), &position_vars), i=0;
                   zend_hash_get_current_data_ex(Z_ARRVAL_P(vars), (void**) &variable_traverse, &position_vars) == SUCCESS; 
			 zend_hash_move_forward_ex(Z_ARRVAL_P(vars), &position_vars), i++) {
			
                         char *keyStr;
                         uint key_len, key_type;
                         long index;
			 zval ** valueType;
			 zend_hash_get_current_key_ex(Z_ARRVAL_PP(variable_traverse), &keyStr, &key_len, &index, 0, &position_vars);
			 zend_hash_find(Z_ARRVAL_PP(variable_traverse), "valueType", sizeof("valueType"), (void **)&valueType);
//			 printf("meh: %ld, %s\n\r", Z_LVAL_PP(valueType), keyStr);	
			 *wrTypes[i] = Z_LVAL_PP(valueType);
			 switch(Z_LVAL_PP(valueType))
			 {
				case STATA_SE_BYTE:
			//		 printf("key: %s byte %ld\n\r", keyStr, Z_LVAL_PP(valueType));
					 OutByteBinary(STATA_SE_BYTE, fp);
			                 break;
				case STATA_SE_INT:
			//		 printf("key: %s int %ld\n\r", keyStr, Z_LVAL_PP(valueType));
 		                         OutByteBinary(STATA_SE_INT, fp);
					 break;
				case STATA_SE_SHORTINT:
					 //printf("key: %s shortint %ld\n\r", keyStr, Z_LVAL_PP(valueType));
					 //OutByteBinary(STATA_SE_SHORTINT, fp);
  					 OutByteBinary(STATA_SE_SHORTINT, fp);
					 break;
				case STATA_SE_FLOAT:
			//		 printf("key: %s float %ld\n\r", keyStr, Z_LVAL_PP(valueType));
					 OutByteBinary(STATA_SE_DOUBLE, fp);
					 break;
				case STATA_SE_DOUBLE:
			//		 printf("key: %s double %ld\n\r", keyStr, Z_LVAL_PP(valueType));
			                 OutByteBinary(STATA_SE_DOUBLE, fp);
					 break;
				default:
					charlen = 0;
			//		printf("key: %s other %ld\n\r", keyStr, Z_LVAL_PP(valueType));
  			                zend_hash_find(Z_ARRVAL_P(data), "data", sizeof("data"), (void **)&data_traverse);
					zval ** findSize;	
					for (zend_hash_internal_pointer_reset_ex(Z_ARRVAL_PP(data_traverse), &position_data);
				                   zend_hash_get_current_data_ex(Z_ARRVAL_PP(data_traverse), (void**) &findSize, &position_data) == SUCCESS;
                    						     zend_hash_move_forward_ex(Z_ARRVAL_PP(data_traverse), &position_data)) {
					
							zval ** strSize;
							zend_hash_find(Z_ARRVAL_PP(findSize), keyStr, key_len, (void **)&strSize);

							k = Z_STRLEN_PP(strSize);
							if (k > charlen)
							{
								charlen = k;
								*types[i] = charlen;
							}

					}
					
					if (charlen > 244)
						printf("character strings of >244 bytes in column %s will be truncated", keyStr);
					
					charlen = ( charlen < 244) ? charlen : 244;

					

					if (charlen == 0)
					{
						charlen = 2;
						*types[i] = charlen;
					}

					OutByteBinary((unsigned char)(charlen+STATA_SE_STRINGOFFSET), fp);
					break;
			 }
	}


/** names truncated to 8 (or 32 for v>=7) characters**/

//    for (i = 0; i < nvar;i ++){
     for (zend_hash_internal_pointer_reset_ex(Z_ARRVAL_P(vars), &position_vars), i=0;
                   zend_hash_get_current_data_ex(Z_ARRVAL_P(vars), (void**) &variable_traverse, &position_vars) == SUCCESS, i < nvar;
                         zend_hash_move_forward_ex(Z_ARRVAL_P(vars), &position_vars), i++) {
			 
		char *keyStr;
                uint key_len, key_type;
                long index;
                zval ** valueType;
                zend_hash_get_current_key_ex(Z_ARRVAL_P(vars), &keyStr, &key_len, &index, 0, &position_vars);

		strncpy(aname, keyStr, namelength);
		OutStringBinary(nameMangleOut(aname, namelength), fp, namelength);
		OutByteBinary(0, fp);
	}
//    }

    /** sortlist -- not relevant **/
 

   for (i = 0; i < 2*(nvar+1); i++) 
	OutByteBinary(0, fp);


    /** format list: arbitrarily write numbers as %9g format
	but strings need accurate types */



    for (i = 0; i < nvar; i++) {
	if (*types[i] != -1){

	    /* string types are at most 244 characters
	       so we can't get a buffer overflow in sprintf **/
	    memset(strformat, 0, 50);
	    sprintf(strformat, "%%%ds", *types[i]);

	    OutStringBinary(strformat, fp, fmtlist_len);
	} else {
	    OutStringBinary(format9g, fp, fmtlist_len);
	}
    }

    /** value labels.  These are stored as the names of label formats,
	which are themselves stored later in the file.
	The label format has the same name as the variable. **/


     for (zend_hash_internal_pointer_reset_ex(Z_ARRVAL_P(vars), &position_vars), i=0;
                   zend_hash_get_current_data_ex(Z_ARRVAL_P(vars), (void**) &variable_traverse, &position_vars) == SUCCESS, i < nvar;
                         zend_hash_move_forward_ex(Z_ARRVAL_P(vars), &position_vars), i++) {

                         char *keyStr;
                         uint key_len, key_type;
                         long index;


                         zval ** valueType;
                         zend_hash_get_current_key_ex(Z_ARRVAL_P(vars), &keyStr, &key_len, &index, 0, &position_vars);

			 zval ** vLabels;

                         zend_hash_find(Z_ARRVAL_PP(variable_traverse), "vlabels", sizeof("vlabels"), (void **)&vLabels);

			 if (Z_STRLEN_PP(vLabels) == 0)
			 {
				  /* no label */
				  for(j = 0; j < namelength+1; j++)
	                          OutByteBinary(0, fp);
			 }
			 else
			 {	
				/* label */ 
				  strncpy(aname, Z_STRVAL_PP(vLabels), namelength);
                       		  OutStringBinary(nameMangleOut(aname, namelength), fp, namelength);
				  OutByteBinary(0, fp);
			 }
	}

 /* no label */


     /* label */
            //If we remember what the value label was called, use that. Otherwise use the var name
/*	    if(!isNull(curr_val_labels) && isString(curr_val_labels) 
	       && LENGTH(curr_val_labels) > i)
	    	strncpy(aname, CHAR(STRING_ELT(curr_val_labels, i)), namelength);
	    else
		strncpy(aname, CHAR(STRING_ELT(names, i)), namelength);
	    OutStringBinary(nameMangleOut(aname, namelength), fp, namelength);
	    OutByteBinary(0, fp);*/

    /** Variable Labels -- Uses "var.labels" attribute
	if is a string vector of the right length, otherwise the
	the variable name (FIXME: this is now just the same abbreviated name) **/
  
  memset(datalabel, 0, 81); 

  for (zend_hash_internal_pointer_reset_ex(Z_ARRVAL_P(vars), &position_vars), i=0;
                   zend_hash_get_current_data_ex(Z_ARRVAL_P(vars), (void**) &variable_traverse, &position_vars) == SUCCESS, i < nvar;
                         zend_hash_move_forward_ex(Z_ARRVAL_P(vars), &position_vars), i++) {

                         char *keyStr;
                         uint key_len, key_type;
                         long index;


                         zval ** valueType;
                         zend_hash_get_current_key_ex(Z_ARRVAL_P(vars), &keyStr, &key_len, &index, 0, &position_vars);

                         zval ** dLabels;

                         zend_hash_find(Z_ARRVAL_PP(variable_traverse), "dlabels", sizeof("dlabels"), (void **)&dLabels);

                         if (Z_STRLEN_PP(dLabels) == 0)
                         {
                                  /* no label */
				  memset(datalabel, 0, 81);
				  OutStringBinary(datalabel, fp, 81);


                         }
                         else
                         {
                                /* label */
                                  strncpy(datalabel, Z_STRVAL_PP(dLabels), 81);
				  datalabel[80] = 0;
                                  OutStringBinary(datalabel, fp, 81);
                         }
        }


    /** Expansion fields. Only existing ones are type-1 fields (first byte is 1).
	This includes dataset/variables characteristics, multilingual data (dataset/variable labels, 
	value label attachments, language list), and notes. 
	Don't have documentation for pre-version7 format (are the first two fields still 33 bytes?) **/


   /* if(!isNull(exp_fields) && TYPEOF(exp_fields) == VECSXP && abs(version) >= 7){
	for(i = 0; i< LENGTH(exp_fields); i++) {
	    char tmp[namelength];
	    PROTECT(exp_field = VECTOR_ELT(exp_fields, i));	
	    if(!isNull(exp_field) && isString(exp_field) && LENGTH(exp_field) == 3) {
		OutByteBinary(1, fp);
		OutIntegerBinary(2*(namelength+1) + (length(STRING_ELT(exp_field,2))+1), fp, 1);
		memset(tmp, 0, namelength);
		strncpy(tmp, CHAR(STRING_ELT(exp_field, 0)), namelength);
		OutStringBinary(tmp, fp, namelength);
		OutByteBinary(0, fp);
		memset(tmp, 0, namelength);
		strncpy(tmp, CHAR(STRING_ELT(exp_field, 1)), namelength);
		OutStringBinary(tmp, fp, namelength);
		OutByteBinary(0, fp);
		OutStringBinary(CHAR(STRING_ELT(exp_field, 2)), fp, length(STRING_ELT(exp_field, 2)));
		OutByteBinary(0, fp);
	    }
	}
    } */

    //The last block is always zeros
    OutByteBinary(0, fp);
    OutByteBinary(0, fp);
    OutByteBinary(0, fp);
    if (version >= 7) { 
  
   /*longer in version 7. This is wrong in the manual*/

	OutByteBinary(0, fp);
	OutByteBinary(0, fp);
    }

    /** The Data **/


   zend_hash_find(Z_ARRVAL_P(data), "data", sizeof("data"), (void **)&data_traverse);
   zval ** variables;
   zval ** observations;
   HashPosition variable_position;

   for (zend_hash_internal_pointer_reset_ex(Z_ARRVAL_PP(data_traverse), &position_data);
        zend_hash_get_current_data_ex(Z_ARRVAL_PP(data_traverse), (void**) &observations, &position_data) == SUCCESS;
        zend_hash_move_forward_ex(Z_ARRVAL_PP(data_traverse), &position_data)) {




        for (zend_hash_internal_pointer_reset_ex(Z_ARRVAL_PP(observations), &variable_position), i=0;
              zend_hash_get_current_data_ex(Z_ARRVAL_PP(observations), (void**) &variables, &variable_position) == SUCCESS;
                zend_hash_move_forward_ex(Z_ARRVAL_PP(observations), &variable_position), i++) {

                char *keyStr;
                uint key_len, key_type;
                long index;
		int k;	

		
	        key_type = zend_hash_get_current_key_ex(Z_ARRVAL_PP(observations), &keyStr, &key_len, &index, 0, &variable_position);

		printf("%s", keyStr);

		switch(Z_TYPE_PP(variables))
		{
			case IS_LONG:
			case IS_BOOL:
				printf("IS LONG %ld\n\r", Z_LVAL_PP(variables));
				printf("%ld %d", Z_LVAL_PP(variables), *types[i]);
				if (*wrTypes[i] == STATA_SE_SHORTINT)
					OutShortIntBinary(Z_LVAL_PP(variables), fp);
				else			
					OutIntegerBinary(Z_LVAL_PP(variables), fp, 0);
				break;
			case IS_DOUBLE:
				OutDoubleBinary(Z_DVAL_PP(variables), fp, 0);
				printf("IS DOUBLE %lf\n\r", Z_DVAL_PP(variables));
				break;
/*
			case IS_BOOL:
				//printf("IS BOOL %s\n\r", Z_LVAL_PP(variables) ? "TRUE" : "FALSE");
				OutDataByteBinary(Z_LVAL_PP(variables), fp);
				break;
*/
			case IS_STRING:
				printf("IS STRING %s %d\n\r", Z_STRVAL_PP(variables), Z_STRLEN_PP(variables));
				k = Z_STRLEN_PP(variables);
				if (k == 0)
				{
				   //k=1;
				   //printf("WRITING X\n\r");
				   //OutStringBinary(" ", fp,1); //printf("empty string is not valid in Stata's documented format\n\r");
				   //for (l = 1; l < *types[i]; l++)
				   OutByteBinary(0, fp);		
				   //printf("types before: %d\n\r", *types[i]);
			           k=1;
				   //printf("types after: %d\n\r", *types[i]);
				}
				else
				{
				        if (k > 244)
					    k = 244;
					OutStringBinary(Z_STRVAL_PP(variables), fp, k);
				}
				int l = 0;
				for (l = *(types[i])-k; l > 0; l--)
				{
				 	//printf("data str: %d %d %d\n\r", *types[i], l, k);
					OutByteBinary(0, fp);
				}
				break;
			default:
				printf("this should not happen\n\r");
				break;
			
		}

	}
    }
    

    /** value labels: pp92-94 of 'Programming' manual in v7.0 **/
    //PROTECT(curr_val_labels = getAttrib(df, install("val.labels")));
    HashPosition valuePosition;
    zval ** value_labels; 
    zval ** inner_labels;
    zend_hash_find(Z_ARRVAL_P(labels), "labels", sizeof("labels"), (void **)&value_labels);

    for (zend_hash_internal_pointer_reset_ex(Z_ARRVAL_PP(value_labels), &valuePosition);
        zend_hash_get_current_data_ex(Z_ARRVAL_PP(value_labels), (void**) &inner_labels, &valuePosition) == SUCCESS;
        zend_hash_move_forward_ex(Z_ARRVAL_PP(value_labels), &valuePosition)) {

        char *keyStr;
        uint key_len, key_type;
        long index;
        int k;

        key_type = zend_hash_get_current_key_ex(Z_ARRVAL_PP(value_labels), &keyStr, &key_len, &index, 0, &valuePosition);
	strncpy(aname, keyStr, namelength);
// 	printf("count: %s %d\n\r", keyStr, zend_hash_num_elements(Z_ARRVAL_PP(inner_labels)));
        writeStataValueLabel(aname, inner_labels, 0, namelength, fp);
    }

    //for(i = 0;i < nvar; i++){
//	theselabels = VECTOR_ELT(leveltable, i);
//	if (!isNull(theselabels)){
            //If we remember what the value label was called, use that. Otherwise use the var name
//	    if(!isNull(curr_val_labels) && isString(curr_val_labels) && LENGTH(curr_val_labels) > i)
//	    	strncpy(aname, CHAR(STRING_ELT(curr_val_labels, i)), namelength);
//	    else
//		strncpy(aname, CHAR(STRING_ELT(names, i)), namelength);

//	    writeStataValueLabel(aname, theselabels, R_NilValue, namelength, fp);
//	}
  //  }

/***
    PROTECT(label_table = getAttrib(df, install("label.table")));
    if(TYPEOF(label_table) == VECSXP){
	PROTECT(names_lt = getAttrib(label_table, R_NamesSymbol));
	if(!isNull(names_lt) && LENGTH(label_table) == LENGTH(names_lt)){
		for(i=0; i<LENGTH(label_table); i++){
			thisnamechar = CHAR(STRING_ELT(names_lt, i));
			//check it this label was noted in val.labels because it would've been already written
			if(!isNull(curr_val_labels) && isString(curr_val_labels)){
				for(j=0; j<LENGTH(curr_val_labels); j++){
					if(strncmp(thisnamechar, CHAR(STRING_ELT(curr_val_labels, j)), namelength) == 0)
						break;
				}
				if(j<LENGTH(curr_val_labels)) continue;
			}
			//check to 
			theselabelslevels = VECTOR_ELT(label_table, i);
			if(!isNull(theselabelslevels)){
				PROTECT(theselabels = getAttrib(theselabelslevels, R_NamesSymbol));
				writeStataValueLabel(thisnamechar, theselabels, theselabelslevels, namelength, fp);
				UNPROTECT(1); ***/
                                /* theselabel */
/***			}
		}
	}
	UNPROTECT(1); ***/  /* names_lt */
/***    }

    UNPROTECT(4); ***/ /* names,types,curr_val_labels, label_table */


    for (i=0; i < nvar; i++)
    {
        efree(types[i]);
	efree(wrTypes[i]);
    }
    efree(types);
    efree(wrTypes);



}


void do_writeStata(char *fileName, zval *data, zval *variables, zval *labels)
{
    FILE *fp;

    if ((sizeof(double) != 8) | (sizeof(int) != 4) | (sizeof(float) != 4))
      error("cannot yet read write .dta on this platform");

    fp = fopen(fileName, "wb");
    if (!fp) 
         error("unable to open file for writing: '%s'", strerror(errno));
    R_SaveStataData(fp, data, variables, labels);
    fclose(fp);

}
