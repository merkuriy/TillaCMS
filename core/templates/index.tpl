<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>TillaCMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Le styles -->  
    <link href="/core/templates/css/bootstrap.css" rel="stylesheet">
    <link href="/core/templates/css/bootstrap-responsive.css" rel="stylesheet">
    <link href="/core/templates/css/tilla.css" rel="stylesheet">
    <link type="text/css" href="/core/templates/css/ui-lightness/jquery-ui-1.8.16.custom.css" rel="stylesheet" />
    <link type="text/css" href="/core/templates/css/jquery-ui-timepicker-addon.css" rel="stylesheet" />
    <link type="text/css" href="/core/templates/js/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css" rel="stylesheet" />
    <link rel="stylesheet" href="/core/templates/js/redactor/redactor.css" />

    <link rel="stylesheet" href="/core/templates/tilla-ui/context-menu.css">

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
    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container" id="top-bar">
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
                  <li><a href="/?action=deauth" data-no-ajax="true">Выход</a></li>
                </ul>
              </li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container" id="content">
      <div class="row">
        <div class="span12">
          <legend>
            Добро пожаловать!
          </legend>
        </div>
      </div>
    </div>

    <div id="alert-block"></div>

    <div class="modal" id="confirm-modal">
      <div class="modal-header">
        <button class="close" data-dismiss="modal">×</button>
        <h3></h3>
      </div>
      <div class="modal-body">
        <p></p>
      </div>
      <div class="modal-footer">
        <a href="#" class="btn" id="confirm-false-btn"></a>
        <a href="#" class="btn btn-primary" id="confirm-true-btn"></a>
      </div>
    </div>

    <script type="text/javascript" src="/core/templates/js/jquery.js"></script>
    <script type="text/javascript" src="/core/templates/js/jquery.tmpl.min.js"></script>
    <script type="text/javascript" src="/core/templates/js/jquery.history.js"></script>
    <script type="text/javascript" src="/core/templates/js/jquery.form.js"></script>
    <script type="text/javascript" src="/core/templates/js/jquery-ui-1.8.20.custom.min.js"></script>
    <script type="text/javascript" src="/core/templates/js/jquery-ui-timepicker-addon.js"></script>

    <script type="text/javascript" src="/core/templates/tilla-ui/vendor/jquery.contextMenu.js"></script>
    <script type="text/javascript" src="/core/templates/tilla-ui/vendor/jquery.timers.js"></script>
    <script type="text/javascript" src="/core/templates/tilla-ui/jquery.tilla-ui.tree.js"></script>

    <script type="text/javascript" src="/core/templates/js/redactor/ru.js"></script>
    <script type="text/javascript" src="/core/templates/js/redactor/fullscreen.js"></script>
    <script type="text/javascript" src="/core/templates/js/redactor/redactor.js"></script>
    <!-- Third party script for BrowserPlus runtime (Google Gears included in Gears runtime now) -->
    <script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>
    <!-- Load plupload and all it's runtimes and finally the jQuery queue widget -->
    <script type="text/javascript" src="/core/templates/js/plupload/plupload.full.js"></script>
    <script type="text/javascript" src="/core/templates/js/plupload/jquery.plupload.queue/jquery.plupload.queue.js"></script>
    <script type="text/javascript" src="/core/templates/js/tiny_mce/jquery.tinymce.js"></script>
    <script type="text/javascript" src="/core/templates/js/bootstrap.js"></script>
    <script type="text/javascript" src="/core/templates/js/model_tree.js"></script>
    <script type="text/javascript" src="/core/templates/js/tilla.js"></script>

  </body>
</html>