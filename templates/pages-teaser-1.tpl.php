<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN"
  "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" version="XHTML+RDFa 1.0">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
@HEAD@
<title>@TITLE@</title>
@STYLES@
@SCRIPTS@
</head>
<body>
<h1>@TITLE@</h1>
@HEADER@
<div class="container">
<ul>
{%LOOP item%}
<li>
  <h2><a href="/@ITEM.URL@">@ITEM.TITLE@</a></h2>
  [%CHECK ITEM.IMAGE%]
  <img src="/@ITEM.IMAGE@" width="300" height="200" />
  [%ENDCHECK ITEM.IMAGE%]
  <div class="text">@ITEM.BODY@</div>
</li>
{%ENDLOOP%}
</ul>
</div>
@FOOTER@
</body>
</html>
