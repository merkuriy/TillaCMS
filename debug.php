<?php


$afile = file('debug/main.txt');

$afile = implode($afile);



$bufer = str_replace( "\n", "\n<br>", 
	str_replace( "  ", " &nbsp;",
		$afile
	) 
);


header('Content-type: text/html; charset="utf-8"',true);


echo '
<!DOCTYPE html>
<html>

<head>
<meta http-equiv="Content-Type"
	content="text/html; charset=utf-8" />
<meta name="keywords"
	content="XHTML,2001,31 мая,C-HTML,CSS,Common Gateway Interface,DHTML,DOM,DTD,Document Object Model,Extensible Stylesheet Language" />



<title>Главная - сеть аптек &laquo;Медуника&raquo;</title>


<link type="text/css" rel="stylesheet" media="all" href="/debug/style.css" />


<script type="text/javascript" src="/css_js/jquery/jquery.js"></script>

<script type="text/javascript" src="/debug/page.js"></script>

<script type="text/javascript"> 
    $(document).ready(function(){
    	main();
    }); 
</script>

</head>

<body class="fonts">

<div id="point_content"><div id="point_content2"></div></div>

<div id="main_content">
	<div id="main_content2">
		'.$afile.'
	</div>
</div>

</body>
</html>
		';



?>