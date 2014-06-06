<?php

$afile = str_replace( "\n", "\n<br>",
	str_replace('  ', ' &nbsp;',
        file_get_contents('debug/main.txt')
	) 
);

header('Content-type: text/html; charset="utf-8"',true);

?><!doctype html>
<html class="no-js" lang="">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Tilla Debuger</title>
    <link rel="stylesheet" href="/debug/style.css" />
</head>
<body class="fonts">

<div id="point_content"><div id="point_content2"></div></div>

<div id="main_content">
	<div id="main_content2">
		<?php echo $afile?>
	</div>
</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="/debug/page.js"></script>

</body>
</html>