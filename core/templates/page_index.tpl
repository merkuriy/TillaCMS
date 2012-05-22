<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>%title% - TillaCMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Le styles -->
		<style type="text/css" media="screen, projection">
			/*<![CDATA[*/
			/*@import "../css_js/panel/default.css";*/
			/*@import "../css_js/panel/style.css";*/
			@import "../css_js/panel/jquery.tree.css";
			@import "../css_js/panel/jquery.contextMenu.css";
			
			@import "../css_js/qui/qui.css";
			@import "../css_js/qui/qui-tree.css";
			/*]]>*/
		</style>
		<link rel="stylesheet" href="/css_js/plupload/plupload.queue.css" type="text/css" media="screen" />
	
    <link href="/core/templates/css/bootstrap.css" rel="stylesheet">
    <link href="/core/templates/css/bootstrap-responsive.css" rel="stylesheet">
    <link href="/core/templates/css/admin.css" rel="stylesheet">

		<script type="text/javascript" src="/core/templates/js/jquery.js"></script>
		<script type="text/javascript" src="/core/templates/js/bootstrap.js"></script>
	
		%js%

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="images/favicon.ico">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="images/apple-touch-icon-114x114.png">
  </head>
  <body>
		<div id="CMS_notify"></div>
		<div><ul id="myMenu" class="contextMenu"></ul></div>

    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="#"><img src="/core/templates/img/logo.png" alt="TillaCMS" /></a>
          <div class="nav-collapse">
            <ul class="nav">
            	%link%
            </ul>
            <ul class="nav pull-right">
              <li><a href="/">На сайт</a></li>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-user icon-white"></i> %userName% <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="/?action=deauth">Выход</a></li>
                </ul>
              </li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container" id="DIVcontentAll">
      <div class="row">
				%content%
			</div>
		</div>
	</body>
</html>