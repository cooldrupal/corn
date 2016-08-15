<!DOCTYPE html>
<!--[if IE 7 ]>    <html lang="ru" class="no-js ie7 lte-ie9"> <![endif]-->
<!--[if IE 8 ]>    <html lang="ru" class="no-js ie8 lte-ie9"> <![endif]-->
<!--[if IE 9 ]>    <html lang="ru" class="no-js ie9 lte-ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html xmlns="http://www.w3.org/1999/xhtml"> <!--<![endif]-->

  <head>

    <!-- Basic Page Needs -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <title>@TITLE@</title>

    <link rel="stylesheet" href="./css/style.css">


    <!--[if lt IE 9]> 
      <script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script>
    <![endif]-->
    
  </head>
  <body>
  {%LOOP item%}
    @ITEM.BODY@
  {%ENDLOOP%}

     <link rel="stylesheet" href="./css/flexslider.css">
    
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
    <script type="text/javascript" src="./js/libs/jquery.flexslider-min.js"></script>
    <script type="text/javascript" src="./js/common.js"></script>

    <!--[if (gte IE 6)&(lte IE 8)]>
      <script type="text/javascript" src="./js/libs/selectivizr.js"></script>
      <script type="text/javascript" src="./js/libs/rem-master/js/rem.min.js"></script>
    <![endif]--> 

    <!--[if lt IE 10]>
      <script type="text/javascript" src="js/libs/PIE.js"></script>
    <![endif]-->

  </body>
</html>
