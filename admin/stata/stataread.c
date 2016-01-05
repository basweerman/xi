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

// make sure to chcon the file "chcon -t httpd_sys_content_t someFile" and issue plenty of memory


#include "stataread.h"
#include "php.h"
static int stata_endian;


/** Low-level input **/

static int InIntegerBinary(FILE * fp, int naok, int swapends)
{
    int i;
    if (fread(&i, sizeof(int), 1, fp) != 1)
        error("a binary read error occurred");
    if (swapends)
        reverse_int(i);
    return ((i == STATA_INT_NA) & !naok ? NA_INTEGER : i);
}
/* read a 1-byte signed integer */
static int InByteBinary(FILE * fp, int naok)
{
    signed char i;
    if (fread(&i, sizeof(char), 1, fp) != 1)
        error("a binary read error occurred");
    return  ((i == STATA_BYTE_NA) & !naok ? NA_INTEGER : (int) i);
}
/* read a single byte  */
static int RawByteBinary(FILE * fp, int naok)
{
    unsigned char i;
    if (fread(&i, sizeof(char), 1, fp) != 1)
        error("a binary read error occurred");
    return  ((i == STATA_BYTE_NA) & !naok ? NA_INTEGER : (int) i);
}

static int InShortIntBinary(FILE * fp, int naok,int swapends)
{
        unsigned first,second;
        int result;

  first = RawByteBinary(fp,1);
  second = RawByteBinary(fp,1);
  if (stata_endian == CN_TYPE_BIG){
    result= (first<<8) | second;
  } else {
    result= (second<<8) | first;
  }
  if (result > STATA_SHORTINT_NA) result -= 65536;
  return ((result == STATA_SHORTINT_NA) & !naok ? NA_INTEGER  : result);
}


static double InDoubleBinary(FILE * fp, int naok, int swapends)
{
    double i;
    if (fread(&i, sizeof(double), 1, fp) != 1)
        error("a binary read error occurred");
    if (swapends)
        reverse_double(i);
    return ((i == STATA_DOUBLE_NA) & !naok ? NA_REAL : i);
}

static double InFloatBinary(FILE * fp, int naok, int swapends)
{
    float i;
    if (fread(&i, sizeof(float), 1, fp) != 1)
        error("a binary read error occurred");
    if (swapends)
        reverse_float(i);
    return ((i == STATA_FLOAT_NA) & !naok ? NA_REAL :  (double) i);
}

static void InStringBinary(FILE * fp, int nchar, char* buffer)
{
    if (fread(buffer, nchar, 1, fp) != 1)
        error("a binary read error occurred");
}

static char* nameMangle(char *stataname, int len){
  return stataname;
}



struct StataDataFile * R_LoadStataData(FILE *fp)
{
    int i, j = 0, nvar, nobs, charlen, version, swapends, 
	varnamelength, nlabels, totlen, res, vlabelCounter = 0;
    unsigned char abyte;
    /* timestamp is used for timestamp and for variable formats */
    char datalabel[81], timestamp[50], aname[33];
    char stringbuffer[245], *txt;
    int *off;
    int fmtlist_len = 12;


    /** first read the header **/

    abyte = (unsigned char) RawByteBinary(fp, 1);   /* release version */
    version = 0;		/* -Wall */
    varnamelength = 0;		/* -Wall */
    switch (abyte) {
    case VERSION_5:
	version = 5;
	varnamelength = 8;
	break;
    case VERSION_6:
	version = 6;
	varnamelength = 8;
	break;
    case VERSION_7:
	version = 7;
	varnamelength = 32;
	break;
    case VERSION_7SE:
	version = -7;
	varnamelength = 32;
	break;
    case VERSION_8:
	version = -8;  /* version 8 automatically uses SE format */
	varnamelength = 32;
	break;
    case VERSION_114:
	version = -10;
	varnamelength = 32;
	fmtlist_len = 49;
    case VERSION_115:
	/* Stata say the formats are identical,
	   but _115 allows business dates */
	version = -12;
	varnamelength = 32;
	fmtlist_len = 49;
	break;
    default:
	error("not a Stata version 5-12 .dta file");
    }
    stata_endian = (int) RawByteBinary(fp, 1);     /* byte ordering */
    swapends = stata_endian != CN_TYPE_NATIVE;

    struct StataDataFile * df = ecalloc(1, sizeof(struct StataDataFile));

    RawByteBinary(fp, 1);            /* filetype -- junk */
    RawByteBinary(fp, 1);            /* padding */
    nvar = (InShortIntBinary(fp, 1, swapends)); /* number of variables */
    nobs = (InIntegerBinary(fp, 1, swapends));  /* number of cases */

    df->nvar = nvar;
    df->nobs = nobs;
    /* data label - zero terminated string */
    switch (abs(version)) {
    case 5:
	InStringBinary(fp, 32, datalabel);
	break;
    case 6:
    case 7:
    case 8:
    case 10:
    case 12:
	InStringBinary(fp, 81, datalabel);
	break;
    }
    /* file creation time - zero terminated string */
    InStringBinary(fp, 18, timestamp);

    /** and now stick the labels on it **/
    df->version = abs(version);
    df->datalabel = (char *)estrdup(datalabel);
    df->timestamp = (char *)estrdup(timestamp);
    /** read variable descriptors **/

    /** types **/

    struct StataVariable * stvcurr = NULL;
 
    if (version > 0){
	for(i = 0; i < nvar; i++){
            struct StataVariable * stv = ecalloc(1, sizeof(struct StataVariable));

	    if (stv == NULL)
		fprintf(stderr, "Out of memory\n\r");
 
            if (df->variables == NULL)
            {
                stvcurr = stv;
                df->variables = stv;

            }
            else
            {
		stvcurr->next = stv;
                stvcurr = stv;
            } 

	    abyte = (unsigned char) RawByteBinary(fp, 1);
	    stvcurr->valueType = abyte;
	    switch (abyte) {
	    case STATA_FLOAT:
	    case STATA_DOUBLE:
		break;
	    case STATA_INT:
	    case STATA_SHORTINT:
	    case STATA_BYTE:
		break;
	    default:
		if (abyte < STATA_STRINGOFFSET)
		    error("unknown data type");
		break;
	    }
	}
    } else {

	for(i = 0; i < nvar; i++){
            struct StataVariable * stv = ecalloc(1, sizeof(struct StataVariable));
	    if (stv == NULL)
		fprintf(stderr, "Out of memory\n\r");

            if (df->variables == NULL)
            {
                stvcurr = stv;
                df->variables = stv;
            }
            else
            {
                stvcurr->next = stv;
                stvcurr = stv;
            }

	    abyte = (unsigned char) RawByteBinary(fp, 1);
            stvcurr->valueType = abyte;
	    switch (abyte) {
	    case STATA_SE_FLOAT:
	    case STATA_SE_DOUBLE:
		break;
	    case STATA_SE_INT:
	    case STATA_SE_SHORTINT:
	    case STATA_SE_BYTE:
		break;
	    default:
		if (abyte > 244)
		    error("unknown data type");
		break;
	    }
	}
    }

    /** names **/
    struct StataVariable * stv = NULL;

    for (i = 0, stv=df->variables; i < nvar, stv; i++, stv = stv->next) {
	InStringBinary(fp, varnamelength+1, aname);
        stv->name = estrdup(aname);  
    }

    /** sortlist -- not relevant **/

    for (i = 0; i < 2*(nvar+1); i++) RawByteBinary(fp, 1);

    /** format list
	passed back to R as attributes.
	Used to identify date variables.
    **/

    for (i = 0, stv=df->variables; i < nvar; i++, stv=stv->next) {	
	InStringBinary(fp, fmtlist_len, timestamp);
        stv->vfmt = estrdup(timestamp);
    }


    /** value labels.  These are stored as the names of label formats,
	which are themselves stored later in the file. **/

    for(i = 0, stv=df->variables; i < nvar; i++, stv=stv->next) {
	InStringBinary(fp, varnamelength+1, aname);
        stv->vlabels = estrdup(aname);
    }

    /** Variable Labels **/

    switch(abs(version)){
    case 5:
	for(i = 0, stv=df->variables; i < nvar; i++, stv=stv->next) {
	    InStringBinary(fp, 32, datalabel);
            stv->dlabels = estrdup(datalabel);
	}
	break;
    case 6:
    case 7:
    case 8:
    case 10:
    case 12:
        for(i = 0, stv=df->variables; i < nvar; i++, stv=stv->next) {
	    InStringBinary(fp, 81, datalabel);
            stv->dlabels = estrdup(datalabel);

	}
    }

    /* Expansion Fields. These include
       variable/dataset 'characteristics' (-char-)
       variable/dataset 'notes' (-notes-)
       variable/dataset/values non-current language labels (-label language-)
    */

    j = 0;
    while(RawByteBinary(fp, 1)) {
	if (abs(version) >= 7) /* manual is wrong here */
	    charlen = (InIntegerBinary(fp, 1, swapends));
	else
	    charlen = (InShortIntBinary(fp, 1, swapends));
	
	if((charlen > 66)) {
	    InStringBinary(fp, 33, datalabel);
	    InStringBinary(fp, 33, datalabel);
	    txt = ecalloc(1, (size_t) (charlen-66));
	    InStringBinary(fp, (charlen-66), txt);
	    efree(txt);
	    j++;
	} else
	    for (i = 0; i < charlen; i++) InByteBinary(fp, 1);
    }
    if(j > 0)
	;	

    if (abs(version) >= 7)
	charlen = (InIntegerBinary(fp, 1, swapends));
    else
	charlen = (InShortIntBinary(fp, 1, swapends));
    if (charlen != 0)
	error("something strange in the file\n (Type 0 characteristic of nonzero length)");

    struct StataObservation * obsp = NULL;
    struct StataObservation *obspcurr = NULL;
    struct StataObservationData * dptr = NULL;
    /** The Data **/
    if (version > 0) { /* not Stata/SE */
	for(i = 0; i < nobs; i++){
            if (df->observations == NULL)
            {
		df->observations = ecalloc(1, sizeof(struct StataObservation));
		if (df->observations == NULL)
			fprintf(stderr, "Out of memory\n\r");
		obspcurr = df->observations;
		obspcurr->n = i;
            }
	    else
	    {
		obspcurr->next = ecalloc(1, sizeof(struct StataObservation));
		if (obspcurr->next == NULL)
			fprintf(stderr, "Out of memory\n\r");
		obspcurr = obspcurr->next;
		obspcurr->n = i;
            }
		
	    for(j = 0, stv = df->variables; j < nvar, stv; j++, stv = stv->next){
	
		if (obspcurr->data == NULL)
                {
			obspcurr->data = ecalloc(1, sizeof(struct StataObservationData));
			dptr = obspcurr->data;
                }
		else
		{
			dptr->next = ecalloc(1, sizeof(struct StataObservationData));
			dptr = dptr->next;
		}	
		switch (stv->valueType) {
		case STATA_FLOAT:
		    dptr->value.d = InFloatBinary(fp, 0, swapends); 
		    break;
		case STATA_DOUBLE:
		    dptr->value.d = InDoubleBinary(fp, 0, swapends);
		    break;
		case STATA_INT:
		    dptr->value.i = InIntegerBinary(fp, 0, swapends);		   
		    break;
		case STATA_SHORTINT:
		    dptr->value.i = InShortIntBinary(fp, 0, swapends);
		    //if (dptr->value.i == 32741)
			//dptr->value.i = 2147483621;
		    break;
		case STATA_BYTE:
		    dptr->value.i = (int) InByteBinary(fp, 0);
		    break;
		default:
		    charlen = stv->valueType - STATA_STRINGOFFSET;
		    if(charlen > 244) {
			printf("invalid character string length -- truncating to 244 bytes");
			charlen = 244;
		    }
		    InStringBinary(fp, charlen, stringbuffer);
		    stringbuffer[charlen] = 0;
		    strncpy(dptr->value.string, stringbuffer, charlen);
		    break;
		}

	
	    }
	}
    }  else {
	for(i = 0; i < nobs; i++){
            if (df->observations == NULL)
            {
                df->observations = ecalloc(1, sizeof(struct StataObservation));
                obspcurr = df->observations;
                obspcurr->n = i;
            }
            else
            {
                obspcurr->next = ecalloc(1, sizeof(struct StataObservation));
                obspcurr = obspcurr->next;
                obspcurr->n = i;
            }

            for(j = 0, stv = df->variables; j < nvar, stv; j++, stv = stv->next){

                if (obspcurr->data == NULL)
                {
                        obspcurr->data = ecalloc(1, sizeof(struct StataObservationData));
                        dptr = obspcurr->data;
                }
                else
                {
                        dptr->next = ecalloc(1, sizeof(struct StataObservationData));
                        dptr = dptr->next;
                }

		switch (stv->valueType) {
		case STATA_SE_FLOAT:
		    dptr->value.d = InFloatBinary(fp, 0, swapends);
		    break;
		case STATA_SE_DOUBLE:
		    dptr->value.d = InDoubleBinary(fp, 0, swapends);
		    break;
		case STATA_SE_INT:
		     dptr->value.i = InIntegerBinary(fp, 0, swapends);
		    break;
		case STATA_SE_SHORTINT:
		     dptr->value.i = InShortIntBinary(fp, 0, swapends);
		     //if (dptr->value.i == 32741)
                      //  dptr->value.i = 2147483621;

		    break;
		case STATA_SE_BYTE:
		     dptr->value.i = (int) InByteBinary(fp, 0);
		    break;
		default:
		    charlen = stv->valueType-STATA_SE_STRINGOFFSET;
		    if(charlen > 244) {
			printf("invalid character string length -- truncating to 244 bytes");
			charlen = 244;
		    }
		    InStringBinary(fp, charlen, stringbuffer);
		    stringbuffer[charlen] = 0;
	            strncpy(dptr->value.string, stringbuffer, charlen);
		    break;
		}
	    }


            
	}
    }


    /** value labels **/
    if (abs(version) > 5) {
 
	struct StataLabel * lblcurr = NULL;

	for(j = 0; ; j++) {
	    /* first int not needed, use fread directly to trigger EOF */
	    res = (int) fread((int *) aname, sizeof(int), 1, fp);
	    if (feof(fp)) break;
	    if (res != 1) printf("a binary read error occurred");

	    InStringBinary(fp, varnamelength+1, aname);
	    RawByteBinary(fp, 1); RawByteBinary(fp, 1); RawByteBinary(fp, 1); /*padding*/
	    nlabels = InIntegerBinary(fp, 1, swapends);

	     
	    
	    totlen = InIntegerBinary(fp, 1, swapends);
	    off =  ecalloc(sizeof(int), (size_t) nlabels);
	    for(i = 0; i < nlabels; i++)
		off[i] = InIntegerBinary(fp, 1, swapends);
            
	    int * levels = ecalloc(sizeof(int), (size_t)nlabels);
	    for(i = 0; i < nlabels; i++)
	        levels[i] =  InIntegerBinary(fp, 0, swapends);
	    txt =  ecalloc(sizeof(char), (size_t) totlen);
	    InStringBinary(fp, totlen, txt);


            for(i = 0; i < nlabels; i++)
            {
	    			if (df->labels == NULL)
				{
					df->labels = ecalloc(1, sizeof(struct StataLabel));
					lblcurr = df->labels;
					lblcurr->name = estrdup(aname);
					lblcurr->value=levels[i];
					lblcurr->string = estrdup(txt+off[i]);


				}
				else
				{
					lblcurr->next = ecalloc(1,  sizeof(struct StataLabel));
					lblcurr = lblcurr->next;
					lblcurr->name = estrdup(aname);
					lblcurr->value=levels[i];
					lblcurr->string = estrdup(txt+off[i]);
						
				}
	
            }



	    efree(off);
	    efree(txt);
	    efree(levels);
	}
    }

    df->nlabels = j;
    /** tidy up **/

    /*PROTECT(row_names = allocVector(STRSXP, nobs));
    for (i = 0; i < nobs; i++) {
	sprintf(datalabel, "%d", i+1);
	SET_STRING_ELT(row_names,i,mkChar(datalabel));
    }
    setAttrib(df, R_RowNamesSymbol, row_names);
    */

    //INTEGER(sversion)[0] = (version == -7)? version : abs(version);

    return df;
}


int do_stataClose(struct StataDataFile * dta)
{
  if (dta != NULL)
  {
   struct StataVariable * stv = NULL;
   struct StataVariable * tmpVar = NULL;
   struct StataLabel * tmpLab = NULL;
   struct StataLabel * stl;
   struct StataObservation * sto = NULL;
   struct StataObservation * tmpSto = NULL;
   struct StataObservationData * std = NULL;
   struct StataObservationData * tmpStd = NULL;


   for (sto = dta->observations; sto; sto = tmpSto)
   {

	for (std = sto->data; std; std = tmpStd)
	{
		tmpStd = std->next;
		efree(std);
	}	

	tmpSto = sto->next;
	efree(sto);
   }


   for (stl = dta->labels; stl; stl = tmpLab)
   {
		efree(stl->name);
                efree(stl->string);
                tmpLab = stl->next;
                efree(stl);
   }

  
  


   for(stv = dta->variables; stv; stv = tmpVar)
   {
        efree(stv->name);
        efree(stv->vfmt);
        efree(stv->vlabels);
        efree(stv->dlabels);
        tmpVar = stv->next;
        efree(stv);

   }

   efree(dta->datalabel);
   efree(dta->timestamp);
   efree(dta);

   return 1;

  }
   
  return 0;
}

struct StataDataFile * do_readStata(char * fileName)
{
    int result;
    FILE *fp;
    struct StataDataFile * df = NULL;

    if ((sizeof(double)!=8) | (sizeof(int)!=4) | (sizeof(float)!=4))
    {
      fprintf(stderr, "can not yet read Stata .dta on this platform");
      return NULL;
    }

    fp = fopen(fileName, "rb");
    if (!fp)
    {
	fprintf(stderr, "unable to open file: '%s'", strerror(errno));
	return NULL;
    }
    df = R_LoadStataData(fp);
    fprintf(stderr, "Observations: %d\n\r",  df->nobs);
    fclose(fp);
    return df;
}


