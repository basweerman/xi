PHP_ARG_ENABLE(stata, whether to enable my extension,
[ --enable-stata   Enable my extension])
 
if test "$PHP_STATA" = "yes"; then
  AC_DEFINE(HAVE_STATA, 1, [Whether you have stata])
  PHP_NEW_EXTENSION(stata, stata.c stataread.c statawrite.c, $ext_shared)
fi
