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
@LINK@
<h1>@TITLE@</h1>
{%EXIST image%}
<img src="/@IMAGE@" width="300" height="200" />
{%ENDEXIST%}
@BODY@
</body>
</html>
